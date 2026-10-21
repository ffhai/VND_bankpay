<?php

declare (strict_types = 1);

namespace app\common\service;


use app\common\basics\Service;
use app\common\enums\StatusEnum;
use app\common\exception\RequestException;
use app\common\model\AppListModel;
use app\common\model\BankCardsModel;
use app\common\model\DeviceNotifyModel;
use app\common\model\OrderModel;

class DeviceNotifyService extends Service
{
    public static function addContent(string $phone_number, string $content, array $userItem)
    {
        DeviceNotifyModel::create([
            'uid'=>$userItem['id'] ?? 0,
            'content'=>$content,
            'phone_number'=>$phone_number,
            'time'=>time(),
        ]);
    }

    public static function getPending(){
        $model = new DeviceNotifyModel();
        $items = $model->where('status','=',StatusEnum::STATUS_0)->field('id,content,time')->order('id')->select();
        if($items->isEmpty()){
            return [];
        }
        return $items->toArray();
    }
    public static function setPending(array $params){
        if (isset($params['balance']) && filter_var($params['balance'], FILTER_VALIDATE_INT) === false) {
            throw new RequestException('金额必须为整数');
        }
        $model = new DeviceNotifyModel();
        $orderModel = new OrderModel();

        $data = [
            'id'=>$params['id'] ?? 0,
            'type'=>$params['type'] ?? '',
            'balance'=>$params['balance'] ?? 0,
            'card_number'=>$params['card_number'] ?? '',
            'remark'=>$params['remark'] ?? '',
            'sms_code'=>$params['sms_code'] ?? '',
        ];
        if($data['id'] <= 0){
            return;
        }

        //银行推送
        if($data['type'] == DeviceNotifyModel::TYPE_BANK_TRADE){
            if($data['balance'] == 0 && empty($data['card_number']) && empty($data['remark'])){
                $data['status'] = DeviceNotifyModel::STATUS_OTHER;
            }else{
                $orderItem = false;
                //附言匹配
                if(!$orderItem && strlen($data['remark']) == config('order.ps_length')){
                    $orderItem = $orderModel->where('ps','=',$data['remark'])->field('order_id')->order('id desc')->find();
                }
                //金额、卡号匹配
                if(!$orderItem && !empty($data['card_number']) && $data['balance'] > 0){
                    $bankCardItem = BankCardsService::getItemByBankNumber($data['card_number']);
                    if($bankCardItem){
                        $amount = $data['balance'] - $bankCardItem['balance'];
                        if($amount > 0){
                            $orderItem = $orderModel
                                ->where('bank_number','=',$data['card_number'])
                                ->where('amount','=',$amount)
                                ->field('order_id')
                                ->order('id desc')->find();
                        }
                    }
                }

                if($orderItem){
                    //匹配成功
                    $data['status'] = DeviceNotifyModel::STATUS_SUCC;
                    $data['order_id'] = $orderItem['order_id'];

                    OrderService::confirmed(['order_id'=>$orderItem['order_id']],[]);
                }else{
                    //未匹配到订单
                    $data['status'] = DeviceNotifyModel::STATUS_FIAL;
                }

                //更新卡号余额
                if(!empty($data['card_number']) && $data['balance'] > 0){
                    BankCardsModel::update([
                        'balance'=>$data['balance'],
                        'update_time'=>time()
                    ],['bank_number'=>$data['card_number']]);
                }
            }
        }

        //短信
        if($data['type'] == DeviceNotifyModel::TYPE_SMS_CODE){
            if(!empty($data['sms_code'])){
                $data['status'] = DeviceNotifyModel::STATUS_SUCC;
            }else{
                $data['status'] = DeviceNotifyModel::STATUS_FIAL;
            }
        }

        if($data['type'] == DeviceNotifyModel::TYPE_OTHER){
            $data['status'] = DeviceNotifyModel::STATUS_OTHER;
        }

        $model->where('id','=',$data['id'])->update($data);
        return $data;
    }
}
