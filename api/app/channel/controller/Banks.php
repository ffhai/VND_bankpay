<?php
namespace app\channel\controller;

use app\common\basics\CrudController;
use app\admin\service\BanksService;
use app\admin\validate\BanksValidate;
use think\App;


class Banks extends CrudController
{
    use \app\common\basics\CrudActions {
        getOptions as public;
    }

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->service = new BanksService();
        $this->validate = new BanksValidate();
    }
}
