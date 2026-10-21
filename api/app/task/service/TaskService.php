<?php

declare (strict_types = 1);

namespace app\task\service;


use app\common\basics\Service;
use think\facade\Cache;

class TaskService extends Service
{
    const REDIS_LIST_KEY = 'redis_task_list';

    public static function push(array $params):string
    {
        $data = [];
        //必填参数
        $data['data'] = $params['data'];//任务内容

        //选填参数
        $data['task_id'] = $params['task_id'] ?? md5(uniqid( ));//任务唯一标识
        $data['expire_time'] = $params['expire_time'] ?? 0;//过期时间 0不过期

        $redis = Cache::store('redis')->handler();
        $redis->rPush(self::REDIS_LIST_KEY, json_encode($data));
        return $data['task_id'];
    }

    public static function pop(){
        $redis = Cache::store('redis')->handler();
        $data = $redis->lPop(self::REDIS_LIST_KEY);
        if(!$data){
            return false;
        }
        $obj = json_decode($data,true);

        //处理超时
        if($obj['expire_time'] > 0 && $obj['expire_time'] < time()){
            return self::pop();
        }

        return $obj;
    }

    public static function getCount(): int
    {
        $redis = Cache::store('redis')->handler();
        $count = $redis->lLen(self::REDIS_LIST_KEY);
        return $count;
    }


}