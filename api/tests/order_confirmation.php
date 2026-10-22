<?php
namespace app\task\service {
    // Isolate callback delivery: never enqueue a real merchant callback from this test.
    class TaskService {
        const KEY_NOTIFY = 'confirmation_test';
        public static $queueFile;
        public static $fail = false;
        public static function addTask(string $key, array $params): string {
            if (self::$fail) { throw new \RuntimeException('Test queue failure'); }
            usleep(100000);
            file_put_contents(self::$queueFile, json_encode($params['data'])."\n", FILE_APPEND | LOCK_EX);
            return 'test';
        }
    }
}
namespace {
    require dirname(__DIR__).'/vendor/autoload.php';
    $app=new \think\App(dirname(__DIR__));
    $app->initialize();
    use app\common\model\OrderModel;
    use app\common\model\AppListModel;
    use app\common\service\OrderService;
    use app\common\exception\RequestException;
    use app\task\service\TaskService;
    use think\facade\Db;

    if (($argv[1] ?? '') === '--worker') {
        TaskService::$queueFile=$argv[3];
        try {
            echo json_encode(OrderService::confirmed(['order_id'=>$argv[2]], []));
            exit(0);
        } catch (\Throwable $e) {
            fwrite(STDERR, get_class($e).': '.$e->getMessage());
            exit(1);
        }
    }
    function checkConfirmation($ok, $message) {
        if (!$ok) { throw new \RuntimeException($message); }
        echo 'PASS '.$message."\n";
    }
    $file=tempnam(dirname(__DIR__).'/runtime', 'confirm_test_');
    TaskService::$queueFile=$file;
    $uid=-random_int(100000000,2000000000);
    $tag='confirm_'.bin2hex(random_bytes(6));
    $orderIds=[];
    $notificationIds=[];
    $appId=null;
    $makeOrder=function($suffix, $extra=[]) use ($uid,$tag,&$orderIds) {
        $id=$tag.'_'.$suffix;
        $order=OrderModel::create(array_merge([
            'order_id'=>$id, 'trade_no'=>$id, 'trade_uid'=>$uid, 'channel_uid'=>$uid,
            'amount'=>123, 'pay_type'=>'bank2bank', 'bank_number'=>'123456789',
            'ps'=>strtoupper(bin2hex(random_bytes(4))), 'status'=>1, 'pay_time'=>0,
            'settlement_time'=>0, 'notify_url'=>'https://example.invalid/callback',
        ],$extra));
        $orderIds[]=$id;
        return OrderModel::find($order['id'])->toArray();
    };
    $queueCount=function() use ($file) { return count(file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)); };
    try {
        $appId=AppListModel::create(['uid'=>$uid,'appid'=>$tag,'secret'=>'test-only','status'=>1])['id'];
        $order=$makeOrder('whitelist');
        $result=OrderService::confirmed([
            'order_id'=>$order['order_id'], 'amount'=>999999, 'trade_uid'=>1, 'channel_uid'=>1,
            'status'=>-1, 'pay_time'=>1, 'settlement_time'=>1, 'notify_url'=>'https://example.invalid/changed',
        ], ['id'=>$uid,'type'=>'channel']);
        $saved=OrderModel::where('order_id',$order['order_id'])->find()->toArray();
        $expected=$order;
        $expected['status']=2;
        $expected['pay_time']=$saved['pay_time'];
        ksort($expected); ksort($saved);
        checkConfirmation($expected===$saved && $saved['pay_time']>1 && $result['result']==='confirmed', 'only internal status/pay_time changed');
        $count=$queueCount();
        $repeat=OrderService::confirmed(['order_id'=>$order['order_id']],[]);
        checkConfirmation($repeat['result']==='already_processed' && $repeat['pay_time']===$result['pay_time'] && $queueCount()===$count, 'repeat confirmation does not write or enqueue');
        parse_str(parse_url(json_decode(file($file)[0],true)['url'], PHP_URL_QUERY),$payload);
        checkConfirmation($payload['status']==='2' && $payload['amount']==='123' && (int)$payload['pay_time']===$result['pay_time'], 'callback contains persisted confirmed values');

        foreach ([['status'=>-1],['status'=>2,'settlement_time'=>time()],['status'=>99]] as $i=>$state) {
            $blocked=$makeOrder('blocked'.$i,$state);
            $rejected=false;
            try { OrderService::confirmed(['order_id'=>$blocked['order_id']],[]); }
            catch (RequestException $e) { $rejected=!empty($e->data['manual_review']); }
            checkConfirmation($rejected && $queueCount()===$count && OrderModel::where('order_id',$blocked['order_id'])->find()->toArray()===$blocked, 'invalid/refunded/settled state is unchanged and requires review');
        }
        $notificationId=Db::name('device_notify')->insertGetId(['uid'=>0,'content'=>$tag,'status'=>1]);
        $notificationIds[]=$notificationId;
        $notification=\app\common\service\DeviceNotifyService::setPending([
            'id'=>$notificationId, 'status'=>-1,
        ]);
        checkConfirmation($notification['status']===-1 && $queueCount()===$count, 'failed decoding only records notification state without confirming an order');
        $notificationId=Db::name('device_notify')->insertGetId(['uid'=>0,'content'=>$tag,'status'=>1]);
        $notificationIds[]=$notificationId;
        $notification=\app\common\service\DeviceNotifyService::setPending([
            'id'=>$notificationId, 'status'=>2,
        ]);
        checkConfirmation($notification['status']===2 && $queueCount()===$count, 'processed notification does not enqueue order callbacks');
        $foreign=$makeOrder('foreign');
        $rejected=false;
        try { OrderService::confirmed(['order_id'=>$foreign['order_id']],['id'=>1,'type'=>'channel']); }
        catch (RequestException $e) { $rejected=true; }
        checkConfirmation($rejected && (int)OrderModel::find($foreign['id'])['status']===1, 'foreign account denied');

        foreach ([['type'=>'custom_provider'], []] as $i=>$identity) {
            $owned=$makeOrder('chan'.$i, ['trade_uid'=>$uid-1, 'notify_url'=>'']);
            $confirmed=OrderService::confirmed(['order_id'=>$owned['order_id']], array_merge($identity, ['id'=>$uid]));
            checkConfirmation($confirmed['result']==='confirmed' && (int)OrderModel::find($owned['id'])['status']===2,
                'channel ownership permits confirmation with custom or omitted role code');
        }
        $merchantOwned=$makeOrder('trade_only', ['channel_uid'=>$uid-1, 'notify_url'=>'']);
        $rejected=false;
        try { OrderService::confirmed(['order_id'=>$merchantOwned['order_id']], ['id'=>$uid, 'type'=>'merchant']); }
        catch (RequestException $e) { $rejected=true; }
        checkConfirmation($rejected && (int)OrderModel::find($merchantOwned['id'])['status']===1,
            'merchant ownership alone cannot confirm an order');
        $adminOrder=$makeOrder('admin_scope', ['trade_uid'=>$uid-1, 'channel_uid'=>$uid-2, 'notify_url'=>'']);
        checkConfirmation(OrderService::confirmed(['order_id'=>$adminOrder['order_id']], [])['result']==='confirmed',
            'administrator or system confirmation without user identity remains supported');

        $rollback=$makeOrder('rollback');
        TaskService::$fail=true;
        $rejected=false;
        try { OrderService::confirmed(['order_id'=>$rollback['order_id']],[]); }
        catch (\RuntimeException $e) { $rejected=true; }
        TaskService::$fail=false;
        checkConfirmation($rejected && OrderModel::find($rollback['id'])->toArray()===$rollback && $queueCount()===$count, 'queue exception rolls back confirmation');

        $concurrent=$makeOrder('race');
        $workers=[];
        for($i=0;$i<2;$i++) {
            $pipes=[];
            $proc=proc_open([PHP_BINARY,__FILE__,'--worker',$concurrent['order_id'],$file], [['pipe','r'],['pipe','w'],['pipe','w']],$pipes,dirname(__DIR__));
            if (!is_resource($proc)) { throw new \RuntimeException('Cannot start confirmation worker'); }
            fclose($pipes[0]);
            $workers[]=[$proc,$pipes];
        }
        $results=[];
        foreach($workers as [$proc,$pipes]) {
            $out=stream_get_contents($pipes[1]); $err=stream_get_contents($pipes[2]);
            fclose($pipes[1]); fclose($pipes[2]);
            if(proc_close($proc)!==0) { throw new \RuntimeException('Worker failed: '.$err); }
            $results[]=json_decode($out,true)['result'] ?? '';
        }
        sort($results);
        checkConfirmation($results===['already_processed','confirmed'] && $queueCount()===$count+1, 'two concurrent connections enqueue exactly once');
    } finally {
        if($notificationIds) { Db::name('device_notify')->where('id','in',$notificationIds)->delete(); }
        if($orderIds) { Db::name('order')->where('order_id','in',$orderIds)->delete(); }
        if($appId) { Db::name('app_list')->where('id',$appId)->delete(); }
        if(is_file($file)) { unlink($file); }
    }
    echo "Confirmation fixtures cleaned up; no real callback sent.\n";
}
