<?php

declare (strict_types = 1);

namespace app\channel\service;

use think\facade\Db;


use app\common\basics\Service;
use app\common\enums\StatusEnum;
use app\common\exception\RequestException;
use app\common\model\BankCardsModel;
use app\common\model\OrderModel;
use app\common\utils\TimeUtils;

class BankCardsService extends Service
{
    public static function lists(array $get,array $userItem): array
    {
        $searchWhere = self::setSearch([
            '%like%'   => ['keyword@bank_cards_model.bank_number'],
            '='        => ['status@bank_cards_model.status','bank_code@bank_cards_model.bank_code','bank_user_id@bank_cards_model.bank_user_id'],
            'datetime' => ['time@bank_cards_model.create_time'],
        ],$get);

        $time = $get['time'] ?? [];
        if(!is_array($time) || count($time) != 2){
            $time = [date('Y-m-d 00:00:00'),date('Y-m-d 23:59:59')];
        }
        $time = [strtotime($time[0]),strtotime($time[1])];

        $model = new BankCardsModel();
        $orderModel = new OrderModel();
        $lists = $model
            ->withJoin([
                'bankinfo' =>['code','bin','short_name','swift_code'],
                'bankuser' =>['id','name','idcard'],
            ],'left')
            ->where('bank_cards_model.uid','=',$userItem['id'])
            ->where($searchWhere)
            ->order('id desc')
            ->paginate([
                'page'      => $get['page']  ?? 1,
                'list_rows' => $get['pagesize'] ?? 20
            ])->toArray();


        foreach ($lists['data'] as &$item) {
            $item['create_time'] = TimeUtils::formatTime($item['create_time']);
            $item['update_time'] = TimeUtils::formatTime($item['update_time']);
            $item['last_time'] = TimeUtils::formatTime($item['last_time']);
            $item['amount'] =$orderModel
                ->where('bank_number','=',$item['bank_number'])
                ->where('status','=',OrderModel::STATUS_PAYED)
                ->where('create_time','between',$time)
                ->sum('amount');
        }

        return ['count'=>$lists['total'], 'items'=>$lists['data']] ?? [];
    }

    public static function add(array $post,array $userItem): void
    {
        foreach (['bank_number', 'online_account', 'online_password', 'phone_number'] as $field) {
            $post[$field] = preg_replace('/[\s\p{Z}\x{200B}\x{FEFF}]+/u', '', (string) ($post[$field] ?? ''));
        }
        foreach (['balance', 'limit_in_max', 'limit_out_max'] as $field) {
            $post[$field] = \app\common\utils\AmountUtils::integer($post[$field] ?? 0);
        }
        if (empty($post['bank_number'])) {
            throw new RequestException('卡号不能为空');
        }
        Db::transaction(function () use ($post, $userItem) {
            $ownerId = (int) $userItem['id'];
            \app\common\service\BankDataService::validateCard($post, $ownerId);
            BankCardsModel::create([
                'uid'          => $userItem['id'],
                'bank_number'          => $post['bank_number'],
                'bank_code'        => $post['bank_code'],
                'bank_user_id'        => $post['bank_user_id'],
                'balance'        => $post['balance'],
                'online_account'        => $post['online_account'],
                'online_password'        => $post['online_password'],
                'phone_number'        => $post['phone_number'],
                'notify_type'        => $post['notify_type'],
                'notify_device'        => $post['notify_device'],
                'limit_in_max'        => $post['limit_in_max'],
                'limit_out_max'        => $post['limit_out_max'],
                'remark'        => $post['remark'] ?? '',
                'status'        => $post['status'] ?? StatusEnum::STATUS_OFF,
                'create_time'  => time(),
                'update_time'  => time()
            ]);
        });
    }

    public static function edit(array $post,array $userItem): void
    {
        foreach (['bank_number', 'online_account', 'online_password', 'phone_number'] as $field) {
            $post[$field] = preg_replace('/[\s\p{Z}\x{200B}\x{FEFF}]+/u', '', (string) ($post[$field] ?? ''));
        }
        foreach (['balance', 'limit_in_max', 'limit_out_max'] as $field) {
            $post[$field] = \app\common\utils\AmountUtils::integer($post[$field] ?? 0);
        }
        if (empty($post['bank_number'])) {
            throw new RequestException('卡号不能为空');
        }
        Db::transaction(function () use ($post, $userItem) {
            $ownerId = (int) $userItem['id'];
            \app\common\service\BankDataService::validateCard($post, $ownerId, (int) $post['id']);
            BankCardsModel::update([
                'bank_number'          => $post['bank_number'],
                'bank_code'        => $post['bank_code'],
                'bank_user_id'        => $post['bank_user_id'],
                'balance'        => $post['balance'],
                'online_account'        => $post['online_account'],
                'online_password'        => $post['online_password'],
                'phone_number'        => $post['phone_number'],
                'notify_type'        => $post['notify_type'],
                'notify_device'        => $post['notify_device'],
                'limit_in_max'        => $post['limit_in_max'],
                'limit_out_max'        => $post['limit_out_max'],
                'remark'        => $post['remark'] ?? '',
                'status'        => $post['status'] ?? StatusEnum::STATUS_OFF,
                'update_time'  => time()
            ], ['id'=>intval($post['id']),'uid'=> $userItem['id']]);
        });
    }

    public static function del(array $ids,array $userItem): void
    {
        \app\common\service\BankDataService::deleteOrDisable('card', $ids, (int) $userItem['id']);
    }

    public static function getOptions(array $userItem): array
    {
        $model = new BankCardsModel();
        $data = $model
            ->where('uid','=',$userItem['id'])
            ->where('status','=',StatusEnum::STATUS_1)
            ->field('id,bank_number')
            ->order('id')
            ->select();
        if($data->isEmpty()){
            return [];
        }else{
            return $data->toArray();
        }
    }

}
