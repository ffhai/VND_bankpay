<?php

declare (strict_types = 1);

namespace app\admin\service;


use app\common\basics\Service;
use app\common\enums\StatusEnum;
use app\common\model\BanksModel;
use app\common\utils\TimeUtils;

class BanksService extends Service
{
    public static function lists(array $get,array $userItem): array
    {
        $searchWhere = self::setSearch([
            '%like%'   => ['keyword@short_name'],
            '='        => ['swift_code','code','status','status_in','status_out'],
            'datetime' => ['time@create_time'],
        ],$get);

        $model = new BanksModel();
        $lists = $model
            ->where($searchWhere)
            ->order('id')
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
        BanksModel::create([
            'name'          => $post['name'],
            'code'        => $post['code'],
            'bin'        => $post['bin'],
            'logo'        => $post['logo'],
            'short_name'        => $post['short_name'],
            'swift_code'        => $post['swift_code'],
            'status'        => $post['status'] ?? StatusEnum::STATUS_OFF,
            'status_in'        => $post['status_in'] ?? StatusEnum::STATUS_OFF,
            'status_out'        => $post['status_out'] ?? StatusEnum::STATUS_OFF,
            'create_time'  => time(),
            'update_time'  => time()
        ]);
    }

    public static function edit(array $post,array $userItem): void
    {
        \think\facade\Db::transaction(function () use ($post) {
            $old = BanksModel::where('id', (int) $post['id'])->lock(true)->find();
            if ($old && $old['code'] !== $post['code']
                && (\app\common\model\BankCardsModel::where('bank_code', $old['code'])->find()
                    || \app\common\model\OrderModel::where('bank_code', $old['code'])->find())) {
                throw new \app\common\exception\RequestException('银行已有银行卡或订单引用，不可修改银行代码');
            }
            BanksModel::update([
                'name'          => $post['name'],
                'code'        => $post['code'],
                'bin'        => $post['bin'],
                'logo'        => $post['logo'],
                'short_name'        => $post['short_name'],
                'swift_code'        => $post['swift_code'],
                'status'        => $post['status'] ?? StatusEnum::STATUS_OFF,
                'status_in'        => $post['status_in'] ?? StatusEnum::STATUS_OFF,
                'status_out'        => $post['status_out'] ?? StatusEnum::STATUS_OFF,
                'update_time'  => time()
            ], ['id'=>intval($post['id'])]);
        });
    }

    public static function del(array $ids,array $userItem): void
    {
        \app\common\service\BankDataService::deleteOrDisable('bank', $ids);
    }

    public static function getOptions(array $userItem): array
    {
        $model = new BanksModel();
        $data = $model
            ->where('status','=',StatusEnum::STATUS_1)
            ->field('id,code,short_name')
            ->order('id')
            ->select();
        if($data->isEmpty()){
            return [];
        }else{
            return $data->toArray();
        }
    }

}