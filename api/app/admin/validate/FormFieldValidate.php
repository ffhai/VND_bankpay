<?php

declare (strict_types = 1);

namespace app\admin\validate;

use app\common\basics\Validate;


class FormFieldValidate extends Validate
{
    protected $rule = [
        'id'           => 'require|integer',
        'ids'          => 'require|array',
        'key'        => 'require|max:32',
        'name'        => 'require',
    ];

    public function __construct()
    {
        $this->field = [
            'key'        => '唯一标志',
            'name'        => '名称',
        ];

        parent::__construct();
    }
}