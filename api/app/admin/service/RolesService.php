<?php

declare (strict_types = 1);

namespace app\admin\service;


use app\common\basics\Service;
use app\common\enums\StatusEnum;
use app\common\exception\RequestException;
use app\common\model\RolePermsModel;
use app\common\model\RolesModel;
use app\common\model\UserModel;
use app\common\utils\CacheUtils;
use app\common\utils\TimeUtils;
use think\facade\Db;

class RolesService extends Service
{

    public static function lists(array $get,array $userItem): array
    {
        $where = self::setSearch([
            // 前端「搜索名称」传 name，按名称模糊匹配；keyword 仍可用于模糊搜索备注
            '%like%'   => ['name@name', 'keyword@remark'],
            '='        => ['type@type'],
            'datetime' => ['time@create_time'],
        ],$get);

        $model = new RolesModel();
        $lists = $model
            ->with('perms')
            ->where($where)
            ->order('id desc')
            ->paginate([
                'page'      => $get['page']  ?? 1,
                'list_rows' => $get['pagesize'] ?? 20
            ])->toArray();
        foreach ($lists['data'] as &$item) {
            $item['create_time'] = TimeUtils::formatTime($item['create_time']);
            $item['update_time'] = TimeUtils::formatTime($item['update_time']);
            $item['perms_ids'] = array_column($item['perms'], 'perms_id');
            unset($item['perms']);
        }
        return ['count'=>$lists['total'], 'items'=>$lists['data']] ?? [];
    }

    public static function getOptions(array $userItem): array
    {
        $model = new RolesModel();
        return $model
            ->field('type,name')
            ->where(['status'=>StatusEnum::STATUS_1])
            ->order('id')
            ->select()->toArray();
    }

    public static function add(array $post,array $userItem): void
    {
        RolesModel::create([
            'type'          => $post['type'],
            'name'        => $post['name'],
            'status'        => $post['status'] ?? StatusEnum::STATUS_1,
            'remark'        => $post['remark'] ?? '',
            'create_time'  => time(),
            'update_time'  => time()
        ]);
    }

    public static function edit(array $post,array $userItem): void
    {
        RolesModel::update([
            'type'          => $post['type'],
            'name'        => $post['name'],
            'status'        => $post['status'] ?? StatusEnum::STATUS_1,
            'remark'        => $post['remark'] ?? '',
            'update_time'  => time()
        ], ['id'=>intval($post['id'])]);
    }

    public static function del(array $ids,array $userItem): void
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (empty($ids)) {
            throw new RequestException('请选择要删除的角色');
        }

        $types = RolesModel::whereIn('id', $ids)->column('type');
        if (empty($types)) {
            return;
        }

        // 引用校验：仍被账号引用的角色不允许删除，避免账号角色悬空
        $inUse = UserModel::whereIn('type', $types)->column('username');
        if (!empty($inUse)) {
            throw new RequestException('角色仍被账号使用，请先调整这些账号的角色：' . implode('、', $inUse));
        }

        Db::transaction(function () use ($ids, $types) {
            // 级联清理角色授权，避免残留孤儿授权
            RolePermsModel::whereIn('type', $types)->delete();
            RolesModel::destroy($ids, true);
        });

        CacheUtils::clearTag('perms');
    }


}