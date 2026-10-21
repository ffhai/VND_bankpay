<?php
namespace app\common\model;;

class ConfigGroupModel extends CommonModel
{
    protected $name = 'config_group';

    protected $schema=[
        'id'=>'int',
        'name'=>'string',
        'sort'=>'int',
        'status'=>'int',
        'create_time'=>'int',
        'update_time'=>'int',
    ];
}

