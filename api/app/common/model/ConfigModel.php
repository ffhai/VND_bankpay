<?php
namespace app\common\model;

use app\common\utils\CacheUtils;
use app\common\utils\UrlUtils;
use think\model\relation\HasOne;

class ConfigModel extends CommonModel
{
    protected $name = 'config';

    public $type_list = ['text','image','select'];

    protected $schema=[
        'id'=>'int',
        'group_id'=>'int',
        'key'=>'string',
        'type'=>'string',
        'name'=>'string',
        'content'=>'string',
        'placeholder'=>'string',
        'params'=>'string',
        'sort'=>'int',
        'show'=>'int',
        'create_time'=>'int',
        'update_time'=>'int',
    ];
    public function getContentByKey($key){
        $cacheKey = 'config_content_' . $key;
        $content = CacheUtils::get($cacheKey);
        if ($content !== null) {
            return $content;
        }

        $req = $this->where('key','=',$key)->find();
        if($req){
            $content = ($req['type'] == 'image') ? UrlUtils::editorAbsoluteSrc($req['content']) : $req['content'];
        } else {
            $content = '';
        }

        CacheUtils::set('config', $cacheKey, $content);
        return $content;
    }

    public function group(): HasOne
    {
        return $this->hasOne(ConfigGroupModel::class, 'id', 'group_id');
    }
}

