<?php

class openapi_api_params_v1_taocrm_pointlog extends openapi_api_params_abstract implements openapi_api_params_interface{

    public function checkParams($method,$params,&$sub_msg,$defined_params=array(),$dataType='kv'){
        if(parent::checkParams($method,$params,$sub_msg,$defined_params,$dataType)){
            return true;
        }else{
            return false;
        }
    }

    public function getAppParams($method){

        $params = array(
            'getlist'=>array(
                'content'=>array('required'=>'true','name'=>'pointlog.getlist','type'=>'json',
                    'cols'=>array(
                        'shop_id'=>array('name'=>'店铺ID','type'=>'string','required'=>false),
                        'member_id'=>array('name'=>'客户ID','type'=>'int','required'=>true),
                        'page_size'=>array('name'=>'每页显示','type'=>'int','required'=>true),
                        'page'=>array('name'=>'页码 从1开始','type'=>'int','required'=>true),
                        //'node_id'=>array('name'=>'节点ID','type'=>'number','required'=>false),
                    ),
                ),
            ),
        );
        return $params[$method];
    }

    public function description($method){
        $desccription = array(
            'getlist'=>array('name'=>'积分日志查询','description'=>'积分日志查询'),
        );
        return $desccription[$method];
    }
}