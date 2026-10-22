<?php
namespace app\common\model;

use think\model\relation\HasOne;

class DeviceNotifyModel extends CommonModel
{
    protected $name = 'device_notify';

    const TYPE_BANK_TRADE = 'BANK_TRADE';
    const TYPE_SMS_CODE = 'SMS_CODE';
    const TYPE_OTHER = 'OTHER';

    protected $schema = [
        'id'=>'int',
        'uid'=>'int',
        'type'=>'string',
        'phone_number'=>'string',
        'content'=>'string',
        'remark'=>'string',
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
