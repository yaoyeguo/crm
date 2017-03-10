<?php

class openapi_api_params_v1_taocrm_posorder extends openapi_api_params_abstract implements openapi_api_params_interface{

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
                'content'=>array('required'=>'true','name'=>'taocrm.posorder.add','type'=>'json',
                    'cols'=>array(
                        'order_bn'=>array('name'=>'订单号','required'=>true,'type'=>'string'),
                        // 'uid'=>array('name'=>'会员编号','required'=>true,'type'=>'string'),
                        'shop_node_id'=>array('name'=>'店铺节点ID','required'=>true,'type'=>'int'),
                        'uname'=>array('name'=>'客户名/昵称','required'=>true,'type'=>'string'),
                        'buy_time'=>array('name'=>'购买时间','required'=>true,'type'=>'datetime'),
                        'is_refund'=>array('name'=>'是否冲红销售0否1是','required'=>true,'type'=>'number'),
                        'refund_order_bn'=>array('name'=>'冲红订单号 ','required'=>false,'type'=>'string'),
                        'order_amount'=>array('name'=>'订单金额','required'=>true,'type'=>'money'),
                        'order_status'=>array('name'=>'订单状态','required'=>true,'type'=>'string'),
                        'pay_status'=>array('name'=>'支付状态','required'=>true,'type'=>'string'),
                        'ship_status'=>array('name'=>'发货状态','required'=>true,'type'=>'string'),
                        'payment'=>array('name'=>'支付方式','required'=>true,'type'=>'string'),
                        'item_amount'=>array('name'=>'商品金额','required'=>true,'type'=>'money'),
                        'shipping'=>array('name'=>'配送方式','required'=>false,'type'=>'string'),
                        'shipping_fee'=>array('name'=>'运费金额','required'=>true,'type'=>'money'),
                        'consignee'=>array('name'=>'收货人','required'=>true,'type'=>'string'),
                        'consignee_state'=>array('name'=>'收货省市','required'=>true,'type'=>'string'),
                        'consignee_city'=>array('name'=>'城市','required'=>true,'type'=>'string'),
                        'consignee_area'=>array('name'=>'地区','required'=>true,'type'=>'string'),
                        'consignee_address'=>array('name'=>'详细地址','required'=>true,'type'=>'string'),
                        'consignee_zip'=>array('name'=>'邮编','required'=>false,'type'=>'string'),
                        'consignee_mobile'=>array('name'=>'手机','required'=>true,'type'=>'string'),
                        'consignee_telephone'=>array('name'=>'电话','required'=>false,'type'=>'string'),
                        'is_shipments'=>array('name'=>'订单发货(0正常1暂停默认0)','required'=>false,'type'=>'number'),
                        //'sale_num'	=>array('name'=>'卖家','type'=>'string','required'=>true,'type'=>'string'),
                        'buy_remark'=>array('name'=>'买家备注','required'=>false,'type'=>'string'),
                        'pay_money'=>array('name'=>'付款金额','required'=>false,'type'=>'money'),
                        'contact'=>array('name'=>'联系人','required'=>false,'type'=>'string'),
                        'pay_time'=>array('name'=>'付款时间','required'=>false,'type'=>'datetime'),
                        'delivery_time'=>array('name'=>'收货时间','required'=>false,'type'=>'datetime'),
                        'finish_time'=>array('name'=>'完成时间','required'=>false,'type'=>'datetime'),
                        'consumer_terminal'=>array('name'=>'消费终端','required'=>false,'type'=>'string'),
                        'op_name'=>array('name'=>'操作人','required'=>false,'type'=>'string'),
                        'buy_msg'=>array('name'=>'买家留言','required'=>false,'type'=>'string'),
                        'order_items'=>array('name'=>'订单商品明细','required'=>true,'type'=>'array',
                            'items'=>array(
                                'goods_bn'=>array('required'=>'true','name'=>'商品编码','type'=>'string'),
                                'name'=>array('required'=>'true','name'=>'商品名称','type'=>'string'),
                                'bn'=>array('required'=>'true','name'=>'ERP商品编码','type'=>'string'),
                                'nums'=>array('required'=>'true','name'=>'商品数量','type'=>'int'),
                                'price'=>array('required'=>'true','name'=>'商品单价','type'=>'money'),
                                'total_price'=>array('required'=>'true','name'=>'商品总价','type'=>'money'),
                            ),
                        ),
                    ),
                ),
            ),
        );
        return $params[$method];
    }

    public function description($method){
        $desccription = array(
            'add'=>array('name'=>'POS订单','description'=>'POS订单'),
        );
        return $desccription[$method];
    }
}