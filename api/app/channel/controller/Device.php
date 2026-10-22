<?php
namespace app\channel\controller;

use app\common\basics\CrudController;
use app\common\enums\StatusEnum;
use app\common\model\AppListModel;
use app\common\utils\AjaxUtils;


class Device extends CrudController
{
    public function getNotifyQrcode(){
        $appModel = new AppListModel();
        $item = $appModel->where('uid','=',$this->userItem['id'])->find();
        if(!$item || $item['status'] != StatusEnum::STATUS_1){
            return AjaxUtils::error('当前账户未开启API权限，或已停用，如有问题请联系管理员！');
        }
        $data = [];
        $data['base_url'] = url('/',[],'',true)->build();
        $data['appid'] = $item['appid'];
        $data['secret'] = $item['secret'];

        $data['qrcode'] = \tekintian\TekinQR::getQRImg(json_encode($data), 10, '', 1);

        return AjaxUtils::success('success',$data);
    }
}
