<?php
namespace app\common\model;

use app\common\utils\UrlUtils;

class EditorModel extends CommonModel
{
    protected $name = 'editor';

    protected $schema=[
        'id'=>'int',
        'key'=>'string',
        'name'=>'string',
        'content'=>'string',
    ];
    public function getContentByKey($key){
        $req= $this->where('key','=',$key)->find();
        if($req){
            return UrlUtils::editorAbsoluteSrc($req['content']);
        }
        return '';
    }
}
