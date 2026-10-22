<?php
namespace app\admin\controller;

use app\common\basics\CrudController;
use app\admin\service\ConfigGroupService;
use app\admin\validate\ConfigGroupValidate;
use think\App;


class ConfigGroup extends CrudController
{
    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->service = new ConfigGroupService();
        $this->validate = new ConfigGroupValidate();
    }
}
