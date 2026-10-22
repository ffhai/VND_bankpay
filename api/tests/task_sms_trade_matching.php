<?php
namespace app\task\service {
    // 隔离回调队列，验证确认行为，不发送真实商户回调。
    class TaskService {
        const KEY_NOTIFY = 'sms_matching_test';
        public static $fail = false;
        public static $queued = [];
        public static function addTask(string $key, array $params): string {
            if (self::$fail) { throw new \RuntimeException('Test callback queue failure'); }
            self::$queued[] = $params;
            return 'test';
        }
    }
}
namespace {
    require dirname(__DIR__).'/vendor/autoload.php';
    $app = new \think\App(dirname(__DIR__));
    $app->initialize();
    $app->debug(true);

    use app\common\enums\StatusEnum;
    use app\task\service\SmsTradeService;
    use app\task\service\TaskService;
    use think\facade\Db;

    function checkSmsMatch($ok, $message) {
        if (!$ok) { throw new \RuntimeException($message); }
        echo 'PASS '.$message.PHP_EOL;
    }

    $config = config('database');
    $config['connections']['mysql']['prefix'] = 'sms_match_test_';
    config($config, 'database');
    config(['ps_length'=>8], 'order');
    foreach (['device_notify','sms_trade','bank_cards','order','app_list'] as $table) {
        Db::execute('CREATE TEMPORARY TABLE `sms_match_test_'.$table.'` LIKE `dt_'.$table.'`');
    }
    $sequence = 0;
    $push = function ($oldBalance, $newBalance, $remark) use (&$sequence) {
        $bank = '800'.str_pad((string)++$sequence, 8, '0', STR_PAD_LEFT);
        Db::name('bank_cards')->insert(['bank_number'=>$bank,'balance'=>$oldBalance]);
        $id = Db::name('device_notify')->insertGetId(['content'=>'raw test push','status'=>StatusEnum::STATUS_1]);
        return ['notify_id'=>$id,'bank_number'=>$bank,'balance'=>$newBalance,'remark'=>$remark];
    };
    $orderSequence = 0;
    $order = function (array $payload, int $amount, array $extra = []) use (&$orderSequence) {
        $id = ++$orderSequence;
        $data = array_merge([
            'order_id'=>'sms_match_'.$id,'trade_no'=>'sms_match_'.$id,'ps'=>sprintf('T%07d',$id),
            'bank_number'=>$payload['bank_number'],'amount'=>$amount,'status'=>1,'settlement_time'=>0,
        ], $extra);
        Db::name('order')->insert($data);
        return $data['order_id'];
    };
    $status = function ($orderId) { return (int)Db::name('order')->where('order_id',$orderId)->value('status'); };
    $send = function ($payload) {
        $response = SmsTradeService::receive($payload);
        checkSmsMatch((int)Db::name('device_notify')->where('id',$payload['notify_id'])->value('status') === StatusEnum::STATUS_2, 'decoded source marked processed');
        checkSmsMatch((int)Db::name('bank_cards')->where('bank_number',$payload['bank_number'])->value('balance') === (int)$payload['balance'], 'latest bank balance persisted');
        return Db::name('sms_trade')->where('id',$response['id'])->find();
    };

    $payload = $push(1000, 1300, 'REF001AA');
    $wrongAmount = $order($payload, 301, ['ps'=>$payload['remark']]);
    $fallback = $order($payload, 300);
    $trade = $send($payload);
    checkSmsMatch((int)$trade['status'] === StatusEnum::STATUS_2 && $trade['order_id'] === '' && $status($wrongAmount) === 1 && $status($fallback) === 1, 'valid-length remark with wrong change fails without fallback');

    $payload = $push(1000, 1300, 'MISSING1');
    $fallback = $order($payload, 300);
    $trade = $send($payload);
    checkSmsMatch((int)$trade['status'] === StatusEnum::STATUS_2 && $trade['order_id'] === '' && $status($fallback) === 1, 'valid-length unknown remark fails without matching by card');

