<?php
namespace app\common\model;

use think\model\relation\HasMany;

class PermsModel extends CommonModel
{
    protected $name = 'perms';

    protected $schema = [
        'id'=>'int',
        'parent_id'=>'int',
        'name'=>'string',
        'node_type'=>'string',
        'permission_key'=>'string',
        'route_path'=>'string',
        'module'=>'string',
        'controller'=>'string',
        'action'=>'string',
        'sort'=>'int',
        'status'=>'int',
        'create_time'=>'int',
        'update_time'=>'int',
    ];

    public function roleperms(): HasMany
    {
        return $this->hasMany(RolePermsModel::class, 'perms_id', 'id');
    }
}
