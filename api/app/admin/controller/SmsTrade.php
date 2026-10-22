<?php
declare(strict_types=1);
namespace app\admin\controller;

use app\common\basics\Auth;
use app\admin\service\SmsTradeService;
use app\admin\validate\SmsTradeValidate;
use app\common\utils\AjaxUtils;

class SmsTrade extends Auth
{
    public function items()
    {
        return AjaxUtils::success(SmsTradeService::lists($this->request->param(), $this->userItem));
    }

    public function del()
    {
        $ids = $this->request->param('ids/a', []);
        (new SmsTradeValidate())->idsCheck(['ids' => $ids]);
        SmsTradeService::del($ids, $this->userItem);
        return AjaxUtils::success();
    }
}
