<?php
declare (strict_types = 1);

namespace app\command;

use app\common\model\BalanceLogModel;
use app\common\model\OrderModel;
use app\common\model\PayTypeModel;
use app\common\service\BalanceService;
use think\facade\Db;
use think\console\Command;
use think\console\Input;
use think\console\Output;

class OrderExpired extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('OrderExpired')
            ->setDescription('the OrderExpired command');
    }

    protected function execute(Input $input, Output $output)
    {
        // 指令输出
        $output->writeln('Start...');
        $orderModel = new OrderModel();
        $items = $orderModel
            ->where('status','=',OrderModel::STATUS_PAYING)
            ->where('expire_time','<',time() - 60*30)//最好是间隔一段时间
            ->order('id')
            ->limit(5)
            ->select();
        foreach ($items as $item) {
            Db::transaction(function () use ($item, $output) {
                $order = OrderModel::where('id', $item['id'])->lock(true)->find();
                if (!$order || (int) $order['status'] !== OrderModel::STATUS_PAYING || (int) $order['expire_time'] >= time() - 60*30) {
                    return;
                }
                if ($order['pay_type'] !== PayTypeModel::TYPE_BANK2BANK) {
                    return;
                }
                BalanceService::change(
                    (int) $order['channel_uid'],
                    (int) $order['amount'],
                    BalanceLogModel::TYPE_ORDER_CANCEL,
                    $order['order_id'],
                    'sys'
                );
                OrderModel::update(['status'=>OrderModel::STATUS_FAIL], ['id'=>$order['id']]);
                $output->writeln('Order ID:' . $order['order_id'] . ' Success !');
            });
        }
        $output->writeln('End');

    }
}
