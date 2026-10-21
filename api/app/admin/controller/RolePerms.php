<?php
//下级
namespace app\admin\controller;

use app\common\basics\CrudController;
use app\admin\service\RolePermsService;
use app\common\utils\AjaxUtils;
use think\App;


class RolePerms extends CrudController
{
    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->service = new RolePermsService();
    }
    public function save(){
        $this->service::save($this->request->param());
        return AjaxUtils::success();
    }
}
