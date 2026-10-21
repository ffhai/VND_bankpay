<?php

declare(strict_types=1);

namespace app\common\model;

use think\model\relation\HasOne;

/**
 * 余额变动记录模型
 */
class BalanceLogModel extends CommonModel
{
    protected $name = 'balance_log';

    protected $schema = [
        'id' => 'int',
        'user_id' => 'int',
        'change_amount' => 'int', // 变动金额（分，可为负）
        'balance_after' => 'int', // 变动后余额（分）
        'type' => 'string',       // 交易类型
        'remark' => 'string',     // 备注
        'operator' => 'string',   // 操作员
        'create_time' => 'int',   // 时间
    ];

    public function user(): HasOne
    {
        return $this->hasOne(UserModel::class, 'id', 'user_id');
    }
}
