<?php
/**
 * 发货同步请求
 * @author ome
 * @access public
 * @copyright www.shopex.cn 2010
 *
 */
class ecorder_rpc_request_shipping extends ome_rpc_request {

    //发货状态
    var $ship_status = array(
        'succ'=>'SUCC',
        'failed'=>'FAILED',
        'cancel'=>'CANCEL',
        'lost'=>'LOST',
        'progress'=>'PROGRESS',
        'timeout'=>'TIMEOUT',
        'ready'=>'READY',
        'stop'=>'STOP',
        'back'=>'BACK',
        'verify' => 'VERIFY',//TODO:新增加的校验
    );
    
    //货品类型
    var $item_type  = array(
        'product'=>'product',
        'gift'=>'gift',
        'adjunct'=>'adjunct',
        'pkg'=>'pkg'
    );
    
    //发货类型
    var $delivery_type = array(
        'Y' => 'delivery_needed',
        'N' => 'virtual_goods',
    );
    
    
    /**
     * 添加交易发货单 b2c
     * @access public
     * @param int $delivery_id 发货单ID
     * @return boolean
     */
    public function add($delivery_id){

        if(!empty($delivery_id)){
            
            //通过内部客户id找到外部客户id
            $deliveryObj = &app::get('ome')->model('delivery');
            $orderObj = &app::get('ome')->model('orders');
            $shopObj = &app::get('ome')->model('shop');
            $delivery_orderObj = &app::get('ome')->model('delivery_order');
            
            $delivery_detail = $deliveryObj->dump($delivery_id, 'is_bind,parent_id');
            $delivery_order = $delivery_orderObj->dump(array('delivery_id'=>$delivery_id));
            $order_detail = $orderObj->dump($delivery_order['order_id'], 'ship_status,shop_id');
            $shop_detail = $shopObj->dump($order_detail['shop_id'], 'node_type');
            
            //判断订单类型
            switch($shop_detail['node_type']){
                case 'paipai':
                case 'youa':
                case 'taobao':
                    //如果是合并的发货单，则向所有合并后的订单发货通知
                    if($delivery_detail['is_bind']=='true'){
                        $delivery_order_list = $delivery_orderObj->getList('order_id',array('delivery_id'=>$delivery_id),0,-1);
                        if ($delivery_order_list)
                        foreach ($delivery_order_list as $k=>$v){
                            $deliveryorder = $orderObj->dump($v['order_id'],'ship_status');
                            //判断此订单是否是已发货状态
                            if ($deliveryorder['ship_status']!=1) continue;
                            $this->_get_shipping_params($delivery_id,$v['order_id'],'',$shop_detail['node_type']);
                        }
                    }else{
                        //判断此订单是否是已发货状态
                        if ($order_detail['ship_status']!=1) return false;
                        $this->_get_shipping_params($delivery_id,'','',$shop_detail['node_type']);
                    }
                    break;
                default:
                    $this->_get_shipping_params($delivery_id,'',$delivery_detail['parent_id'],$shop_detail['node_type']);
            }
        }
        else{
            return false;
        }
    }
    function shipping_add_callback($result){
        return $this->callback($result);
    }
    
