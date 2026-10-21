<?php

declare (strict_types = 1);

namespace app\admin\service;

use app\common\basics\Service;
use app\common\enums\StatusEnum;
use app\common\exception\RequestException;
use app\common\model\PermsModel;
use app\common\model\RolePermsModel;
use app\common\utils\CacheUtils;
use think\facade\Db;

class PermsService extends Service
{
    private const NODE_TYPES = ['group', 'page', 'action', 'api'];

    public static function getTreeData(int $parentId = 0): array
    {
        $rows = (new PermsModel())
            ->field('id,parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status')
            ->order('sort', 'asc')
            ->order('id', 'asc')
            ->select()
            ->toArray();

        $childrenByParent = [];
        foreach ($rows as $row) {
            $childrenByParent[(int) $row['parent_id']][] = $row;
        }

        $build = function (int $id, array $ancestors = []) use (&$build, $childrenByParent): array {
            $result = [];
            foreach ($childrenByParent[$id] ?? [] as $row) {
                $nodeId = (int) $row['id'];
                if (isset($ancestors[$nodeId])) {
                    continue;
                }

                $nextAncestors = $ancestors;
                $nextAncestors[$nodeId] = true;
                $children = $build($nodeId, $nextAncestors);
                if (!empty($children)) {
                    $row['children'] = $children;
                }
                $result[] = $row;
            }
            return $result;
        };

        return $build($parentId);
    }

    public static function add(array $post, array $userItem): void
    {
        $data = self::normalize($post);
        $data['create_time'] = time();
        $data['update_time'] = $data['create_time'];
        PermsModel::create($data);
        CacheUtils::clearTag('perms');
    }

    public static function edit(array $post, array $userItem): void
    {
        $id = (int) ($post['id'] ?? 0);
        if ($id <= 0 || !PermsModel::find($id)) {
            throw new RequestException('权限节点不存在');
        }

        $data = self::normalize($post, $id);
        $data['update_time'] = time();
        PermsModel::update($data, ['id' => $id]);
        CacheUtils::clearTag('perms');
    }

    public static function createCurd(int $id): array
    {
        $parent = PermsModel::find($id);
        if (!$parent) {
            throw new RequestException('节点不存在');
        }
        $module = trim((string) $parent['module']);
        $controller = trim((string) $parent['controller']);
        if ($parent['node_type'] !== 'page' || $module === '' || $controller === '') {
            throw new RequestException('请先为页面节点配置 CURD 模块和控制器');
        }

        $fields = [
            ['name' => '列表', 'action' => 'items'],
            ['name' => '新增', 'action' => 'add'],
            ['name' => '编辑', 'action' => 'edit'],
            ['name' => '删除', 'action' => 'del'],
        ];
        $messages = [];
        $now = time();

        Db::transaction(function () use ($fields, $parent, $module, $controller, &$messages, $now) {
            foreach ($fields as $field) {
                $key = self::buildPermissionKey(
                    $module,
                    $controller,
                    $field['action']
                );
                $exists = PermsModel::where('module', '=', $module)
                    ->where('controller', '=', $controller)
                    ->where('action', '=', $field['action'])
                    ->find();
                if ($exists) {
                    $messages[] = $field['name'] . '已存在';
                    continue;
                }
                if (!check_action_exists($module, $controller, $field['action'])) {
                    $messages[] = $field['name'] . '对应的方法不存在，已跳过';
                    continue;
                }

                PermsModel::create([
                    'parent_id' => $parent['id'],
                    'name' => $field['name'],
                    'node_type' => 'action',
                    'permission_key' => $key,
                    'route_path' => null,
                    'module' => $module,
                    'controller' => $controller,
                    'action' => $field['action'],
                    'sort' => 50,
                    'status' => StatusEnum::STATUS_1,
                    'create_time' => $now,
                    'update_time' => $now,
                ]);
                $messages[] = $field['name'] . '添加成功';
            }
        });

        CacheUtils::clearTag('perms');
        return $messages;
    }

    public static function del(array $ids, array $userItem): void
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (empty($ids)) {
            throw new RequestException('请选择要删除的节点');
        }

        $childMap = [];
        foreach (PermsModel::field('id,parent_id')->select()->toArray() as $node) {
            $childMap[(int) $node['parent_id']][] = (int) $node['id'];
        }

        $deleteIds = [];
        $stack = $ids;
        while (!empty($stack)) {
            $id = (int) array_pop($stack);
            if (isset($deleteIds[$id])) {
                continue;
            }
            $deleteIds[$id] = true;
            foreach ($childMap[$id] ?? [] as $childId) {
                $stack[] = $childId;
            }
        }
        $deleteIds = array_keys($deleteIds);

        Db::transaction(function () use ($deleteIds) {
            RolePermsModel::whereIn('perms_id', $deleteIds)->delete();
            PermsModel::whereIn('id', $deleteIds)->delete();
        });

