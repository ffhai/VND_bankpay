<?php
//下级
namespace app\admin\controller;

use app\common\basics\CrudController;
use app\common\model\EditorModel;
use app\common\utils\AjaxUtils;
use app\common\utils\UrlUtils;
use think\App;
use think\response\Json;


class Editor extends CrudController
{

    public function items():Json
    {
        $model = new EditorModel();
        $data = $model->select();
        $items = [];
        foreach($data as $item){
            $item['content'] = UrlUtils::editorAbsoluteSrc($item['content']);
            $items[]=$item;
        }
        return AjaxUtils::success("获取成功",$items);
    }

    public function save(){
        $params = $this->request->param();

        $model = new EditorModel();

        foreach($params as $item){
            if(isset($item['key']) && !empty($item['key'])){
                $item['content'] = UrlUtils::editorRelativeSrc($item['content']);
                $model->where('key','=',$item['key'])->update(array('content'=>$item['content']));
            }
        }
        return AjaxUtils::success("保存成功");
    }



}