    /**
     * 根据店铺类型输入不同的参数
     * @access private
     * @param $delivery_id  发货单BN
     * @param $shop_type 店铺类型
     * @param $order_id 订单ID
     * @param $parent_id 合并后的发货单ID
     * @return boolean
     */
    private function _get_shipping_params($delivery_id='', $order_id='', $parent_id='', $shop_type=''){
        
        $deliveryObj = &app::get('ome')->model('delivery');
        $order_delivery = &app::get('ome')->model('delivery_order');
        $orderObj = &app::get('ome')->model('orders');
        $memberObj = &app::get('ome')->model('members');
        $shopObj = &app::get('ome')->model('shop');
            
        $delivery_detail = $deliveryObj->dump($delivery_id, '*');
        if ($parent_id){
            $parent_delivery_detail = $deliveryObj->dump(array('delivery_id'=>$parent_id), '*');
            $delivery_detail['status'] = $parent_delivery_detail['status'];
            $delivery_detail['logi_id'] = $parent_delivery_detail['logi_id'];
            $delivery_detail['logi_name'] = $parent_delivery_detail['logi_name'];
            $delivery_detail['logi_no'] = $parent_delivery_detail['logi_no'];
        }
        $ord_delivery_info = $order_delivery->dump(array('delivery_id'=>$delivery_id));
        
        if (!$order_id)
        $order_id = $ord_delivery_info['order_id'];
        $orderinfo = $orderObj->dump($order_id,'order_bn,shop_id,is_delivery,mark_text');
        $shop_id = $orderinfo['shop_id'];
        $shop_detail = $shopObj->dump($shop_id,'*');
        
        if ($shop_type=='ecos.b2c'){
            $consignee_area = $delivery_detail['consignee']['area'];
        }else{
            $consignee_area = $shop_detail['area'];
        }
       
        kernel::single('ome_func')->split_area($consignee_area);
        $receiver_state = $consignee_area[0];
        $receiver_city = $consignee_area[1];
        $receiver_district = $consignee_area[2];

        switch ($shop_type){
            case 'shopex_b2c'://485
            case 'ecos.b2c'://ec store
                $smemberObj = &app::get('ome')->model('shop_members');
                $delivery_itemsObj = &app::get('ome')->model('delivery_items');
                $develiyitems = $delivery_itemsObj->getList('item_type as sku_type,product_name as name,bn,number',array("delivery_id"=>$delivery_id),0,-1);
                $memberinfo = $memberObj->dump($delivery_detail['member_id'],'uname,name');

                $params = array(
                    'tid' => $orderinfo['order_bn'],
                    'shipping_fee' => $delivery_detail['delivery_cost_actual'] ? $delivery_detail['delivery_cost_actual'] :'',
                    'shipping_id' => $delivery_detail['delivery_bn'],
                    'create_time' => date("Y-m-d H:i:s",$delivery_detail['create_time']),
                    'is_protect' => $delivery_detail['is_protect'],
                    'is_cod' => $delivery_detail['is_cod'],
                    'buyer_id' => $memberinfo['account']['uname'],
                    'status' => $this->ship_status[$delivery_detail['status']],
                    'shipping_type' => $delivery_detail['delivery'] ? $delivery_detail['delivery'] : '',
                    'logistics_id' => $delivery_detail['logi_id'] ? $delivery_detail['logi_id'] : '',
                    'logistics_company' => $delivery_detail['logi_name'] ? $delivery_detail['logi_name'] : '',
                    'logistics_no' => $delivery_detail['logi_no'] ? $delivery_detail['logi_no'] : '',
                    'receiver_name' => $delivery_detail['consignee']['name'] ? $delivery_detail['consignee']['name'] : '',
                    'receiver_state' => $receiver_state ? $receiver_state : '',
                    'receiver_city' => $receiver_city ? $receiver_city : '',
                    'receiver_district' => $receiver_district ? $receiver_district : '',
                    'receiver_address' => $delivery_detail['consignee']['addr'] ? $delivery_detail['consignee']['addr'] :'',
                    'receiver_zip' => $delivery_detail['consignee']['zip']?$delivery_detail['consignee']['zip']:'',
                    'receiver_email' => $delivery_detail['consignee']['email']?$delivery_detail['consignee']['email']:'',
                    'receiver_mobile' => $delivery_detail['consignee']['mobile']?$delivery_detail['consignee']['mobile']:'',
                    'receiver_phone' => $delivery_detail['consignee']['telephone']?$delivery_detail['consignee']['telephone']:'',
                    'memo' => $delivery_detail['memo']?$delivery_detail['memo']:'',
                    't_begin' => date("Y-m-d H:i:s",$delivery_detail['create_time']),
                    'refund_operator' => kernel::single('desktop_user')->get_login_name(),
                    'shipping_items' => json_encode($develiyitems),
                );
                if (!trim($delivery_detail['logi_no']) and !$parent_id){
                    $api_name = 'store.trade.shipping.add';
                }
                else{
                    $this->logistics_update($delivery_id,$parent_id);
                    return false;
                }
                break;
            case 'paipai'://拍拍
            case 'youa'://有啊
            case 'taobao'://淘宝
                $dly_corpObj = &app::get('ome')->model('dly_corp');
                $dly_detail = $dly_corpObj->dump(array('corp_id'=>$delivery_detail['logi_id']),'type,name');
                //订单备注
                $oldmemo = unserialize($orderinfo['mark_text']);
                if ($oldmemo)
                foreach($oldmemo as $k=>$v){
                    $memo = $v['op_content']."<br/>";
                }
                if ($receiver_district){
                    $receiver_district = '_'.$receiver_district;
                }
                $params = array(
                    'tid' => $orderinfo['order_bn'],
                    'send_type' => $this->delivery_type[$orderinfo['is_delivery']],
                    'logistics_code' => $dly_detail['type'],
                    'logistics_company' => $dly_detail['name']?$dly_detail['name']:'',
                    'logistics_no' => $delivery_detail['logi_no'] ? $delivery_detail['logi_no'] : '',
                    'seller_name' => $shop_detail['default_sender'] ? $shop_detail['default_sender'] : '',
                    'seller_area_id' => $receiver_state.'_'.$receiver_city.$receiver_district,
                    'seller_address' => $shop_detail['addr'] ? $shop_detail['addr'] : '',
                    'seller_zip' => $shop_detail['zip'] ? $shop_detail['zip'] : '',
                    'seller_mobile' => $shop_detail['mobile'] ? $shop_detail['mobile'] : '',
                    'seller_phone' => $shop_detail['tel'] ? $shop_detail['tel'] : '',
                    'memo' => $memo ? $memo : '',
                );
                $api_name = 'store.trade.delivery.send';
                break;
        }
        $callback = array(
           'class' => 'ome_rpc_request_shipping',
           'method' => 'shipping_add_callback',
        );
        if($shop_id){
            $shop_info = $shopObj->dump($shop_id,'name');
            $title = '店铺('.$shop_info['name'].')添加[交易发货单](订单号:'.$orderinfo['order_bn'].',发货单号:'.$delivery_detail['delivery_bn'].')';
        }else{
            $title = '添加交易发货单';
        }
         
        $this->request($api_name,$params,$callback,$title,$shop_id,'',true);
    }

