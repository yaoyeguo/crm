<?php

class openapi_api_params_v1_taocrm_orders extends openapi_api_params_abstract implements openapi_api_params_interface{

    public function checkParams($method,$params,&$sub_msg,$defined_params=array(),$dataType='kv'){
        if(parent::checkParams($method,$params,$sub_msg,$defined_params,$dataType)){
            return true;
        }else{
            return false;
        }
    }

    public function getAppParams($method){

        $params = array(
            'search'=>array(
                'content'=>array('required'=>'true','name'=>'taocrm.orders.search','type'=>'json',
                    'cols'=>array(
                        'shop_id'=>array('name'=>'店铺ID','type'=>'string','required'=>false),
                        'member_id'=>array('name'=>'客户ID','type'=>'int','required'=>true),
                        'start_created_date'=>array('name'=>'订单创建时间','type'=>'string','required'=>false),
                        'page_size'=>array('name'=>'每页显示','type'=>'int','required'=>true),
                        'page'=>array('name'=>'页码','type'=>'int','required'=>true),
                    ),
                ),
            ),
        );
        return $params[$method];
    }

    public function description($method){
        $desccription = array(
            'search'=>array('name'=>'客户历史订单查询','description'=>'客户历史订单查询'),
        );
        return $desccription[$method];
    }
}