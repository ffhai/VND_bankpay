<?php
namespace app\admin\validate;

use app\common\basics\Validate;

class SmsCodeValidate extends Validate
{
    protected $rule = ['ids' => 'require|array'];
}
