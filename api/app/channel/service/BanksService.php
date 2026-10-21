<?php

declare (strict_types = 1);

namespace app\channel\service;


use app\common\basics\Service;
use app\common\enums\StatusEnum;
use app\common\model\BanksModel;

class BanksService extends Service
{


    public static function getOptions(array $userItem): array
    {
        $model = new BanksModel();
        $data = $model
            ->where('status','=',StatusEnum::STATUS_1)
            ->field('id,code,short_name')
            ->order('id')
            ->select();
        if($data->isEmpty()){
            return [];
        }else{
            return $data->toArray();
        }
    }

}