<?php

declare (strict_types = 1);

namespace app\admin\validate;

use app\common\basics\Validate;


class BanksValidate extends Validate
{
    protected $rule = [
        'id'           => 'require|integer',
        'ids'          => 'require|array',
        'code'        => 'require',
        'bin'        => 'require',
        'short_name'        => 'require',
        'swift_code'        => 'require',
        'status'   => 'require|in:-1,1',
        'status_in'   => 'require|in:-1,1',
        'status_out'   => 'require|in:-1,1',
    ];

    public function __construct()
    {
        $this->field = [
            'uid'       => '账号',
            'code'      => '短代码',
            'bin'       => 'Bin',
            'short_name'        => '短名称',
            'swift_code'        => '长代码',
            'status'        => '状态',
            'status_in'     => '入账状态',
            'status_out'        => '出账状态',
        ];
        parent::__construct();
    }
}