<?php
//下级
namespace app\admin\controller;

use app\common\basics\CrudController;
use app\admin\service\AppListService;
use app\admin\service\UserService;
use app\common\exception\RequestException;
use app\admin\validate\AppListValidate;
use think\App;


class AppList extends CrudController
{
    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->service = new AppListService();
        $this->validate = new AppListValidate();
    }

    protected function beforeAdd(array $params): array
    {
        return $this->resolveUid($params);
    }

    protected function beforeEdit(array $params): array
    {
        return $this->resolveUid($params);
    }

    /**
     * 账号名 -> uid，账号不存在时交由统一异常处理返回错误
     */
    private function resolveUid(array $params): array
    {
        $username = $params['username'] ?? '';
        if ($username === '') {
            return $params;
        }

        $userItem = UserService::getItemByUsername($username);
        if (empty($userItem)) {
            throw new RequestException('账号不存在');
        }
        $params['uid'] = $userItem['id'];

        return $params;
    }
}
