<?php
/**
 * 订单业务同步请求
 * @author ome
 * @access public
 * @copyright www.shopex.cn 2010
 *
 */
class ecorder_rpc_request_order extends ome_rpc_request {
    //订单状态
    var $status = array(
        'active' => 'TRADE_ACTIVE',
        'dead' => 'TRADE_CLOSED',
        'finish' => 'TRADE_FINISHED',
    );
    //订单状态名称
    var $status_name = array(
        'active' => '活动',
        'dead' => '取消',
        'finish' => '完成',
    );
    //订单支付状态
    var $pay_status = array(
        '0' => 'PAY_NO',
        '1' => 'PAY_FINISH',
        '2' => 'PAY_TO_MEDIUM',
        '3' => 'PAY_PART',
        '4' => 'REFUND_PART',
        '5' => 'REFUND_ALL',
    );
    //订单发货状态
    var $ship_status = array(
        '0' => 'SHIP_NO',
        '1' => 'SHIP_FINISH',
        '2' => 'SHIP_PART',
        '3' => 'RETRUN_PART',
        '4' => 'RETRUN_ALL',
    );
    //订单旗标(b0:灰色  b1:红色  b2:橙色  b3:黄色  b4:蓝色  b5:紫色)
    var $mark_type = array(
        'b0' => '0',
        'b1' => '1',
        'b2' => '2',
        'b3' => '3',
        'b4' => '4',
        'b5' => '5',
    );
    //订单类型。可选值:goods(商品),gift(赠品)。默认为goods 
    var $obj_type = array(
        'goods' => 'goods',
        'gift' => 'gift',
    );
    //货品状态:默认为false（正常）,true：取消
    var $product_status = array(
        'false' => 'normal',
        'true' => 'cancel',
    );
    
