<?php
namespace app\task\service;

use app\common\basics\Service;
use app\common\enums\StatusEnum;
use app\common\exception\RequestException;
use app\common\model\BankCardsModel;
use app\common\model\DeviceNotifyModel;
use app\common\model\OrderModel;
use app\common\model\SmsTradeModel;
use app\common\service\OrderService;
use think\facade\Db;

class SmsTradeService extends Service
{
    public static function receive(array $params): array
    {
        $notifyId = $params['notify_id'];
        $balance = $params['balance'];

        return Db::transaction(function () use ($params, $notifyId, $balance): array {
            // 锁定同一推送，避免轮询和网络重试造成重复入库。
            $notify = DeviceNotifyModel::where('id', $notifyId)->lock(true)->find();
            if (!$notify) {
                throw new RequestException('设备推送不存在');
            }

            // 同一推送由上面的行锁串行处理，无需对不存在的交易加间隙锁。
            $trade = SmsTradeModel::where('notify_id', $notifyId)->find();
            if ($trade) {
                // 已提交的交易不重复匹配，也不覆盖银行卡的后续余额。
                if ((int) $notify['status'] !== StatusEnum::STATUS_2) {
                    $notify->save(['status' => StatusEnum::STATUS_2]);
                }
                return ['id' => (int) $trade['id'], 'notify_id' => $notifyId];
            }

            $bankCard = BankCardsModel::where('bank_number', $params['bank_number'])
                ->field('id,balance')->lock(true)->find();
            if (!$bankCard) {
                throw new RequestException('银行卡不存在');
            }
            $change = (int) $balance - (int) $bankCard['balance'];

            $order = null;
            if ($change > 0) {
                $query = OrderModel::where('settlement_time', 0)->where('status', OrderModel::STATUS_PAYING);
                if (mb_strlen($params['remark'], 'UTF-8') === (int) config('order.ps_length')) {
                    $query->where('ps', $params['remark']);
                } else {
                    $query->where('bank_number', $params['bank_number'])->where('amount', $change);
                }
                $order = $query->field('order_id,amount')->order('id', 'desc')->lock(true)->find();
            }

            $matched = $order && (int) $order['amount'] === $change;
            if ($matched) {
                OrderService::confirmed(['order_id' => $order['order_id']], []);
            }

            // InnoDB 下只写入一次最终结果，任一步骤异常均回滚本次数据库变更。
            $trade = SmsTradeModel::create([
                'notify_id' => $notifyId,
                'bank_number' => $params['bank_number'],
                'balance' => $balance,
                'change' => $change,
                'remark' => $params['remark'],
                'order_id' => $matched ? $order['order_id'] : '',
                'status' => $matched ? StatusEnum::STATUS_1 : StatusEnum::STATUS_2,
            ]);
            $bankCard->save(['balance' => (int) $balance, 'update_time' => time()]);
            $notify->save(['status' => StatusEnum::STATUS_2]);
            return ['id' => (int) $trade['id'], 'notify_id' => $notifyId];
        });
    }
}
