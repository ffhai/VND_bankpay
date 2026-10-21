<?php
//下级
namespace app\admin\controller;

use app\common\basics\CrudController;
use app\common\utils\AjaxUtils;
use app\admin\service\DeviceNotifyService;
use app\admin\validate\DeviceNotifyValidate;
use think\App;


class DeviceNotify extends CrudController
{
    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->service = new DeviceNotifyService();
        $this->validate = new DeviceNotifyValidate();
    }

    public function reset(){
        $params = $this->request->param();
        $this->validate->goCheck('reset',$params);
        $this->service::reset($params);
        return AjaxUtils::success();
    }


}
