<?php

declare (strict_types = 1);

namespace app\admin\service;


use app\common\basics\Service;
use app\common\exception\RequestException;
use app\common\model\PermsModel;
use app\common\model\RolePermsModel;
use app\common\model\RolesModel;
use app\common\utils\CacheUtils;
use think\facade\Db;

class RolePermsService extends Service
{
    public static function save(array $post): void
    {
        $type = trim((string) ($post['type'] ?? ''));
        $rawPermsIds = (array) ($post['perms_ids'] ?? []);
        $permsIds = [];
        foreach ($rawPermsIds as $rawId) {
            if (!is_scalar($rawId) || !preg_match('/^[1-9][0-9]*$/', (string) $rawId)) {
                throw new RequestException('授权列表包含无效的权限节点 ID');
            }
            $permsIds[] = (int) $rawId;
        }
        $permsIds = array_values(array_unique($permsIds));
        if ($type === '') {
            throw new RequestException('缺少角色标识');
        }
        if (!RolesModel::where('type', '=', $type)->find()) {
            throw new RequestException('角色不存在');
        }

        $existingIds = [];
        if (!empty($permsIds)) {
            $existingIds = array_map('intval', (new PermsModel())->whereIn('id', $permsIds)->column('id'));
            if (count($existingIds) !== count($permsIds)) {
                throw new RequestException('授权列表包含不存在的权限节点');
            }
        }

        $items = [];
        $now = time();
        foreach ($permsIds as $permsId) {
            $items[] = [
                'type' => $type,
                'perms_id' => $permsId,
                'create_time' => $now,
                'update_time' => $now,
            ];
        }

        $model = new RolePermsModel();
        Db::transaction(function () use ($model, $type, $items) {
            $model->where('type','=',$type)->delete();
            if (!empty($items)) {
                $model->insertAll($items);
            }
        });

        CacheUtils::clearTag('perms');
    }

}