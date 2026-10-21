<?php
namespace app\admin\controller;

use app\common\basics\CrudController;
use app\admin\service\BankUserService;
use app\admin\validate\BankUserValidate;
use think\App;


class BankUser extends CrudController
{
    // 禁用父类继承的新增入口，包括超级管理员访问。
    public function add()
    {
        abort(404, '不支持新增');
    }

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->service = new BankUserService();
        $this->validate = new BankUserValidate();
    }
}
