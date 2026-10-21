<?php
namespace app\common\model;

use think\model\relation\HasOne;

class AppListModel extends CommonModel
{
    protected $name = 'app_list';

    protected $schema = [
        'id'=>'int',
        'uid'=>'int',
        'appid'=>'string',
        'secret'=>'string',
        'status'=>'int',
        'remark'=>'string',
        'create_time'=>'int',
        'update_time'=>'int'
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
