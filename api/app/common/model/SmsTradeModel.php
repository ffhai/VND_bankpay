<?php
namespace app\common\model;

class SmsTradeModel extends CommonModel
{
    protected $name = 'sms_trade';

    protected $schema = [
        'id' => 'int',
        'notify_id' => 'int',
        'bank_number' => 'string',
        'balance' => 'int',
        'change' => 'int',
        'remark' => 'string',
        'order_id' => 'string',
        'status' => 'int',
        'time' => 'int',
    ];

}
