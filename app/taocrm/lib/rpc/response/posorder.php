<?php

class taocrm_rpc_response_posorder extends taocrm_rpc_response
{

    public function add($sdf, &$responseObj)
    {
        //err_log($sdf, 'taocrm_rpc_response_posorder');
        $apiParams = array(
            'order_bn'=>array('label'=>'订单号','required'=>true),
            'shop_node_id'=>array('label'=>'节点ID','required'=>true),
            'uname'=>array('label'=>'客户名','required'=>false),
            'name'=>array('label'=>'客户真实姓名','required'=>false),
            'buy_time'=>array('label'=>'购买时间','required'=>true),
            'is_refund'=>array('label'=>'是否冲红销售','required'=>false),
            'refund_order_bn'=>array('label'=>'冲红订单号','required'=>false),
            'order_amount'=>array('label'=>'订单金额','required'=>true),
            'order_status'=>array('label'=>'订单状态','required'=>false),
            'pay_status'=>array('label'=>'支付状态','required'=>false),
            'ship_status'=>array('label'=>'发货状态','required'=>false),
            'shipping'=>array('label'=>'配送方式','required'=>false),
            //'item_nums'=>array('label'=>'商品数量','required'=>true),
            'item_amount'=>array('label'=>'商品金额','required'=>true),
            'shipping_fee'=>array('label'=>'运费金额','required'=>false),
            'consignee'=>array('label'=>'收货人','required'=>false),
            'consignee_state'=>array('label'=>'收货省市','required'=>false),
            'consignee_city'=>array('label'=>'城市','required'=>false),
            'consignee_area'=>array('label'=>'地区','required'=>false),
            'consignee_address'=>array('label'=>'详细地址','required'=>false),
         	'consignee_zip'=>array('label'=>'邮编','required'=>false),
        	'consignee_mobile'=>array('label'=>'手机','required'=>false),
            'consignee_telephone'=>array('label'=>'电话','required'=>false),
        	'payment'=>array('label'=>'支付方式','required'=>false),
            'pay_time'=>array('label'=>'付款时间','required'=>false),
            /*'pay_trade_no'=>array('label'=>'付款交易号','required'=>true),
            'pay_account'=>array('label'=>'付款账号','required'=>true),
            'pay_currency'=>array('label'=>'付款币别','required'=>true),*/
            'pay_money'=>array('label'=>'付款时间','required'=>false),
            'delivery_time'=>array('label'=>'发货时间','required'=>false),
            'finish_time'=>array('label'=>'完成时间','required'=>false),
            //'soure_shop'=>array('label'=>'来源店铺','required'=>true),
            'consumer_terminal'=>array('label'=>'消费终端','required'=>true),
            'op_name'=>array('label'=>'操作人','required'=>false),
            'buy_msg'=>array('label'=>'买家留言','required'=>false),
            'buy_remark'=>array('label'=>'买家备注','required'=>false),
            'order_items'=>array('label'=>'订单商品明细','required'=>false), 
            'card_no'=>array('label'=>'会员卡号','required'=>false),
            'sex'=>array('label'=>'买家性别','required'=>false),
        );
        
        if($sdf['order_status'] == '活动') $sdf['order_status'] = 'active';
        if($sdf['order_status'] == '已完成' or !$sdf['order_status'])
            $sdf['order_status'] = 'finish';
         
        $this->checkApiParams($apiParams,$sdf, $responseObj);

        $order_objects = array();
        $sdf['order_items'] = json_decode($sdf['order_items'], true);

        //$orders_number = count($sdf['order_items']);
        foreach($sdf['order_items'] as $item){
            $item['sendnum'] = 0;
            if($sdf['ship_status'] == 1){
                $item['sendnum'] = $item['nums'];
            }

            $order_items = array();
            $order_items[] = array(
                'status' => 'active',
                'name' =>$item['name'],
                //'pmt_price' => 0,
                'sale_price' => $item['total_price'],
                'bn' => $item['bn'],
                'sale_amount' => $item['total_price'],
                //'product_attr' =>'',
                'item_type' => 'product',
                'amount' => $item['total_price'],
                'cost' => $item['nums'] * $item['price'],
                //'shop_goods_id' => '0',
                //'original_str' => '',
                'sendnum' =>  $item['sendnum'],
                //'score' => '',
                'quantity' => $item['nums'],
                'price' => $item['price'],
                //'shop_product_id' => '0',
            );

            $order_objects[] = array (
                'obj_type' => 'goods',
                'name' =>  $item['name'],
                //'weight' => '',
                //'pmt_price' => '0',
                'bn' => $item['goods_bn'],
                //'oid' => '1004027597',
                'order_items' => $order_items,
                'amount' => $item['total_price'],
                //'score' => '',
                //'shop_goods_id' => '',
                //'obj_alias' => '',
                'sale_price' => $item['total_price'],
                'price' => $item['price'],
                'quantity' => $item['nums'],
            );

        }

        $payment_detail = array (
            //'trade_no' => '',
            //'paymethod' => '',
            //'pay_account' => '',
            'currency' => 'CNY',
            'pay_time' => $sdf['pay_time'],
            'money' => $sdf['pay_money'],
        );
         
        //会员信息
        if(!$sdf['uname'] && $sdf['consignee']){
            $sdf['uname'] = $sdf['consignee'];
        }
        $member_info = array('uname'=>$sdf['uname']);
        
        $consignee = array(
            //'r_time'=>'',
            'card_no'=>$sdf['card_no'],
            'addr'=>$sdf['consignee_address'],
            'zip'=>$sdf['consignee_zip'],
            'mobile'=>$sdf['consignee_mobile'],
            'telephone'=>$sdf['consignee_telephone'],
            'area_city'=>$sdf['consignee_city'],
            'area_state'=>$sdf['consignee_state'],
            'area_district'=>$sdf['consignee_area'],
            'name'=>$sdf['consignee'],
            'uname'=>$sdf['uname'],
            'sex'=> $sdf['sex']=='女' ? 'female' : 'male',
        );
        $shipping = array(
            'shipping_name'=>$sdf['shipping'],
            'cost_protect'=>'',
            'cost_shipping'=>$sdf['shipping_fee']
        );

        $order = array (
            'member_id'=>0,
            'shop_id'=>$sdf['shop_id'],
            'is_refund'=>intval($sdf['is_refund']),
            'refund_order_bn'=>$sdf['refund_order_bn'],
            'tradetype' => 'fixed',
            'cur_rate' => '1.0000',
            'consignee' => json_encode($consignee),
            'app_id' => 'ecos.taocrm',
            'currency' => 'CNY',
            'node_type' => 'offlinepos',
            'cost_item' => $sdf['item_amount'],
            'custom_mark' => $sdf['buy_msg'],
            'mark_text' => $sdf['buy_remark'],
            //'from_node_id' => '1539353331',
            //'lastmodify' => '',
            'consigner' => '{}',
            'is_delivery' => 'Y',
            //'node_version' => '',
            //'from_type' => 'ecos.dzg',
            'payinfo' => '{}',  
            'order_bn' => $sdf['order_bn'],  
            //'shipping_tid' => '1',
            //'selling_agent' => '{"website": {}, "member_info": {"sex": ""}}',
            'pay_status' => $sdf['pay_status'], 
            'status' => $sdf['order_status'], 
            'score_u' => '0',
            //'timestamp' => '1346225797.03',
            'member_info' => json_encode($member_info),
            'discount' => '0.00',
            'node_id' => $sdf['shop_node_id'],
            'score_g' => '0',
            'date' => date('Y-m-d H:i:s'),
            //'orders_number' => $orders_number,
            'task' => time(),
            'total_amount' => $sdf['order_amount'],
            'to_type' => 'ecos.taocrm',
            'ship_status' => $sdf['ship_status'],
            'cur_amount' => $sdf['order_amount'],
            'modified' => time(),
            'shipping' => json_encode($shipping),
            'payed' => $sdf['pay_money'],
            'order_objects' => json_encode($order_objects),
            'payment_detail' => json_encode($payment_detail),
            //'is_tax' => 'false',
            
            'createtime' => str_replace('/','-',$sdf['buy_time']),
            'delivery_time'=>str_replace('/','-',$sdf['delivery_time']),
            'finish_time'=>str_replace('/','-',$sdf['finish_time']),
            
            'consumer_terminal'=>$sdf['consumer_terminal'],
            'source'=>$sdf['source_shop'] ? $sdf['source_shop'] : 'pos',
            'source_shop'=>$sdf['source_shop'] ? $sdf['source_shop'] : 'pos',
            'op_name'=>$sdf['op_name'],
            'card_no'=>$sdf['card_no'],
            //'buyer_id' => '105',
        );

        $this->create_member($order);
        
        $this->order_app = app::get('ecorder');
        $this->order_mdl = $this->order_app->model('orders');
        $this->order_item_mdl = $this->order_app->model('order_items');
        
        //考虑订单号在不同pos机可能重复
        $filter = array(
            'order_bn'=>$order['order_bn'],
            'source'=>$order['source_shop'],
            'consumer_terminal'=>$order['consumer_terminal'],
        );
        $rs_order = $this->order_mdl->dump($filter, 'order_id');
        if($rs_order){
            $order['order_id'] = $rs_order['order_id'];
            $res = $this->update_order($order);
        }else{
            $res = $this->create_order($order);
            $res['member_id'] = $order['member_id'];
        }
        
        return $res;
        //$response = kernel::single('base_rpc_service');
        //base_rpc_service::$node_id = $sdf['shop_node_id'];

        //$orderObj = new ecorder_rpc_response_order_add();
        //return  $orderObj->add($order, $response);
    }

