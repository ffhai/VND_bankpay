<?php

declare (strict_types = 1);

namespace app\openapi\validate;

use app\common\basics\Validate;


class OrderValidate extends Validate
{
    protected $rule = [
        'order_id'           => 'require',
        'pay_type'           => 'require',
        'trade_no'           => 'require',
        'amount'           => 'require|integer|egt:1|elt:9999999999999',
        'notify_url'           => 'require',
    ];

    public function __construct()
    {
        $this->field = [
            'order_id'   => '系统订单号',
            'pay_type'   => '支付方式',
            'trade_no'   => '商户订单号',
            'amount'   => '金额',
            'notify_url'   => '异步回调地址',
        ];
        parent::__construct();
        if (strtolower((string) app('http')->getName()) === 'openapi') {
            $messages = [];
            foreach (['order_id', 'pay_type', 'trade_no', 'amount', 'notify_url'] as $field) {
                $messages[$field . '.require'] = \think\facade\Lang::get($field . '_required');
            }
            foreach (['integer', 'egt', 'elt'] as $rule) {
                $messages['amount.' . $rule] = \think\facade\Lang::get('amount_invalid');
            }
            $this->message($messages);
        }
    }

    protected $scene = [
        'createOrder'  =>  ['pay_type','trade_no','amount','notify_url'],
        'createOrderBank2Bank'  =>  ['order_id','pay_type','trade_no','amount','notify_url'],
        'searchOrder'  =>  ['order_id'],
    ];
}
