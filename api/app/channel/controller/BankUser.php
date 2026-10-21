<?php
namespace app\channel\controller;

use app\common\basics\CrudController;
use app\channel\service\BankUserService;
use app\channel\validate\BankUserValidate;
use app\common\utils\AjaxUtils;
use think\App;
use think\response\Json;


class BankUser extends CrudController
{
    protected function beforeAdd(array $params): array
    {
        $params['status'] = $params['status'] ?? \app\common\enums\StatusEnum::STATUS_1;
        return $params;
    }

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->service = new BankUserService();
        $this->validate = new BankUserValidate();
    }
}
