<?php
namespace app\common\model;

use think\model\relation\HasOne;

class UserModel extends CommonModel
{
    protected $name = 'user';

    //账号类型
    const TYPE_SUPER = 'super';

    protected $schema = [
        'id'=>'int',
        'type'=>'string',
        'username'=>'string',
        'nickname'=>'string',
        'password'=>'string',
        'avatar'=>'string',
        'balance'=>'int',
        'status'=>'int',
        'create_time'=>'int',
        'update_time'=>'int'
    ];

    public function getItemByUsername($username){
        return $this->where('username','=',$username)->find();
    }

    public function role(): HasOne
    {
        return $this->hasOne(RolesModel::class, 'type', 'type');
    }

}
