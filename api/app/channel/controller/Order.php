<?php

namespace app\channel\controller;

use app\common\basics\CrudController;
use app\channel\service\OrderService;
use app\common\utils\AjaxUtils;
use app\channel\validate\OrderValidate;
use think\App;
use think\facade\Log;
use think\response\Json;


class Order extends CrudController
{
    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->service = new OrderService();
        $this->validate = new OrderValidate();
    }

    public function confirmed(){
        $data = $this->request->param();
        $this->validate->goCheck('confirmed',$data);
        Log::info('确认订单：'.'uid->'.$this->userItem['id'].' '.json_encode($data));
        $result = \app\common\service\OrderService::confirmed($data,$this->userItem);
        return AjaxUtils::success($result['result'] === 'already_processed' ? '订单已处理，无需重复确认' : '确认成功', $result);
    }

    public function notify(){
        $data = $this->request->param();
        $this->validate->goCheck('notify',$data);
        Log::info('回调订单：'.'uid->'.$this->userItem['id'].' '.json_encode($data));
        $ret =  \app\common\service\OrderService::notify($data,$this->userItem);
        if($ret === true){
            return AjaxUtils::success();
        }
        return AjaxUtils::error($ret);
    }
}
