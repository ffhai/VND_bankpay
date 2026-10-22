<?php
require dirname(__DIR__).'/vendor/autoload.php';
$app = new think\App(dirname(__DIR__));
$app->initialize();
$app->debug(true);
use think\facade\Db;
use app\admin\service\StatisticsService as AdminStats;
use app\merchant\service\StatisticsService as MerchantStats;
use app\channel\service\StatisticsService as ChannelStats;
function dashboardCheck($ok, $message) {
    if (!$ok) { throw new RuntimeException($message); }
    echo 'PASS '.$message.PHP_EOL;
}
trait DashboardIdentity {
    protected function resolveUser(): array { return $GLOBALS['dashboard_test_identity']; }
}
class TestAdminDashboard extends app\admin\controller\Statistics { use DashboardIdentity; }
class TestMerchantDashboard extends app\merchant\controller\Statistics { use DashboardIdentity; }
class TestChannelDashboard extends app\channel\controller\Statistics { use DashboardIdentity; }
$config = config('database');
$config['connections']['mysql']['prefix'] = 'dashboard_test_';
config($config, 'database');
foreach (['order','perms','role_perms','roles','user'] as $table) {
    Db::execute('CREATE TEMPORARY TABLE `dashboard_test_'.$table.'` LIKE `dt_'.$table.'`');
}
foreach (['audit_custom','seller_custom','provider_custom','both_custom','disabled_custom','admin'] as $role) {
    Db::name('roles')->insert(['type'=>$role,'name'=>$role]);
}
foreach (['admin','merchant','channel'] as $i=>$module) {
    Db::name('perms')->insert(['id'=>10+$i,'name'=>$module,'node_type'=>'api','permission_key'=>$module.'.order.items','module'=>$module,'controller'=>'Order','action'=>'items','status'=>1]);
}
Db::name('perms')->insertAll([
    ['id'=>20,'parent_id'=>0,'name'=>'disabled','node_type'=>'page','module'=>'','controller'=>'','action'=>'','status'=>-1],
    ['id'=>21,'parent_id'=>20,'name'=>'disabled list','node_type'=>'api','module'=>'admin','controller'=>'Order','action'=>'items','status'=>1],
]);
foreach (['audit_custom'=>[10],'seller_custom'=>[11],'provider_custom'=>[12],'both_custom'=>[10,11],'disabled_custom'=>[21]] as $role=>$ids) {
    foreach ($ids as $id) { Db::name('role_perms')->insert(['type'=>$role,'perms_id'=>$id]); }
}
$migrate=require dirname(__DIR__).'/migrations/20261022_dashboard_statistics.php';
$migrate();
$app->request->setController('Statistics')->setAction('orders');
foreach (['admin'=>TestAdminDashboard::class,'merchant'=>TestMerchantDashboard::class,'channel'=>TestChannelDashboard::class] as $module=>$class) {
    $app->http->name($module);
    foreach (['audit_custom','seller_custom','provider_custom','both_custom','disabled_custom','admin'] as $role) {
        $GLOBALS['dashboard_test_identity']=['id'=>10,'type'=>$role,'status'=>1];
        $allowed=($role==='audit_custom' && $module==='admin') || ($role==='seller_custom' && $module==='merchant') || ($role==='provider_custom' && $module==='channel') || ($role==='both_custom' && in_array($module,['admin','merchant'],true));
        $actual=true;
        try { new $class($app); } catch (app\common\exception\NotPermsException $e) { $actual=false; }
        dashboardCheck($actual===$allowed, 'module endpoint authorization: '.$module.' / '.$role);
    }
    $GLOBALS['dashboard_test_identity']=['id'=>1,'type'=>'anything','status'=>1];
    new $class($app);
    dashboardCheck(true,'superuser keeps endpoint permissions: '.$module);
}
$adminId=Db::name('perms')->where('permission_key','admin.statistics.orders')->value('id');
Db::name('role_perms')->where('type','audit_custom')->where('perms_id',$adminId)->delete();
$migrate();
dashboardCheck(!Db::name('role_perms')->where('type','audit_custom')->where('perms_id',$adminId)->find(),'rerunning migration preserves revoked permissions');
Db::name('perms')->where('id',$adminId)->update(['status'=>-1]);
$app->http->name('admin');
$GLOBALS['dashboard_test_identity']=['id'=>10,'type'=>'both_custom','status'=>1];
$denied=false;
try { new TestAdminDashboard($app); } catch (app\common\exception\NotPermsException $e) { $denied=true; }
dashboardCheck($denied,'disabled statistics endpoint denies access');
$now=strtotime('2026-10-22 12:00:00'); $today=strtotime('2026-10-22 00:00:00');
$fixtures=[[10,20,100,1,$today],[20,10,200,2,$now],[10,10,50,-1,$today+1],[10,20,300,2,$today-86400],[20,10,400,2,$today-86401],[88,99,900,2,$today+2],[10,20,10000,1,$now+1]];
foreach($fixtures as $i=>$f) {
    Db::name('order')->insert(['order_id'=>'dashboard_'.$i,'trade_no'=>'dashboard_'.$i,'ps'=>'DASH'.$i,'trade_uid'=>$f[0],'channel_uid'=>$f[1],'amount'=>$f[2],'status'=>$f[3],'create_time'=>$f[4]]);
}
$user=['id'=>10,'type'=>'anything'];
$a=AdminStats::orders($user,$now); $m=MerchantStats::orders($user,$now); $c=ChannelStats::orders($user,$now);
dashboardCheck($a['today']===['count'=>2,'amount'=>'1100'] && $a['yesterday']===['count'=>1,'amount'=>'300'] && $a['total']===['count'=>4,'amount'=>'1800'],'admin aggregates successful orders only; pending and failed excluded');
dashboardCheck($m['today']===['count'=>0,'amount'=>'0'] && $m['yesterday']===['count'=>1,'amount'=>'300'] && $m['total']===['count'=>1,'amount'=>'300'],'merchant module only trade_uid ownership');
dashboardCheck($c['today']===['count'=>1,'amount'=>'200'] && $c['yesterday']===['count'=>0,'amount'=>'0'] && $c['total']===['count'=>2,'amount'=>'600'],'channel module only channel_uid ownership');
dashboardCheck(MerchantStats::orders(['id'=>123],$now)['total']===['count'=>0,'amount'=>'0'],'empty account returns zero');
Db::name('order')->delete(true);
$rows=[];
for($i=0;$i<1000;$i++){ $rows[]=['order_id'=>'large_'.$i,'trade_no'=>'large_'.$i,'ps'=>'LARGE'.$i,'trade_uid'=>10,'channel_uid'=>20,'amount'=>9999999999999,'status'=>2,'create_time'=>$today]; }
Db::name('order')->insertAll($rows);
dashboardCheck(MerchantStats::orders($user,$now)['total']['amount']==='9999999999999000','large aggregate remains exact decimal string');
dashboardCheck(!class_exists('app\common\controller\Statistics') && !class_exists('app\common\service\StatisticsService'),'obsolete common dashboard API and scope service removed');

