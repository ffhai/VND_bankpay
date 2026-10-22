<?php
namespace app\common\model;


class RolePermsModel extends CommonModel
{
    protected $name = 'role_perms';

    protected $schema = [
        'id'=>'int',
        'type'=>'string',
        'perms_id'=>'int',
        'create_time'=>'int',
        'update_time'=>'int',
    ];

}
