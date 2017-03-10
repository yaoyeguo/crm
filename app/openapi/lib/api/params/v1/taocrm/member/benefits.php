<?php

class openapi_api_params_v1_taocrm_member_benefits extends openapi_api_params_abstract implements openapi_api_params_interface{

    public function checkParams($method,$params,&$sub_msg,$defined_params=array(),$dataType='kv'){
        if(parent::checkParams($method,$params,$sub_msg,$defined_params,$dataType)){
            return true;
        }else{
            return false;
        }
    }

    public function getAppParams($method){

        $params = array(
            'add'=>array(
                'content'=>array('required'=>'true','name'=>'taocrm.member_benefits.add','type'=>'json',
                    'cols'=>array(
                        'member_id'=>array('name'=>'客户ID','type'=>'int','required'=>true),
                        'benefits_type'=>array('name'=>'权益类型0金额1计次2折扣3其他','type'=>'number','required'=>true),
                        'get_benefits_mode'=>array('name'=>'获取权益方式0收费1赠送2其他 ','type'=>'number','required'=>true),
                        'op_mode'=>array('name'=>'新增或扣减权益0新增1扣减(使用) ','type'=>'number','required'=>true),
                        'get_benefits_desc'=>array('name'=>'获取权益说明 ','type'=>'string','required'=>false),
                        'benefits_code'=>array('name'=>'权益项代码 ','type'=>'string','required'=>true),
                        'benefits_name'=>array('name'=>'权益项名称 ','type'=>'string','required'=>true),
                        'nums'=>array('name'=>'权益值 (金额或者次数或者折扣)','type'=>'string','required'=>true),
                        'effectie_time'=>array('name'=>'生效时间 ','type'=>'string','required'=>true),
                        'failure_time'=>array('name'=>'失效时间 (不填就是永久有效)','type'=>'string','required'=>false),
                        'is_enable'=>array('name'=>'是否可用 0：可用 1：不可用 ','type'=>'string','required'=>true),
                        'source_order_bn'=>array('name'=>'来源关联单号 ','type'=>'string','required'=>false),
                        'source_business_code'=>array('name'=>'来源业务CodeLQ易开店','type'=>'string','required'=>false),
                        'source_business_name'=>array('name'=>'来源业务名称 ','type'=>'string','required'=>false),
                        'source_store_name'=>array('name'=>'来源门店代码','type'=>'string','required'=>false),
                        'source_terminal_code'=>array('name'=>'来源终端代码 ','type'=>'string','required'=>false),
                        'memo'=>array('name'=>'说明备注 ','type'=>'string','required'=>false),
                        'op_name'=>array('name'=>'创建人','type'=>'string','required'=>true),
                        'op_time'=>array('name'=>'创建时间 ','type'=>'date','required'=>true),
                    ),
                ),
            ),
            'additem'=>array(
                'content'=>array('required'=>'true','name'=>'taocrm.member_benefits.additem','type'=>'json',
                    'cols'=>array(
                        'benefits_code'=>array('name'=>'权益项代码 ','type'=>'string','required'=>true),
                        'benefits_name'=>array('name'=>'权益项名称 ','type'=>'string','required'=>true),
                        'source'=>array('name'=>'来源业务 ','type'=>'string','required'=>true),
                        'is_enable'=>array('name'=>'是否可用(0不可用1可用)','type'=>'number','required'=>true),
                        'op_name'=>array('name'=>'创建人','type'=>'string','required'=>true),
                        'op_time'=>array('name'=>'创建时间 ','type'=>'date','required'=>true),
                    ),
                ),
            ),
            'getlogs'=>array(
                'content'=>array('required'=>'true','name'=>'taocrm.member_benefits.getlogs','type'=>'json',
                    'cols'=>array(
                        'member'=>array('name'=>'客户ID  ','type'=>'int','required'=>true),
                        'start_date'=>array('name'=>'开始时间  ','type'=>'date','required'=>true),
                        'end_date'=>array('name'=>'结束时间  ','type'=>'date','required'=>true),
                    ),
                ),
            ),
        );
        return $params[$method];
    }

    public function description($method){
        $desccription = array(
            'add'=>array('name'=>'新增客户储值权益','description'=>'新增客户储值权益'),
            'additem'=>array('name'=>'客户新增权益项','description'=>'客户新增权益项'),
            'getlogs '=>array('name'=>'查询客户权益账户变更明细','description'=>'查询客户权益账户变更明细'),
        );
        return $desccription[$method];
    }
}