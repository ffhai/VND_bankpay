<?php

declare (strict_types = 1);

namespace app\common\service;


use app\common\basics\Service;
use app\common\enums\StatusEnum;
use app\common\exception\RequestException;
use app\common\model\AppListModel;
use app\common\model\BalanceLogModel;
use app\common\model\BankCardsModel;
use app\common\model\OrderModel;
use app\common\model\UserModel;
use app\openapi\validate\OrderValidate;
use app\task\service\TaskService;
use think\facade\Log;
use think\facade\Db;

class OrderService extends Service
{
    public static function confirmed(array $post, array $userItem): array
    {
        $orderId = $post['order_id'] ?? null;
        if (!is_string($orderId) || $orderId === '') {
            throw new RequestException('订单号不能为空');
        }

        return Db::transaction(function () use ($orderId, $userItem) {
            $order = OrderModel::where('order_id', $orderId)->lock(true)->find();
            if (!$order) {
                throw new RequestException('订单不存在');
            }
            $orderItem = $order->toArray();
            if (isset($userItem['id'])) {
                // 传入账号时仅允许确认归属于该码商的订单，不依据角色代码判断。
                if ((int) $userItem['id'] !== (int) $orderItem['channel_uid']) {
                    throw new RequestException('无权限操作当前订单');
                }
            }

            $reviewReason = null;
            if ((int) $orderItem['settlement_time'] > 0) {
                $reviewReason = 'already_settled';
                $message = '订单已结算，禁止再次确认，请人工核查';
            } elseif ((int) $orderItem['status'] === OrderModel::STATUS_FAIL) {
                $reviewReason = 'refunded_or_closed';
                $message = '订单已退款或关闭，禁止再次确认，请人工核查';
            } elseif (!in_array((int) $orderItem['status'], [OrderModel::STATUS_PAYING, OrderModel::STATUS_PAYED], true)) {
                $reviewReason = 'invalid_status';
                $message = '当前订单状态不允许确认，请人工核查';
            }
            if ($reviewReason !== null) {
                Log::warning('订单确认需人工核查：' . json_encode(['order_id'=>$orderId, 'reason'=>$reviewReason]));
                throw new RequestException($message, 0, [
                    'order_id'=>$orderId, 'manual_review'=>true, 'reason'=>$reviewReason,
                ]);
            }
            if ((int) $orderItem['status'] === OrderModel::STATUS_PAYED) {
                return [
                    'order_id'=>$orderId, 'result'=>'already_processed',
                    'status'=>OrderModel::STATUS_PAYED, 'pay_time'=>(int) $orderItem['pay_time'],
                ];
            }

            // Never use request fields as the update payload.
            $changes = ['status'=>OrderModel::STATUS_PAYED, 'pay_time'=>time()];
            OrderModel::where('id', $orderItem['id'])->update($changes);
            $orderItem = array_merge($orderItem, $changes);

            if (!empty($orderItem['notify_url'])) {
                $appItem = (new AppListModel())->getItemByUid($orderItem['trade_uid']);
                if ($appItem) {
                    $url = self::buildNotifyUrl($orderItem, $appItem);
                    TaskService::addTask(TaskService::KEY_NOTIFY, [
                        'data'=>['order_id'=>$orderId, 'url'=>$url],
                    ]);
                }
            }
            return [
                'order_id'=>$orderId, 'result'=>'confirmed',
                'status'=>OrderModel::STATUS_PAYED, 'pay_time'=>$changes['pay_time'],
            ];
        });
    }

