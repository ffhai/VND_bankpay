<?php
namespace app\task\controller;

use app\BaseController;
use app\task\service\TaskService;
use think\App;


class Index extends BaseController
{

   /* public function index(){
        $data=[];
        $data['data'] = ['time'=>time()];
        $data['expire_time'] = time() + 5;
        $task_id = TaskService::push($data);
        return $task_id;
    }
    public function pop(){
        $data = TaskService::pop();
        if($data){
            return json($data);
        }
        return 'no';
    }
    public function count(){
        return TaskService::getCount();
    }*/
}
