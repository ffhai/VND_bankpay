<?php

declare (strict_types = 1);

namespace app\common\service;


use app\common\basics\Service;
use app\common\model\BankUserModel;

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