<?php
declare(strict_types=1);
namespace app\admin\controller;

use app\common\basics\CrudController;
use app\admin\service\SmsTradeService;
use app\admin\validate\SmsTradeValidate;
use think\App;

class SmsTrade extends CrudController
{
    use \app\common\basics\CrudActions {
        items as public;
        del as public;
    }

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->service = new SmsTradeService();
        $this->validate = new SmsTradeValidate();
    }
}
