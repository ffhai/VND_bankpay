<?php

declare (strict_types = 1);

namespace app\common\service;


use app\common\basics\Service;
use app\common\enums\StatusEnum;
use app\common\model\BankUserModel;
use app\common\utils\TimeUtils;

class BankUserService extends Service
{

    public static function getItemById(int $id)
    {
        $model = new BankUserModel();
        $data = $model
            ->find($id);
        if(!$data){
            return false;
        }else{
            return $data->toArray();
        }
    }

}