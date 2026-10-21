<?php
namespace app\channel\controller;

use app\common\basics\CrudController;
use app\channel\service\BankCardsService;
use app\channel\validate\BankCardsValidate;
use think\App;


class BankCards extends CrudController
{
    public function testVietqr()
    {
        return \app\common\utils\AjaxUtils::success(
            \app\common\service\BankCardsService::testVietqr($this->request->param(), $this->userItem, true)
        );
    }

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->service = new BankCardsService();
        $this->validate = new BankCardsValidate();
    }

}
