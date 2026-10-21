<?php

declare (strict_types = 1);

namespace app\admin\service;


use app\common\basics\Service;
use app\common\model\DeviceNotifyModel;
use app\common\utils\TimeUtils;

class DeviceNotifyService extends Service
{
    public static function lists(array $get,array $userItem): array
    {
        $searchWhere = self::setSearch([
            '%like%'   => ['order_id@device_notify_model.order_id','card_number@device_notify_model.card_number','remark@device_notify_model.remark'],
            '='        => ['status@device_notify_model.status','username@user.username'],
            'datetime' => ['time@device_notify_model.time'],
        ],$get);

        $model = new DeviceNotifyModel();
        $lists = $model
            ->withJoin([
                'user'=>['id','username']
            ])
            ->where($searchWhere)
            ->order('id desc')
            ->paginate([
                'page'      => $get['page']  ?? 1,
                'list_rows' => $get['pagesize'] ?? 20
            ])->toArray();

        foreach ($lists['data'] as &$item) {
            $item['time'] = TimeUtils::formatTime($item['time']);
        }

        return ['count'=>$lists['total'], 'items'=>$lists['data']] ?? [];
    }
    public static function del(array $ids,array $userItem): void
    {
        DeviceNotifyModel::destroy($ids,true);
    }

    public static function reset(array $params){
        DeviceNotifyModel::update([
            'status'=>DeviceNotifyModel::STATUS_NO
        ],[
            'id'=>$params['id']
        ]);
    }


}