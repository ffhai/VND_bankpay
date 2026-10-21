<?php

declare (strict_types = 1);

namespace app\admin\validate;

use app\common\basics\Validate;


class ConfigValidate extends Validate
{
    protected $rule = [
        'id'           => 'require|integer',
        'ids'          => 'require|array',
        'key'        => 'require|max:50|unique:config',
        'name'        => 'require',
        'show'   => 'require|in:-1,1'
    ];

    public function __construct()
    {
        $this->field = [
            'key'        => '唯一标识',
            'name'   => '名称',
            'show'   => '是否显示',
        ];
        parent::__construct();
    }
}