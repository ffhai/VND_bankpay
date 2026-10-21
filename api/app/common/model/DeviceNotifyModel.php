<?php
namespace app\common\model;

use think\model\relation\HasOne;

class DeviceNotifyModel extends CommonModel
{
    protected $name = 'device_notify';

    const STATUS_NO = 0;
    const STATUS_SUCC = 1;
    const STATUS_FIAL = 2;
    const STATUS_OTHER = -1;

    const TYPE_BANK_TRADE = 'BANK_TRADE';
    const TYPE_SMS_CODE = 'SMS_CODE';
    const TYPE_OTHER = 'OTHER';

    protected $schema = [
        'id'=>'int',
        'uid'=>'int',
        'type'=>'string',
        'phone_number'=>'string',
        'content'=>'string',
        'card_number'=>'string',
        'balance'=>'int',
        'sms_code'=>'string',
        'remark'=>'string',
        'order_id'=>'string',
        'time'=>'int',
        'status'=>'int'
    ];

    /**
     * 单关联
     *
     * @author zero
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(UserModel::class, 'id', 'uid')
            ->field('id,username');
    }

}
