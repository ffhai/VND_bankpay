<?php

declare (strict_types = 1);

namespace app\common\utils;

use think\facade\Cache;

/**
 * 缓存工具（调试模式自动关闭缓存）
 */
class CacheUtils
{
    /**
     * 缓存是否开启（调试环境不缓存）
     */
    public static function enabled(): bool
    {
        return !app()->isDebug();
    }

    /**
     * 读取缓存
     *
     * @param mixed $default 未命中时的默认值
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        if (!self::enabled()) {
            return $default;
        }
        return Cache::get($key, $default);
    }

    /**
     * 写入缓存（带标签，便于按组清除）
     */
    public static function set(string $tag, string $key, $value, int $ttl = 3600): bool
    {
        if (!self::enabled()) {
            return true;
        }
        Cache::tag($tag)->set($key, $value, $ttl);
        return true;
    }

    /**
     * 按标签清除缓存
     */
    public static function clearTag(string $tag): bool
    {
        if (!self::enabled()) {
            return true;
        }
        return Cache::tag($tag)->clear();
    }
}
