<?php
namespace app\task\controller;
use app\common\model\OrderModel;
use think\facade\Log;


use app\common\utils\AjaxUtils;
use app\task\service\TaskService;


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
    public function getNotifyTask(){
        $data = TaskService::pop(TaskService::KEY_NOTIFY);
        if($data){
            return AjaxUtils::success($data);
        }
        return AjaxUtils::error('暂无任务');
    }
    public function setNotifyStatus(){
        $order_id = $this->request->param('order_id');
        $status = $this->request->param('status');

        Log::info("自动回调：".json_encode($this->request->param()));

        OrderModel::update([
            'notify_status'=>$status,
            'notify_time'=>time()
        ],[
            'order_id'=>$order_id
        ]);

        return AjaxUtils::success();
    }
}
