<?php
namespace app\admin\validate;

use app\common\basics\Validate;

class SmsTradeValidate extends Validate
{
    protected $rule = ['ids' => 'require|array'];
}