    //创建会员信息
    function create_member(&$sdf)
    {
        $member_mdl = app::get('taocrm')->model('members');
        $member_card_mdl = app::get('taocrm')->model('member_card');
        $member_info = json_decode($sdf['consignee'],true);
        $member_id = 0;
        
        if(!$member_info['mobile'] && !$member_info['card_no']){
            return false;
        }
        //echo('<pre>');var_dump($member_info);exit;

        if($member_info['mobile']){
            $rs_member = $member_mdl->dump(array('mobile'=>$member_info['mobile']));
            if($rs_member) $member_id = $rs_member['member_id'];
        }elseif($member_info['card_no']){
            $rs_member = $member_mdl->dump(array('member_card'=>$member_info['card_no']));
            if($rs_member) $member_id = $rs_member['member_id'];
        }        
        
        $area = $member_info['area_state'] . '/' . $member_info['area_city'] . '/' . $member_info['area_district'];
        kernel::single("ecorder_func")->region_validate($area);
        $area = str_replace('::', '', $area);
        $member_info['area'] = $area;

        //省份
        if(!empty($member_info['area_state'])){
            $row = kernel::database()->selectrow('select region_id from sdb_ectools_regions where local_name="'.$member_info['area_state'].'"');
            $member_info['state'] = $row['region_id'];
        }

        //城市
        if(!empty($member_info['area_city'])){
            $row = kernel::database()->selectrow('select region_id from sdb_ectools_regions where local_name="'.$member_info['area_city'].'"');
            $member_info['city'] = $row['region_id'];
        }

        //地区
        if(!empty($member_info['area_district'])){
            $row = kernel::database()->selectrow('select region_id from sdb_ectools_regions where local_name="'.$member_info['area_district'].'"');
            $member_info['district'] = $row['region_id'];
        }
        
        if(!$member_id) {
            $save_member = array(
                'uname'=>$member_info['uname'],
                'unique_code'=>time(),
                'name'=>$member_info['name'],
                'source_terminal'=>'POS',
                'parent_member_id'=>0,
                'member_card'=>$member_info['card_no'],
                'area'=>$member_info['area'],
                'state'=>$member_info['state'],
                'city'=>$member_info['city'],
                'district'=>$member_info['district'],
                'addr'=>$member_info['addr'],
                'zip'=>$member_info['zip'],
                'mobile'=>$member_info['mobile'],
                'tel'=>$member_info['telephone'],
                'sex'=> $member_info['sex'],
                'create_time'=>time(),
                'update_time'=>time(),
            );
            $member_mdl->insert($save_member);
            
            $member_id = $save_member['member_id'];
            
            //店铺会员分析表
            if($sdf['shop_id']){
                $m_member_analysis = app::get('taocrm')->model('member_analysis');
                $member_analysis = array(
                    'member_id' => $member_id,
                    'shop_id' => $sdf['shop_id'],
                );
                $m_member_analysis->insert($member_analysis);
            }
            
            $taocrm_service_member = kernel::single('taocrm_service_member');
            $taocrm_service_member->saveMemberContact($save_member);
            $taocrm_service_member->saveMemberReceiver($save_member);
        }
        
        $sdf['member_id'] = $member_id;
    }
    
    //创建订单信息
    function create_order(&$sdf)
    {
        $response = kernel::single('base_rpc_service');
        base_rpc_service::$node_id = $sdf['node_id'];

        //$orderObj = new ecorder_rpc_response_order_add_offlinepos();
        $sdf['shop_type'] = 'offlinepos';
        $orderObj = new ecorder_rpc_response_order();
        return  $orderObj->add($sdf, $response);
    }
    
    //更新订单信息
    function update_order(&$sdf)
    {
        //pos订单只创建，不更新
        $res = array(
            'tid' => $sdf['order_bn'],
            'member_id' => $sdf['member_id'],
        );
        return $res;
    }
}