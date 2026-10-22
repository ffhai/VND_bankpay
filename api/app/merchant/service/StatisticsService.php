<?php
declare(strict_types=1);
namespace app\merchant\service;

use think\facade\Db;
use app\common\model\OrderModel;


class StatisticsService
{
    public static function orders(array $user, ?int $now = null): array
    {
        $now = $now ?? time();
        $today = strtotime(date('Y-m-d', $now));
        $yesterday = strtotime('-1 day', $today);
        $query = Db::name('order')->where('status', OrderModel::STATUS_PAYED)->where('trade_uid', (int) $user['id'])->where('create_time', '<=', $now);
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
