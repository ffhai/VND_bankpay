<?php
declare(strict_types=1);
namespace app\admin\controller;

use app\common\basics\Auth;
use app\admin\service\StatisticsService;
use app\common\utils\AjaxUtils;

class Statistics extends Auth
{
    public function accounts()
    {
        return AjaxUtils::success(StatisticsService::accounts());
    }

    public function orders()
    {
        return AjaxUtils::success(StatisticsService::orders($this->userItem));
    }
}
