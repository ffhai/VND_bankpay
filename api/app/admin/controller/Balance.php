<?php

declare(strict_types=1);

namespace app\admin\controller;

use app\common\basics\CrudController;
use app\common\service\BalanceService;
use app\common\utils\AjaxUtils;
use think\App;
use think\response\Json;

/**
 * 余额管理（admin 模块，管理员）
 */
class Balance extends CrudController
{
    /**
     * 只读静态参考数据，任何已登录账号都可获取（如交易类型下拉项）
     */
    protected $notNeedPower = ['types'];

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->service = new BalanceService();
    }

    /**
     * 交易类型选项（来源：BalanceService::$typeMap 静态配置）
     */
    public function types(): Json
    {
        return AjaxUtils::success(BalanceService::getTypeOptions());
    }

}
