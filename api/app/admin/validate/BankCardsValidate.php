<?php

declare (strict_types = 1);

namespace app\admin\validate;

use app\common\basics\Validate;


class BankCardsValidate extends Validate
{
    protected $rule = [
        'balance' => 'integer|egt:0|elt:9999999999999',
        'limit_in_max' => 'integer|egt:0|elt:9999999999999',
        'limit_out_max' => 'integer|egt:0|elt:9999999999999',
        'id'           => 'require|integer',
        'ids'          => 'require|array',
        'bank_number'        => 'require',
        'bank_code'        => 'require',
        'bank_user_id'        => 'require',
        'status'   => 'require|in:-1,1',
    ];

    public function __construct()
    {
        $this->field = [
            'bank_number'        => '卡号',
            'bank_code'        => '银行代码',
            'bank_user_id'        => '银行户名',
            'status'   => '状态',
        ];
        parent::__construct();
    }
}
