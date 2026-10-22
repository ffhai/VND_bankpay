<?php
namespace app\admin\controller;

use app\common\basics\CrudController;
use app\admin\service\BanksService;
use app\admin\validate\BanksValidate;
use think\App;


class Banks extends CrudController
{
    use \app\common\basics\CrudActions {
        items as public;
        add as public;
        edit as public;
        getOptions as public;
    }

    public function del(){
        $ids = $this->request->param('ids/a');
        $this->validate->idsCheck($ids);
        $result = \app\common\service\BankDataService::deleteOrDisable('bank', $ids);
        return \app\common\utils\AjaxUtils::success('已停用' . $result['disabled'] . '项，删除未引用资料' . $result['deleted'] . '项', $result);
    }

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->service = new BanksService();
        $this->validate = new BanksValidate();
    }
}
