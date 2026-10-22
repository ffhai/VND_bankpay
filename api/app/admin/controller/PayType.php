<?php

namespace app\admin\controller;

use app\common\basics\CrudController;
use app\admin\service\PayTypeService;
use app\admin\validate\PayTypeValidate;
use think\App;


class PayType extends CrudController
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
        $this->service = new PayTypeService();
        $this->validate = new PayTypeValidate();
    }
}
