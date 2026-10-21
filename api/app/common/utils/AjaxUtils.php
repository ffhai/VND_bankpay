<?php

declare (strict_types = 1);

namespace app\common\utils;

use app\common\enums\CodeEnum;
use think\response\Json;

/**
 * 响应工具
 */
class AjaxUtils
{
    /**
     * 规定格式
     *
     * @param int $code   (状态码)
     * @param string $msg (提示)
     * @param array $data (返回数据集)
     * @return array
     * @author zero
     */

    private static function result(int $code, string $msg, array $data=[]): array
    {
        $data = [
            'code' => $code,
            'msg'  => $msg,
            'data' => $data
        ];

        return $data ?? [];
    }

    /**
     * 响应成功
     *
     * @param array $data       (数据集)
     * @param string|array $msg (提示)
     * @param int $code         (状态码)
     * @param int $httpCode     (Http状态码)
     * @return Json
     * @author zero
     */
    public static function success($msg='操作成功', array $data=[], int $code=1, int $httpCode=200): Json
    {
        if (is_array($msg) && empty($data)) {
            $data = $msg;
            $msg  = '获取成功';
        }

        $data = self::result($code, $msg, $data);
        return json($data, $httpCode);
    }

    /**
     * 请求错误
     *
     * @param string $msg   (提示)
     * @param array $data   (数据集)
     * @param int $code     (状态码)
     * @param int $httpCode (Http状态码)
     * @return Json
     * @author zero
     */
    public static function error(string $msg='请求错误', array $data=[], int $code=CodeEnum::ERROR, int $httpCode=200): Json
    {
        $data = self::result($code, $msg, $data);
        return json($data, $httpCode);
    }
}