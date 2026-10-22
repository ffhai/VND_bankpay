<?php
declare(strict_types=1);
namespace app\admin\controller;

use app\common\basics\CrudController;
use app\admin\service\SmsCodeService;
use app\admin\validate\SmsCodeValidate;
use think\App;

class SmsCode extends CrudController
{
    use \app\common\basics\CrudActions {
        items as public;
        del as public;
    }

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->service = new SmsCodeService();
        $this->validate = new SmsCodeValidate();
    }
}
