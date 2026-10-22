<?php
namespace app\common\model;

use think\Model;

class CommonModel extends Model
{
    protected $schema = [];

    /**
     * @param $id
     * @param int $cache_time 缓存时间 整数 秒
     * @return array|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getItemById($id){
        $item = $this->find($id);
        if($item){
            return $item->toArray();
        }else{
            return false;
        }
    }

    /** 获取所有字段
     * @param string $prefix
     * @return array
     */
    public function getFields($prefix = ''){
        if(empty($prefix)){
            return array_keys($this->schema);
        }
        $fields=[];
        foreach(array_keys($this->schema) as $f){
            $fields[]=$prefix.$f;
        }
        return $fields;
    }
}
