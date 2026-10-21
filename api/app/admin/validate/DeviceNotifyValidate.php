<?php

declare (strict_types = 1);

namespace app\admin\validate;

use app\common\basics\Validate;


class DeviceNotifyValidate extends Validate
{
    protected $rule = [
        'id'           => 'require|integer',
        'ids'          => 'require|array',
        'uid'        => 'require',
    ];

    public function __construct()
    {
        $this->field = [
            'uid'        => '账号',
        ];
        parent::__construct();
    }

    protected $scene = [
        'reset'  =>  ['id'],
    ];
}