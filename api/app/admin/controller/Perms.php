<?php
//下级
namespace app\admin\controller;

use app\common\basics\CrudController;
use app\admin\service\PermsService;
use app\common\utils\AjaxUtils;
use app\admin\validate\PermsValidate;
use think\App;
use think\response\Json;


class Perms extends CrudController
{
    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->service = new PermsService();
        $this->validate = new PermsValidate();
    }
    public function items():Json
    {
        $list = $this->service::getTreeData();
        return AjaxUtils::success($list);
    }
    public function createCurd(){
        $id = $this->request->param('id',0,'intval');
        if ($id <= 0) {
            return AjaxUtils::error('参数错误');
        }
        return AjaxUtils::success($this->service::createCurd($id));
    }
}
