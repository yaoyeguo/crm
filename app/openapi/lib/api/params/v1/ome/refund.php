<?php

class openapi_api_params_v1_ome_refund extends openapi_api_params_abstract implements openapi_api_params_interface{

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
                'content'=>array('required'=>'true','name'=>'ome.refund.add','type'=>'json',
                    'cols'=>array(
                        'refund_id'=>array('name'=>'退款单编号','type'=>'string','required'=>true),
                        'good_status'=>array('name'=>'货品状态','type'=>'string','required'=>false),
                        'tid'=>array('name'=>'订单编号 ','type'=>'string','required'=>false),
                        'has_good_return'=>array('name'=>'是否需要退货 true/false','type'=>'string','required'=>false),
                        'desc'=>array('name'=>'退款单备注 ','type'=>'string','required'=>false),
                        'status'=>array('name'=>'退款单状态 ','type'=>'string','required'=>true),
                        'logistics_company'=>array('name'=>'物流公司 ','type'=>'string','required'=>false),
                        'logistics_no'=>array('name'=>'物流单号 ','type'=>'string','required'=>false),
                        'reason'=>array('name'=>'退款原因 ','type'=>'string','required'=>false),
                        'refund_fee'=>array('name'=>'退款金额 ','type'=>'money','required'=>true),
                        'total_fee'=>array('name'=>'订单总金额 ','type'=>'money','required'=>true),
                        'buyer_nick'=>array('name'=>'买家帐号 ','type'=>'string','required'=>false),
                        'created'=>array('name'=>'退款申请时间 ','type'=>'date','required'=>true),
                        'modified'=>array('name'=>'退款单更新时间 ','type'=>'date','required'=>true),
                    ),
                ),
            ),
        );
        return $params[$method];
    }

    public function description($method){
        $desccription = array(
            'add'=>array('name'=>'退款单增加','description'=>'退款单增加'),
        );
        return $desccription[$method];
    }
}