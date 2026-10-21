<?php
require dirname(__DIR__).'/vendor/autoload.php';
$app=new \think\App(dirname(__DIR__));
$app->initialize();
use think\facade\Db;
use app\common\model\UserModel;
use app\common\model\BanksModel;
use app\common\model\BankUserModel;
use app\common\model\BankCardsModel;
use app\common\model\OrderModel;
use app\common\service\OrderService;
use app\common\service\BankDataService;
use app\common\exception\RequestException;
function verifyIntegrity($ok,$message){if(!$ok){throw new \RuntimeException($message);} echo 'PASS '.$message."\n";}
function rejectIntegrity($callback,$message){$rejected=false;try{$callback();}catch(RequestException $e){$rejected=true;}verifyIntegrity($rejected,$message);}
foreach(Db::query("SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN ('dt_bank_cards','dt_bank_user','dt_device_notify','dt_order','dt_user','dt_balance_log')") as $table){
    verifyIntegrity($table['ENGINE']==='InnoDB','transactional table uses InnoDB');
}
Db::startTrans();
$tag='integrity_'.bin2hex(random_bytes(4));
try{
    $channel=UserModel::create(['type'=>'channel','username'=>$tag.'_c','password'=>'','balance'=>10000,'status'=>1]);
    $other=UserModel::create(['type'=>'channel','username'=>$tag.'_x','password'=>'','balance'=>10000,'status'=>1]);
    $merchant=UserModel::create(['type'=>'merchant','username'=>$tag.'_m','password'=>'','balance'=>0,'status'=>1]);
    do{$bin=(string)random_int(800000,899999);}while(BanksModel::where('bin',$bin)->find());
    $bank=BanksModel::create(['code'=>'T'.bin2hex(random_bytes(4)),'bin'=>$bin,'name'=>'Original Bank','short_name'=>'Original','status'=>1,'status_in'=>1,'status_out'=>1]);
    $holder=BankUserModel::create(['uid'=>$channel['id'],'name'=>'ORIGINAL HOLDER','idcard'=>$tag,'status'=>1]);
    $foreignHolder=BankUserModel::create(['uid'=>$other['id'],'name'=>'OTHER HOLDER','idcard'=>$tag,'status'=>1]);
    $number='88'.(string)random_int(100000000,999999999);
    $data=['bank_number'=>$number,'bank_code'=>$bank['code'],'bank_user_id'=>$holder['id'],'balance'=>0,'online_account'=>' account ','online_password'=>' pass word ','phone_number'=>' 09 12 ','notify_type'=>'app','notify_device'=>'','limit_in_max'=>0,'limit_out_max'=>0,'status'=>1];
    \app\channel\service\BankCardsService::add($data,$channel->toArray());
    $card=BankCardsModel::where('bank_number',$number)->find();
    verifyIntegrity($card && $card['online_password']==='password','card creation validates references and removes spaces');
    rejectIntegrity(function()use($data,$channel,$foreignHolder){\app\channel\service\BankCardsService::add(array_merge($data,['bank_number'=>'77'.random_int(100000000,999999999),'bank_user_id'=>$foreignHolder['id']]),$channel->toArray());},'foreign holder rejected');
    rejectIntegrity(function()use($data,$other,$foreignHolder){\app\channel\service\BankCardsService::add(array_merge($data,['bank_user_id'=>$foreignHolder['id']]),$other->toArray());},'card number unique across channels');
    BanksModel::where('id',$bank['id'])->update(['status'=>-1]);
    rejectIntegrity(function()use($data,$channel){\app\channel\service\BankCardsService::add(array_merge($data,['bank_number'=>'66'.random_int(100000000,999999999)]),$channel->toArray());},'disabled bank rejected for active card');
    BanksModel::where('id',$bank['id'])->update(['status'=>1]);
    $order=OrderService::createOrder(['trade_no'=>$tag,'pay_type'=>'bank2bank','bank_code'=>$bank['code'],'amount'=>123,'notify_url'=>'https://example.invalid/callback','return_url'=>''],['uid'=>$merchant['id']]);
    $saved=OrderModel::where('order_id',$order['order_id'])->find()->toArray();
    verifyIntegrity($saved['bank_card_id']==$card['id'] && $saved['bank_bin']===$bin && $saved['account_name']==='ORIGINAL HOLDER' && $saved['snapshot_source']==='created','order saves complete original bank/card/holder snapshot');
    BankUserModel::where('id',$holder['id'])->update(['name'=>'CHANGED HOLDER']);
    BanksModel::where('id',$bank['id'])->update(['bin'=>(string)random_int(700000,799999),'short_name'=>'Changed']);
    $unchanged=OrderModel::find($saved['id'])->toArray();
    verifyIntegrity($unchanged['account_name']==='ORIGINAL HOLDER' && $unchanged['bank_bin']===$bin && $unchanged['bank_short_name']==='Original','reference edits cannot alter historical snapshot');
    $view=new class {
        public $data=[];
        public function assign($name,$value){$this->data[$name]=$value;}
        public function fetch($template='',$vars=[]){return ['template'=>$template,'data'=>array_merge($this->data,$vars)];}
    };
    $app->instance('view',$view);
    $renderPayment=function($id)use($app){
        $request=(new \think\Request())->withGet(['oid'=>$id]);
        $request->setMethod('GET');
        $controller=new \app\openapi\controller\Pay($app);
        $property=new \ReflectionProperty(\app\BaseController::class,'request');
        $property->setAccessible(true);
        $property->setValue($controller,$request);
        return $controller->index();
    };
    $qrKey='QRCODE_SNAPSHOT_'.$order['order_id'];
    \think\facade\Cache::set($qrKey,'TEST_IMAGE',60);
    $page=$renderPayment($order['order_id']);
    verifyIntegrity($page['data']['bankinfo']['bin']===$bin && $page['data']['bankuser']['name']==='ORIGINAL HOLDER' && $page['data']['order']['bankcard']['bank_number']===$number,'payment controller reads frozen snapshots, not current base data');
    $legacy=OrderModel::create(['order_id'=>$tag.'_legacy','trade_no'=>$tag.'_legacy','ps'=>strtoupper(bin2hex(random_bytes(4))),'status'=>1,'expire_time'=>time()+600,'snapshot_source'=>'missing']);
    $page=$renderPayment($legacy['order_id']);
    verifyIntegrity($page['template']==='error','missing historical snapshots cannot generate a payment QR');
    rejectIntegrity(function()use($data,$channel,$card){\app\channel\service\BankCardsService::edit(array_merge($data,['id'=>$card['id'],'bank_number'=>'55'.random_int(100000000,999999999)]),$channel->toArray());},'referenced account number cannot be reassigned');
    $balanceBefore=(int)UserModel::find($channel['id'])['balance'];
    OrderModel::where('id', $saved['id'])->update(['expire_time'=>time()-3600]);
    $cancelled=OrderService::expireOrder(['order_id'=>$order['order_id']]);
    verifyIntegrity($cancelled['result']==='refunded' && (int)UserModel::find($channel['id'])['balance']===$balanceBefore+123 && OrderModel::find($saved['id']),'timeout refunds integer amount and preserves order');
    $again=OrderService::expireOrder(['order_id'=>$order['order_id']]);
    verifyIntegrity($again['result']==='already_processed' && (int)UserModel::find($channel['id'])['balance']===10000 && Db::name('balance_log')->where('remark',$order['order_id'])->where('type','CancelOrder')->count()===1,'repeat timeout processing writes one refund only');
    rejectIntegrity(function()use($saved){\app\admin\service\OrderService::del([$saved['id']],[]);},'physical order deletion rejected');
    $result=BankDataService::deleteOrDisable('card',[$card['id']],(int)$channel['id']);
    verifyIntegrity($result['disabled']===1 && (int)BankCardsModel::find($card['id'])['status']===-1,'referenced card disabled instead of deleted');
    $result=BankDataService::deleteOrDisable('holder',[$holder['id']],(int)$channel['id']);
    verifyIntegrity($result['disabled']===1 && BankUserModel::find($holder['id']),'referenced holder retained');
    $result=BankDataService::deleteOrDisable('bank',[$bank['id']]);
    verifyIntegrity($result['disabled']===1 && BanksModel::find($bank['id']),'referenced bank retained');
    $unused=BankUserModel::create(['uid'=>$channel['id'],'name'=>'UNUSED','idcard'=>$tag.'_u','status'=>1]);
    $result=BankDataService::deleteOrDisable('holder',[$unused['id']],(int)$channel['id']);
    verifyIntegrity($result['deleted']===1 && !BankUserModel::find($unused['id']),'unreferenced holder can be deleted');
    $paid=OrderModel::create(['order_id'=>$tag.'_paid','trade_no'=>$tag.'_paid','ps'=>strtoupper(bin2hex(random_bytes(4))),'channel_uid'=>$channel['id'],'trade_uid'=>$merchant['id'],'status'=>2,'amount'=>123,'pay_type'=>'bank2bank','settlement_time'=>0]);
    rejectIntegrity(function()use($paid){OrderService::expireOrder(['order_id'=>$paid['order_id']]);},'paid order cannot be refunded by timeout');
    $closed=OrderModel::create(['order_id'=>$tag.'_closed','trade_no'=>$tag.'_closed','ps'=>strtoupper(bin2hex(random_bytes(4))),'channel_uid'=>$channel['id'],'status'=>-1,'amount'=>123,'pay_type'=>'bank2bank']);
    rejectIntegrity(function()use($closed){OrderService::expireOrder(['order_id'=>$closed['order_id']]);},'legacy closed order without refund evidence requires review');
}finally{
    Db::rollback();
    if(isset($qrKey)){\think\facade\Cache::delete($qrKey);}
}
verifyIntegrity(!UserModel::where('username',$tag.'_c')->find(),'all test fixtures rolled back');