    /**
     * 订单编辑
     * @access public
     * @param int $order_id 订单主键ID
     * @return boolean
     */
    public function update_order($order_id=''){
        
        if(!empty($order_id)){

            $orderObj = &app::get('ome')->model('orders');
            $productsObj = &app::get('ome')->model('products');
            $membersObj = &app::get('ome')->model('members');
            $shopObj = &app::get('ome')->model('shop');
            $specificationObj = &app::get('ome')->model('specification');
            $pmtObj = &app::get('ome')->model('order_pmt');
            $order = $orderObj->dump($order_id, '*', array('order_objects'=>array('*',array('order_items'=>array('*')))));
            
            //-- 子订单信息
            $object_key = 0;
            if ($order['order_objects']){
                foreach ($order['order_objects'] as $objects){
                    $order_objects['order'][$object_key] = array(
                        'oid' => $objects['bn'],
                        'type' => $this->obj_type[$objects['']['obj_type']]?$this->obj_type[$objects['obj_type']]:'goods',
                        'type_alias' => $objects['obj_alias'],
                        'iid' => $objects['goods_id'],
                        'title' => $objects['name'],
                        'items_num' => $objects['quantity'],
                        'total_order_fee' => $objects['amount'],
                        'weight' => $objects['weight'],
                    );
                    if ($objects['order_items'])
                    foreach ($objects['order_items'] as $items){
                       $product_id = $items['product_id'];
                       $products_info = $productsObj->dump($product_id, 'spec_desc');
                       $product_attr = array();
                       if ($products_info['spec_value'])
                       foreach ($products_info['spec_value'] as $spec_key=>$spec_val){
                           $specification_info = $specificationObj->dump(array('spec_id'=>$spec_key), 'spec_name');
                           $product_attr[] = $specification_info['spec_name'].":".$spec_val;
                       }
                       $product_attr = implode(';',$product_attr);
                       $order_objects['order'][$object_key]['order_items']['item'][] = array(
                           'sku_id' => $product_id,
                           //'iid' => ,//商品ID
                           'bn' => $items['bn'],
                           'name' => $items['name'],
                           'sku_properties' => $product_attr,
                           'weight' => $items['weight'],
                           'score' => $items['score'],
                           'price' => $items['price'],
                           'total_item_fee' => $items['amount'],
                           'num' => $items['quantity'],
                           'sendnum' => $items['sendnum'],
                           'item_type' => $items['item_type']?$items['item_type']:'product',
                           'item_status' => $this->product_status[$items['delete']],
                       );
                    }
                    $object_key++;
                }
            }
            //优惠方案
            $pmt_detail = $pmtObj->getList('pmt_amount as promotion_fee,pmt_describe as promotion_name', array('order_id'=>$order['order_id']), 0, -1);
            //收货人地区信息
            $area = $order['consignee']['area'];
            kernel::single('ome_func')->split_area($area);
            //买家客户信息
            $members_info = $membersObj->dump(array('member_id'=>$order['member_id']), '*');
            $member_area = $members_info['contact']['area'];
            kernel::single('ome_func')->split_area($member_area);
            //卖家信息
            $shop_id = $order['shop_id'];
            $shop_info = $shopObj->dump($shop_id, '*');
            //交易备注
            $oldmemo = unserialize($order['mark_text']);
            $memo = $oldmemo[count($oldmemo)-1]['op_content'];
            
            $params = array(
                'tid' => $order['order_bn'],
                'created' => date('Y-m-d H:i:s',$order['createtime']),
                'modified' => date('Y-m-d H:i:s',$order['last_modified']),
                'status' => $this->status[$order['status']],
                'pay_status' => $this->pay_status[$order['pay_status']],
                'ship_status' => $this->ship_status[$order['ship_status']],
                'is_delivery' => $order['is_delivery']=='Y'?'true':'false',
                'is_cod' => $order['shipping']['is_cod'],
                'has_invoice' => $order['is_tax'],
                'invoice_title' => $order['tax_title'],
                'invoice_fee' => $order['cost_tax'],
                'total_goods_fee' => $order['cost_item'],
                'total_trade_fee' => $order['cost_item'] + $order['shipping']['cost_shipping'],
                'total_currency_fee' => $order['total_amount'],
                'discount_fee' => $order['discount'],
                'goods_discount_fee' => $order['pmt_goods'],
                'orders_discount_fee' => $order['pmt_order'],
                'promotion_details' => $pmt_detail ? json_encode($pmt_detail) : '',
                'payed_fee' => $order['payed'],
                'currency' => $order['currency']?$order['currency']:'CNY',
                'currency_rate' => $order['cur_rate'],
                'pay_cost' => $order['payinfo']['cost_payment'],
                'buyer_obtain_point_fee' => $order['score_g'],
                'point_fee' => $order['score_u'],
                //'shipping_tid' => $order[''],//TODO：物流方式ID
                'shipping_type' => $order['shipping']['shipping_name'],
                'shipping_fee' => $order['shipping']['cost_shipping'],
                'is_protect' => $order['shipping']['is_protect'],
                'protect_fee' => $order['shipping']['cost_protect'],
                //'payment_tid' => $order[''],//支付方式ID
                'payment_type' => $order['payinfo']['pay_name'],
                //'pay_time' => $order[''],//支付时间
                //'end_time' => $order[''],//交易成功时间
                //'consign_time' => $order[''],//卖家发货时间
                'receiver_name' => $order['consignee']['name'],
                'receiver_email' => $order['consignee']['email'],
                'receiver_state' => $area[0],
                'receiver_city' => $area[1],
                'receiver_district' => $area[2],
                'receiver_address' => $order['consignee']['addr'],
                'receiver_zip' => $order['consignee']['zip'],
                'receiver_mobile' => $order['consignee']['mobile'],
                'receiver_phone' => $order['consignee']['telephone'],
                'receiver_time' => $order['consignee']['r_time'],
                //'buyer_alipay_no' => ,//买家支付宝账号
                //'seller_uname' => ,//卖家帐号
                //'buyer_id' => ,//买家（客户）ID
                'buyer_uname' => $members_info['account']['uname'],
                'buyer_name' => $members_info['contact']['name'],
                'buyer_mobile' => $members_info['contact']['phone']['mobile'],
                'buyer_phone' => $members_info['contact']['phone']['telephone'],
                'buyer_email' => $members_info['contact']['email'],
                'buyer_state' => $member_area[0],
                'buyer_city' => $member_area[1],
                'buyer_district' => $member_area[2],
                'buyer_address' => $members_info['contact']['addr'],
                'buyer_zip' => $members_info['contact']['zipcode'],
                //'seller_rate' => ,//卖家是否已评价
                //'buyer_rate' => ,//买家是否已评价
                //'commission_fee' => ,//交易佣金
                //'seller_alipay_no' => ,//卖家支付宝账号 
                'seller_mobile' => $shop_info['mobile'],
                'seller_phone' => $shop_info['tel'],
                'seller_name' => $shop_info['default_sender'],
                //'seller_email' => $shop_info['email'],//邮箱地址
                //'trade_memo' => $memo,//交易备注 
                //'orders_number' => ,//当前交易下订单数量
                'total_weight' => $order['weight'],
                'orders' => $order_objects ? json_encode($order_objects) : '',
            );

            if($shop_id){
                $title = '店铺('.$shop_info['name'].')订单编辑(订单号:'.$order['order_bn'].')';
            }else{
                return false;
            }
            
            $callback = array(
                'class' => 'ome_rpc_request_order',
                'method' => 'update_order_callback',
            );
            $api_name = 'store.trade.update';
            
            $this->request($api_name,$params,$callback,$title,$shop_id);
        }else{
            return false;
        }
    }
    function update_order_callback($result){
        return $this->callback($result);
    }
    
