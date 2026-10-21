<?php
namespace app\task\controller;

use app\common\enums\StatusEnum;
use app\common\model\BankCardsModel;
use app\common\model\DeviceNotifyModel;
use app\common\model\OrderModel;
use app\common\service\BankCardsService;
use app\common\service\DeviceNotifyService;
use app\common\service\OrderService;
use app\common\utils\AjaxUtils;
use think\App;
use think\facade\Log;

//网银
class Online extends \app\common\basics\Api
{
    /*
     * 获取待绑定银行卡
     */
    public function getBank(){
        $bankCardModel = new BankCardsModel();
        $notify_device = $this->request->param('notify_device');
        if(empty($notify_device)){
            return AjaxUtils::error("参数错误");
        }
        //查询是否已经绑定
        $data = $bankCardModel
            ->where('uid','=',$this->userItem['id'])
            ->where('notify_type','=',BankCardsModel::NOTIFY_TYPE_ONLINE)
            ->where('notify_device','=',$notify_device)
            ->where('status','=',StatusEnum::STATUS_1)
            ->field('id,bank_number,bank_code,online_account,online_password,phone_number,notify_device')
            ->find();
        if($data){
            $data = $data->toArray();
            return AjaxUtils::success($data);
        }

        //解除所有绑定
        $bankCardModel
            ->where('uid','=',$this->userItem['id'])
            ->where('notify_type','=',BankCardsModel::NOTIFY_TYPE_ONLINE)
            ->update(['notify_device'=>'']);

        //查询新的待绑定
        $bankCardModel->startTrans();
        $data = $bankCardModel
            ->where('uid','=',$this->userItem['id'])
            ->where('online_account','<>','')
            ->where('online_password','<>','')
            ->where('notify_type','=',BankCardsModel::NOTIFY_TYPE_ONLINE)
            ->where('notify_device','=','')
            ->where('status','=',StatusEnum::STATUS_1)
            ->field('id,bank_number,bank_code,online_account,online_password,phone_number,notify_device')
            ->lock(true)
            ->find();

        if(!$data){
            $bankCardModel->rollback();
            return AjaxUtils::error("暂无");
        }
        $bankCardModel->where('id','=',$data['id'])->update(['notify_device'=>$notify_device]);
        $bankCardModel->commit();

        $data = $data->toArray();
        return AjaxUtils::success($data);
    }

    public function getSmsCode(){
        $bank_card_id = $this->request->param('bank_card_id',0,'intval');
        if($bank_card_id <= 0){
            return AjaxUtils::error("参数错误");
        }
        $bankCardItem = BankCardsService::getItemById($bank_card_id);
        if(!$bankCardItem){
            return AjaxUtils::error("记录不存在");
        }
        if($bankCardItem['uid'] != $this->userItem['id']){
            return AjaxUtils::error("仅支持本账号上传卡号");
        }
        $phone_number = $bankCardItem['phone_number'];
        if(empty($phone_number)){
            return AjaxUtils::error("该卡号未绑定手机号码");
        }
        $deviceNotifyModel = new DeviceNotifyModel();
        $deviceNotifyItem = $deviceNotifyModel
            ->where('uid','=',$this->userItem['id'])
            ->where('type','=',DeviceNotifyModel::TYPE_SMS_CODE)
            ->where('phone_number','=',$phone_number)
            ->where('time','>',time() - 10)
            ->order('id desc')
            ->field('sms_code')
            ->find();

        return AjaxUtils::success(['sms_code'=>$deviceNotifyItem['sms_code'] ?? '']);
    }

    public function upTransactions(){
        $params = $this->request->param();
        //'bank_card_id' => '1',
        //'data' => '[{"date": "11/10/2026", "content": "6284IBT1aJWXZ4Z6.ABCD1234.20261011.192723.000005013802.VO THI TUYET HONG.970433", "reference": "Reference number: 5414 - 32443", "amount": "+ 123,000 VND"}, {"date": "11/10/2026", "content": "6284IBT1aJWXZB2X.VO THI TUYET HONG chuyen tien.20261011.192634.000005013802.VO THI TUYET HONG.970433", "reference": "Reference number: 5423 - 27978", "amount": "+ 10,000 VND"}, {"date": "11/10/2026", "content": "6284IBT1aJWX6H1N.VO THI TUYET HONG chuyen tien.20261011.191349.000005013802.VO THI TUYET HONG.970433", "reference": "Reference number: 5424 - 63325", "amount": "+ 10,000 VND"}]'
        $balance = \app\common\utils\AmountUtils::integer($this->request->param('balance', 0));
        $bank_card_id = $this->request->param('bank_card_id',0,'intval');
        $transactions = $this->request->param('data','[]');
        $transactions = json_decode($transactions,true);

        $bankCardItem = BankCardsService::getItemById($bank_card_id);
        if(!$bankCardItem){
            return AjaxUtils::error("卡号不存在");
        }
        if($bankCardItem['uid'] != $this->userItem['id']){
            return AjaxUtils::error("无权限回调其他码商");
        }

        //更新余额
        if($balance >= 0){
            BankCardsModel::update([
                'balance'=>$balance
            ],['id'=>$bankCardItem['id']]);
        }

        $result = [];
        foreach ($transactions as $item) {
            if (preg_match('/^[^\.]+\.(.+)\.(\d{8})\.(\d{6})\./U', $item['content'], $matches)) {
                $remark = $matches[1];
                $d = $matches[2]; // 例：20261011
                $t = $matches[3]; // 例：192723

                // 格式化时间为 YYYY-MM-DD HH:MM:SS
                $datetime = sprintf(
                    '%s-%s-%s %s:%s:%s',
                    substr($d, 0, 4), // 年
                    substr($d, 4, 2), // 月
                    substr($d, 6, 2), // 日
                    substr($t, 0, 2), // 时
                    substr($t, 2, 2), // 分
                    substr($t, 4, 2)  // 秒
                );

                // 2. 解析与清理金额（去除符号、千分位逗号及单位，支持负数）
                // 提取正负号和数字部分
                preg_match('/([-+]?)\s*([\d,]+)/', $item['amount'], $amountMatches);
                $sign = $amountMatches[1] === '-' ? -1 : 1;
                $num = \app\common\utils\AmountUtils::integer(str_replace(',', '', $amountMatches[2]));
                $amount = $sign * $num;

                $result[] = [
                    'remark'   => $remark,
                    'datetime' => $datetime,
                    'amount'   => $amount
                ];
            }
        }
        Log::info($result);

        //匹配订单
        $orderModel = new OrderModel();
        $manualReview = [];
        foreach($result as $item){
            if(strlen($item['remark']) == config('order.ps_length')){
                $orderItem = $orderModel->where('ps','=',$item['remark'])->field('order_id')->order('id desc')->find();
                //&& $orderItem['status'] == OrderModel::STATUS_PAYING
                if($orderItem ){
                    Log::info('网银匹配订单：' . $orderItem['order_id']);
                    try {
                        OrderService::confirmed(['order_id'=>$orderItem['order_id']],[]);
                    } catch (\app\common\exception\RequestException $e) {
                        if (empty($e->data['manual_review'])) {
                            throw $e;
                        }
                        $manualReview[] = $e->data;
                        Log::warning('网银确认异常：' . json_encode($e->data));
                    }
                }
            }
        }
        return AjaxUtils::success(['sleep'=>config('order.online_transaction_sleep'), 'manual_review'=>$manualReview]);
    }


}
