<?php
namespace app\admin\validate;

use app\common\basics\Validate;

class PermsValidate extends Validate
{
    protected $rule =   [
        'name'  => 'require',
        'node_type' => 'require|in:group,page,action,api',
    ];

    public function __construct()
    {
        $this->field = [
            'name'        => '名称',
            'node_type'   => '节点类型',
        ];
        parent::__construct();
    }

    protected $scene = [
        'add'  =>  ['name', 'node_type'],
        'edit'  =>  ['name', 'node_type'],
    ];

}