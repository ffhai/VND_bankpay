<?php
// Stub external transport; validate actual card lookup, ownership and payload without external requests.
class Vietqr {
    public static $params;
    public function generate(array $params) {
        self::$params=$params;
        return ['code'=>'00','data'=>['qrDataURL'=>'data:image/png;base64,TEST']];
    }
}
require dirname(__DIR__).'/vendor/autoload.php';
$app=new \think\App(dirname(__DIR__));
$app->initialize();
use app\common\service\BankCardsService;
use think\facade\Db;
$tag='qr_check_'.bin2hex(random_bytes(4));
$userId=0;
$cardId=0;
try {
    $userId=Db::name('bank_user')->insertGetId(['uid'=>0,'name'=>$tag,'idcard'=>$tag,'status'=>1]);
    $cardId=Db::name('bank_cards')->insertGetId(['uid'=>0,'bank_number'=>'99'.bin2hex(random_bytes(6)),'bank_code'=>'ICB','bank_user_id'=>$userId,'status'=>1]);
    $params=['id'=>$cardId,'amount'=>123,'remark'=>'TEST 123'];
    $result=BankCardsService::testVietqr($params,['id'=>0],true);
    if ($result['qrcode']!=='data:image/png;base64,TEST' || Vietqr::$params['amount']!==123 || Vietqr::$params['accountName']!==$tag || Vietqr::$params['addInfo']!=='TEST 123' || empty(Vietqr::$params['acqId'])) {
        throw new \RuntimeException('Card QR payload mismatch');
    }
    echo "PASS own card, bank BIN, account name, integer amount and remark\n";
    BankCardsService::testVietqr(array_merge($params,['remark'=>'','amount'=>1000]),['id'=>0],true);
    if (Vietqr::$params['addInfo']!=='' || Vietqr::$params['amount']!==1000) {
        throw new \RuntimeException('Empty remark payload mismatch');
    }
    echo "PASS empty remark and amount 1000\n";
    foreach ([[$params,['id'=>-1]], [array_merge($params,['amount'=>1.5]),['id'=>0]], [array_merge($params,['amount'=>0]),['id'=>0]], [array_merge($params,['remark'=>'!']),['id'=>0]]] as $case) {
        $rejected=false;
        try { BankCardsService::testVietqr($case[0],$case[1],true); }
        catch (\app\common\exception\RequestException $e) { $rejected=true; }
        if (!$rejected) { throw new \RuntimeException('Invalid QR request accepted'); }
    }
    echo "PASS foreign card, decimal/zero amount and invalid remark rejected\n";
    if (!isset(\app\common\service\PermsAccessService::getPowerSet('channel')['channel.BankCards.testVietqr'])) {
        throw new \RuntimeException('Missing channel QR permission');
    }
    echo "PASS channel QR permission\n";
} finally {
    if ($cardId) { Db::name('bank_cards')->where('id',$cardId)->delete(); }
    if ($userId) { Db::name('bank_user')->where('id',$userId)->delete(); }
}
