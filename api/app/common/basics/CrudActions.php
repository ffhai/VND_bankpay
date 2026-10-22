<?php
declare(strict_types=1);

namespace app\common\basics;

use app\common\utils\AjaxUtils;
use think\response\Json;

/** 控制器通过 as public 显式公开实际使用的 CRUD 操作。 */
trait CrudActions
{
    protected function items():Json
    {
        $params = $this->request->param();
        $list = $this->service::lists($params,$this->userItem);
        return AjaxUtils::success($list);
    }

    protected function add(){
        $params = $this->beforeAdd($this->request->param());
        $this->validate->addCheck($params);
        $this->service::add($params,$this->userItem);
        return AjaxUtils::success();
    }

    protected function edit(){
        $params = $this->beforeEdit($this->request->param());
        $this->validate->editCheck($params);
        $this->service::edit($params,$this->userItem);
        return AjaxUtils::success();
    }

    protected function del(){
        $ids = $this->request->param('ids/a', []);
        $this->validate->idsCheck(['ids' => $ids]);
        $this->service::del($ids,$this->userItem);
        return AjaxUtils::success($ids);
    }

    protected function getOptions(){
        return AjaxUtils::success($this->service::getOptions($this->userItem));
    }

}
