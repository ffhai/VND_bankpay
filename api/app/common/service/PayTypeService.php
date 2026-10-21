<?php
declare (strict_types = 1);

namespace app\common\service;


use app\common\basics\Service;
use app\common\enums\StatusEnum;
use app\common\model\PayTypeModel;

class PayTypeService extends Service
{
    public static function getOptions(array $userItem): array
    {
        $model = new PayTypeModel();
        return $model
            ->field('code,name')
            ->where(['status'=>StatusEnum::STATUS_1])
            ->order('id')
            ->select()->toArray();
    }

    public static function getItemByCode(string $code){
        $model = new PayTypeModel();
        $item = $model->where('code','=',$code)->find();
        if($item){
            return $item->toArray();
        }
        return false;
    }
}