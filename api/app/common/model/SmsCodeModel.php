<?php
namespace app\common\model;

class SmsCodeModel extends CommonModel
{
    protected $name = 'sms_code';

    protected $schema = [
        'id' => 'int',
        'notify_id' => 'int',
        'phone_number' => 'string',
        'verify_code' => 'string',
        'time' => 'int',
    ];
}
