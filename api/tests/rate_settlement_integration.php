<?php
require dirname(__DIR__).'/vendor/autoload.php';
$app = new think\App(dirname(__DIR__)); $app->initialize();
use think\facade\Db;
use app\common\model\OrderModel;
use app\common\model\UserModel;
// 同连接临时表隔离，关闭连接即删除，不结算真实订单。
$dbConfig = config('database');
$dbConfig['connections']['mysql']['prefix'] = 'rate_';
config($dbConfig, 'database');
foreach (['user', 'order', 'balance_log'] as $table) {
    Db::execute('CREATE TEMPORARY TABLE `rate_'.$table.'` LIKE `dt_'.$table.'`');
}
Db::name('user')->insertAll([
    ['id'=>1,'username'=>'rate_merchant','type'=>'merchant','balance'=>0,'rate'=>'1.0000'],
    ['id'=>2,'username'=>'rate_channel','type'=>'channel','balance'=>100,'rate'=>'1.0000'],
]);
$now = time();
foreach ([1,2,3,4,5,6,7,8] as $i) {
    Db::name('order')->insert(['order_id'=>'rate_test_'.$i,'trade_no'=>'rate_test_'.$i,
        'ps'=>'RATE'.$i,'trade_uid'=>1,'channel_uid'=>2,'amount'=>25,'status'=>$i===7 ? OrderModel::STATUS_PAYING : OrderModel::STATUS_PAYED,
        'pay_time'=>$i===8 ? $now+3600 : $now,'settlement_time'=>0]);
}
$command = new app\command\OrderSettlement();
$command->run(new think\console\Input([]), new think\console\Output());
if ((int) UserModel::find(1)['balance'] !== 148 || (int) UserModel::find(2)['balance'] !== 102) {
    throw new RuntimeException('Incorrect aggregate balances');
}
if (Db::name('order')->where('settlement_time','>',0)->count() !== 6) { throw new RuntimeException('Incorrect cutoff/status selection'); }
$logs = Db::name('balance_log')->count();
$command->run(new think\console\Input([]), new think\console\Output());
if (Db::name('balance_log')->count() !== $logs || (int) UserModel::find(1)['balance'] !== 148) {
    throw new RuntimeException('Duplicate settlement');
}
echo "PASS aggregate settlement, more than five orders, current cutoff, pending/future exclusion, ledger and idempotency\n";
