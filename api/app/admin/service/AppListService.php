<?php

declare (strict_types = 1);

namespace app\admin\service;


use app\common\basics\Service;
use app\common\enums\StatusEnum;
use app\common\model\AppListModel;
use app\common\utils\TimeUtils;

class AppListService extends Service
{
    public static function lists(array $get,array $userItem): array
    {
        $where = self::setSearch([
            '%like%'   => ['keyword@app_list_model.remark'],
            '='        => ['status@app_list_model.status','appid@app_list_model.appid','username@user.username'],
            'datetime' => ['time@app_list_model.create_time'],
        ],$get);

        $model = new AppListModel();
        $lists = $model
            ->withJoin('user')
            ->where($where)
            ->order('id desc')
            ->paginate([
                'page'      => $get['page']  ?? 1,
                'list_rows' => $get['pagesize'] ?? 20
            ])->toArray();

        foreach ($lists['data'] as &$item) {
            $item['username'] = $item['user']['username'] ?? '';
            $item['create_time'] = TimeUtils::formatTime($item['create_time']);
            $item['update_time'] = TimeUtils::formatTime($item['update_time']);
            unset($item['user']);
        }

        return ['count'=>$lists['total'], 'items'=>$lists['data']] ?? [];
    }

    public static function add(array $post,array $userItem): void
    {
        AppListModel::create([
            'uid'          => $post['uid'],
            'appid'        => $post['appid'],
            'secret'        => $post['secret'],
            'status'        => $post['status'] ?? StatusEnum::STATUS_1,
            'remark'        => $post['remark'] ?? '',
            'create_time'  => time(),
            'update_time'  => time()
        ]);
    }

    public static function edit(array $post,array $userItem): void
    {
        AppListModel::update([
            'uid'          => $post['uid'],
            'appid'        => $post['appid'],
            'secret'        => $post['secret'],
            'status'        => $post['status'] ?? StatusEnum::STATUS_1,
            'remark'        => $post['remark'] ?? '',
            'update_time'  => time()
        ], ['id'=>intval($post['id'])]);
    }

    public static function del(array $ids,array $userItem): void
    {
        AppListModel::destroy($ids,true);
    }


}