    $payload = $push(1000, 1300, 'short');
    $older = $order($payload, 300);
    $latest = $order($payload, 300);
    $wrongAmount = $order($payload, 301);
    $wrongBank = $order($payload, 300, ['bank_number'=>'99999999999']);
    $closed = $order($payload, 300, ['status'=>-1]);
    $paid = $order($payload, 300, ['status'=>2]);
    $settled = $order($payload, 300, ['settlement_time'=>time()]);
    $trade = $send($payload);
    checkSmsMatch($trade['order_id'] === $latest && (int)$trade['status'] === StatusEnum::STATUS_1 && $status($latest) === 2 && $status($older) === 1, 'short remark matches only latest pending order for the same card and change');
    checkSmsMatch($status($wrongAmount) === 1 && $status($wrongBank) === 1 && $status($closed) === -1 && $status($paid) === 2 && $status($settled) === 1, 'wrong amount/card and closed/paid/settled candidates are not confirmed');

    $payload = $push(1000, 1300, 'REF002AA');
    $closed = $order($payload, 300, ['ps'=>$payload['remark'],'status'=>-1]);
    $trade = $send($payload);
    checkSmsMatch((int)$trade['status'] === StatusEnum::STATUS_2 && $status($closed) === -1, 'closed order cannot be confirmed through its remark');

    $payload = $push(1000, 1300, 'too-long-remark');
    $trade = $send($payload);
    checkSmsMatch((int)$trade['status'] === StatusEnum::STATUS_2 && $trade['order_id'] === '', 'no card/amount candidate records match failure');

    config(['ps_length'=>6], 'order');
    $payload = $push(1000, 1300, 'REF006');
    $byRemark = $order($payload, 300, ['ps'=>$payload['remark']]);
    $newer = $order($payload, 300);
    $trade = $send($payload);
    checkSmsMatch($trade['order_id'] === $byRemark && $status($byRemark) === 2 && $status($newer) === 1, 'remark length follows order.ps_length configuration');
    config(['ps_length'=>8], 'order');

    $payload = $push(1000, 1500, 'ROLLBACK');
    $rollbackOrder = $order($payload, 500, ['ps'=>$payload['remark']]);
    $rolledBack = false;
    try {
        Db::transaction(function () use ($payload) {
            SmsTradeService::receive($payload);
            throw new \RuntimeException('Test failure after all database writes');
        });
    } catch (\RuntimeException $e) { $rolledBack = true; }
    checkSmsMatch($rolledBack && !Db::name('sms_trade')->where('notify_id',$payload['notify_id'])->find() && $status($rollbackOrder) === 1 && (int)Db::name('device_notify')->where('id',$payload['notify_id'])->value('status') === StatusEnum::STATUS_1 && (int)Db::name('bank_cards')->where('bank_number',$payload['bank_number'])->value('balance') === 1000, 'InnoDB rollback removes inserted trade and restores confirmed order, source status and card balance');

    Db::name('app_list')->insert(['uid'=>991,'appid'=>'sms_match_app','secret'=>'test-only','status'=>1]);
    $payload = $push(1000, 1400, 'retry');
    $original = $order($payload, 400, ['trade_uid'=>991,'notify_url'=>'https://example.invalid/callback']);
    TaskService::$fail = true;
    $rejected = false;
    try { SmsTradeService::receive($payload); } catch (\RuntimeException $e) { $rejected = true; }
    TaskService::$fail = false;
    $persisted = Db::name('sms_trade')->where('notify_id',$payload['notify_id'])->find();
    checkSmsMatch($rejected && !$persisted && $status($original) === 1 && (int)Db::name('device_notify')->where('id',$payload['notify_id'])->value('status') === StatusEnum::STATUS_1 && (int)Db::name('bank_cards')->where('bank_number',$payload['bank_number'])->value('balance') === 1000, 'confirmation exception leaves no partial trade and rolls back order, source status and bank balance');
    $newer = $order($payload, 400, ['trade_uid'=>991,'notify_url'=>'https://example.invalid/callback']);
    $trade = $send($payload);
    checkSmsMatch($trade['order_id'] === $newer && (int)$trade['status'] === StatusEnum::STATUS_1 && $status($original) === 1 && $status($newer) === 2 && count(TaskService::$queued) === 1, 'retry after rollback matches the latest eligible order and queues one callback');
    SmsTradeService::receive($payload);
    checkSmsMatch(count(TaskService::$queued) === 1 && Db::name('sms_trade')->where('notify_id',$payload['notify_id'])->count() === 1, 'duplicate successful upload does not enqueue callback again');
}
