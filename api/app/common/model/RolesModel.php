<?php
namespace app\common\model;

use think\model\relation\HasOne;

class RolesModel extends CommonModel
{
    protected $name = 'roles';

    protected $schema = [
        'id'=>'int',
        'type'=>'string',
        'name'=>'string',
        'status'=>'int',
        'remark'=>'string',
        'create_time'=>'int',
        'update_time'=>'int'
    ];

    public function perms()
    {
        return $this->hasMany(RolePermsModel::class, 'type', 'type');
    }
}
