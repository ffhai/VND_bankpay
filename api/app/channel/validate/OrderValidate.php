<?php

declare (strict_types = 1);

namespace app\channel\validate;

use app\common\basics\Validate;


class OrderValidate extends Validate
{
    protected $rule = [
        'id'           => 'require|integer',
        'ids'          => 'require|array',
        'status'   => 'require|in:-1,1',
        'order_id'  => 'require',
    ];

    public function __construct()
    {
        $this->field = [
            'status'   => '状态',
            'order_id'   => '订单号',
        ];
        parent::__construct();
    }

    protected $scene = [
        'confirmed'  =>  ['order_id'],
        'notify'  =>  ['order_id'],
    ];
}