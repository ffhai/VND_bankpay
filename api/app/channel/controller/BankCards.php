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

    public function del(){
        $ids = $this->request->param('ids/a');
        $this->validate->idsCheck($ids);
        $result = \app\common\service\BankDataService::deleteOrDisable('card', $ids, (int) $this->userItem['id']);
        return \app\common\utils\AjaxUtils::success('已停用' . $result['disabled'] . '项，删除未引用资料' . $result['deleted'] . '项', $result);
    }

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->service = new BankCardsService();
        $this->validate = new BankCardsValidate();
    }

}
