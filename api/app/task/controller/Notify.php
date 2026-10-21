<?php
namespace app\task\controller;

use app\common\service\DeviceNotifyService;
use app\common\utils\AjaxUtils;
use think\App;
use think\facade\Log;


class Notify extends \app\common\basics\Api
{
    public function add(){
        $params = $this->request->param();
        $list = [
            'com.google.android.googlequicksearchbox',
            //'org.autojs.autojs6.listensysnotify',
            'com.google.android.gms'
        ];
        if(in_array($params['package_name'],$list) || empty($params['package_name'])){
            return AjaxUtils::error("黑名单");
        }
        $data = [
            'package_name'=>$params['package_name'] ?? '',
            'text'=>$params['text'] ?? '',
            'tickerText'=>$params['tickerText'] ?? '',
            'extrasText'=>$params['extrasText'] ?? '',
        ];
        Log::debug($data);
        DeviceNotifyService::addContent($params['phone_number'],json_encode($data),$this->userItem);
        return AjaxUtils::success('success',$params);
    }

    public function getPending(){
        $data = DeviceNotifyService::getPending();
        if(count($data) > 0){
            return AjaxUtils::success('success',$data);
        }else{
            return AjaxUtils::error('暂无');
        }
    }

    public function setPending(){
        $params = $this->request->param();
        $data = DeviceNotifyService::setPending($params);
        return AjaxUtils::success($data);
    }


}
