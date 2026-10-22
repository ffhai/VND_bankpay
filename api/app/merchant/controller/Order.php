<?php

namespace app\merchant\controller;

use app\common\basics\CrudController;
use app\common\utils\AjaxUtils;
use app\merchant\service\OrderService;
use think\App;
use think\facade\Log;

class Order extends CrudController
{
    use \app\common\basics\CrudActions {
        items as public;
    }

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->service = new OrderService();
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
