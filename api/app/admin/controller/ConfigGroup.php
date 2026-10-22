<?php
namespace app\admin\controller;

use app\common\basics\CrudController;
use app\admin\service\ConfigGroupService;
use app\admin\validate\ConfigGroupValidate;
use think\App;


class ConfigGroup extends CrudController
{
    use \app\common\basics\CrudActions {
        items as public;
        add as public;
        edit as public;
        del as public;
        getOptions as public;
    }

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->service = new ConfigGroupService();
        $this->validate = new ConfigGroupValidate();
    }
}
