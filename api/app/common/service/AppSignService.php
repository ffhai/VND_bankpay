<?php

declare (strict_types = 1);

namespace app\common\service;


use app\common\basics\Service;
use app\common\enums\StatusEnum;
use app\common\exception\RequestException;
use app\common\model\AppListModel;

class AppSignService extends Service
{
    /**
     * 签名有效期（秒）：请求时间戳与服务器时间的最大允许偏差
     */
    private const SIGN_EXPIRE_SECONDS = 60;

    /**
     * 校验 appid + sign 签名，返回应用信息
     *
     * @param array $params
     * @return array
     * @throws RequestException
     */
    public static function check($params = []){
        ksort($params);
        $appid = $params['appid'] ?? '';
        $sign = $params['sign'] ?? '';
        $time = intval($params['time'] ?? 0);

        // 双向校验时间戳：既拒绝过期请求，也拒绝未来时间戳，
        // 否则带上远期 time 的签名永远落在窗口内，可被无限重放
        if(empty($appid) || empty($sign) || abs(time() - $time) > self::SIGN_EXPIRE_SECONDS){
            throw new RequestException('参数错误');
        }

        $appModel = new AppListModel();
        $appItem = $appModel->where('appid','=',$appid)->find();
        if(!$appItem || $appItem['status'] != StatusEnum::STATUS_1){
            throw new RequestException('appid不存在');
        }
        $appItem = $appItem->toArray();
        // 用 hash_equals 做定长比较，避免 != 的短路比较被用来逐字节爆破签名
        if(!is_string($sign) || !hash_equals(self::order_sign($params,$appItem['secret']),$sign)){
            throw new RequestException('sign错误',-1,['sign_str'=>self::build_sign_param($params) . '&secret=****']);
        }
        return $appItem;
    }

    /*
     * 签名
     */
    public static function order_sign(array $params,string $secret){
        $data = self::build_sign_param($params);
        return md5($data.'&secret='.$secret);
    }
    /*
     * 拼接待签名参数
     */
    public static function build_sign_param(array $params){
        ksort($params);
        unset($params['sign']);

        $parts = [];
        foreach ($params as $k => $v) {
            $v = ''.$v;
            if (!empty($v)) {
                $parts[] = $k . '=' . $v;
            }
        }

        return implode('&', $parts);
    }
}