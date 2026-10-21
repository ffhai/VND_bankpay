<?php

declare (strict_types = 1);

namespace app\admin\service;

use app\common\exception\RequestException;
use app\common\model\BalanceLogModel;
use app\common\model\UserModel;

/**
 * 账号删除前的数据引用校验（钩子形式）
 *
 * 删除流程只负责「校验 + 事务内清理」，具体「还有哪些数据不允许删除」
 * 拆成一个个独立钩子，新增判断项时：
 *
 *   1. 在 $builtinGuards 里加一个方法（同一模块内的常规数据）；或
 *   2. 在其它模块启动时调用 UserDeleteGuard::register(...) 注册（跨模块数据）
 *
 * 每个钩子签名为 function (array $ids): void，校验不通过时抛 RequestException。
 */
class UserDeleteGuard
{
    /**
     * 外部注册的校验钩子
     * @var callable[]
     */
    private static $extraGuards = [];

    /**
     * 注册额外校验钩子
     *
     * @param callable $guard function (array $ids): void
     */
    public static function register(callable $guard): void
    {
        self::$extraGuards[] = $guard;
    }

    /**
     * 清空外部注册的钩子（测试用）
     */
    public static function reset(): void
    {
        self::$extraGuards = [];
    }

    /**
     * 执行全部校验
     *
     * @param array $ids 待删除的账号ID
     * @throws RequestException
     */
    public static function check(array $ids): void
    {
        if (empty($ids)) {
            return;
        }

        foreach (self::guards() as $guard) {
            $guard($ids);
        }
    }

    /**
     * 内置钩子 + 外部注册的钩子
     *
     * @return callable[]
     */
    private static function guards(): array
    {
        $builtinGuards = [
            // 余额需为 0，避免账目资金凭空消失
            [self::class, 'ensureBalanceEmpty'],
            // 存在余额变动记录的账号不可删除，避免流水失去归属
            [self::class, 'ensureNoBalanceLog'],
        ];

        return array_merge($builtinGuards, self::$extraGuards);
    }

    /**
     * 余额必须为 0
     */
    public static function ensureBalanceEmpty(array $ids): void
    {
        $usernames = UserModel::whereIn('id', $ids)
            ->where('balance', '<>', 0)
            ->column('username');

        if (!empty($usernames)) {
            throw new RequestException('账号余额不为 0，请先结清：' . implode('、', $usernames));
        }
    }

    /**
     * 不能存在余额变动记录
     */
    public static function ensureNoBalanceLog(array $ids): void
    {
        $userIds = BalanceLogModel::whereIn('user_id', $ids)->column('user_id');

        if (!empty($userIds)) {
            throw new RequestException('账号存在余额流水，不允许删除');
        }
    }
}