    /**
     * 更新订单状态
     * @access public
     * @param int $order_id 订单主键ID
     * @return boolean
     */
    public function order_status_update($order_id){
        
        if(!empty($order_id)){
            $orderObj = &app::get('ome')->model('orders');
            $order = $orderObj->dump($order_id, 'order_bn,shop_id,status');
            $params['tid'] = $order['order_bn'];
            $shop_id = $order['shop_id'];
            $shop_info = &app::get('ome')->model('shop')->dump($shop_id,'name');
            
            $params['status'] = $this->status[$order['status']];
            if($shop_id){
                $title = '店铺('.$shop_info['name'].')更新[订单状态]:'.$this->status_name[$order['status']].'(订单号:'.$order['order_bn'].')';
            }else{
                return false;
            }
            
            $callback = array(
                'class' => 'ome_rpc_request_order',
                'method' => 'order_status_update_callback',
            );
            $api_name = 'store.trade.status.update';
            
            $this->request($api_name,$params,$callback,$title,$shop_id);
        }else{
            return false;
        }
    }
    function order_status_update_callback($result){
        return $this->callback($result);
    }
    
    /**
     * 更新订单发货状态 
     * @access public
     * @param int $order_id 订单主键ID
     * @param boolean $queue 是否走队列
     */
    public function ship_status_update($order_id,$queue=false){
        
        if(!empty($order_id)){
            $orderObj = &app::get('ome')->model('orders');
            $order = $orderObj->dump($order_id, 'order_bn,shop_id,ship_status');
            $params['tid'] = $order['order_bn'];
            $shop_id = $order['shop_id'];
            $shop_info = &app::get('ome')->model('shop')->dump($shop_id,'name');
            
            $params['ship_status'] = $this->ship_status[$order['ship_status']];
            if($shop_id){
                $title = '店铺('.$shop_info['name'].')更新[订单发货状态]:'.$params['ship_status'].'(订单号:'.$order['order_bn'].')';
            }else{
                return false;
            }
            
            $callback = array(
                'class' => 'ome_rpc_request_order',
                'method' => 'ship_status_update_callback',
            );
            
            $api_name = 'store.trade.ship_status.update';
            $this->request($api_name,$params,$callback,$title,$shop_id,'',$queue);
        }else{
            return false;
        }
    }
    function ship_status_update_callback($result){
        return $this->callback($result);
    }
    
