<?php
declare(strict_types=1);
namespace app\merchant\controller;

use app\common\basics\Auth;
use app\merchant\service\StatisticsService;
use app\common\utils\AjaxUtils;

class Statistics extends Auth
{
    public function orders()
    {
        return AjaxUtils::success(StatisticsService::orders($this->userItem));
    }
}