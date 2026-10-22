<?php
namespace app\task\service;

use app\common\basics\Service;
use app\common\enums\StatusEnum;
use app\common\exception\RequestException;
use app\common\model\DeviceNotifyModel;
use app\common\model\SmsCodeModel;
use think\facade\Db;

class SmsCodeService extends Service
{
    public static function receive(array $params): array
    {
        $notifyId = filter_var($params['notify_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $verifyCode = $params['verify_code'] ?? '';
        if ($notifyId === false || !is_string($verifyCode) || !preg_match('/^[0-9]{4,20}$/D', $verifyCode)) {
            throw new RequestException('短信验证码参数错误');
        }
        return Db::transaction(function () use ($notifyId, $verifyCode): array {
            $notify = DeviceNotifyModel::where('id', $notifyId)->lock(true)->find();
            if (!$notify) {
                throw new RequestException('设备推送不存在');
            }
            $code = SmsCodeModel::where('notify_id', $notifyId)->find();
            if (!$code) {
                $code = SmsCodeModel::create([
                    'notify_id' => $notifyId,
                    'phone_number' => $notify['phone_number'],
                    'verify_code' => $verifyCode,
                    'time' => (int) $notify['time'],
                ]);
            }
            if ((int) $notify['status'] !== StatusEnum::STATUS_2) {
                $notify->save(['status' => StatusEnum::STATUS_2]);
            }
            return ['id' => (int) $code['id'], 'notify_id' => $notifyId];
        });
    }
}
