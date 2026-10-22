<?php
require dirname(__DIR__).'/vendor/autoload.php';
$app=new think\App(dirname(__DIR__)); $app->initialize(); $app->debug(true);
use think\facade\Db;
use app\common\model\DeviceNotifyModel;
use app\common\enums\StatusEnum;
use app\admin\service\DeviceNotifyService;
function checkNotifyContent($ok,$message) {
    if (!$ok) { throw new RuntimeException($message); }
    echo 'PASS '.$message.PHP_EOL;
}
$config=config('database'); $config['connections']['mysql']['prefix']='notify_content_'; config($config,'database');
foreach(['device_notify','user'] as $table) { Db::execute('CREATE TEMPORARY TABLE `notify_content_'.$table.'` LIKE `dt_'.$table.'`'); }
// 构造旧表结构，确保迁移测试在升级前后均可重复执行。
$columns=array_column(Db::query('SHOW COLUMNS FROM notify_content_device_notify'),'Field');
foreach(['card_number','balance','sms_code','order_id'] as $field) {
    if (!in_array($field,$columns,true)) { Db::execute('ALTER TABLE notify_content_device_notify ADD `'.$field.'` varchar(50) DEFAULT NULL'); }
}
Db::execute('ALTER TABLE notify_content_device_notify MODIFY status tinyint NULL DEFAULT 0');
Db::name('user')->insert(['id'=>11,'username'=>'notify_owner','type'=>'channel']);
foreach([0,1,2,-1,null,99] as $i=>$status) {
    Db::execute('INSERT INTO notify_content_device_notify (id,uid,phone_number,content,time,status,card_number,balance,sms_code,order_id) VALUES (?,?,?,?,?,?,?,?,?,?)', [$i+1,11,'0912345678','bank_push_'.$i.' 原文😀',time(),$status,'123456','100','1234','legacy']);
}
$original=array_column(Db::query('SELECT id,content FROM notify_content_device_notify ORDER BY id'),'content','id');
$sql=str_replace('`dt_device_notify`','`notify_content_device_notify`',file_get_contents(dirname(__DIR__).'/migrations/20261022_device_notify_content.sql'));
foreach(preg_split('/;\s*(?:\r?\n|$)/',trim($sql)) as $statement) { if (trim($statement)!=='') { Db::execute($statement); } }
$columns=array_column(Db::query('SHOW COLUMNS FROM notify_content_device_notify'),'Field');
checkNotifyContent(!array_intersect(['card_number','balance','sms_code','order_id'],$columns),'four parsed columns removed');
checkNotifyContent(array_column(Db::query('SELECT id,content FROM notify_content_device_notify ORDER BY id'),'content','id')===$original,'push text and Unicode preserved');
checkNotifyContent(array_map('intval',Db::name('device_notify')->order('id')->column('status'))===[StatusEnum::STATUS_1,StatusEnum::STATUS_2,StatusEnum::STATUS_OFF,StatusEnum::STATUS_2,StatusEnum::STATUS_1,StatusEnum::STATUS_1],'legacy statuses normalized to three processing states');
$list=DeviceNotifyService::lists(['username'=>'notify_owner','content'=>'bank_push','phone_number'=>'0912','status'=>(string)StatusEnum::STATUS_1],[]);
checkNotifyContent($list['count']===3,'account, raw content, phone and pending-status filters work after dropping columns');
checkNotifyContent(!isset($list['items'][0]['balance']) && !isset($list['items'][0]['card_number']),'list excludes removed fields');
DeviceNotifyService::reset(['id'=>2]);
checkNotifyContent((int)DeviceNotifyModel::find(2)['status']===StatusEnum::STATUS_1,'reset marks record as pending');
app\common\service\DeviceNotifyService::addContent('0912345678','{"text":"new push😀"}',['id'=>11]);
$raw=Db::name('device_notify')->order('id','desc')->find();
checkNotifyContent($raw['content']==='{"text":"new push😀"}' && (int)$raw['status']===StatusEnum::STATUS_1,'unchanged push receiver persists raw content with pending status');

$pending=app\common\service\DeviceNotifyService::getPending();
checkNotifyContent(count($pending)===5 && !array_intersect([3,4],array_column($pending,'id')),'pending retrieval uses StatusEnum::STATUS_1');
$failed=DeviceNotifyService::lists(['status'=>(string)StatusEnum::STATUS_OFF],[]);
checkNotifyContent($failed['count']===1 && (int)$failed['items'][0]['id']===3,'failed filter uses StatusEnum::STATUS_OFF');
Db::execute('CREATE TEMPORARY TABLE notify_status_upgrade LIKE dt_device_notify');
Db::execute('INSERT INTO notify_status_upgrade (status,content) VALUES (0,\'pending\'),(1,\'processed\'),(2,\'failed\')');
$upgrade=str_replace('`dt_device_notify`','`notify_status_upgrade`',file_get_contents(dirname(__DIR__).'/migrations/20261022_device_notify_status_enum.sql'));
foreach(preg_split('/;\s*(?:\r?\n|$)/',trim($upgrade)) as $statement) { if (trim($statement)!=='') { Db::execute($statement); } }
$states=array_map('intval',array_column(Db::query('SELECT status FROM notify_status_upgrade ORDER BY id'),'status'));
checkNotifyContent($states===[StatusEnum::STATUS_1,StatusEnum::STATUS_2,StatusEnum::STATUS_OFF],'previous content-only schema upgrades without changing state meanings');
$definition=Db::query("SHOW COLUMNS FROM notify_status_upgrade LIKE 'status'")[0];
checkNotifyContent((int)$definition['Default']===StatusEnum::STATUS_1,'table default is pending StatusEnum::STATUS_1');
