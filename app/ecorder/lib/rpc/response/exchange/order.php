<?php

/**
 * 前端积分兑换订单数据业务处理
 * @author ome
 * @access public
 * @copyright www.shopex.cn 2010
 *
 */
class ecorder_rpc_response_exchange_order extends ecorder_rpc_response {

    var $shop_info;

    /**
     * 订单创建
     * @access public
     * @param Array $sdf 订单标准结构的数据
     * @param Object $responseObj 框架API接口实例化对象
     * @return array('order_id'=>'订单主键ID')
     */
    public function add($sdf, &$responseObj)
    {
        $log = app::get('ecorder')->model('api_log');
        $logTitle = '积分订单新增 - '. $sdf['order_bn'] .'';
        $logInfo = '积分订单：<BR>';
        $logInfo .= '接收参数 $sdf 信息：' . var_export($sdf, true) . '<BR>';
            
        //err_log($sdf);
        $order_objects = json_decode($sdf['order_objects'], true);
        $consignee = json_decode($sdf['consignee'], true);
        $member_info = json_decode($sdf['member_info'], true);
        
        $this->fetch_shop_info();
        
        if(!$consignee['mobile'] && $this->chk_mobile($consignee['telephone'])){
            $consignee['mobile'] = $consignee['telephone'];
        }
        
        //订单数据结构转换
        $mdl_exchange_orders = app::get('ecorder')->model('exchange_orders');
        $order = array(
            'shop_id' => $this->shop_info['shop_id'],
            'member_id' => $this->get_member_id($member_info),
            'order_bn' => $sdf['order_bn'],
            'uname' => $member_info['uname'],
            'receiver' => $consignee['name'],
            'tel' => $consignee['telephone'],
            'mobile' => $consignee['mobile'],
            'state' => $consignee['area_state'],
            'city' => $consignee['area_city'],
            'area' => $consignee['area_district'],
            'addr' => $consignee['addr'],
            'goods_bn' => $order_objects[0]['order_items'][0]['bn'],
            'goods_name' => $order_objects[0]['order_items'][0]['name'],
            'num' => $order_objects[0]['order_items'][0]['quantity'],
            'pay_status' => $sdf['pay_status'],
            'ship_status' => $sdf['ship_status'],
            'status' => $sdf['status'],
            'source' => 'ecshop',
            'create_time' => time(),
            'modified_time' => time(),
        );
        
        $filter = array(
            'order_bn'=>$sdf['order_bn'],
            'source'=>'ecshop'
        );
        $rs = $mdl_exchange_orders->dump($filter, 'order_id');
        if($rs){
            $order_id = $rs['order_id'];
            $logTitle = '积分订单更新 - '. $sdf['order_bn'] .'';
            $mdl_exchange_orders->update($order, array('order_id'=>$order_id));
        }else{
            $mdl_exchange_orders->insert($order);
            $order_id = $order['order_id'];
        }
        
        $log->write_log($log->gen_id(), $logTitle, __CLASS__, __METHOD__, '', '', 'response', 'success', $logInfo, array('task_id'=>$sdf['order_bn']));
        return array('order_id'=>$order_id);
    }
    
    private function get_member_id($member_info)
    {
        $member_id = 0;
        if( ! $member_info['uname']){
            return $member_id;
        }
        
        $shop_id = $this->shop_info['shop_id'];
        $uname = $this->member_info['uname'];
        
        $mdl_members = app::get('taocrm')->model('members');
        $rs = $mdl_members->dump(array('uname'=>$uname, 'shop_id'=>$shop_id), 'member_id');
        if($rs){
            $member_id = $rs['member_id'];
        }
        
        return $member_id;
    }
    
    private function fetch_shop_info()
    {
        $nodeId = base_rpc_service::$node_id;
        $this->shop_info = app::get('ecorder')->model('shop')->dump(array('node_id' => $nodeId), '*');
    }
    
    private function chk_mobile($mobile)
    {
        return preg_match("/1[0-9]{10}/",$mobile);
    }
}
