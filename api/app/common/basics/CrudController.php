<?php
namespace app\common\basics;

use think\App;


abstract class CrudController extends Auth
{
    protected $validate;
    protected $service;
    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->validate = new Validate();
        $this->service = new Service();
    }

    /**
     * 新增前的数据处理（子类可覆盖：补全派生字段、做前置校验）
     */
    protected function beforeAdd(array $params): array
    {
        return $params;
    }

    /**
     * 编辑前的数据处理（子类可覆盖）
     */
    protected function beforeEdit(array $params): array
    {
        return $params;
    }

}
