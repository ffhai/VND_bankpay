<?php

declare (strict_types = 1);

namespace app\admin\service;


use app\common\basics\Service;
use app\common\model\ConfigGroupModel;
use app\common\utils\TimeUtils;

class ConfigGroupService extends Service
{
    public static function lists(array $get,array $userItem): array
    {
        $where = self::setSearch([
            '%like%'   => ['keyword@name'],
            '='        => ['status@status'],
            'datetime' => ['time@create_time'],
        ],$get);

        $model = new ConfigGroupModel();
        $lists = $model
            ->where($where)
            ->order('sort desc')
            ->paginate([
                'page'      => $get['page']  ?? 1,
                'list_rows' => $get['pagesize'] ?? 20
            ])->toArray();

        foreach ($lists['data'] as &$item) {
            $item['create_time'] = TimeUtils::formatTime($item['create_time']);
            $item['update_time'] = TimeUtils::formatTime($item['update_time']);
        }

        return ['count'=>$lists['total'], 'items'=>$lists['data']] ?? [];
    }

    public static function add(array $post,array $userItem): void
    {
        ConfigGroupModel::create([
            'name'          => $post['name'],
            'sort'        => $post['sort'],
            'status'        => $post['status'],
            'create_time'  => time(),
            'update_time'  => time()
        ]);
    }

    public static function edit(array $post,array $userItem): void
    {
        ConfigGroupModel::update([
            'name'          => $post['name'],
            'sort'        => $post['sort'],
            'status'        => $post['status'],
            'update_time'  => time()
        ], ['id'=>intval($post['id'])]);
    }

    public static function del(array $ids,array $userItem): void
    {
        ConfigGroupModel::destroy($ids,true);
    }

    public static function getOptions(array $userItem):array
    {
        $model = new ConfigGroupModel();
        $lists = $model
            ->order('sort desc')
            ->select();
        $items = [];
        foreach($lists as $item){
            $items[]=[
                'id'=>$item['id'],
                'name'=>$item['name'],
            ];
        }
        return $items;
    }


}