<?php
namespace app\common\model;

class UploadModel extends CommonModel
{
    protected $name = 'upload';
    protected $schema=[
        'id'=>'int',
        'name'=>'string',
        'size'=>'int',
        'mime'=>'string',
        'type'=>'string',
        'path'=>'string',
        'time'=>'int',
    ];
}
