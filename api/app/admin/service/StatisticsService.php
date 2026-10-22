<?php
declare(strict_types=1);
namespace app\admin\service;

use think\facade\Db;
use app\common\model\OrderModel;


class StatisticsService
{
    public static function accounts(): array
    {
        // 包含启用、停用账号；其他类型不计入商户与码商的合计。
        $row = Db::name('user')->whereIn('type', ['channel', 'merchant'])->fieldRaw(
            "COUNT(*) AS total_count, COALESCE(SUM(balance), 0) AS total_balance, " .
            "COALESCE(SUM(CASE WHEN type = 'channel' THEN 1 ELSE 0 END), 0) AS channel_count, " .
            "COALESCE(SUM(CASE WHEN type = 'channel' THEN balance ELSE 0 END), 0) AS channel_balance, " .
            "COALESCE(SUM(CASE WHEN type = 'merchant' THEN 1 ELSE 0 END), 0) AS merchant_count, " .
            "COALESCE(SUM(CASE WHEN type = 'merchant' THEN balance ELSE 0 END), 0) AS merchant_balance"
        )->find();
        $result = [];
        foreach (['channel', 'merchant', 'total'] as $group) {
            $result[$group] = ['count' => (int) $row[$group . '_count'], 'balance' => (string) $row[$group . '_balance']];
        }
        return $result;
    }

    public static function orders(array $user, ?int $now = null): array
    {
        $now = $now ?? time();
        $today = strtotime(date('Y-m-d', $now));
        $yesterday = strtotime('-1 day', $today);
        $query = Db::name('order')->where('status', OrderModel::STATUS_PAYED)->where('create_time', '<=', $now);
        // 边界均为服务端生成的整数，一次聚合查询返回三个区间，金额保持十进制整数精度。
        $row = $query->fieldRaw(
            'COUNT(*) AS total_count, COALESCE(SUM(amount), 0) AS total_amount, ' .
            'COALESCE(SUM(CASE WHEN create_time >= ' . $today . ' THEN 1 ELSE 0 END), 0) AS today_count, ' .
            'COALESCE(SUM(CASE WHEN create_time >= ' . $today . ' THEN amount ELSE 0 END), 0) AS today_amount, ' .
            'COALESCE(SUM(CASE WHEN create_time >= ' . $yesterday . ' AND create_time < ' . $today . ' THEN 1 ELSE 0 END), 0) AS yesterday_count, ' .
            'COALESCE(SUM(CASE WHEN create_time >= ' . $yesterday . ' AND create_time < ' . $today . ' THEN amount ELSE 0 END), 0) AS yesterday_amount'
        )->find();
        $result = [];
        foreach (['today', 'yesterday', 'total'] as $period) {
            $result[$period] = [
                'count' => (int) $row[$period . '_count'],
                'amount' => (string) $row[$period . '_amount'],
            ];
        }
        return $result;
    }
}
