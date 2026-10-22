<?php
declare(strict_types=1);
namespace app\channel\controller;

use app\common\basics\Auth;
use app\channel\service\StatisticsService;
use app\common\utils\AjaxUtils;

class Statistics extends Auth
{
    public function orders()
    {
        return AjaxUtils::success(StatisticsService::orders($this->userItem));
    }
}