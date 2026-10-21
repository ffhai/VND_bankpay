<?php
namespace app\admin\controller;

use app\common\basics\CrudController;
use app\admin\service\BankCardsService;
use app\admin\validate\BankCardsValidate;
use think\App;


class BankCards extends CrudController
{
    // 禁用父类继承的新增入口，包括超级管理员访问。
    public function add()
    {
        abort(404, '不支持新增');
    }

    public function testVietqr()
    {
        return \app\common\utils\AjaxUtils::success(
            \app\common\service\BankCardsService::testVietqr($this->request->param(), $this->userItem)
        );
    }

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->service = new BankCardsService();
        $this->validate = new BankCardsValidate();
    }
}
