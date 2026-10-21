<?php
//下级
namespace app\admin\controller;

use app\common\basics\CrudController;
use app\admin\service\UserService;
use app\common\service\BalanceService;
use app\common\utils\AjaxUtils;
use app\admin\validate\UserValidate;
use think\App;


class Member extends CrudController
{
    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->service = new UserService();
        $this->validate = new UserValidate();
    }
    public function add(){
        $data = $this->request->param();
        $this->validate->addCheck($data);

        $data['password'] = make_password($data['password']);
        $this->service::add($data,$this->userItem);
        return AjaxUtils::success();
    }

    public function edit(){
        $data = $this->request->param();
        if(!empty($data['password'])){
            $data['password'] = make_password($data['password']);
        }else{
            unset($data['password']);
        }
        $this->validate->goCheck('edit',$data);

        $this->service::edit($data,$this->userItem);
        return AjaxUtils::success();
    }

    /**
     * 调整账号余额
     */
    public function adjust(){
        $userId = intval($this->request->param('user_id'));
        $changeAmount = intval($this->request->param('change_amount'));
        $type = (string) $this->request->param('type', 'adjust');
        $remark = (string) $this->request->param('remark', '');
        $operator = $this->userItem['username'] ?? '';

        if (empty($userId)) {
            return AjaxUtils::error('请选择账号');
        }
        if ($changeAmount == 0) {
            return AjaxUtils::error('变动金额不能为0');
        }

        $balanceAfter = BalanceService::change($userId, $changeAmount, $type, $remark, $operator);
        return AjaxUtils::success('调整成功', ['balance_after' => $balanceAfter]);
    }
}
