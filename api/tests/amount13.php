<?php
require dirname(__DIR__).'/vendor/autoload.php';
$app=new \think\App(dirname(__DIR__));$app->initialize();
use app\common\utils\AmountUtils;
use app\common\service\BalanceService;
use app\common\model\UserModel;
use app\common\model\OrderModel;
use app\common\exception\RequestException;
use think\facade\Db;
function amountCheck($ok,$message){if(!$ok){throw new \RuntimeException($message);}echo 'PASS '.$message."\n";}
amountCheck(AmountUtils::integer('9999999999999')===AmountUtils::MAX,'13-digit maximum preserved');
amountCheck(AmountUtils::integer('-9999999999999',true)===-AmountUtils::MAX,'signed adjustment supports 13 digits');
foreach(['10000000000000','-10000000000000','00000000000001','1.5',1.5,'1e3',null]as$value){
    $rejected=false;try{AmountUtils::integer($value,true);}catch(RequestException $e){$rejected=true;}
    amountCheck($rejected,'decimal/scientific/overlength input rejected');
}
Db::startTrans();
try{
    $tag='amount_'.bin2hex(random_bytes(5));
    $user=UserModel::create(['username'=>$tag,'password'=>'','type'=>'channel','balance'=>AmountUtils::MAX-10,'status'=>1]);
    BalanceService::change((int)$user['id'],10,'adjust',$tag);
    amountCheck((int)UserModel::find($user['id'])['balance']===AmountUtils::MAX,'maximum resulting balance is exact');
    $before=Db::name('balance_log')->where('user_id',$user['id'])->count();
    $rejected=false;try{BalanceService::change((int)$user['id'],1,'adjust',$tag);}catch(RequestException $e){$rejected=true;}
    amountCheck($rejected && (int)UserModel::find($user['id'])['balance']===AmountUtils::MAX && Db::name('balance_log')->where('user_id',$user['id'])->count()===$before,'balance overflow leaves balance and ledger unchanged');
    $rejected=false;try{BalanceService::change((int)$user['id'],10000000000000,'adjust',$tag);}catch(RequestException $e){$rejected=true;}
    amountCheck($rejected,'overlength change rejected');
    $order=OrderModel::create(['order_id'=>$tag,'trade_no'=>$tag,'amount'=>AmountUtils::MAX,'ps'=>strtoupper(bin2hex(random_bytes(4)))]);
    amountCheck((int)OrderModel::find($order['id'])['amount']===AmountUtils::MAX,'order BIGINT stores all 13 digits');
    $id=Db::name('bank_cards')->insertGetId(['bank_number'=>'99'.random_int(1000000000,9999999999),'balance'=>AmountUtils::MAX,'limit_in_max'=>AmountUtils::MAX,'limit_out_max'=>AmountUtils::MAX]);
    $card=Db::name('bank_cards')->where('id',$id)->find();
    amountCheck((int)$card['balance']===AmountUtils::MAX && (int)$card['limit_in_max']===AmountUtils::MAX,'bank balance and limits store all 13 digits');
    amountCheck(!method_exists(\app\admin\controller\Order::class,'cancel') && !method_exists(\app\common\service\OrderService::class,'cancel'),'manual cancellation methods removed');
    amountCheck(!Db::name('perms')->where('permission_key','admin.order.cancel')->find(),'manual cancellation permission removed');
}finally{Db::rollback();}
echo "Amount fixtures rolled back.\n";