    /**
     * @param array $post
     * @return bool|string
     */
    public static function  notify(array $post,array $userItem)
    {
        $model = new OrderModel();
        $orderItem = $model->where('order_id','=',$post['order_id'])->find();
        if(!$orderItem){
            return '订单不存在';
        }
        $orderItem = $orderItem->toArray();

        if (isset($userItem['id'])) {
            $ownerField = ($userItem['type'] ?? '') === 'channel' ? 'channel_uid' : 'trade_uid';
            if ($userItem['id'] != $orderItem[$ownerField]) {
                return '无权限操作当前订单';
            }
        }

        if(empty($orderItem['notify_url'])){
            return '该笔订单未填写回调地址';
        }
        $applist = new AppListModel();
        $appItem =  $applist->getItemByUid($orderItem['trade_uid']);
        if(!$appItem){
            return 'api不存在';
        }

        $url = self::buildNotifyUrl($orderItem, $appItem);
        $ret = curl_get($url);
        if(!$ret){
            return '请求失败:'.$url;
        }
        if(strpos($ret,'success') !== false || strpos($ret,'ok') !== false){
            OrderModel::update([
                'notify_status'=>StatusEnum::STATUS_2,
                'notify_time'=>time(),
            ],['id'=>$orderItem['id']]);
            return true;
        }else{
            return $ret;
        }
    }
    /**
     * 合并原回调参数，订单参数覆盖同名项，再按现有协议对全部参数签名。
     */
    private static function buildNotifyUrl(array $orderItem, array $appItem): string
    {
        $urlParts = explode('#', $orderItem['notify_url'], 2);
        $fragment = isset($urlParts[1]) ? '#' . $urlParts[1] : '';
        $queryParts = explode('?', $urlParts[0], 2);
        parse_str($queryParts[1] ?? '', $query);
        $params = array_replace($query, self::build_order_params($orderItem, $appItem));
        unset($params['sign']);
        $params['sign'] = AppSignService::order_sign($params, $appItem['secret']);
        return $queryParts[0] . '?' . http_build_query($params) . $fragment;
    }

    /*
     * 组装订单参数 用于对外
     */
    public static function build_order_params(array $orderItem,array $appItem):array
    {
        $params = [];
        $params['appid'] = $appItem['appid'];
        $params['order_id'] = $orderItem['order_id'];
        $params['trade_no'] = $orderItem['trade_no'];
        $params['pay_type'] = $orderItem['pay_type'];
        $params['bank_number'] = $orderItem['bank_number'];
        $params['amount'] = $orderItem['amount'].'';
        $params['ps'] = $orderItem['ps'];
        $params['status'] = $orderItem['status'].'';
        $params['pay_time'] = $orderItem['pay_time'].'';
        $params['time'] = time().'';
        return $params;
    }

