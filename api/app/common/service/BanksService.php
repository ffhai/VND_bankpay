<?php

declare (strict_types = 1);

namespace app\common\service;


use app\common\basics\Service;
use app\common\enums\StatusEnum;
use app\common\model\BanksModel;
use app\common\utils\TimeUtils;

class BanksService extends Service
{
    public static function getItemByCode(string $code)
    {
        $model = new BanksModel();
        $data = $model
            ->where('code','=',$code)
            ->find();
        if(!$data){
            return false;
        }else{
            return $data->toArray();
        }
    }

}