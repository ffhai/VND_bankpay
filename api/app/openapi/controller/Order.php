<?php
namespace app\openapi\controller;

use app\common\basics\Api;
use app\common\enums\StatusEnum;
use app\common\model\BanksModel;
use app\common\model\OrderModel;
use app\common\service\AppSignService;
use app\common\service\OrderService;
use app\common\utils\AjaxUtils;
use app\openapi\validate\OrderValidate;
use app\admin\service\BanksService;
use think\App;
use think\facade\Log;


class Order extends Api
{

    /*
     * 创建订单
     * {"code":1,"msg":"获取成功","data":{"order_id":"N91414505198565510","pay_url":"https:\/\/www.baidu.com"}}
     */
    public function create(){
        $params = $this->request->param();

        (new OrderValidate())->goCheck('createOrder',$params);
        $data=[
            'trade_no'=>$params['trade_no'],
            'pay_type'=>$params['pay_type'],
            'bank_code'=>$params['bank_code'] ?? '',
            'amount'=>$params['amount'],
            'notify_url'=>$params['notify_url'],
            'return_url'=>$params['return_url'] ?? '',
        ];
        //Log::info($data);

        $orderItem = OrderService::createOrder($data,$this->appItem);

        $data = [
            'order_id'=>$orderItem['order_id'],
            'pay_url'=>$orderItem['pay_url'],
        ];
        return AjaxUtils::success($data);
    }

    /*
     * 订单查询
     * {"code":1,"msg":"获取成功","data":{"order_id":"N91414505198565510","trade_no":"1786690251","amount":"200.00","status":1,"pay_time":0}}
     */
    public function query(){
        $params = $this->request->param();
        (new OrderValidate())->goCheck('searchOrder',$params);

        $orderModel = new OrderModel();
        $orderItem = $orderModel->where('order_id','=',$params['order_id'])->find();
        if(!$orderItem){
            return AjaxUtils::error("订单不存在");
        }
        $orderItem = $orderItem->toArray();

        if($orderItem['trade_uid'] != $this->userItem['id']){
            return AjaxUtils::error("无权限查看此订单");
        }
        $data = OrderService::build_order_params($orderItem,$this->appItem);
        $data['sign'] = AppSignService::order_sign($data,$this->appItem['secret']);
        return AjaxUtils::success($data);
    }

    public function banks(){
        $model = new BanksModel();
        $data = $model
            ->where('status','=',StatusEnum::STATUS_1)
            ->where('status_in','=',StatusEnum::STATUS_1)
            ->field('code,short_name')
            ->order('code')
            ->select();
        $items = [];
        if(!$data->isEmpty()){
            $items = $data->toArray();
        }
        return AjaxUtils::success($items);
    }
}
