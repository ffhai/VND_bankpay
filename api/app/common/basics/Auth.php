<?php

declare (strict_types = 1);

namespace app\common\basics;

use app\common\exception\NotAuthException;
use app\common\model\UserModel;
use app\common\service\LoginService;

/**
 * 后台基类（Api-Token / JWT 鉴权）
 *
 * 登录态来自 Api-Token（JWT）校验，权限按登录账号的角色校验。
 * 对比开放接口基类：{@see \app\common\basics\Api} 使用 appid + sign 签名。
 */
abstract class Auth extends AbstractAuth
{
    /**
     * 从 Api-Token 解析登录账号
     *
     * @return array
     * @throws NotAuthException
     */
    protected function resolveUser(): array
    {
        $token = $this->request->header('Api-Token');
        if (empty($token)) {
            throw new NotAuthException('请登录后再操作!');
        }

        $userData = LoginService::checkAuthorization($token);
        if (empty($userData)) {
            throw new NotAuthException('请登录后再操作!');
        }

        $userItem = (new UserModel())->getItemById($userData->uid ?? 0);

        return is_array($userItem) ? $userItem : [];
    }
}
