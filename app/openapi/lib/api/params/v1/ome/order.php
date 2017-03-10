<?php

class openapi_api_params_v1_ome_order extends openapi_api_params_abstract implements openapi_api_params_interface{

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
                'content'=>array('required'=>'true','name'=>'ome.order.add','type'=>'array',
                    'cols'=>array(
                        'order_bn'=>array('name'=>'订单编号','type'=>'string','required'=>true),
                        'createtime'=>array('name'=>'订单创建时间 ','type'=>'int','required'=>true),
                        'modified'=>array('name'=>'订单最后修改时间 ','type'=>'int','required'=>true),
                        'status'=>array('name'=>'订单状态','type'=>'string','required'=>true,'remarks'=>'active 活动订单'),
                        'pay_status'=>array('name'=>'付款状态','type'=>'number','required'=>true,'remarks'=>'0未付款，1已付款'),
                        'ship_status'=>array('name'=>'发货状态','type'=>'number','required'=>true,'remarks'=>' 0未发货1已发货'),
                        'payed'=>array('name'=>'付款金额 ','type'=>'money','required'=>true),
                        'total_amount'=>array('name'=>'订单总金额','type'=>'money','required'=>true),
                        'delivery_time'=>array('name'=>'发货时间 ','type'=>'int','required'=>false),
                        'finish_time'=>array('name'=>'订单完成时间 ','type'=>'int','required'=>false),
                        'is_cod'=>array('name'=>'是否货到付款true/false ','type'=>'string','required'=>true),
                        'weight'=>array('name'=>'商品重量','type'=>'number','required'=>false),
                       // 'member_info'=>array('name'=>'会员信息','type'=>'number','required'=>false),
                        'member_info'=>array('name'=>'会员信息','required'=>false,'type'=>'array',
                            'items'=>array(
                                'tel'=>array('required'=>'false','name'=>'电话号码','type'=>'string'),
                                'uname'=>array('required'=>'false','name'=>'昵称','type'=>'string'),
                                'area_district'=>array('required'=>'false','name'=>'省','type'=>'string'),
                                'area_city'=>array('required'=>'false','name'=>'市','type'=>'string'),
                                'area_state'=>array('required'=>'false','name'=>'区','type'=>'string'),
                                'addr'=>array('required'=>'false','name'=>'地址','type'=>'string'),
                                'name'=>array('required'=>'false','name'=>'姓名','type'=>'string'),
                                'zip'=>array('required'=>'false','name'=>'邮编','type'=>'string'),
                                'mobile'=>array('required'=>'false','name'=>'手机号码','type'=>'string'),
                                'alipay_no'=>array('required'=>'false','name'=>'支付宝账号','type'=>'string'),
                                'email'=>array('required'=>'false','name'=>'电子邮箱','type'=>'string'),
                            ),
                        ),
                        //'consignee'=>array('name'=>'收货人信息 转化成jsonArray存入','type'=>'string','required'=>true),
                        'consignee'=>array('name'=>'收货人信息','required'=>true,'type'=>'array',
                            'items'=>array(
                                'telephone'=>array('required'=>'false','name'=>'电话号码','type'=>'string'),
                                'area_district'=>array('required'=>'false','name'=>'省','type'=>'string'),
                                'area_city'=>array('required'=>'false','name'=>'市','type'=>'string'),
                                'area_state'=>array('required'=>'false','name'=>'区','type'=>'string'),
                                'addr'=>array('required'=>'false','name'=>'地址','type'=>'string'),
                                'name'=>array('required'=>'false','name'=>'姓名','type'=>'string'),
                                'zip'=>array('required'=>'false','name'=>'邮编','type'=>'string'),
                                'mobile'=>array('required'=>'false','name'=>'手机号码','type'=>'string'),
                                'email'=>array('required'=>'false','name'=>'电子邮箱','type'=>'string'),
                            ),
                        ),
                        //'order_objects'=>array('name'=>'订单商品明细 转化成jsonArray存入','type'=>'string','required'=>true),
                        'order_objects'=>array('name'=>'订单商品明细','required'=>true,'type'=>'array',
                            'items'=>array(
                                'logistics_company'=>array('required'=>'false','name'=>'物流公司','type'=>'string'),
                                'name'=>array('required'=>'false','name'=>'商品名称','type'=>'string'),
                                'pmt_price'=>array('required'=>'false','name'=>'优惠金额','type'=>'string'),
                                'bn'=>array('required'=>'false','name'=>'货号','type'=>'string'),
                                'oid'=>array('required'=>'false','name'=>'子订单号','type'=>'string'),
                                'logistics_code'=>array('required'=>'false','name'=>'物流运单号','type'=>'string'),
                                'order_items'=>array('name'=>'商品明细','required'=>true,'type'=>'array',
                                    'items'=>array(
                                        'status'=>array('required'=>'false','name'=>'商品状态','type'=>'string'),
                                        'name'=>array('required'=>'false','name'=>'商品名称','type'=>'string'),
                                        'pmt_price'=>array('required'=>'false','name'=>'优惠价格','type'=>'string'),
                                        'sale_price'=>array('required'=>'false','name'=>'销售价格','type'=>'string'),
                                        'bn'=>array('required'=>'false','name'=>'商品编码','type'=>'string'),
                                        'sale_amount'=>array('required'=>'false','name'=>'销售总价','type'=>'string'),
                                        'product_attr'=>array('required'=>'false','name'=>'商品属性','type'=>'string'),
                                        'amount'=>array('required'=>'false','name'=>'小计','type'=>'string'),
                                        'cost'=>array('required'=>'false','name'=>'成本价','type'=>'string'),
                                        'shop_goods_id'=>array('required'=>'false','name'=>'前端商城商品id','type'=>'string'),
                                        'sendnum'=>array('required'=>'false','name'=>'发货数量','type'=>'string'),
                                        'score'=>array('required'=>'false','name'=>'获取积分','type'=>'string'),
                                        'quantity'=>array('required'=>'false','name'=>'数量','type'=>'string'),
                                        'price'=>array('required'=>'false','name'=>'单价','type'=>'string'),
                                        'shop_product_id'=>array('required'=>'false','name'=>'货品ID','type'=>'string'),
                                    ),
                                ),
                                'amount'=>array('required'=>'false','name'=>'小计','type'=>'string'),
                                'score'=>array('required'=>'false','name'=>'获得积分','type'=>'string'),
                                'shop_goods_id'=>array('required'=>'false','name'=>'前端商城商品id','type'=>'string'),
                                'sale_price'=>array('required'=>'false','name'=>'市场价','type'=>'string'),
                                'price'=>array('required'=>'false','name'=>'单价','type'=>'string'),
                                'quantity'=>array('required'=>'false','name'=>'数量','type'=>'string'),
                            ),
                        ),
                        'has_invoice'=>array('name'=>'是否需要发票 true/false','type'=>'string','required'=>false),
                        'invoice_title'=>array('name'=>'发票抬头 ','type'=>'string','required'=>false),
                        'invoice_fee'=>array('name'=>'发票费用 ','type'=>'money','required'=>false),
                        'custom_mark'=>array('name'=>'买家备注 ','type'=>'string','required'=>false),
                        //'payments'=>array('name'=>'付款单明细 ','type'=>'string','required'=>false),
                        'payments'=>array('name'=>'付款单明细','required'=>false,'type'=>'array',
                            'items'=>array(
                                'account'=>array('required'=>'false','name'=>'账号','type'=>'string'),
                                'pay_time'=>array('required'=>'false','name'=>'支付时间','type'=>'string'),
                                'pay_account'=>array('required'=>'false','name'=>'支付账号','type'=>'string'),
                                'paymethod'=>array('required'=>'false','name'=>'支付方式','type'=>'string'),
                                'money'=>array('required'=>'false','name'=>'支付金额','type'=>'string'),
                                'memo'=>array('required'=>'false','name'=>'备注','type'=>'string'),
                                'pay_bn'=>array('required'=>'false','name'=>'支付订单号','type'=>'string'),
                                'paycost'=>array('required'=>'false','name'=>'付款手续费','type'=>'string'),
                            ),
                        ),
                        'source'=>array('name'=>'订单来源','type'=>'string','required'=>false,'remark'=>' 如果source为manual，订单会实时处理。否则进入订单处理队列。'),
                        'logistics_no'=>array('name'=>'物流单号 ','type'=>'string','required'=>false),
                    ),
                ),
            ),
        );
        return $params[$method];
    }

    public function description($method){
        $desccription = array(
            //'add'=>array('name'=>'订单增加','description'=>'订单增加'),
        );
        return $desccription[$method];
    }
}