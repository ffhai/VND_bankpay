<?php
declare(strict_types=1);
namespace app\common\utils;

use app\common\exception\RequestException;

class RateUtils
{
    // 百分数保留四位，例如 1.2500 表示 1.25%。
    public static function normalize($value): string
    {
        if ((!is_string($value) && !is_int($value) && !is_float($value)) ||
            !preg_match('/^\d{1,3}(?:\.\d{1,4})?$/D', (string) $value) || (float) $value > 100) {
            throw new RequestException('费率必须为0至100的百分数，最多4位小数');
        }
        return number_format((float) $value, 4, '.', '');
    }

    public static function fee(int $total, $rate): int
    {
        $units = (int) str_replace('.', '', self::normalize($rate));
        // 拆分计算，避免13位金额乘费率溢出或浮点误差。
        return intdiv($total, 1000000) * $units
            + intdiv(($total % 1000000) * $units + 500000, 1000000);
    }
}
