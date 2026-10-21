<?php

declare (strict_types = 1);

namespace app\merchant\service;

use app\common\basics\Service;
use app\common\model\OrderModel;
use app\common\utils\TimeUtils;

class OrderService extends Service
{
    public static function lists(array $get, array $userItem): array
    {
        $searchWhere = self::setSearch([
            '%like%'   => ['order_id','trade_no', 'ps'],
            '='        => [
                'pay_type',
                'status',
            ],
            'datetime' => ['time@create_time'],
        ], $get);

        $model = new OrderModel();
        $lists = $model
            ->where('trade_uid', '=', $userItem['id'])
            ->where($searchWhere)
            ->field('order_id,trade_no,pay_type,bank_number,amount,ps,status,create_time,expire_time,pay_time,notify_status')
            ->order('id desc')
            ->paginate([
                'page'      => $get['page'] ?? 1,
                'list_rows' => $get['pagesize'] ?? 20,
            ])->toArray();
        $amount = $model
            ->where('trade_uid', '=', $userItem['id'])
            ->where($searchWhere)
            ->sum('amount');

        foreach ($lists['data'] as &$item) {
            $item['create_time'] = TimeUtils::formatTime($item['create_time']);
            $item['expire_time'] = TimeUtils::formatTime($item['expire_time']);
            $item['pay_time'] = TimeUtils::formatTime($item['pay_time']);
        }

        return [
            'count' => $lists['total'],
            'items' => $lists['data'],
            'sum'   => ['amount' => $amount],
        ];
    }
}
