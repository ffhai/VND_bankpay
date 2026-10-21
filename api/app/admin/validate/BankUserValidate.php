<?php

declare (strict_types = 1);

namespace app\admin\validate;

use app\common\basics\Validate;


class BankUserValidate extends Validate
{
    protected $rule = [
        'id'           => 'require|integer',
        'ids'          => 'require|array',
        'name'        => 'require',
        'idcard'        => 'require',
        'status'   => 'require|in:-1,1',
    ];

    public function __construct()
    {
        $this->field = [
            'name'        => '姓名',
            'idcard'        => '身份证号码',
            'status'   => '状态',
        ];
        parent::__construct();
    }
}