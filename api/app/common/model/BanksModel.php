<?php
namespace app\common\model;

class BanksModel extends CommonModel
{
    protected $name = 'banks';

    protected $schema = [
        'id'=>'int',
        'name'=>'int',
        'code'=>'string',
        'bin'=>'string',
        'logo'=>'string',
        'short_name'=>'string',
        'swift_code'=>'string',
        'status'=>'int',
        'status_in'=>'int',
        'status_out'=>'int',
        'create_time'=>'int',
        'update_time'=>'int',
    ];

}
