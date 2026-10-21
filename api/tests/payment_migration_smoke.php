<?php
ob_start();
// Run: php tests/payment_migration_smoke.php (all fixtures are rolled back).
require dirname(__DIR__) . '/vendor/autoload.php';
$app = new \think\App(dirname(__DIR__));
$app->initialize();
use think\facade\Db;
use app\common\model\UserModel;
use app\common\model\OrderModel;
use app\common\model\BankUserModel;
use app\common\model\BankCardsModel;
use app\common\service\OrderService;
function check($ok, $message) {
    if (!$ok) { throw new \RuntimeException($message); }
    echo 'PASS ' . $message . "\n";
}
if (Db::name('order')->count() || Db::name('bank_cards')->count()) {
    throw new \RuntimeException('Run only against an empty local business database.');
}
Db::startTrans();
try {
    $suffix = bin2hex(random_bytes(5));
    $channel = UserModel::create(['type'=>'channel','username'=>'check_c_'.$suffix,'password'=>'','balance'=>1000000,'status'=>1]);
    $merchant = UserModel::create(['type'=>'merchant','username'=>'check_m_'.$suffix,'password'=>'','balance'=>0,'status'=>1]);
    $bankuser = BankUserModel::create(['uid'=>$channel['id'],'name'=>'CHECK USER','idcard'=>'check','status'=>1]);
    $card = BankCardsModel::create(['uid'=>$channel['id'],'bank_number'=>'999'.$suffix,'bank_code'=>'ICB','bank_user_id'=>$bankuser['id'],'status'=>1,'notify_type'=>'notify','balance'=>0]);
    $params = ['trade_no'=>'check_'.$suffix,'pay_type'=>'bank2bank','bank_code'=>'ICB','amount'=>100,'notify_url'=>'','return_url'=>''];
    $params['notify_url'] = 'https://example.invalid/callback';
    $order = OrderService::createOrder($params, ['uid'=>$merchant['id']]);
    check((int) UserModel::find($channel['id'])['balance'] === 999900, '100 order amount debits 100 balance units');
    $log=Db::name('balance_log')->where('user_id',$channel['id'])->order('id desc')->find();
    check((int)$log['change_amount'] === -100 && (int)$log['balance_after'] === 999900, 'integer ledger matches balance');
    check(Db::name('balance_log')->where('remark',$order['order_id'])->count() === 1, 'one ledger entry per order debit');
    $ledgerCount=Db::name('balance_log')->where('user_id',$channel['id'])->count();
    foreach ([[0,'CreateOrder'],[-10000000,'CreateOrder'],[100,'unknown_type']] as $invalid) {
        $rejected=false;
        try {
            \app\common\service\BalanceService::change((int)$channel['id'],$invalid[0],$invalid[1]);
        } catch (\app\common\exception\RequestException $e) {
            $rejected=true;
        }
        check($rejected,'framework rejects invalid balance change');
    }
    check((int)UserModel::find($channel['id'])['balance']===999900 && Db::name('balance_log')->where('user_id',$channel['id'])->count()===$ledgerCount,'rejected balance changes leave balance and ledger unchanged');
    $orderRow=OrderModel::where('order_id',$order['order_id'])->find();
    check($orderRow && $order['pay_url'], 'order persisted and payment URL returned');
    // No merchant API fixture is created, so confirmation cannot contact external services.
    OrderService::confirmed(['order_id'=>$order['order_id']], $channel->toArray());
    check((int)OrderModel::find($orderRow['id'])['status']===2,'payment confirmation');
    OrderModel::update(['pay_time'=>time()-3600],['id'=>$orderRow['id']]);
    $app->console->call('OrderSettlement');
    check((int)UserModel::find($merchant['id'])['balance']===100,'settlement credits current balance as integers');
    $app->console->call('OrderSettlement');
    check((int)UserModel::find($merchant['id'])['balance']===100,'settled order is not credited twice');
    check(Db::name('balance_log')->where('remark',$order['order_id'])->count()===2,'debit and settlement each write one ledger entry');
    $params['trade_no'].='_expire';
    $expired=OrderService::createOrder($params,['uid'=>$merchant['id']]);
    OrderModel::update(['expire_time'=>time()-3600],['order_id'=>$expired['order_id']]);
    $app->console->call('OrderExpired');
    check((int)UserModel::find($channel['id'])['balance']===999900,'expired order refunds channel as integers');
    $app->console->call('OrderExpired');
    check((int)UserModel::find($channel['id'])['balance']===999900,'expired order is not refunded twice');
    check(Db::name('balance_log')->where('remark',$expired['order_id'])->count()===2,'debit and refund each write one ledger entry');
    $ownLedger=\app\common\service\BalanceService::lists([], $merchant->toArray(), true);
    check($ownLedger['count']===1 && $ownLedger['items'][0]['user_id']==$merchant['id'], 'framework balance page lists only own ledger');
    foreach (['admin','channel','merchant'] as $module) {
        foreach (['OrderService','BankCardsService','BankUserService','BanksService','PayTypeService','DeviceNotifyService'] as $service) {
            $class='app\\'.$module.'\\service\\'.$service;
            if (class_exists($class)) {
                $class::lists([],['id'=>$channel['id']]);
            }
        }
    }
    check(true, 'business lists execute against migrated schema');
    foreach (['channel','merchant','task'] as $role) {
        $set=\app\common\service\PermsAccessService::getPowerSet($role);
        check(count($set)>0, $role.' permissions resolve');
    }
    foreach (['channel','merchant'] as $role) {
        $set=\app\common\service\PermsAccessService::getPowerSet($role);
        check(isset($set['user.Balance.info'], $set['user.Balance.items']), $role.' framework balance permissions');
        check(!isset($set[$role.'.BalanceLog.items']), $role.' copied ledger permission removed');
        check(isset($set['admin.PayType.getOptions']), $role.' payment selector permission');
        check(count(\app\common\service\PermsAccessService::getRoutes($role))>0, $role.' menus resolve');
    }
    $queue='vnd_bankpay_smoke_'.bin2hex(random_bytes(6));
    \app\task\service\TaskService::addTask($queue,['data'=>['order_id'=>'check']]);
    $task=\app\task\service\TaskService::pop($queue);
    check($task['data']['order_id']==='check' && \app\task\service\TaskService::getCount($queue)===0, 'callback Redis queue push/pop');
    check(class_exists('Vietqr') && class_exists('GuzzleHttp\\Client') && class_exists('think\\view\\driver\\Think'), 'payment view and VietQR dependencies load');
    $qr=\tekintian\TekinQR::getQRImg('migration-check', 10, null, 1);
    check(is_string($qr) && strlen($qr)>50, 'device QR generation');
    $fields=Db::query('SHOW COLUMNS FROM dt_user');
    check(!in_array('balance_virtual',array_column($fields,'Field')), 'user schema has no virtual currency');
} finally {
    Db::rollback();
    // Source bank tables use MyISAM; remove only this test's nontransactional fixtures.
    if (isset($card)) { Db::name('bank_cards')->where('id', $card['id'])->delete(); }
    if (isset($bankuser)) { Db::name('bank_user')->where('id', $bankuser['id'])->delete(); }
}
check(Db::name('user')->where('username', 'like', '%'.$suffix)->count() === 0, 'transactional fixtures rolled back');
echo "All fixtures cleaned up.\n";
