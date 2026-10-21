<?php

declare (strict_types = 1);

namespace app\admin\validate;

use app\common\basics\Validate;


class AppListValidate extends Validate
{
    protected $rule = [
        'id'           => 'require|integer',
        'ids'          => 'require|array',
        'uid'        => 'require',
        'appid'        => 'max:32',
        'secret'        => 'max:32',
        'status'   => 'require|in:-1,1'
    ];

    public function __construct()
    {
        $this->field = [
            'uid'        => '账号',
            'appid'        => 'APPID',
            'secret'        => 'SECRET',
            'status'   => '状态',
        ];
        parent::__construct();
    }
}