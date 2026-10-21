<?php
declare (strict_types = 1);

namespace app\command;

use app\common\model\OrderModel;
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
            try {
                $result = \app\common\service\OrderService::expireOrder(['order_id'=>$item['order_id']]);
                $output->writeln('Order ID:' . $item['order_id'] . ' ' . $result['result']);
            } catch (\app\common\exception\RequestException $e) {
                if (empty($e->data['manual_review'])) { throw $e; }
                \think\facade\Log::warning('超时退款需核查：' . $item['order_id'] . ' ' . $e->getMessage());
                $output->writeln('Order ID:' . $item['order_id'] . ' manual_review');
            }
        }
        $output->writeln('End');

    }
}
