<?php
require dirname(__DIR__).'/vendor/autoload.php';
$app=new \think\App(dirname(__DIR__));
$app->initialize();
use app\common\model\OrderModel;
use app\common\model\UserModel;
use app\common\service\OrderService;
use think\facade\Db;
if (($argv[1] ?? '')==='--worker') {
    try {
        $result=$argv[2]==='expire' ? OrderService::expireOrder(['order_id'=>$argv[3]]) : OrderService::confirmed(['order_id'=>$argv[3]],[]);
        echo json_encode($result);
    } catch (\app\common\exception\RequestException $e) {
        echo json_encode(['result'=>'blocked','manual_review'=>$e->data['manual_review'] ?? false]);
    }
    exit;
}
function raceExpiry(array $actions, string $orderId): array {
    $workers=[];
    foreach($actions as $action){
        $pipes=[];
        $proc=proc_open([PHP_BINARY,__FILE__,'--worker',$action,$orderId],[['pipe','r'],['pipe','w'],['pipe','w']],$pipes,dirname(__DIR__));
        if(!is_resource($proc)){throw new \RuntimeException('Cannot start worker');}
        fclose($pipes[0]);$workers[]=[$proc,$pipes];
    }
    $results=[];
    foreach($workers as [$proc,$pipes]){
        $out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);
        fclose($pipes[1]);fclose($pipes[2]);
        if(proc_close($proc)!==0){throw new \RuntimeException('Worker failed: '.$err);}
        $results[]=json_decode($out,true)['result'] ?? '';
    }
    sort($results);return $results;
}
$tag='expiry_'.bin2hex(random_bytes(5));$users=[];$orders=[];
try{
    foreach([['expire','expire'],['expire','confirmed']] as $index=>$actions){
        $user=UserModel::create(['username'=>$tag.'_'.$index,'password'=>'','type'=>'channel','balance'=>877,'status'=>1]);
        $users[]=$user['id'];
        $id=$tag.'_'.$index;
        $order=OrderModel::create(['order_id'=>$id,'trade_no'=>$id,'channel_uid'=>$user['id'],'amount'=>123,'status'=>1,'pay_type'=>'bank2bank','ps'=>strtoupper(bin2hex(random_bytes(4))),'notify_url'=>'','expire_time'=>time()-3600,'settlement_time'=>0]);
        $orders[]=$id;
        $results=raceExpiry($actions,$id);
        $row=OrderModel::find($order['id']);
        $balance=(int)UserModel::find($user['id'])['balance'];
        $refunds=Db::name('balance_log')->where('user_id',$user['id'])->where('type','CancelOrder')->count();
        if($index===0){
            if($results!==['already_processed','refunded'] || $balance!==1000 || $refunds!==1 || (int)$row['status']!==-1){throw new \RuntimeException('Concurrent refund failed');}
            echo "PASS concurrent timeout workers produce exactly one refund\n";
        }else{
            $refundWins=$results===['blocked','refunded'] && (int)$row['status']===-1 && $balance===1000 && $refunds===1;
            $confirmWins=$results===['blocked','confirmed'] && (int)$row['status']===2 && $balance===877 && $refunds===0;
            if(!$refundWins && !$confirmWins){throw new \RuntimeException('Confirm/refund race inconsistent');}
            echo "PASS confirmation/timeout race has one valid financial outcome\n";
        }
    }
}finally{
    if($orders){Db::name('order')->where('order_id','in',$orders)->delete();}
    if($users){Db::name('balance_log')->where('user_id','in',$users)->delete();Db::name('user')->where('id','in',$users)->delete();}
}
echo "Concurrency fixtures cleaned up; no merchant callback sent.\n";
