<?php

declare (strict_types = 1);

namespace app\channel\service;


use app\common\basics\Service;
use app\common\enums\StatusEnum;
use app\common\model\BankUserModel;
use app\common\utils\TimeUtils;

class BankUserService extends Service
{
    public static function lists(array $get,array $userItem): array
    {
        $searchWhere = self::setSearch([
            '%like%'   => ['keyword@name','idcard@idcard'],
            '='        => ['status'],
            'datetime' => ['time@create_time'],
        ],$get);

        $model = new BankUserModel();
        $lists = $model
            ->where($searchWhere)
            ->where('uid','=',$userItem['id'])
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
        BankUserModel::create([
            'uid'          => $userItem['id'],
            'name'          => $post['name'],
            'idcard'        => $post['idcard'],
            'remark'        => $post['remark'] ?? '',
            'status'        => $post['status'] ?? StatusEnum::STATUS_1,
            'create_time'  => time(),
            'update_time'  => time()
        ]);
    }

    public static function edit(array $post,array $userItem): void
    {
        BankUserModel::update([
            'name'          => $post['name'],
            'idcard'        => $post['idcard'],
            'remark'        => $post['remark'] ?? '',
            'status'        => $post['status'] ?? StatusEnum::STATUS_OFF,
            'update_time'  => time()
        ], ['id'=>intval($post['id']),'uid'=>$userItem['id']]);
    }

    public static function del(array $ids,array $userItem): void
    {
        \app\common\service\BankDataService::deleteOrDisable('holder', $ids, (int) $userItem['id']);
    }

    public static function getOptions(array $userItem): array
    {
        $model = new BankUserModel();
        $data = $model
            ->where('uid','=',$userItem['id'])
            ->where('status','=',StatusEnum::STATUS_1)
            ->field('id,name,idcard')
            ->order('id')
            ->select();
        if($data->isEmpty()){
            return [];
        }else{
            return $data->toArray();
        }
    }

}