    /**
     * 更新交易发货状态
     * 注意：发货单打回也需要更新状态
     * @param $delivery_id
     * @param $status
     */
    function status_update($delivery_id, $status=''){
       
           if(!empty($delivery_id)){
            $deliveryObj = &app::get('ome')->model('delivery');
            $delivery_oObj = &app::get('ome')->model('delivery_order');
            $orderObj = &app::get('ome')->model('orders');
            
            $delivery_detail = $deliveryObj->dump(array('delivery_id'=>$delivery_id), 'delivery_bn,shop_id,is_bind,status,parent_id');
            
            if($delivery_detail['is_bind'] == 'true'){
                $delivery_ids = $deliveryObj->getItemsByParentId($delivery_id,'array');
                if ($delivery_ids)
                foreach($delivery_ids as $v){
                    $this->status_update($v);
                }
            }else{
                
                $dlyOrder = $delivery_oObj->dump(array('delivery_id'=>$delivery_id), 'order_id');
                $order = $orderObj->dump($dlyOrder['order_id'], 'order_bn');
                
                //如果是合并后的发货单，发货单状态为合并后的发货单状态
                if($delivery_detail['parent_id']>0)
                {
                    $parent_delivery_detail = $deliveryObj->dump(array('delivery_id'=>$delivery_detail['parent_id']), 'status');
                    $delivery_detail['status'] = $parent_delivery_detail['status'];
                }
                if ($status) $delivery_detail['status'] = $status;
                
                $params['tid'] = $order['order_bn'];
                $params['shipping_id'] = $delivery_detail['delivery_bn'];
                $params['status'] = $this->ship_status[$delivery_detail['status']];
    
                $callback = array(
                    'class' => 'ome_rpc_request_shipping',
                    'method' => 'shipping_status_update_callback',
                );
                
                $shop_id = $delivery_detail['shop_id'];
                if($shop_id){
                    $shop_info = &app::get('ome')->model('shop')->dump($shop_id,'name');
                    $title = '店铺('.$shop_info['name'].')更新[发货单]状态:'.$params['status'].'(发货单号:'.$delivery_detail['delivery_bn'].')';
                }else{
                    $title = '更新发货单状态';
                }
                $api_name = 'store.trade.shipping.status.update';
                $queue = true;//进队列
    
                if ($params['status']){
                    $this->request($api_name,$params,$callback,$title,$shop_id,'',$queue);
                }
                
            }
        }else{
            return false;
        }
    }
    
