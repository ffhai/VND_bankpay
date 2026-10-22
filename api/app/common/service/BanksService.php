<?php

declare (strict_types = 1);

namespace app\common\service;


use app\common\basics\Service;
use app\common\model\BanksModel;

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