    /**
     * 更新订单支付状态 
     * @access public
     * @param int $order_id 订单主键ID
     * @return boolean
     */
    public function pay_status_update($order_id){
        
        if(!empty($order_id)){
            $orderObj = &app::get('ome')->model('orders');
            $order = $orderObj->dump($order_id, 'order_bn,shop_id,pay_status');
            $params['tid'] = $order['order_bn'];
            $shop_id = $order['shop_id'];
            $shop_info = &app::get('ome')->model('shop')->dump($shop_id,'name');
            
            $params['pay_status'] = $this->ship_status[$order['pay_status']];
            if($shop_id){
                    $title = '店铺('.$shop_info['name'].')更新[订单支付状态]:'.$params['pay_status'].'(订单号:'.$order['order_bn'].')';
            }else{
                return false;
            }
            
            $callback = array(
                'class' => 'ome_rpc_request_order',
                'method' => 'pay_status_update_callback',
            );
            
            $api_name = 'store.trade.pay_status.update';
            $this->request($api_name,$params,$callback,$title,$shop_id);
        }else{
            return false;
        }
    }
    
    function pay_status_update_callback($result){
        return $this->callback($result);
    }
    
    /**
     * 更新订单交易备注
     * @access public
     * @param int $order_id 订单主键ID
     * @param array $memo 备注内容
     * @return boolean
     */
    public function memo_update($order_id,$memo){
        
        if(!empty($order_id)){
            $orderObj = &app::get('ome')->model('orders');
            $order = $orderObj->dump($order_id, 'order_bn,shop_id,mark_type');
            $params['tid'] = $order['order_bn'];
            $params['memo'] = $memo['op_content'];
            $params['flag'] = $this->mark_type[$order['mark_type']]?$this->mark_type[$order['mark_type']]:'';
            $params['sender'] = $memo['op_name'];
            $params['add_time'] = $memo['op_time'];

            $callback = array(
                'class' => 'ome_rpc_request_order',
                'method' => 'memo_update_callback',
            );
            
            $shop_id = $order['shop_id'];
            if($shop_id){
                $shop_info = &app::get('ome')->model('shop')->dump($shop_id,'name');
                $title = '店铺('.$shop_info['name'].')订单备注(订单号:'.$order['order_bn'].')';
            }else{
                $title = '订单留言';
            }

            $this->request('store.trade.memo.update',$params,$callback,$title,$shop_id);
        }else{
            return false;
        }
    }
    
    function memo_update_callback($result){
        return $this->callback($result);
    }
    
    /**
     * 添加订单交易备注
     * @access public
     * @param int $order_id 订单主键ID
     * @param array $memo 备注内容
     * @return boolean
     */
    public function memo_add($order_id,$memo){
        
        if(!empty($order_id)){
            $orderObj = &app::get('ome')->model('orders');
            $order = $orderObj->dump($order_id, 'order_bn,shop_id,mark_type');
            $params['tid'] = $order['order_bn'];
            $params['memo'] = $memo['op_content'];
            $params['flag'] = $this->mark_type[$order['mark_type']]?$this->mark_type[$order['mark_type']]:'';
            $params['sender'] = $memo['op_name'];
            $params['add_time'] = $memo['op_time'];

            $callback = array(
                'class' => 'ome_rpc_request_order',
                'method' => 'memo_add_callback',
            );
            
            $shop_id = $order['shop_id'];
            if($shop_id){
                $shop_info = &app::get('ome')->model('shop')->dump($shop_id,'name');
                $title = '店铺('.$shop_info['name'].')订单备注(订单号:'.$order['order_bn'].')';
            }else{
                $title = '订单留言';
            }

            $this->request('store.trade.memo.add',$params,$callback,$title,$shop_id);
        }else{
            return false;
        }
    }
    
