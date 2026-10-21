<?php
namespace app\common\model;

use think\model\relation\HasOne;

class BankUserModel extends CommonModel
{
    protected $name = 'bank_user';

    protected $schema = [
        'id'=>'int',
        'uid'=>'int',
        'name'=>'string',
        'idcard'=>'string',
        'remark'=>'string',
        'status'=>'int',
        'create_time'=>'int',
        'update_time'=>'int',
    ];
    public function user(): HasOne
    {
        return $this->hasOne(UserModel::class, 'id', 'uid');
    }

}
