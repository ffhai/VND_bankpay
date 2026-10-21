<?php
declare(strict_types=1);
namespace app\common\utils;

use app\common\exception\RequestException;

class AmountUtils
{
    const MAX = 9999999999999;

    public static function integer($value, bool $signed = false, bool $allowZero = true): int
    {
        $pattern = $signed ? '/^-?\d{1,13}$/D' : '/^\d{1,13}$/D';
        if ((!is_int($value) && !is_string($value)) || !preg_match($pattern, (string) $value)) {
            throw new RequestException('金额必须为整数，数字长度不超过13位');
        }
        $amount = (int) $value;
        if (!$allowZero && $amount === 0) { throw new RequestException('金额不能为0'); }
        return $amount;
    }
}
