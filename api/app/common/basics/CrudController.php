<?php
namespace app\common\basics;

use app\common\utils\AjaxUtils;
use think\App;
use think\response\Json;


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

    public function items():Json
    {
        $params = $this->request->param();
        $list = $this->service::lists($params,$this->userItem);
        return AjaxUtils::success($list);
    }

    public function add(){
        $params = $this->beforeAdd($this->request->param());
        $this->validate->addCheck($params);
        $this->service::add($params,$this->userItem);
        return AjaxUtils::success();
    }

    public function edit(){
        $params = $this->beforeEdit($this->request->param());
        $this->validate->editCheck($params);
        $this->service::edit($params,$this->userItem);
        return AjaxUtils::success();
    }

    public function del(){
        $ids = $this->request->param('ids/a', []);
        $this->validate->idsCheck(['ids' => $ids]);
        $this->service::del($ids,$this->userItem);
        return AjaxUtils::success($ids);
    }

    public function getOptions(){
        return AjaxUtils::success($this->service::getOptions($this->userItem));
    }

}