    function shipping_status_update_callback($result){
        return $this->callback($result);
    }
    
    /**
     * 更改发货物流信息
     * @access public
     * @param int $delivery_id 发货单主键ID
     * @param int $parent_id 支付单主键ID
     * @return boolean
     */
    public function logistics_update($delivery_id,$parent_id=''){
        
        if(!empty($delivery_id)){
            $deliveryObj = &app::get('ome')->model('delivery');
            $delivery_oObj = &app::get('ome')->model('delivery_order');
            $orderObj = &app::get('ome')->model('orders');
            $shopObj = &app::get('ome')->model('shop');
            
            $delivery_detail = $deliveryObj->dump(array('delivery_id'=>$delivery_id), '*');
            $dlyOrder = $delivery_oObj->dump(array('delivery_id'=>$delivery_id));
            $order = $orderObj->dump($dlyOrder['order_id'], 'order_bn');
            
            $params['tid'] = $order['order_bn'];
            $params['shipping_id'] = $delivery_detail['delivery_bn'];
            
            $parent_id = $delivery_detail['parent_id'];
            //如果是合并后的发货单，发货单状态为合并后的发货单状态
            if($parent_id>0)
            {
                $parent_delivery_detail = $deliveryObj->dump(array('delivery_id'=>$parent_id), 'logi_name,logi_no');
                $delivery_detail['logi_name'] = $parent_delivery_detail['logi_name'];
                $delivery_detail['logi_no'] = $parent_delivery_detail['logi_no'];
            }
            $params['logistics_company'] = $delivery_detail['logi_name']?$delivery_detail['logi_name']:'';
            $params['logistics_no'] = $delivery_detail['logi_no']?$delivery_detail['logi_no']:'';
   
            $callback = array(
                'class' => 'ome_rpc_request_shipping',
                'method' => 'logistics_update_callback',
            );
            
            $shop_id = $delivery_detail['shop_id'];
            //排除发送给网店端
            $shop_detail = $shopObj->dump($shop_id, 'node_type');
            $foreground_shop_list = ome_shop_type::shop_list();
            if (in_array($shop_detail['node_type'],$foreground_shop_list)) return false;
            
            if($shop_id){
                $shop_info = &app::get('ome')->model('shop')->dump($shop_id,'name');
                $title = '店铺('.$shop_info['name'].')更改[发货物流信息](物流单号:'.$params['logistics_no'].',发货单号:'.$delivery_detail['delivery_bn'].')';
            }else{
                $title = '更改发货物流信息';
            }

            $this->request('store.trade.shipping.update',$params,$callback,$title,$shop_id);
        }else{
            return false;
        }
    }
    
    function logistics_update_callback($result){
        return $this->callback($result);
    }
    
}