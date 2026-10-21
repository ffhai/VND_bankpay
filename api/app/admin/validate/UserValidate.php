<?php
namespace app\admin\validate;


use app\common\basics\Validate;

class UserValidate extends Validate
{
    protected $rule =   [
        'type'  => 'require',
        'username'  => 'require|unique:user',
        'password'  => 'require|min:6'
    ];
    public function __construct()
    {
        $this->field = [
            'type'        => '角色代码',
            'username'        => '账号名称',
            'password'        => '密码',
        ];
        parent::__construct();
    }
    protected $scene = [
        // 编辑场景由 Member::edit 显式指定，仅校验角色与账号名
        'edit'  =>  ['type','username'],
    ];

}