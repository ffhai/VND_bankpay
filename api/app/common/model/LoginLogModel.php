<?php
namespace app\common\model;

class LoginLogModel extends CommonModel
{
    protected $name = 'login_log';

    protected $schema=[
        'id'=>'int',
        'uid'=>'int',
        'device'=>'string',
        'time'=>'int',
        'status'=>'int',
        'desc'=>'string',
        'ip'=>'string',
    ];

    public function log($uid,$device,$status,$ip,$desc=''){
        return $this->insert(array('uid'=>$uid,'device'=>$device,'time'=>time(),'status'=>$status,'desc'=>$desc,'ip'=>$ip));
    }

}