<?php
namespace app\admin\controller;

use app\common\basics\Auth;
use app\admin\service\UploadManageService;
use app\common\basics\Validate;
use app\common\utils\AjaxUtils;

class Upload extends Auth
{
    public function items()
    {
        return AjaxUtils::success(UploadManageService::lists($this->request->param()));
    }

    public function del()
    {
        $ids = $this->request->param('ids/a');
        (new Validate())->idsCheck($ids);
        UploadManageService::del($ids);
        return AjaxUtils::success('删除成功');
    }
}
