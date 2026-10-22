<?php

namespace app\admin\controller;

use app\common\basics\CrudController;
use app\admin\service\OrderService;
use app\common\utils\AjaxUtils;
use app\admin\validate\OrderValidate;
use think\App;
use think\facade\Log;


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
        Log::info('管理员确认订单：'.'uid->'.$this->userItem['id'].' '.json_encode($data));
        $result = \app\common\service\OrderService::confirmed($data,[]);
        return AjaxUtils::success($result['result'] === 'already_processed' ? '订单已处理，无需重复确认' : '确认成功', $result);
    }

    public function notify(){
        $data = $this->request->param();
        $this->validate->goCheck('notify',$data);
        Log::info('管理员回调订单：'.'uid->'.$this->userItem['id'].' '.json_encode($data));
        $ret =  \app\common\service\OrderService::notify($data,[]);
        if($ret === true){
            return AjaxUtils::success();
        }
        return AjaxUtils::error($ret);
    }

}
