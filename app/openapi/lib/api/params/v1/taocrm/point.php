<?php

class openapi_api_params_v1_taocrm_point extends openapi_api_params_abstract implements openapi_api_params_interface{

    public function checkParams($method,$params,&$sub_msg,$defined_params=array(),$dataType='kv'){
        if(parent::checkParams($method,$params,$sub_msg,$defined_params,$dataType)){
            return true;
        }else{
            return false;
        }
    }

    public function getAppParams($method){

        $params = array(
            'update'=>array(
                'content'=>array('required'=>'true','name'=>'taocrm.point.update','type'=>'json',
                    'cols'=>array(
                        'shop_id'=>array('name'=>'店铺ID','type'=>'string','required'=>true),
                        'member_id'=>array('name'=>'客户ID','type'=>'int','required'=>true),
                        'point'=>array('name'=>'积分数值','type'=>'number','required'=>true),
                        'type'=>array('name'=>'积分类型','type'=>'string','required'=>false),
                        'point_desc'=>array('name'=>'积分描述','type'=>'string','required'=>true),
                        //'node_id'=>array('name'=>'节点ID','type'=>'int','required'=>false),
                    ),
                ),
            ),
            'get'=>array(
                'content'=>array('required'=>'true','name'=>'taocrm.point.get','type'=>'json',
                    'cols'=>array(
                        'member_id'=>array('name'=>'客户ID','type'=>'int','required'=>true),
                        // 'node_id'=>array('name'=>'节点ID','type'=>'int','required'=>false),
                    ),
                ),
            ),
            'update_by_parent_code'=>array(
                'content'=>array('required'=>'true','name'=>'taocrm.point.update_by_parent_code','type'=>'json',
                    'cols'=>array(
                        'register_crm_member_id'=>array('name'=>'注册人CRM会员ID ','type'=>'int','required'=>true),
                        'parent_code'=>array('name'=>'推荐人的推荐码 ','type'=>'int','required'=>true),
                        'point'=>array('name'=>'积分修改值 ','type'=>'int','required'=>true)
                    ),
                ),
            ),
        );
        return $params[$method];
    }

    public function description($method){
        $desccription = array(
            'update'=>array('name'=>'更新用户积分添加修改','description'=>'更新用户积分添加修改'),
            'get'=>array('name'=>'积分查询','description'=>'积分查询'),
            'update_by_parent_code'=>array('name'=>'根据推荐码更新积分接口','description'=>'根据推荐码更新积分接口'),
        );
        return $desccription[$method];
    }
}