    /*
     * 统一创建订单
     */
    public static function createOrder(array $params,array $appItem):array
    {
        $params['amount'] = \app\common\utils\AmountUtils::integer($params['amount'] ?? null, false, false);
        $orderModel = new OrderModel();

        $orderItem = [
            'order_id'=>create_order_id(),
            'trade_no'=>$params['trade_no'],
            'trade_uid'=>$appItem['uid'],
            'pay_type'=>$params['pay_type'],
            'bank_code'=>$params['bank_code'],
            'amount'=>$params['amount'],
            'notify_url'=>$params['notify_url'],
            'return_url'=>$params['return_url'] ?? '',
            'ps'=>self::generateRandomString(config('order.ps_length')),
        ];

        //检测订单号是否重复
        $res = $orderModel
            ->where('trade_uid','=',$orderItem['trade_uid'])
            ->where('trade_no','=',$orderItem['trade_no'])
            ->field('id')
            ->cache(true)
            ->find();
        if($res){
            throw new RequestException('请重新下单[商户订单号（trade_no）已存在]');
        }

        $payTypeItem = PayTypeService::getItemByCode($orderItem['pay_type']);
        if(!$payTypeItem || $payTypeItem['status'] != StatusEnum::STATUS_1){
            throw new RequestException('支付方式不存在或已停用');
        }

        if($orderItem['pay_type'] == 'bank2bank'){
            (new OrderValidate())->goCheck('createOrderBank2Bank',$orderItem);
            $orderItem['amount'] = (int) $orderItem['amount'];

            $bankCardsModel = new BankCardsModel();


            $where=[];
            //账号正常
            $where[]=['user.status','=',StatusEnum::STATUS_1];
            //有足够余额可以扣除
            $where[]=['user.balance','>=',$orderItem['amount']];
            //银行户名正常开启
            $where[]=['bankuser.status','=',StatusEnum::STATUS_1];
            //银行状态正常开启
            $where[]=['bankinfo.status','=',StatusEnum::STATUS_1];
            //银行入款状态正常开启
            $where[]=['bankinfo.status_in','=',StatusEnum::STATUS_1];

            //指定银行
            if(!empty($orderItem['bank_code'])){
                $where[]=['bank_cards_model.bank_code','=',$orderItem['bank_code']];
            }
            //银行卡状态正常启用
            $where[]=['bank_cards_model.status','=',StatusEnum::STATUS_1];

            //取出可用卡
            $bankCardsItem = $bankCardsModel
                ->withJoin([
                    'user'=>['id','username','balance','status'],
                    'bankuser'=>['id','name','idcard','status'],
                    'bankinfo'=>['id','name','code','bin','short_name','swift_code','status','status_in','status_out'],
                ])
                ->where($where)
                /*->where(function ($query) use ($orderItem) {
                    $query->where('limit_in_min', 0)
                        ->whereOr('limit_in_min', '<=', $orderItem['amount']);
                })
                ->where(function ($query) use ($orderItem) {
                    $query->where('limit_in_max', 0)
                        ->whereOr('limit_in_max', '>=', $orderItem['amount']);
                })*/
                ->order('bank_cards_model.last_time')
                ->find();
            if(!$bankCardsItem){
                throw new RequestException('暂时无码可用');
            }
            $bankCardsItem = $bankCardsItem->toArray();

            //订单参数
            $orderItem['bank_number'] = $bankCardsItem['bank_number'];
            $orderItem['channel_uid'] = $bankCardsItem['uid'];


        }else{
            throw new RequestException('支付方式不存在');
        }


        $orderItem['create_time'] = time();
        $orderItem['expire_time'] = $orderItem['create_time'] + $payTypeItem['expire_time'];
        $orderItem['pay_url'] = url('pay/index',['oid'=>$orderItem['order_id']],'',true)->build();
        return Db::transaction(function () use ($orderModel, $orderItem, $bankCardsItem) {
            BankDataService::validateCard($bankCardsItem, (int) $bankCardsItem['uid'], (int) $bankCardsItem['id']);
            $card = BankCardsModel::where('id', $bankCardsItem['id'])->lock(true)->find();
            if ((int) $card['status'] !== StatusEnum::STATUS_1
                || $card['bank_number'] !== $bankCardsItem['bank_number']
                || $card['bank_code'] !== $bankCardsItem['bank_code']
                || (int) $card['bank_user_id'] !== (int) $bankCardsItem['bank_user_id']) {
                throw new RequestException('收款资料已变更，请重新下单');
            }
            $bank = \app\common\model\BanksModel::where('code', $card['bank_code'])->find();
            $holder = \app\common\model\BankUserModel::find($card['bank_user_id']);
            if ((int) $bank['status_in'] !== StatusEnum::STATUS_1) {
                throw new RequestException('当前银行已关闭入款');
            }
            $orderItem = array_merge($orderItem, [
                'bank_card_id'=>(int) $card['id'],
                'bank_user_id'=>(int) $holder['id'],
                'bank_number'=>$card['bank_number'],
                'bank_code'=>$bank['code'],
                'bank_bin'=>$bank['bin'],
                'bank_name'=>$bank['name'],
                'bank_short_name'=>$bank['short_name'],
                'account_name'=>$holder['name'],
                'snapshot_source'=>'created',
            ]);
            BalanceService::change(
                (int) $orderItem['channel_uid'],
                -(int) $orderItem['amount'],
                BalanceLogModel::TYPE_ORDER_CREATE,
                $orderItem['order_id'],
                'sys'
            );
            $orderModel->save($orderItem);
            return $orderItem;
        });
    }


