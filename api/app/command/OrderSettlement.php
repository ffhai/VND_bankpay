<?php
declare(strict_types=1);
namespace app\command;

use app\common\model\BalanceLogModel;
use app\common\model\OrderModel;
use app\common\model\UserModel;
use app\common\service\BalanceService;
use app\common\utils\RateUtils;
use think\facade\Db;
use think\console\Command;
use think\console\Input;
use think\console\Output;

class OrderSettlement extends Command
{
    protected function configure()
    {
        $this->setName('OrderSettlement')->setDescription('汇总截至当前时间的成功订单并按账号费率结算');
    }

    protected function execute(Input $input, Output $output)
    {
        $cutoff = time();
        $output->writeln('Start...');
        $count = Db::transaction(function () use ($cutoff) {
            // 固定截止时间；锁定本批订单，余额、流水和结算标记同一事务提交。
            $orders = OrderModel::where('status', OrderModel::STATUS_PAYED)
                ->where('pay_time', '<=', $cutoff)
                ->where('settlement_time', 0)
                ->order('id')->lock(true)->select();
            $totals = [];
            foreach ($orders as $order) {
                foreach (['trade_uid' => 'merchant', 'channel_uid' => 'channel'] as $field => $kind) {
                    $uid = (int) $order[$field];
                    if ($uid <= 0) {
                        throw new \RuntimeException('订单缺少结算账号：' . $order['order_id']);
                    }
                    $totals[$uid][$kind] = ($totals[$uid][$kind] ?? 0) + (int) $order['amount'];
                    if (!is_int($totals[$uid][$kind])) {
                        throw new \RuntimeException('结算合计金额超出整数范围');
                    }
                }
            }
            ksort($totals, SORT_NUMERIC);
            foreach ($totals as $uid => $amounts) {
                $user = UserModel::where('id', $uid)->lock(true)->find();
                if (!$user) {
                    throw new \RuntimeException('结算账号不存在：' . $uid);
                }
                foreach ($amounts as $kind => $total) {
                    $fee = RateUtils::fee($total, $user['rate']);
                    // 商户原有本金入账逻辑保留；手续费、码商佣金分别记账。
                    $remark = '截止' . date('Y-m-d H:i:s', $cutoff) . '，合计' . $total . '，费率' . $user['rate'] . '%';
                    if ($kind === 'merchant') {
                        BalanceService::change($uid, $total, BalanceLogModel::TYPE_ORDER_SETTLEMENT, $remark . '，本金', 'sys');
                    }
                    if ($fee > 0) {
                        BalanceService::change($uid, $kind === 'channel' ? $fee : -$fee,
                            BalanceLogModel::TYPE_ORDER_SETTLEMENT,
                            $remark . ($kind === 'channel' ? '，码商佣金' : '，商户手续费'), 'sys');
                    }
                }
            }
            foreach ($orders as $order) {
                OrderModel::where('id', $order['id'])->update(['settlement_time' => $cutoff]);
            }
            return count($orders);
        });
        $output->writeln('Settled: ' . $count);
        $output->writeln('End');
    }
}
