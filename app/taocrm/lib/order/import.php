<?php

class taocrm_order_import{
    
    public function run(&$cursor_id,$params){
        
        base_kvstore::instance('taocrm_orders')->fetch($params['file_name'],$contents);
        $contents = unserialize( $contents );
        $orderObj = &app::get(ORDER_APP)->model('orders');
        $oShop = &app::get(ORDER_APP)->model('shop');
        
        $i = 0;
        while( $v = array_shift( $contents ) ){

            $rs_shop = $oShop->dump($v['shop_id']);
            $this->insertIntoOrder($v, $rs_shop['node_id']);
            
            /*$orderObj->create_order($v);
            
            //新增订单数据处理
            if ($service = kernel::servicelist('service.order')){
                foreach ($service as $object => $instance){
                    if (method_exists($instance, 'save_member')){
                        $member_id = $instance->save_member($v);
                        if($member_id){
                            $v['member_id'] = $member_id;
                        }
                    }
                }
            }
            */
           
            if( ++$i == 100 ){
                base_kvstore::instance('taocrm_orders')->store($params['file_name'],serialize($contents));
                return 1;
                break;
            }
        }
        base_kvstore::instance('taocrm_orders')->delete($params['file_name']);
        return 0;
    }
    
    private function insertIntoOrder($sdf, $nodeId) {
        
        static $orderObj = null;
        static $response = null;
        
        if (!$orderObj) {
            $orderObj = kernel::single('ecorder_rpc_response_order');
            $response = kernel::single('base_rpc_service');
        }
        base_rpc_service::$node_id = $nodeId;
        
        $orderObj->add($sdf, $response);
    }
}
