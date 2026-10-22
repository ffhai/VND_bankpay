<?php
require dirname(__DIR__).'/vendor/autoload.php';
$app = new think\App(dirname(__DIR__));
$app->initialize();
$app->debug(true);

use app\common\enums\StatusEnum;
use app\common\exception\RequestException;
use app\common\exception\NotPermsException;
use app\common\service\AppSignService;
use app\common\service\DeviceNotifyService;
use app\task\service\SmsTradeService;
use think\facade\Db;

function checkTaskTrade($ok, $message) {
    if (!$ok) { throw new RuntimeException($message); }
    echo 'PASS '.$message.PHP_EOL;
}

$config = config('database');
$config['connections']['mysql']['prefix'] = 'task_trade_test_';
config($config, 'database');
foreach (['device_notify','sms_trade','bank_cards','order','perms','role_perms','user','app_list'] as $table) {
    Db::execute('CREATE TEMPORARY TABLE `task_trade_test_'.$table.'` LIKE `dt_'.$table.'`');
}
$fields = array_column(Db::query('SHOW COLUMNS FROM task_trade_test_sms_trade'), 'Field');
checkTaskTrade($fields === ['id','notify_id','bank_number','balance','change','remark','order_id','status'], 'existing SMS table schema unchanged');
foreach (range(1, 8) as $id) {
    Db::name('device_notify')->insert(['id'=>$id,'uid'=>99,'content'=>'raw push '.$id,'status'=>StatusEnum::STATUS_1]);
}
Db::name('bank_cards')->insert(['id'=>1,'bank_number'=>'80000186204','balance'=>70000]);
Db::name('bank_cards')->insert(['id'=>2,'bank_number'=>'000123456789','balance'=>100]);
Db::name('order')->insert(['id'=>1,'order_id'=>'sms_order','trade_no'=>'sms_order','ps'=>'DCZ8P1HF','bank_number'=>'80000186204','amount'=>300,'status'=>1]);
$originalBank = Db::name('bank_cards')->find(1);
$originalOrder = Db::name('order')->find(1);
$originalNotify = Db::name('device_notify')->find(1);
$payload = ['notify_id'=>1,'bank_number'=>'80000186204','balance'=>70300,'remark'=>'DCZ8P1HF'];
$result = SmsTradeService::receive($payload + ['order_id'=>'injected','change'=>999,'status'=>1,'uid'=>11]);
$trade = Db::name('sms_trade')->find($result['id']);
foreach ($payload as $field=>$value) {
    checkTaskTrade((string)$trade[$field] === (string)$value, 'received field: '.$field);
}
checkTaskTrade((int)$trade['status'] === StatusEnum::STATUS_1 && (int)$trade['change'] === 300 && $trade['order_id'] === 'sms_order', 'remark and computed change match the order; injected fields ignored');
$processedNotify = $originalNotify;
$processedNotify['status'] = StatusEnum::STATUS_2;
checkTaskTrade(Db::name('device_notify')->find(1) === $processedNotify, 'source notification only changes processing status');
checkTaskTrade((int)Db::name('bank_cards')->where('id',1)->value('balance') === 70300 && (int)Db::name('order')->where('id',1)->value('status') === 2, 'successful match confirms order and updates bank balance');
checkTaskTrade(SmsTradeService::receive($payload) === $result && Db::name('sms_trade')->count() === 1, 'repeat upload returns the same trade without inserting again');
Db::name('bank_cards')->where('id',1)->update(['balance'=>71000]);
SmsTradeService::receive($payload);
checkTaskTrade((int)Db::name('sms_trade')->where('id',$result['id'])->value('change') === 300 && (int)Db::name('bank_cards')->where('id',1)->value('balance') === 71000, 'repeat upload preserves original change and does not overwrite newer bank balance');
Db::name('bank_cards')->where('id',1)->update(['balance'=>70300]);
// 管理端重置已入库推送后，再次接收仍只恢复处理状态。
Db::name('device_notify')->where('id',1)->update(['status'=>StatusEnum::STATUS_1]);
Db::name('bank_cards')->where('id',1)->update(['balance'=>71000]);
checkTaskTrade(SmsTradeService::receive($payload) === $result && Db::name('sms_trade')->count() === 1 && (int)Db::name('device_notify')->where('id',1)->value('status') === StatusEnum::STATUS_2 && (int)Db::name('bank_cards')->where('id',1)->value('balance') === 71000, 'reset notification restores processed state without reapplying the trade or old bank balance');
Db::name('bank_cards')->where('id',1)->update(['balance'=>70300]);

