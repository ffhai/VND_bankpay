<?php
declare (strict_types = 1);

namespace app\admin\validate;

use app\common\basics\Validate;


class PayTypeValidate extends Validate
{
    protected $rule = [
        'id'           => 'require|integer',
        'ids'          => 'require|array',
        'code'        => 'require',
        'name'        => 'require',
        'status'   => 'require|in:-1,1'
    ];

    public function __construct()
    {
        $this->field = [
            'code'        => '唯一标识',
            'name'        => '名称',
            'status'   => '状态',
        ];

        parent::__construct();
    }
}