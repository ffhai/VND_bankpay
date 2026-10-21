<?php
namespace app\common\model;

use think\model\relation\HasOne;

class OrderModel extends CommonModel
{
    protected $name = 'order';

    const STATUS_PAYING = 1;
    const STATUS_PAYED = 2;
    const STATUS_FAIL = -1;

    protected $schema = [
        'id'=>'int',
        'order_id'=>'string',
        'trade_no'=>'string',
        'trade_uid'=>'int',
        'pay_type'=>'string',
        'channel_uid'=>'int',
        'bank_number'=>'string',
        'amount'=>'int',
        'ps'=>'string',
        'status'=>'int',
        'notify_status'=>'int',
        'notify_time'=>'int',
        'notify_url'=>'string',
        'return_url'=>'string',
        'create_time'=>'int',
        'expire_time'=>'int',
        'pay_time'=>'int',
        'settlement_time'=>'int',
    ];

    /**
     * 单关联
     *
     * @author zero
     * @return HasOne
     */
    public function trade(): HasOne
    {
        return $this->hasOne(UserModel::class, 'id', 'trade_uid')
            ->field('id,username');
    }
    public function channelUser(): HasOne
    {
        return $this->hasOne(UserModel::class, 'id', 'channel_uid');
    }
    public function bankcard(): HasOne
    {
        return $this->hasOne(BankCardsModel::class, 'bank_number', 'bank_number');
    }

}
