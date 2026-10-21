<?php
namespace app\common\model;

use think\model\relation\HasOne;

class BankCardsModel extends CommonModel
{
    protected $name = 'bank_cards';

    //回调方式
    const NOTIFY_TYPE_APP = 'app';
    const NOTIFY_TYPE_ONLINE = 'online';

    protected $schema = [
        'id'=>'int',
        'uid'=>'int',
        'bank_number'=>'string',
        'bank_code'=>'string',
        'bank_user_id'=>'int',
        'balance'=>'int',
        'online_account'=>'string',
        'online_password'=>'string',
        'phone_number'=>'string',
        'notify_type'=>'string',//回调方式
        'notify_device'=>'string',//回调设备
        'limit_in_max'=>'int',
        'limit_out_max'=>'int',
        'remark'=>'string',
        'status'=>'int',
        'last_time'=>'int',
        'create_time'=>'int',
        'update_time'=>'int',
    ];

    public function user(): HasOne
    {
        return $this->hasOne(UserModel::class, 'id', 'uid');
    }
    public function bankinfo(): HasOne
    {
        return $this->hasOne(BanksModel::class, 'code', 'bank_code');
    }
    public function bankuser(): HasOne
    {
        return $this->hasOne(BankUserModel::class, 'id', 'bank_user_id');
    }

}
