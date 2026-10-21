<?php

declare (strict_types = 1);

namespace app\common\basics;

use app\BaseController;
use app\common\enums\StatusEnum;
use app\common\exception\NotAuthException;
use app\common\exception\NotPermsException;
use app\common\service\PermsAccessService;
use think\App;

/**
 * 鉴权基类
 *
 * 只负责「校验登录 → 校验权限」的通用流程与判定规则，
 * 登录身份从哪里来（Api-Token / appid+sign）由子类通过 resolveUser() 提供。
 *
 * @see \app\common\basics\Auth    后台账号（Api-Token）
 * @see \app\common\basics\Api     开放接口（appid + sign 签名）
 */
abstract class AbstractAuth extends BaseController
{
    /**
     * 超级管理员账号ID（不受权限限制）
     */
    protected const SUPER_ADMIN_ID = 1;

    /**
     * 账号信息
     * @var array
     */
    protected $userItem = [];

    /**
     * 不校验登录的方法，['*'] 表示全部不校验
     * @var array
     */
    protected $notNeedLogin = [];

    /**
     * 不校验权限的方法，['*'] 表示全部不校验
     * @var array
     */
    protected $notNeedPower = [];

    /**
     * 构造方法
     *
     * @param App $app
     * @throws NotAuthException
     * @throws NotPermsException
     */
    public function __construct(App $app)
    {
        parent::__construct($app);

        $this->checkLogin();

        $this->checkPower();
    }

    /**
     * 解析当前请求的登录账号
     *
     * @return array 账号信息（至少包含 id / type / status），空数组表示身份无效
     * @throws NotAuthException
     */
    abstract protected function resolveUser(): array;

    /**
     * 账号不存在时的提示语
     */
    protected function userNotFoundMessage(): string
    {
        return '登录账号不存在，请重新登录!';
    }

    /**
     * 账号停用时的提示语
     */
    protected function userDisabledMessage(): string
    {
        return '账号已停用，请联系管理员!';
    }

    /**
     * 验证登录
     *
     * @return void
     * @throws NotAuthException
     */
    protected function checkLogin(): void
    {
        if ($this->isWithoutLogin()) {
            return;
        }

        $userItem = $this->resolveUser();
        if (empty($userItem)) {
            throw new NotAuthException($this->userNotFoundMessage());
        }
        if ((int) $userItem['status'] !== StatusEnum::STATUS_1) {
            throw new NotAuthException($this->userDisabledMessage());
        }

        $this->userItem = $userItem;
    }

    /**
     * 验证权限
     *
     * @return bool
     * @throws NotPermsException
     */
    protected function checkPower(): bool
    {
        $module = app('http')->getName();
        $controller = $this->request->controller();
        $action = $this->request->action();
        // 免登录的方法没有登录身份，也就无从校验权限，直接放行
        if ($this->isWithoutLogin() || $this->isSkip($this->notNeedPower, $action)) {
            return true;
        }
        if (empty($this->userItem['type'])) {
            throw new NotPermsException('无权限['.$this->requestUrl().']');
        }
        if ((int) $this->userItem['id'] === self::SUPER_ADMIN_ID) {
            return true;
        }

        $endpoint = $module . '.' . $controller . '.' . $action;
        $permsSet = PermsAccessService::getPowerSet($this->userItem['type']);
        if (isset($permsSet[$endpoint])) {
            return true;
        }

        throw new NotPermsException('无权限['.$this->requestUrl().']');
    }

    /**
     * 当前请求是否免登录
     *
     * 免登录的方法不会注入登录身份，因此也不参与权限校验。
     *
     * @return bool
     */
    protected function isWithoutLogin(): bool
    {
        return $this->isSkip($this->notNeedLogin, $this->request->action());
    }

    /**
     * 是否命中免校验名单，['*'] 表示全部免校验
     *
     * @param array $skipList
     * @param string $action
     * @return bool
     */
    protected function isSkip(array $skipList, string $action): bool
    {
        return in_array('*', $skipList, true) || in_array($action, $skipList, true);
    }

    /**
     * 当前请求的规范化标识（用于错误提示）
     *
     * @return string
     */
    protected function requestUrl(): string
    {
        $url = ltrim(lcfirst($this->request->baseUrl()), '/');

        return strtolower(($url === 'index' || !$url) ? 'index/index' : $url);
    }
}
