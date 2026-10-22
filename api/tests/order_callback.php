<?php
namespace app\task\service {
    class TaskService {
        const KEY_NOTIFY = 'callback_test';
        public static $jobs = [];
        public static function addTask(string $key, array $params): string {
            self::$jobs[] = $params['data'];
            return 'callback_test';
        }
    }
}
namespace app\common\service {
    function curl_get(string $url) {
        $GLOBALS['callback_test_url'] = $url;
        return 'success';
    }
}
namespace {
    require dirname(__DIR__).'/vendor/autoload.php';
    $app = new \think\App(dirname(__DIR__));
    $app->initialize();
    use think\facade\Db;
    use app\common\service\OrderService;
    use app\task\service\TaskService;
    function callbackCheck($ok, $message) {
        if (!$ok) { throw new \RuntimeException($message); }
        echo 'PASS '.$message.PHP_EOL;
    }
    $config = config('database');
    $config['connections']['mysql']['prefix'] = 'callback_test_';
    config($config, 'database');
    foreach (['order', 'app_list'] as $table) {
        Db::execute('CREATE TEMPORARY TABLE `callback_test_'.$table.'` LIKE `dt_'.$table.'`');
    }
    $scenarios = [
        ['https://example.invalid/callback', []],
        ['https://example.invalid/callback?tenant=7&amount=999&status=-1&appid=old&sign=old', ['tenant'=>'7']],
        ['https://example.invalid/callback?note=%E4%B8%AD%E6%96%87%20%26%20a%2Bb&empty=&zero=0#done', ['note'=>'中文 & a+b','empty'=>'','zero'=>'0']],
        ['https://example.invalid/callback?#done', []],
        ['https://example.invalid/callback?order_id=old&trade_no=old&pay_time=1&time=1&bank_number=old', []],
    ];
    foreach ($scenarios as $i=>$scenario) {
        $uid = 50000 + $i;
        $orderId = 'callback_'.$i;
        Db::name('app_list')->insert(['uid'=>$uid, 'appid'=>'callback_app_'.$i, 'secret'=>'callback-test-only', 'status'=>1]);
        Db::name('order')->insert([
            'order_id'=>$orderId,'trade_no'=>$orderId,'trade_uid'=>$uid,'channel_uid'=>60000,
            'bank_number'=>'123456789','amount'=>123,'pay_type'=>'bank2bank','ps'=>'CALLBK'.$i,
            'status'=>1,'notify_url'=>$scenario[0],
        ]);
        $confirmed=OrderService::confirmed(['order_id'=>$orderId],[]);
        $automatic=TaskService::$jobs[count(TaskService::$jobs)-1]['url'];
        callbackCheck(OrderService::notify(['order_id'=>$orderId],[])===true, 'manual callback succeeds: '.$i);
        foreach (['automatic'=>$automatic,'manual'=>$GLOBALS['callback_test_url']] as $kind=>$url) {
            $parts=parse_url($url);
            parse_str($parts['query'],$params);
            callbackCheck($parts['scheme']==='https' && $parts['host']==='example.invalid' && $parts['path']==='/callback' && substr_count($url,'?')===1, $kind.' callback base URL preserved: '.$i);
            callbackCheck(($parts['fragment'] ?? '')===(parse_url($scenario[0],PHP_URL_FRAGMENT) ?? ''), $kind.' fragment preserved: '.$i);
            foreach ($scenario[1] as $key=>$value) {
                callbackCheck(isset($params[$key]) && $params[$key]===$value, $kind.' custom parameter retained: '.$key);
            }
            callbackCheck($params['appid']==='callback_app_'.$i && $params['order_id']===$orderId && $params['trade_no']===$orderId && $params['amount']==='123' && $params['status']==='2' && $params['bank_number']==='123456789' && (int)$params['pay_time']===$confirmed['pay_time'] && (int)$params['time']>1, $kind.' persisted order fields override URL parameters: '.$i);
            $actualSign=$params['sign'];
            unset($params['sign']); ksort($params);
            $pairs=[];
            foreach ($params as $key=>$value) { if ($value!=='' && $value!=='0') { $pairs[]=$key.'='.$value; } }
            callbackCheck(hash_equals(md5(implode('&',$pairs).'&secret=callback-test-only'),$actualSign), $kind.' signature covers all merged parameters and excludes old sign: '.$i);
        }
    }
    echo "Callbacks isolated with temporary tables, fake HTTP and fake queue.\n";
}
