<?php


namespace app\common\enums;

/**
 * code枚举类
 */
class CodeEnum
{
    const SUCCESS   = 1; //操作成功;
    const ERROR   = -1; //操作失败;
    const NOT_AUTH   = -10001; //未登录
    const NOT_PERMS   = -10002; //无权限


    /**
     * 根据Code获取描述
     *
     * @param int $code
     * @return string
     * @author zero
     */
    public static function getMsgByCode(int $code): string
    {
        $desc = [
            self::SUCCESS     => '操作成功',
            self::ERROR     => '操作失败',
            self::NOT_AUTH     => '未登录',
            self::NOT_PERMS     => '无权限',
        ];

        return $desc[$code] ?? '未知异常';
    }
}