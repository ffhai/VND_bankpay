<?php
namespace app\openapi\controller;

use app\BaseController;
use app\common\model\OrderModel;
use app\common\service\BanksService;
use app\common\service\BankUserService;
use app\common\utils\AjaxUtils;
use think\App;
use think\facade\Cache;
use think\facade\Log;
use think\facade\View;


class Pay extends BaseController
{
    public function index(){
        $oid = $this->request->param('oid');
        if(empty($oid)){
            return View::fetch('error',['msg'=>'参数错误']);
        }
        $orderModel = new OrderModel();
        $orderItem = $orderModel
            ->withJoin([
                'bankcard'=>['bank_number','bank_code','bank_user_id']
            ])
            ->where('order_id','=',$oid)
            ->field('order_id,amount,ps,expire_time')
            ->find();
        if(!$orderItem){
            return View::fetch('error',['msg'=>'订单不存在']);
        }
        $orderItem = $orderItem->toArray();
        $bankInfo = BanksService::getItemByCode($orderItem['bankcard']['bank_code']);
        if(!$bankInfo){
            Log::error("openapi/pay/index获取银行信息失败：".$orderItem['bankcard']['bank_code']);
            return View::fetch('error',['msg'=>'银行异常，请稍后重试！']);
        }
        $bankUser = BankUserService::getItemById($orderItem['bankcard']['bank_user_id']);
        if(!$bankUser){
            Log::error("openapi/pay/index获取户名信息失败：".$orderItem['bankcard']['bank_user_id']);
            return View::fetch('error',['msg'=>'银行异常，请稍后重试！']);
        }

        $qrcodeImg = Cache::get('QRCODE_'.$orderItem['order_id']);
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
                return View::fetch('error',['msg'=>'Qr系统异常，请稍后重试！']);
            }
            if($obj['code'] != '00'){
                Log::error("openapi/pay/index qr失败：".json_encode($obj));
                return View::fetch('error',['msg'=>'QR异常，请稍后重试！']);
            }

            $qrcodeText = $obj['data']['qrCode'];
            $qrcodeImg = $obj['data']['qrDataURL'];
            Cache::set('QRCODE_'.$orderItem['order_id'],$qrcodeImg);
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