    function memo_add_callback($result){
        return $this->callback($result);
    }
    
    /**
     * 添加买家留言
     * @access public
     * @param int $order_id 订单主键ID
     * @param array $memo 备注内容
     * @return boolean
     */
    public function custom_mark_add($order_id,$memo){
  
        if(!empty($order_id)){
            $orderObj = &app::get('ome')->model('orders');
            $order = $orderObj->dump($order_id, 'order_bn,shop_id');
            $params['tid'] = $order['order_bn'];
            $params['message'] = $memo['op_content'];
            $params['sender'] = $memo['op_name'];
            $params['add_time'] = $memo['op_time'];
     
            $callback = array(
                'class' => 'ome_rpc_request_order',
                'method' => 'custom_mark_add_callback',
            );
            
            $shop_id = $order['shop_id'];
            if($shop_id){
                $shop_info = &app::get('ome')->model('shop')->dump($shop_id,'name');
                $title = '店铺('.$shop_info['name'].')订单附言(订单号:'.$order['order_bn'].')';
            }else{
                $title = '买家留言';
            }

            $this->request('store.trade.buyer_message.add',$params,$callback,$title,$shop_id);
        }else{
            return false;
        }
    }
    
    function custom_mark_add_callback($result){
        return $this->callback($result);
    }
    
    /**
     * 更新交易收货人信息
     * @access public
     * @param int $order_id 订单主键ID
     * @return boolean
     */
    public function shippinginfo_update($order_id){

        if(!empty($order_id)){

            $orderObj = &app::get('ome')->model('orders');
            
            $order = $orderObj->dump($order_id, '*');

            $consignee_area = $order['consignee']['area'];
            if(strpos($consignee_area,":")){
                $t_area = explode(":",$consignee_area);
                $t_area_1 = explode("/",$t_area[1]);
                $receiver_state = $t_area_1[0];
                $receiver_city = $t_area_1[1];
                $receiver_district = $t_area_1[2];
            }
            $params['tid'] = $order['order_bn'];
            $params['receiver_name'] = $order['consignee']['name']?$order['consignee']['name']:'';
            $params['receiver_state'] = $receiver_state?$receiver_state:'';
            $params['receiver_city'] = $receiver_city?$receiver_city:'';
            $params['receiver_district'] = $receiver_district?$receiver_district:'';
            $params['receiver_address'] = $order['consignee']['addr']?$order['consignee']['addr']:'';
            $params['receiver_zip'] = $order['consignee']['zip']?$order['consignee']['zip']:'';
            $params['receiver_email'] = $order['consignee']['email']?$order['consignee']['email']:'';
            $params['receiver_mobile'] = $order['consignee']['mobile']?$order['consignee']['mobile']:'';
            $params['receiver_phone'] = $order['consignee']['telephone']?$order['consignee']['telephone']:'';
            $params['receiver_time'] = $order['consignee']['r_time']?$order['consignee']['r_time']:'';

            $callback = array(
                'class' => 'ome_rpc_request_order',
                'method' => 'shippinginfo_update_callback',
            );
            
            $shop_id = $order['shop_id'];
            if($shop_id){
                $shop_info = &app::get('ome')->model('shop')->dump($shop_id,'name');
                $title = '店铺('.$shop_info['name'].')更新[交易收货人信息]:'.$params['receiver_name'].'(订单号:'.$order['order_bn'].')';
            }else{
                $title = '更新交易收货人信息';
            }

            $this->request('store.trade.shippingaddress.update',$params,$callback,$title,$shop_id);
        }else{
            return false;
        }
    }
    
    function shippinginfo_update_callback($result){
        return $this->callback($result);
    }
    
}