// 已部署的 Dashboard 权限更名时，保留节点ID、禁用状态及现有授权。
Db::name('perms')->where('id',$adminId)->update(['permission_key'=>'admin.dashboard.orders','controller'=>'Dashboard']);
$grants=Db::name('role_perms')->where('perms_id',$adminId)->count();
$migrate();
$renamed=Db::name('perms')->where('permission_key','admin.statistics.orders')->find();
dashboardCheck((int)$renamed['id']===(int)$adminId && $renamed['controller']==='Statistics' && (int)$renamed['status']===-1 && Db::name('role_perms')->where('perms_id',$adminId)->count()===$grants,'legacy permission renamed without changing grants or disabled state');
dashboardCheck(!class_exists('app\common\service\OrderStatisticsService') && !class_exists('app\admin\controller\Dashboard') && !class_exists('app\merchant\controller\Dashboard') && !class_exists('app\channel\controller\Dashboard'),'public statistics service and obsolete controllers removed');
// 账号统计独立授权，仅包含码商、商户；停用账号仍保留余额，计入总数及总余额。
Db::name('perms')->insert(['id'=>500,'name'=>'members','node_type'=>'api','module'=>'admin','controller'=>'Member','action'=>'items','status'=>1]);
Db::name('role_perms')->insert(['type'=>'audit_custom','perms_id'=>500]);
$accountMigration=require dirname(__DIR__).'/migrations/20261022_account_statistics.php';
$accountMigration();
$app->http->name('admin'); $app->request->setAction('accounts');
foreach (['audit_custom'=>true,'seller_custom'=>false,'provider_custom'=>false] as $role=>$allowed) {
    $GLOBALS['dashboard_test_identity']=['id'=>10,'type'=>$role,'status'=>1];
    $actual=true;
    try { new TestAdminDashboard($app); } catch (app\common\exception\NotPermsException $e) { $actual=false; }
    dashboardCheck($actual===$allowed,'account statistics permission: '.$role);
}
dashboardCheck(AdminStats::accounts()['total']===['count'=>0,'balance'=>'0'],'empty account statistics');
foreach ([['channel',100,1],['channel',250,-1],['merchant',500,1],['merchant',50,-1],['admin',99999,1]] as $i=>$u) {
    Db::name('user')->insert(['username'=>'account_stat_'.$i,'type'=>$u[0],'balance'=>$u[1],'status'=>$u[2]]);
}
$accounts=AdminStats::accounts();
dashboardCheck($accounts['channel']===['count'=>2,'balance'=>'350'] && $accounts['merchant']===['count'=>2,'balance'=>'550'] && $accounts['total']===['count'=>4,'balance'=>'900'],'account category totals, combined totals, disabled accounts and unrelated role exclusion');
Db::name('user')->delete(true);
$users=[];
for($i=0;$i<1000;$i++){ $users[]=['username'=>'large_balance_'.$i,'type'=>'channel','balance'=>9999999999999]; }
Db::name('user')->insertAll($users);
dashboardCheck(AdminStats::accounts()['total']['balance']==='9999999999999000','large total balance remains exact');