        CacheUtils::clearTag('perms');
    }

    public static function buildPermissionKey(string $module, string $controller, string $action): string
    {
        return trim($module) . '.' . trim($controller) . '.' . trim($action);
    }

    private static function isIdentifierPartValid(string $part): bool
    {
        return preg_match('/^[A-Za-z0-9_-]+$/', $part) === 1;
    }

    private static function normalize(array $post, int $id = 0): array
    {
        $name = trim((string) ($post['name'] ?? ''));
        $nodeType = trim((string) ($post['node_type'] ?? ''));
        $parentId = filter_var($post['parent_id'] ?? 0, FILTER_VALIDATE_INT);
        $sort = filter_var($post['sort'] ?? 0, FILTER_VALIDATE_INT);
        $status = filter_var($post['status'] ?? StatusEnum::STATUS_1, FILTER_VALIDATE_INT);

        if ($name === '') {
            throw new RequestException('请输入节点名称');
        }
        if ($parentId === false || $sort === false || $status === false) {
            throw new RequestException('上级节点、排序或状态值无效');
        }
        if (!in_array($nodeType, self::NODE_TYPES, true)) {
            throw new RequestException('请选择有效的节点类型');
        }
        if ($id > 0 && in_array($nodeType, ['action', 'api'], true)
            && PermsModel::where('parent_id', '=', $id)->count() > 0
        ) {
            throw new RequestException('包含子节点的目录/页面不能改为操作或 API 权限');
        }
        if ($parentId < 0 || ($parentId === $id && $parentId > 0)) {
            throw new RequestException('上级节点无效');
        }
        if (!in_array($status, [0, 1], true) || $sort < 0) {
            throw new RequestException('节点状态或排序值无效');
        }

        $module = trim((string) ($post['module'] ?? ''));
        $controller = trim((string) ($post['controller'] ?? ''));
        $action = trim((string) ($post['action'] ?? ''));
        $permissionKey = trim((string) ($post['permission_key'] ?? ''));
        $routePath = trim((string) ($post['route_path'] ?? ''));

        if ($nodeType === 'page' && $routePath === '') {
            throw new RequestException('页面节点必须选择前端路由');
        }
        if ($nodeType === 'group' || $nodeType === 'page') {
            if ($routePath !== '' && !preg_match('#^/[A-Za-z0-9/_-]+$#', $routePath)) {
                throw new RequestException('前端路由格式无效');
            }
            if ($nodeType === 'group') {
                $module = '';
                $controller = '';
            } elseif (($module === '') !== ($controller === '')) {
                throw new RequestException('CURD 模块和控制器需要同时填写或同时留空');
            } elseif ($module !== ''
                && (!self::isIdentifierPartValid($module) || !self::isIdentifierPartValid($controller))
            ) {
                throw new RequestException('CURD 模块和控制器格式无效');
            }
            $action = '';
            $permissionKey = '';
            $routePath = $routePath === '' ? null : $routePath;
        } else {
            if ($module === '' || $controller === '' || $action === '') {
                throw new RequestException('操作/API 节点必须填写模块、控制器和方法');
            }
            foreach ([$module, $controller, $action] as $part) {
                if (!self::isIdentifierPartValid($part)) {
                    throw new RequestException('模块、控制器和方法仅允许字母、数字、下划线和连字符');
                }
            }
            if ($permissionKey === '') {
                throw new RequestException('请输入稳定的权限标识');
            }
            if (preg_match('/^[A-Za-z0-9][A-Za-z0-9._:-]{0,190}$/', $permissionKey) !== 1) {
                throw new RequestException('权限标识格式无效');
            }
            if (!check_action_exists($module, $controller, $action)) {
                throw new RequestException('控制器方法不存在');
            }
            $routePath = null;
            if (strlen($permissionKey) > 191) {
                throw new RequestException('权限标识过长');
            }
        }

        if ($permissionKey !== '') {
            $duplicate = PermsModel::where('permission_key', '=', $permissionKey)->find();
            if ($duplicate && (int) $duplicate['id'] !== $id) {
                throw new RequestException('该稳定权限标识已被使用');
            }
        }
        if ($nodeType === 'action' || $nodeType === 'api') {
            $duplicate = PermsModel::whereIn('node_type', ['action', 'api'])
                ->where('module', '=', $module)
                ->where('controller', '=', $controller)
                ->where('action', '=', $action)
                ->find();
            if ($duplicate && (int) $duplicate['id'] !== $id) {
                throw new RequestException('该后端处理器已绑定到其他权限节点');
            }
        }
        if ($routePath !== null) {
            $duplicate = PermsModel::where('route_path', '=', $routePath)->find();
            if ($duplicate && (int) $duplicate['id'] !== $id) {
                throw new RequestException('该前端路由已绑定到其他权限节点');
            }
        }

        if ($parentId > 0) {
            $parent = PermsModel::find($parentId);
            if (!$parent || in_array($parent['node_type'], ['action', 'api'], true)) {
                throw new RequestException('上级节点必须是目录或页面');
            }
            self::assertNoParentCycle($id, $parentId);
        }

        return [
            'parent_id' => $parentId,
            'name' => $name,
            'node_type' => $nodeType,
            'permission_key' => $permissionKey === '' ? null : $permissionKey,
            'route_path' => $routePath,
            'module' => $module,
            'controller' => $controller,
            'action' => $action,
            'sort' => $sort,
            'status' => $status,
        ];
    }

    private static function assertNoParentCycle(int $id, int $parentId): void
    {
        $visited = [];
        $cursor = $parentId;
        while ($cursor > 0) {
            if ($cursor === $id || isset($visited[$cursor])) {
                throw new RequestException('不能将节点移动到自身或其子级中');
            }
            $visited[$cursor] = true;
            $cursor = (int) PermsModel::where('id', '=', $cursor)->value('parent_id');
        }
    }
}
