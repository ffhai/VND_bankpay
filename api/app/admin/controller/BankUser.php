<?php
namespace app\admin\controller;

use app\common\basics\CrudController;
use app\admin\service\BankUserService;
use app\admin\validate\BankUserValidate;
use think\App;


class BankUser extends CrudController
{
    use \app\common\basics\CrudActions {
        items as public;
        edit as public;
        getOptions as public;
    }

    public function del(){
        $ids = $this->request->param('ids/a');
        $this->validate->idsCheck($ids);
        $result = \app\common\service\BankDataService::deleteOrDisable('holder', $ids);
        return \app\common\utils\AjaxUtils::success('已停用' . $result['disabled'] . '项，删除未引用资料' . $result['deleted'] . '项', $result);
    }

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->service = new BankUserService();
        $this->validate = new BankUserValidate();
    }
}
