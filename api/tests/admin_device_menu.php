<?php
require dirname(__DIR__).'/vendor/autoload.php';
$app=new think\App(dirname(__DIR__)); $app->initialize(); $app->debug(true);
use think\facade\Db;
use app\common\service\PermsAccessService;
function checkDeviceMenu($ok,$message){if(!$ok){throw new RuntimeException($message);}echo 'PASS '.$message.PHP_EOL;}
$config=config('database');$config['connections']['mysql']['prefix']='device_menu_';config($config,'database');
foreach(['perms','role_perms']as$table){Db::execute('CREATE TEMPORARY TABLE `device_menu_'.$table.'` LIKE `dt_'.$table.'`');}
Db::name('perms')->insert(['id'=>114,'parent_id'=>0,'name'=>'订单管理','node_type'=>'group','route_path'=>'/admin/order','status'=>1]);
Db::name('perms')->insert(['id'=>154,'parent_id'=>114,'name'=>'设备推送','node_type'=>'page','route_path'=>'/admin/order/deviceNotify','module'=>'admin','controller'=>'DeviceNotify','status'=>1]);
foreach(['items','reset']as$i=>$action){
Db::name('perms')->insert(['id'=>155+$i,'parent_id'=>154,'name'=>$action,'node_type'=>'action','permission_key'=>'admin.devicenotify.'.$action,'module'=>'admin','controller'=>'DeviceNotify','action'=>$action,'status'=>1]);
Db::name('role_perms')->insert(['type'=>'custom_admin','perms_id'=>155+$i]);
}
$before=PermsAccessService::getPowerSet('custom_admin');
$grants=Db::name('role_perms')->select()->toArray();
$apply=function(){
$sql=str_replace('`dt_perms`','`device_menu_perms`',file_get_contents(dirname(__DIR__).'/migrations/20261022_admin_device_menu.sql'));
$sql=preg_replace('/^--.*$/m','',$sql);
foreach(preg_split('/;\s*(?:\r?\n|$)/',trim($sql))as$statement){if(trim($statement)!=='' && !in_array(trim($statement),['START TRANSACTION','COMMIT'],true)){Db::execute($statement);}}
};
$apply();
$page=Db::name('perms')->where('id',154)->find();
$group=Db::name('perms')->where('route_path','/admin/device')->find();
checkDeviceMenu($page['name']==='推送记录' && $page['route_path']==='/admin/device/notifyRecords' && (int)$page['parent_id']===(int)$group['id'],'existing page moved under device menu with same ID');
checkDeviceMenu(PermsAccessService::getPowerSet('custom_admin')===$before && Db::name('role_perms')->select()->toArray()===$grants,'endpoint permissions and role grants unchanged');
$routes=PermsAccessService::getRoutes('custom_admin');
checkDeviceMenu(count($routes)===1 && $routes[0]['name']==='/admin/device' && $routes[0]['children'][0]['name']==='/admin/device/notifyRecords','authorized role receives new parent and child menu');
checkDeviceMenu(PermsAccessService::getRoutes('no_grants')===[],'menu remains hidden for unauthorized roles');
$apply();
checkDeviceMenu(Db::name('perms')->where('route_path','/admin/device')->count()===1 && Db::name('perms')->where('route_path','/admin/device/notifyRecords')->count()===1,'migration can repeat without duplicate nodes');
Db::name('perms')->where('id',154)->update(['status'=>-1]);$apply();
checkDeviceMenu((int)Db::name('perms')->where('id',154)->value('status')===-1 && PermsAccessService::getRoutes('custom_admin')===[],'disabled page stays disabled after migration');
