<?php
//下级
namespace app\admin\controller;

use app\common\basics\CrudController;
use app\admin\service\RolesService;
use app\admin\validate\RolesValidate;
use think\App;


class Roles extends CrudController
{
    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->service = new RolesService();
        $this->validate = new RolesValidate();
    }
}
