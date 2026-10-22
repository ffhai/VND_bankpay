<?php

declare (strict_types = 1);

namespace app\common\service;

use app\common\basics\Service;
use app\common\enums\StatusEnum;
use app\common\exception\RequestException;
use app\common\model\DeviceNotifyModel;
use think\facade\Db;

class DeviceNotifyService extends Service
{
    public static function addContent(string $phone_number, string $content, array $userItem)
    {
        DeviceNotifyModel::create([
            'uid'=>$userItem['id'] ?? 0,
            'content'=>$content,
            'phone_number'=>$phone_number,
            'time'=>time(),
            'status'=>StatusEnum::STATUS_1,
        ]);
    }

    public static function getPending(){
        $model = new DeviceNotifyModel();
        $items = $model->where('status','=',StatusEnum::STATUS_1)->field('id,content,time')->order('id')->select();
        if($items->isEmpty()){
            return [];
        }
        return $items->toArray();
    }

    public static function setPending(array $params): array
    {
        $id = filter_var($params['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $status = filter_var($params['status'] ?? null, FILTER_VALIDATE_INT);
        if ($id === false || !in_array($status, [StatusEnum::STATUS_2, StatusEnum::STATUS_OFF], true)) {
            throw new RequestException('推送处理状态参数错误');
        }
        return Db::transaction(function () use ($id, $status): array {
            $notify = DeviceNotifyModel::where('id', $id)->lock(true)->find();
            if (!$notify) {
                throw new RequestException('设备推送不存在');
            }
            // 只回报处理结果，不再解析交易、匹配订单或修改银行卡余额。
            // 已完成的推送不被迟到的失败回报覆盖。
            if ((int) $notify['status'] === StatusEnum::STATUS_1) {
                $notify->save(['status' => $status]);
            }
            return ['id' => $id, 'status' => (int) $notify['status']];
        });
    }
}
