<?php
namespace app\admin\validate;

use app\common\basics\Validate;

class RolesValidate extends Validate
{
    protected $rule =   [
        'type'  => 'require|max:10|unique:roles',
        'name'  => 'require'
    ];
    public function __construct()
    {
        $this->field = [
            'type'        => '角色代码',
            'name'        => '名称',
        ];
        parent::__construct();
    }
}