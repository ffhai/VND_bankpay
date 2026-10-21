<?php
namespace app\common\model;

class PayTypeModel extends CommonModel
{
    protected $name = 'pay_type';

    const STATUS_ON = 1;
    const STATUS_OFF = -1;

    const TYPE_BANK2BANK = 'bank2bank';

    protected $schema = [
        'id'=>'int',
        'code'=>'string',
        'name'=>'string',
        'remark'=>'string',
        'expire_time'=>'int',
        'status'=>'int',
        'create_time'=>'int',
        'update_time'=>'int'
    ];

}
