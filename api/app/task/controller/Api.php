<?php
namespace app\task\controller;

use app\BaseController;
use app\common\utils\AjaxUtils;
use app\task\service\TaskService;
use think\App;


class Api extends \app\common\basics\Api
{
    public function getTask(){
        $data = TaskService::pop();
        if($data){
            return AjaxUtils::success($data);
        }
        return AjaxUtils::error('暂无任务');
    }
    public function setReport(){
        $task_id = $this->request->param('task_id');
        $data = $this->request->param('data');

        return AjaxUtils::success();
    }
}
