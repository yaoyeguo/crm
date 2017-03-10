<?php
class taocrm_member_orders {
     
    function getOrdersList($shop_id,$member_id,$page_size,$page,& $msg){
        $page_size = intval(abs($page_size));
        $page = intval(abs($page));
        $db = kernel::database();
        $member = $db->selectRow('select member_id from sdb_taocrm_members where member_id='.$member_id);
        if(!$member){
            $msg = '此客户不存在';
            return false;
        }

        $whereSql = array('member_id='.$member_id);
        if($shop_id){
            $whereSql[] = 'shop_id="'.$shop_id.'"';
        }
        $totalSql = 'select count(*) as total from sdb_ecorder_orders where '.implode(' and ', $whereSql);
        $total = $db->selectRow($totalSql);
        $totalResult = intval($total['total']);

        $sql = 'select order_bn,status,pay_status,ship_status,shipping,payment,item_num,skus,createtime,pay_time,delivery_time,shop_id,ship_name,ship_area,ship_addr,ship_zip,ship_tel,ship_mobile,cost_item,total_amount,pmt_goods,pmt_order,payed,custom_mark,mark_text from sdb_ecorder_orders';
        $sql .= ' where '.implode(' and ', $whereSql) . ' order by order_id limit '.(($page-1)*$page_size) .','.$page_size;
        $ordersList = $db->select($sql);
        if($ordersList){
            foreach($ordersList as $k=>$order){
                if(!empty($order['ship_area'])){
                    $arr = explode(':', $order['ship_area']);
                    $order['ship_area'] = $arr[1];
                }
                
                $order['createtime'] = date('Y-m-d H:i:s',$order['createtime']);
                $order['pay_time'] = (!empty($order['pay_time'])) ? date('Y-m-d H:i:s',$order['pay_time']) : '';
                $order['delivery_time'] = (!empty($order['delivery_time'])) ? date('Y-m-d H:i:s',$order['delivery_time']) : '';
                $ordersList[$k]= $order;
            }
        }else{
            $ordersList = array();
        }

        return array('orders'=>$ordersList,'totalResult'=>$totalResult);
    }

    //获取物流信息
    function getTradesInfo($tid,$mobile){
        $db = kernel::database();
        if(!$mobile){
            $msg = '手机号不能为空';
            return false;
        }
        if(!$tid){
            $msg = '订单号不能为空';
            return false;
        }
        $whereSql = array(
            'tid='.$tid,
            'ship_mobile='.$mobile
        );
        $sql = 'select tid,transit_step_info,buyer_nick,logi_company,ship_mobile from sdb_plugins_trades where  '.implode(' and ', $whereSql);
        $trades_info = $db->select($sql);
        return array('trades'=>$trades_info);
    }

    //获取单个订单的详细信息
    function get_single_order($order_id,$ship_mobile){
        //加载DB类
        if(!$order_id){
            $msg = '订单参数错误';
            return $msg;exit;
        }
        if(!$ship_mobile){
            $msg = '手机号码不能为空错误';
            return $msg;exit;
        }
        $filter = array(
            'order_bn' => $order_id,
            'ship_mobile'=> $ship_mobile
        );
        $mdl_orders = app::get('ecorder')->model('orders');
        $order_info = $mdl_orders->dump($filter);
        if(empty($order_info)){
             $msg = '订单不存在,请确认订单号!';
            return $msg;exit;
        }
        $db = kernel::database();
        $order_item_Sql = array(
            'a.order_id='.$order_info['order_id'],
            'a.goods_id=b.goods_id'
            );
        $sql_order_item = 'select a.name,a.nums,b.pic_url,b.price from sdb_ecorder_order_items as a, sdb_ecgoods_shop_goods as b where '.implode(' and ', $order_item_Sql);
        if(empty($sql_order_item)){
             $msg = '订单数据错误,请重试!';
            return $msg;exit;
        }
        $order_item_info = $db->select($sql_order_item);
        $ship_status_arr = array(
            0 => '未发货',
            1 => '已发货',
            2 => '部分发货',
            3 => '部分退货',
            4 => '已退货',
        );
        $return_data = array(
          'consignee' => $order_info['consignee']['name'],
          'area' => $order_info['consignee']['area'],
          'addr' => $order_info['consignee']['addr'],
          'mobile' => $order_info['consignee']['mobile'],
          'ship_status' => $ship_status_arr[$order_info['ship_status']],
          'order_id' => $order_info['order_id'],
          'total_amount' => $order_info['total_amount'],
          'product_item' => $order_item_info
        );
        //tid 订单编号
        $filter_trades = array(
            'tid' => $order_id
        );
        $mdl_orders = app::get('plugins')->model('trades');
        $trades_info = $mdl_orders->dump($filter_trades);
        if(!empty($trades_info)){
            $return_data['logi_no'] = $trades_info['logi_no'];
            $return_data['logi_company'] = $trades_info['logi_company'];
        }
        else{
            $return_data['logi_no'] = '';
            $return_data['logi_company'] = '';
        }
        return array('order_info'=>$return_data);
    }
}