$zero = SmsTradeService::receive(['notify_id'=>2,'bank_number'=>'000123456789','balance'=>'0','remark'=>'']);
checkTaskTrade((int)Db::name('sms_trade')->where('id',$zero['id'])->value('balance') === 0 && (int)Db::name('sms_trade')->where('id',$zero['id'])->value('change') === -100, 'zero received balance records the full decrease and preserves leading-zero bank number');
$rejected = false;
try { SmsTradeService::receive(array_merge($payload, ['notify_id'=>9999])); } catch (RequestException $e) { $rejected = true; }
checkTaskTrade($rejected && Db::name('sms_trade')->count() === 2 && (int)Db::name('device_notify')->where('id',3)->value('status') === StatusEnum::STATUS_1, 'missing source notification leaves no trade');
$decreased = SmsTradeService::receive(array_merge($payload, ['notify_id'=>6,'balance'=>69900]));
$unchanged = SmsTradeService::receive(array_merge($payload, ['notify_id'=>7,'balance'=>69900]));
checkTaskTrade((int)Db::name('sms_trade')->where('id',$decreased['id'])->value('change') === -400 && (int)Db::name('sms_trade')->where('id',$unchanged['id'])->value('change') === 0, 'consecutive pushes calculate signed deltas from the newly saved balance');
$rejected = false;
try { SmsTradeService::receive(array_merge($payload, ['notify_id'=>8,'bank_number'=>'999999'])); } catch (RequestException $e) { $rejected = true; }
checkTaskTrade($rejected && Db::name('sms_trade')->count() === 4 && (int)Db::name('device_notify')->where('id',8)->value('status') === StatusEnum::STATUS_1, 'missing system card cannot create a trade with a fabricated change');
checkTaskTrade((int)Db::name('bank_cards')->where('id',1)->value('balance') === 69900 && (int)Db::name('order')->where('id',1)->value('status') === 2, 'unmatched pushes still update bank balance and do not change the confirmed order');

DeviceNotifyService::setPending(['id'=>3,'status'=>StatusEnum::STATUS_OFF]);
DeviceNotifyService::setPending(['id'=>4,'status'=>StatusEnum::STATUS_2]);
DeviceNotifyService::setPending(['id'=>1,'status'=>StatusEnum::STATUS_OFF]);
checkTaskTrade((int)Db::name('device_notify')->where('id',3)->value('status') === StatusEnum::STATUS_OFF && (int)Db::name('device_notify')->where('id',4)->value('status') === StatusEnum::STATUS_2 && (int)Db::name('device_notify')->where('id',1)->value('status') === StatusEnum::STATUS_2, 'state reports use existing statuses and cannot overwrite a completed upload');

// 在临时权限表验证迁移、签名及实际控制器调用，不使用真实应用密钥。
foreach ([[1,0,'group','task','',''],[2,1,'group','task','Notify',''],[3,2,'api','task','Notify','setPending']] as $row) {
    Db::name('perms')->insert(['id'=>$row[0],'parent_id'=>$row[1],'name'=>'test '.$row[0],'node_type'=>$row[2],'module'=>$row[3],'controller'=>$row[4],'action'=>$row[5],'status'=>1]);
}
Db::name('role_perms')->insert(['type'=>'custom_notify_worker','perms_id'=>3]);
$applyPermissions = function () {
    $sql = file_get_contents(dirname(__DIR__).'/migrations/20261022_task_notify_sms_trade.sql');
    $sql = preg_replace('/^--.*$/m', '', str_replace('`dt_', '`task_trade_test_', $sql));
    Db::transaction(function () use ($sql) {
        foreach (preg_split('/;\s*(?:\r?\n|$)/', trim($sql)) as $statement) {
            $statement = trim($statement);
            if ($statement !== '' && !in_array($statement, ['START TRANSACTION','COMMIT'], true)) { Db::execute($statement); }
        }
    });
};
$applyPermissions();
$permissionId = Db::name('perms')->where('permission_key','task.notify.addsmstrade')->value('id');
checkTaskTrade($permissionId && Db::name('role_perms')->where(['type'=>'custom_notify_worker','perms_id'=>$permissionId])->count() === 1, 'new endpoint inherits an existing worker permission');
Db::name('user')->insert(['id'=>11,'username'=>'test_worker','type'=>'custom_notify_worker','status'=>1]);
Db::name('app_list')->insert(['uid'=>11,'appid'=>'sms_test','secret'=>'test-only','status'=>1]);
$app->http->name('task');
$request = function (array $params) use ($app) {
    $req = new think\Request();
    $req->withGet($params)->setController('Notify')->setAction('addSmsTrade');
    $app->instance('request', $req);
};
$signed = array_merge($payload, ['notify_id'=>5,'appid'=>'sms_test','time'=>(string)time()]);
$signed['sign'] = AppSignService::order_sign($signed, 'test-only');
$request($signed);
$response = (new app\task\controller\Notify($app))->addSmsTrade()->getData();
checkTaskTrade($response['code'] === 1 && $response['data']['notify_id'] === 5 && Db::name('sms_trade')->count() === 5, 'signed task endpoint accepts and persists the four upload fields');
$tampered = $signed;
$tampered['balance']++;
$request($tampered);
$rejected = false;
try { new app\task\controller\Notify($app); } catch (RequestException $e) { $rejected = true; }
checkTaskTrade($rejected, 'tampered signature rejected');
Db::name('role_perms')->where('perms_id',$permissionId)->delete();
$applyPermissions();
$request($signed);
$rejected = false;
try { new app\task\controller\Notify($app); } catch (NotPermsException $e) { $rejected = true; }
checkTaskTrade($rejected && Db::name('perms')->where('permission_key','task.notify.addsmstrade')->count() === 1, 'migration is idempotent and revoked receiver permission remains revoked');
