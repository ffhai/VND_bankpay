<?php

declare(strict_types=1);

namespace app\common\enums;

/**
 * 余额交易类型的存储值
 *
 * map（存储值 => 展示名称）、写入校验与展示方法见 BalanceService::$typeMap
 */
class BalanceTypeEnum
{
    const RECHARGE = 'recharge'; // 充值
    const DEDUCT = 'deduct';     // 扣减
    const REFUND = 'refund';     // 退款
    const ADJUST = 'adjust';     // 调整
    const CONSUME = 'consume';   // 消费
}
