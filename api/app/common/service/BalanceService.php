<?php

declare(strict_types=1);

namespace app\common\service;

use app\common\basics\Service;
use app\common\enums\BalanceTypeEnum;
use app\common\exception\RequestException;
use app\common\model\BalanceLogModel;
use app\common\model\UserModel;
use app\common\utils\TimeUtils;
use think\facade\Db;

/**
 * 余额服务
 */
class BalanceService extends Service
{
    /**
     * 交易类型（静态配置，不入库）：存储值 => 展示名称
     *
     * 写入时的合法性校验、列表「类型」列展示、前端下拉选项都以这份 map 为准，
     * 新增类型只需在 map 里加一行（键建议用 BalanceTypeEnum 中的常量）。
     */
    public static $typeMap = [
        BalanceTypeEnum::RECHARGE => '充值',
        BalanceTypeEnum::DEDUCT => '扣减',
        BalanceTypeEnum::REFUND => '退款',
        BalanceTypeEnum::ADJUST => '调整',
        BalanceTypeEnum::CONSUME => '消费',
    ];

    /**
     * 交易类型 map
     */
    public static function getTypeMap(): array
    {
        return static::$typeMap;
    }

    /**
     * 交易类型中文描述（未登记的类型原样返回，避免脏数据显示为空）
     */
    public static function getTypeName(string $type): string
    {
        return static::$typeMap[$type] ?? $type;
    }

    /**
     * 前端下拉选项：[{ value, label }]
     */
    public static function getTypeOptions(): array
    {
        $options = [];
        foreach (static::$typeMap as $value => $label) {
            $options[] = ['value' => (string) $value, 'label' => $label];
        }

        return $options;
    }

    /**
     * 是否为合法类型（写入前校验，避免无法展示、无法统计的脏值入库）
     */
    public static function isValidType(string $type): bool
    {
        return array_key_exists($type, static::$typeMap);
    }

    /**
     * 获取用户余额（分）
     */
    public static function getBalance(int $userId): int
    {
        $user = UserModel::where('id', $userId)->field('balance')->find();
        return $user ? (int) $user['balance'] : 0;
    }

    /**
     * 变动余额（分），并写入变动记录
     *
     * @param int $userId 用户ID
     * @param int $changeAmount 变动金额（分，正为增加，负为扣减）
     * @param string $type 交易类型
     * @param string $remark 备注
     * @param string $operator 操作员
     * @return int 变动后余额（分）
     */
    public static function change(int $userId, int $changeAmount, string $type, string $remark = '', string $operator = ''): int
    {
        if ($changeAmount == 0) {
            throw new RequestException('变动金额不能为0');
        }
        if (!self::isValidType($type)) {
            throw new RequestException('交易类型不合法：' . $type);
        }

        return Db::transaction(function () use ($userId, $changeAmount, $type, $remark, $operator) {
            // 行锁读取当前余额，防止并发超扣
            $user = UserModel::where('id', $userId)->lock(true)->field('balance')->find();
            if (!$user) {
                throw new RequestException('用户不存在');
            }

            $before = (int) $user['balance'];
            $after = $before + $changeAmount;
            if ($after < 0) {
                throw new RequestException('余额不足，无法扣减');
            }

            UserModel::where('id', $userId)->update(['balance' => $after]);

            BalanceLogModel::create([
                'user_id' => $userId,
                'change_amount' => $changeAmount,
                'balance_after' => $after,
                'type' => $type,
                'remark' => $remark,
                'operator' => $operator,
                'create_time' => time(),
            ]);

            return $after;
        });
    }

    /**
     * 交易记录列表
     *
     * @param bool $ownOnly 仅查询当前用户自己的记录
     */
    public static function lists(array $get, array $userItem, bool $ownOnly = false): array
    {
        // 字段必须带表别名：dt_balance_log 与 dt_user 都有 type/create_time 等同名列，
        // 不带前缀会在 JOIN 查询里触发 "Column 'type' in where clause is ambiguous"
        $where = self::setSearch([
            '=' => ['user_id@balance_log_model.user_id', 'type@balance_log_model.type'],
            'datetime' => ['time@balance_log_model.create_time'],
        ], $get);

        $model = new BalanceLogModel();
        $query = $model->withJoin(['user'], 'LEFT')->where($where);

        if ($ownOnly) {
            // 仅查看自己的记录
            $query->where('balance_log_model.user_id', (int) $userItem['id']);
        }

        $lists = $query->order('id desc')->paginate([
            'page' => $get['page'] ?? 1,
            'list_rows' => $get['pagesize'] ?? 20,
        ])->toArray();

        foreach ($lists['data'] as &$item) {
            $item['username'] = $item['user']['username'] ?? '';
            $item['type_name'] = self::getTypeName((string) $item['type']);
            $item['create_time'] = TimeUtils::formatTime((int) $item['create_time']);
            unset($item['user']);
        }
        unset($item);

        return ['count' => $lists['total'], 'items' => $lists['data']] ?? [];
    }

    /**
     * 删除交易记录
     *
     * 财务流水属于账目凭据，不允许物理删除（会破坏余额变动的可追溯性），
     * 如需修正金额请通过「调整余额」新增一笔冲正记录。
     *
     * @param array $ids 记录ID
     * @param array $userItem 当前账号（兼容基类签名，此处未使用）
     */
    public static function del(array $ids, array $userItem = []): void
    {
        throw new RequestException('余额流水不允许删除，如需修正请新增一笔冲正记录');
    }
}
