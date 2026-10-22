<?php
namespace app\channel\controller;

use app\common\basics\CrudController;
use app\channel\service\BankUserService;
use app\channel\validate\BankUserValidate;
use app\common\utils\AjaxUtils;
use think\App;


class BankUser extends CrudController
{
protected function beforeAdd(array $params): array
    {
        $params['status'] = $params['status'] ?? \app\common\enums\StatusEnum::STATUS_1;
        return $params;
    }

    public function del(){
        $ids = $this->request->param('ids/a');
        $this->validate->idsCheck($ids);
        $result = \app\common\service\BankDataService::deleteOrDisable('holder', $ids, (int) $this->userItem['id']);
        return \app\common\utils\AjaxUtils::success('已停用' . $result['disabled'] . '项，删除未引用资料' . $result['deleted'] . '项', $result);
    }

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->service = new BankUserService();
        $this->validate = new BankUserValidate();
    }
}
