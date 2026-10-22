<?php
require dirname(__DIR__).'/vendor/autoload.php';
$app=new think\App(dirname(__DIR__)); $app->initialize(); $app->debug(true);
use think\facade\Db;
use app\common\enums\StatusEnum;
use app\admin\service\SmsTradeService;
function checkSmsTrade($ok,$message){if(!$ok){throw new RuntimeException($message);}echo 'PASS '.$message.PHP_EOL;}
$config=config('database');$config['connections']['mysql']['prefix']='sms_trade_test_';config($config,'database');
Db::execute('CREATE TEMPORARY TABLE sms_trade_test_sms_trade LIKE dt_sms_trade');
foreach(['perms','role_perms']as$table){Db::execute('CREATE TEMPORARY TABLE `sms_trade_test_'.$table.'` LIKE `dt_'.$table.'`');}
$fields=array_column(Db::query('SHOW COLUMNS FROM sms_trade_test_sms_trade'),'Field');
checkSmsTrade($fields===['id','notify_id','bank_number','balance','change','remark','order_id','status'],'actual table has exactly the expected eight fields');
checkSmsTrade((new app\common\model\SmsTradeModel())->getFields()===$fields,'model fields exactly match existing table');
foreach([[11,'123456',123,100,StatusEnum::STATUS_1],[0,'999999',99,-20,StatusEnum::STATUS_2],[11,'123456',2147483647,0,StatusEnum::STATUS_2]]as$i=>$row){
Db::name('sms_trade')->insert(['id'=>$i+1,'notify_id'=>$row[0],'bank_number'=>$row[1],'balance'=>$row[2],'change'=>$row[3],'status'=>$row[4],'remark'=>'REF'.$i,'order_id'=>'sms_order_'.$i]);
}
$list=SmsTradeService::lists([],[]);
checkSmsTrade($list['count']===3 && array_keys($list['items'][0])===$fields,'list only returns actual table fields');
$filtered=SmsTradeService::lists(['notify_id'=>11,'bank_number'=>'123','status'=>StatusEnum::STATUS_1,'remark'=>'REF0','order_id'=>'sms_order_0'],[]);
checkSmsTrade($filtered['count']===1 && $filtered['items'][0]['id']===1 && $filtered['items'][0]['change']===100,'filters use only existing table columns');
checkSmsTrade((int)$list['items'][0]['balance']===2147483647 && (int)$list['items'][1]['change']===-20,'existing integer balance and signed change preserved');
checkSmsTrade(count(SmsTradeService::lists(['page'=>2,'pagesize'=>2],[])['items'])===1,'pagination');
SmsTradeService::del([1],[]);
checkSmsTrade(Db::name('sms_trade')->count()===2 && !Db::name('sms_trade')->where('id',1)->find(),'delete only selected rows');
$rejected=false;try{SmsTradeService::del([],[]);}catch(app\common\exception\RequestException $e){$rejected=true;}
checkSmsTrade($rejected,'empty deletion rejected');
Db::name('perms')->insert(['id'=>1,'parent_id'=>0,'name'=>'短信交易','node_type'=>'page','route_path'=>'/admin/device/smsTrade','status'=>1]);
foreach(['items','del']as$i=>$action){Db::name('perms')->insert(['id'=>$i+2,'parent_id'=>1,'name'=>$action,'node_type'=>'action','module'=>'admin','controller'=>'SmsTrade','action'=>$action,'status'=>1]);}
Db::name('role_perms')->insert(['type'=>'sms_query_only','perms_id'=>2]);
Db::name('role_perms')->insert(['type'=>'sms_delete_only','perms_id'=>3]);
class TestSmsTradeController extends app\admin\controller\SmsTrade {
 protected function resolveUser():array{return $GLOBALS['sms_test_identity'];}
}
$app->http->name('admin');$app->request->setController('SmsTrade');
foreach(['items','del']as$action){
$app->request->setAction($action);
foreach(['sms_query_only','sms_delete_only','sms_none']as$role){
$GLOBALS['sms_test_identity']=['id'=>11,'type'=>$role,'status'=>1];$allowed=true;
try{new TestSmsTradeController($app);}catch(app\common\exception\NotPermsException $e){$allowed=false;}
checkSmsTrade($allowed===($action==='items' ? $role==='sms_query_only' : $role==='sms_delete_only'),'separate query/delete permissions: '.$role.' / '.$action);
}}
foreach(['add','edit','reset','getOptions']as$method){checkSmsTrade(!method_exists(app\admin\controller\SmsTrade::class,$method),'no extra endpoint: '.$method);}
