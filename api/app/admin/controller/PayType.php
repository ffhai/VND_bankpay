<?php

namespace app\admin\controller;

use app\common\basics\CrudController;
use app\admin\service\PayTypeService;
use app\admin\validate\PayTypeValidate;
use think\App;


class PayType extends CrudController
{
    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->service = new PayTypeService();
        $this->validate = new PayTypeValidate();
    }
}
