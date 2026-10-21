<?php

declare (strict_types = 1);

namespace app\admin\service;

use app\common\basics\Service;
use app\common\enums\StatusEnum;
use app\common\exception\RequestException;
use app\common\model\AppListModel;
use app\common\model\UserModel;
use app\common\service\LoginService;
use app\common\utils\TimeUtils;
use think\facade\Db;

class UserService extends Service
{
    public static function getItemByUsername($username){
        $model = new UserModel();
        return $model->where('username','=',$username)->find();
    }

    public static function lists(array $get,array $userItem): array
    {
        $where = self::setSearch([
            '%like%'   => ['username@user_model.username'],
            'datetime' => ['time@user_model.create_time'],
        ],$get);
        $model = new UserModel();
        $lists = $model
            ->withoutField(['password'])
            ->withJoin(['role'],'LEFT')
            ->where($where)
            ->order('id desc')
            ->paginate([
                'page'      => $get['page']  ?? 1,
                'list_rows' => $get['pagesize'] ?? 20
            ])->toArray();
        foreach ($lists['data'] as &$item) {
            $item['role_name'] = $item['role']['name'] ?? '';
            $item['create_time'] = TimeUtils::formatTime($item['create_time']);
            $item['update_time'] = TimeUtils::formatTime($item['update_time']);

            unset($item['role']);
        }

        return ['count'=>$lists['total'], 'items'=>$lists['data']] ?? [];
    }

    public static function add(array $post,array $userItem): void
    {
        UserModel::create([
            'type'          => $post['type'],
            'username'        => $post['username'],
            'password'        => $post['password'],
            'status'        => $post['status'] ?? StatusEnum::STATUS_1,
            'create_time'  => time(),
            'update_time'  => time()
        ]);
    }

    public static function edit(array $post,array $userItem): void
    {
        $data = [
            'type'          => $post['type'],
            'username'        => $post['username'],
            'password'        => $post['password'] ?? '',
            'status'        => $post['status'] ?? StatusEnum::STATUS_1,
            'update_time'  => time()
        ];
        if(empty($data['password'])){
            unset($data['password']);
        }
        UserModel::update($data, ['id'=>intval($post['id'])]);

        // 管理员改过账号后：解除登录锁定、清空失败计数
        LoginService::clearLoginFail((string) $data['username']);
        // 重置了密码：此前的登录态立即失效
        if (!empty($post['password'])) {
            LoginService::invalidateUserTokens(intval($post['id']));
        }
    }

    public static function del(array $ids,array $userItem): void
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (empty($ids)) {
            throw new RequestException('请选择要删除的账号');
        }
        // 身份类校验：超管与当前登录账号不允许删除
        if (in_array(1, $ids, true)) {
            throw new RequestException('超级管理员账号不允许删除');
        }
        if (in_array((int) ($userItem['id'] ?? 0), $ids, true)) {
            throw new RequestException('不能删除当前登录账号');
        }

        $usernames = UserModel::whereIn('id', $ids)->column('username');
        if (empty($usernames)) {
            return;
        }

        // 数据引用校验走钩子，新增判断项只需在 UserDeleteGuard 中扩展
        UserDeleteGuard::check($ids);

        Db::transaction(function () use ($ids) {
            // 级联清理账号绑定的开放接口应用
            AppListModel::whereIn('uid', $ids)->delete();
            UserModel::destroy($ids, true);
        });

        foreach ($usernames as $username) {
            LoginService::clearLoginFail((string) $username);
        }
    }

    public static function getOptions(array $userItem): array
    {
        $model = new UserModel();
        return $model
            ->field('id,username')
            ->where(['status'=>StatusEnum::STATUS_1])
            ->order('id')
            ->select()->toArray();
    }


}