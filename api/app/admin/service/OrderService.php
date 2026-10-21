<?php

declare (strict_types = 1);

namespace app\admin\service;


use app\common\basics\Service;
use app\common\model\OrderModel;
use app\common\utils\TimeUtils;

class OrderService extends Service
{
    public static function lists(array $get,array $userItem): array
    {
        $searchWhere = self::setSearch([
            '%like%'   => ['keyword@order_model.order_id','trade_no@order_model.trade_no','ps@order_model.ps'],
            '='        => [
                'pay_type@order_model.pay_type',
                'status@order_model.status',
                'trade_uid@order_model.trade_uid',
                'channel_uid@order_model.channel_uid',
                'channel_uid@order_model.channel_uid',
                'bank_number@order_model.bank_number',
            ],
            'datetime' => ['time@order_model.create_time'],
        ],$get);

        $model = new OrderModel();
        $lists = $model
            ->withJoin([
                'trade'=>[
                    'id','username'
                ],
                'channelUser'=>[
                    'id','username'
                ]
            ])
            ->where($searchWhere)
            ->order('order_model.id desc')
            ->paginate([
                'page'      => $get['page']  ?? 1,
                'list_rows' => $get['pagesize'] ?? 20
            ])->toArray();
        $amount = $model
            ->withJoin(['trade','channelUser'])
            ->where($searchWhere)
            ->sum('amount');

        foreach ($lists['data'] as &$item) {
            $item['create_time'] = TimeUtils::formatTime($item['create_time']);
            $item['expire_time'] = TimeUtils::formatTime($item['expire_time']);
            $item['pay_time'] = TimeUtils::formatTime($item['pay_time']);
        }

        return ['count'=>$lists['total'], 'items'=>$lists['data'],'sum'=>['amount'=>$amount > 0 ? $amount : 0]] ?? [];
    }
    public static function del(array $ids,array $userItem): void
    {
        throw new \app\common\exception\RequestException('订单记录不可删除');
    }


}
