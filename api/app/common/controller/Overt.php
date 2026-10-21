<?php
namespace app\common\controller;

use app\common\basics\Auth;
use app\common\enums\StatusEnum;
use app\common\model\ConfigModel;
use app\common\service\LoginService;
use app\common\utils\AjaxUtils;
use app\common\utils\UrlUtils;
use jolalau\captcha\facade\CaptchaApi;
use think\response\Json;


class Overt extends Auth
{
    protected $notNeedLogin = ['captcha','login','init'];

    public function captcha(){
        $data = CaptchaApi::create();
        $data=array(
            'key'=>$data['key'],
            'data'=>str_replace("\r\n","",$data['base64'])
        );
        return AjaxUtils::success("获取成功",$data);
    }
    public function login():Json
    {
        $params=[];
        $params['username']=$this->request->param('username');
        $params['password']=$this->request->param('password');
        $params['captcha']=$this->request->param('captcha');
        $params['captcha_key']=$this->request->param('captcha_key');

        $config = new ConfigModel();
        if(intval($config->getContentByKey('captcha_status')) == StatusEnum::STATUS_1){
            if(empty($params['captcha'])){
                return AjaxUtils::error("请输入验证码");
            }
            if ( ! CaptchaApi::check($params['captcha'],$params['captcha_key'])) {
                return AjaxUtils::error("验证码不正确");
            }
        }
        if(empty($params['username']) || empty($params['password'])){
            return AjaxUtils::error("账号或密码不能为空");
        }

        $ret=[];
        $ret['token'] = LoginService::login($params);
        return AjaxUtils::success("登录成功",$ret);
    }

    public function init(){
        $config = new ConfigModel();
        $data=[];
        $data['site_name'] = $config->getContentByKey('site_name');
        $data['site_status'] = intval($config->getContentByKey('site_status'));
        $data['site_stop_msg'] = $config->getContentByKey('site_stop_msg');
        $data['captcha_status'] = intval($config->getContentByKey('captcha_status'));
        $data['logo'] = $config->getContentByKey('logo');
        if(!empty($data['logo'])){
            $data['logo'] = UrlUtils::toAbsoluteUrl($data['logo']);
        }
        return AjaxUtils::success("获取成功",$data);
    }
}