    public static function expireOrder(array $params): array
    {
        if (!is_string($params['order_id'] ?? null) || $params['order_id'] === '') {
            throw new RequestException('订单号不能为空');
        }
        return Db::transaction(function () use ($params) {
            $order = OrderModel::where('order_id', (string) ($params['order_id'] ?? ''))->lock(true)->find();
            if (!$order) { throw new RequestException('订单不存在'); }
            if ((int) $order['settlement_time'] > 0 || (int) $order['status'] === OrderModel::STATUS_PAYED) {
                throw new RequestException('已支付或已结算订单禁止超时退款，请人工核查', 0, ['manual_review'=>true]);
            }
            if ((int) $order['status'] === OrderModel::STATUS_FAIL) {
                $refunded = BalanceLogModel::where('user_id', $order['channel_uid'])
                    ->where('type', BalanceLogModel::TYPE_ORDER_CANCEL)
                    ->where('remark', $order['order_id'])->where('change_amount', $order['amount'])->find();
                if (!$refunded) {
                    throw new RequestException('订单已关闭但未找到退款流水，请人工核查', 0,
                        ['manual_review'=>true, 'reason'=>'closed_without_refund_record']);
                }
                return ['order_id'=>$order['order_id'], 'result'=>'already_processed'];
            }
            if ((int) $order['status'] !== OrderModel::STATUS_PAYING || $order['pay_type'] !== \app\common\model\PayTypeModel::TYPE_BANK2BANK) {
                throw new RequestException('当前状态或支付方式不支持超时退款');
            }
            if ((int) $order['expire_time'] >= time() - 60*30) {
                return ['order_id'=>$order['order_id'], 'result'=>'not_expired'];
            }
            if (BalanceLogModel::where('user_id', $order['channel_uid'])
                ->where('type', BalanceLogModel::TYPE_ORDER_CANCEL)->where('remark', $order['order_id'])->find()) {
                throw new RequestException('订单已有退款流水，禁止重复退款，请人工核查', 0,
                    ['manual_review'=>true, 'reason'=>'refund_record_already_exists']);
            }
            BalanceService::change((int) $order['channel_uid'], (int) $order['amount'],
                BalanceLogModel::TYPE_ORDER_CANCEL, $order['order_id'], 'sys');
            OrderModel::where('id', $order['id'])->update(['status'=>OrderModel::STATUS_FAIL]);
            return ['order_id'=>$order['order_id'], 'result'=>'refunded'];
        });
    }

    public static function weightedRandom($items,$weight = 'weight') {
        // 计算总权重
        $totalWeight = array_sum(array_column($items, $weight));

        // 生成随机数 (1 ~ totalWeight)
        $rand = mt_rand(1, $totalWeight);

        // 遍历累加权重，找到对应元素
        $current = 0;
        foreach ($items as $item) {
            $current += $item[$weight];
            if ($rand <= $current) {
                return $item;
            }
        }

        // 兜底返回最后一个
        return end($items);
    }

    /**
     * 生成指定长度的随机大写字母和数字组合（不重复）
     *
     * @param int $length 生成字符串的长度，默认8位
     * @return string 返回生成的随机字符串
     */
    public static function generateRandomString(int $length = 8): string
    {
        // 定义可用字符集：大写字母 A-Z 和数字 0-9
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $charLength = strlen($characters);

        // 检查长度是否合法
        if ($length < 1) {
            return '';
        }

        // 如果请求的长度超过字符集总数（36），则无法保证不重复
        if ($length > $charLength) {
            return '';
        }

        // 将字符集打乱，然后截取前 $length 个字符，保证不重复
        $shuffled = str_shuffle($characters);
        return substr($shuffled, 0, $length);
    }
}
