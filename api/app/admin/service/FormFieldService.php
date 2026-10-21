<?php

declare (strict_types = 1);

namespace app\admin\service;


use app\common\basics\Service;
use app\common\model\FormFieldModel;
use app\common\utils\TimeUtils;

class FormFieldService extends Service
{
    public static function lists(array $get,array $userItem): array
    {
        $where = self::setSearch([
            '%like%'   => ['name'],
            '='        => ['key'],
            'datetime' => ['create_time'],
        ],$get);

        $model = new FormFieldModel();
        $lists = $model
            ->where($where)
            ->order('id desc')
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
        FormFieldModel::create([
            'key'          => $post['key'],
            'name'        => $post['name'],
            'field_json'        => $post['field_json'] ?? '',
            'create_time'  => time(),
            'update_time'  => time()
        ]);
    }

    public static function edit(array $post,array $userItem): void
    {
        FormFieldModel::update([
            'key'          => $post['key'],
            'name'        => $post['name'],
            'field_json'        => $post['field_json'] ?? '',
            'update_time'  => time()
        ], ['id'=>intval($post['id'])]);
    }

    public static function del(array $ids,array $userItem): void
    {
        FormFieldModel::destroy($ids,true);
    }


}