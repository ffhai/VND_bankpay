<?php

declare (strict_types = 1);

namespace app\common\basics;

use app\common\exception\NotAuthException;
use app\common\exception\NotPermsException;
use app\common\model\UserModel;
use app\common\service\AppSignService;

/**
 * 开放接口基类（appid + sign 签名鉴权）
 *
 * 登录态来自 AppSignService 的签名校验，而非 Api-Token（JWT）；
 * 权限按 appid 所绑定账号的角色校验；开放接口仅允许 openapi/task 两个模块。
 */
abstract class Api extends AbstractAuth
{
    /**
     * appid 信息
     * @var array
     */
    protected $appItem = [];

    /**
     * appid 绑定的账号不存在时的提示
     */
    protected function userNotFoundMessage(): string
    {
        return '应用绑定的账号不存在';
    }

    /**
     * appid 绑定的账号已停用时的提示
     */
    protected function userDisabledMessage(): string
    {
        return '应用绑定的账号已停用';
    }

    /**
     * 从 appid + sign 签名解析应用绑定的账号
     *
     * @return array
     * @throws NotAuthException
     */
    protected function resolveUser(): array
    {
        $this->appItem = AppSignService::check($this->request->param());

        $userItem = (new UserModel())->getItemById($this->appItem['uid'] ?? 0);

        return is_array($userItem) ? $userItem : [];
    }

    protected function checkPower(): bool
    {
        $module = strtolower(app('http')->getName());
        if (!in_array($module, ['openapi', 'task'], true)) {
            throw new NotPermsException('不支持的开放接口模块');
        }
        return parent::checkPower();
    }
}
