<?php
namespace app\openapi\controller;

use app\BaseController;
use app\common\model\OrderModel;
use app\common\utils\AjaxUtils;
use think\App;
use think\facade\Cache;
use think\facade\Log;
use think\facade\View;


class Pay extends BaseController
{
    protected function initialize()
    {
        $locale = \think\facade\Lang::getLangSet();
        $messages = [];
        foreach (['copy_failed', 'copy_unsupported', 'copied', 'copy_success', 'expired', 'payment_success'] as $key) {
            $messages[$key] = \think\facade\Lang::get($key);
        }
        View::assign('locale', $locale);
        View::assign('i18n_json', json_encode($messages, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT));
    }

    public function index(){
        $oid = $this->request->param('oid');
        if(empty($oid)){
            return View::fetch('error',['msg'=>\think\facade\Lang::get('参数错误')]);
        }
        $orderModel = new OrderModel();
        $orderItem = $orderModel->where('order_id', $oid)->find();
        if (!$orderItem) {
            return View::fetch('error', ['msg'=>\think\facade\Lang::get('订单不存在')]);
        }
        $orderItem = $orderItem->toArray();
        if ($orderItem['snapshot_source'] === 'missing' || empty($orderItem['bank_bin']) || empty($orderItem['account_name'])) {
            return View::fetch('error', ['msg'=>\think\facade\Lang::get('历史收款快照缺失，请联系管理员人工核查')]);
        }
        if ((int) $orderItem['status'] !== OrderModel::STATUS_PAYING || (int) $orderItem['expire_time'] <= time()) {
            return View::fetch('error', ['msg'=>\think\facade\Lang::get('订单已完成、关闭或过期，不可继续付款')]);
        }
        $bankInfo = ['bin'=>$orderItem['bank_bin'], 'name'=>$orderItem['bank_name'], 'short_name'=>$orderItem['bank_short_name']];
        $bankUser = ['name'=>$orderItem['account_name']];
        $orderItem['bankcard'] = ['bank_number'=>$orderItem['bank_number']];

        $qrcodeImg = Cache::get('QRCODE_SNAPSHOT_'.$orderItem['order_id']);
        if(empty($qrcodeImg)){
            $vietqr = new \Vietqr();
            $obj = $vietqr->generate([
                'accountNo'=>$orderItem['bankcard']['bank_number'],
                'accountName'=>$bankUser['name'],
                'acqId'=>$bankInfo['bin'],
                'addInfo'=>$orderItem['ps'],
                'amount'=>$orderItem['amount'],
            ]);
            if(!$obj){
                Log::error($obj);
                return View::fetch('error',['msg'=>\think\facade\Lang::get('Qr系统异常，请稍后重试！')]);
            }
            if($obj['code'] != '00'){
                Log::error("openapi/pay/index qr失败：".json_encode($obj));
                return View::fetch('error',['msg'=>\think\facade\Lang::get('QR异常，请稍后重试！')]);
            }

            $qrcodeText = $obj['data']['qrCode'];
            $qrcodeImg = $obj['data']['qrDataURL'];
            Cache::set('QRCODE_SNAPSHOT_'.$orderItem['order_id'],$qrcodeImg,max(1, $orderItem['expire_time'] - time()));
        }

        View::assign('qrcode',$qrcodeImg);
        View::assign('bankinfo',$bankInfo);
        View::assign('order',$orderItem);
        View::assign('bankuser',$bankUser);
        View::assign('timeout',$orderItem['expire_time'] - time());
        return View::fetch();

    }

    public function result(){
        $order_id = $this->request->param('oid');
        if(empty($order_id)){
            return AjaxUtils::error('参数错误');
        }
        $orderModel = new OrderModel();
        $orderItem = $orderModel->where('order_id','=',$order_id)->field('status')->find();
        if(!$orderItem){
            return AjaxUtils::error('订单不存在');
        }else{
            $status = 'paying';
            if($orderItem['status'] == OrderModel::STATUS_PAYED){
                $status = 'payed';
            }
            if($orderItem['status'] == OrderModel::STATUS_FAIL){
                $status = 'fail';
            }
            return AjaxUtils::success(['status'=>$status]);
        }
    }

}
