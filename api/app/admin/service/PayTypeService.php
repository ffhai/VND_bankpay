<?php
declare (strict_types = 1);

namespace app\admin\service;


use app\common\basics\Service;
use app\common\enums\StatusEnum;
use app\common\model\OrderModel;
use app\common\model\PayTypeModel;
use app\common\utils\TimeUtils;

class PayTypeService extends Service
{
    public static function lists(array $get,array $userItem): array
    {
        $searchWhere = self::setSearch([
            '%like%'   => ['keyword@name'],
            '='        => ['status','code'],
            'datetime' => ['time@create_time'],
        ],$get);

        $model = new PayTypeModel();
        $orderModel = new OrderModel();
        $lists = $model
            ->where($searchWhere)
            ->order('id desc')
            ->paginate([
                'page'      => $get['page']  ?? 1,
                'list_rows' => $get['pagesize'] ?? 20
            ])->toArray();

        foreach ($lists['data'] as &$item) {
            $item['create_time'] = TimeUtils::formatTime ($item['create_time']);
            $item['update_time'] = TimeUtils::formatTime($item['update_time']);
        }

        return ['count'=>$lists['total'], 'items'=>$lists['data']] ?? [];
    }

    public static function add(array $post,array $userItem): void
    {
        PayTypeModel::create([
            'code'          => $post['code'],
            'name'        => $post['name'],
            'remark'        => $post['remark'],
            'expire_time'        => $post['expire_time'],
            'status'        => $post['status'] ?? '',
            'create_time'  => time(),
            'update_time'  => time()
        ]);
    }

    public static function edit(array $post,array $userItem): void
    {
        PayTypeModel::update([
            'code'          => $post['code'],
            'name'        => $post['name'],
            'remark'        => $post['remark'],
            'expire_time'        => $post['expire_time'],
            'status'        => $post['status'] ?? '',
            'update_time'  => time()
        ], ['id'=>intval($post['id'])]);
    }

    public static function del(array $ids,array $userItem): void
    {
        PayTypeModel::destroy($ids,true);
    }

    public static function getOptions(array $userItem): array
    {
        $model = new PayTypeModel();
        return $model
            ->field('code,name')
            ->where(['status'=>StatusEnum::STATUS_1])
            ->order('id')
            ->select()->toArray();
    }
}