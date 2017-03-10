<?php
class taocrm_mdl_rebate extends dbeav_model{

    public function modifier_rebate_amount($row){
        return $row;
    }

    public function modifier_order_id($row){
        $ecorder_orders = app::get('ecorder')->model('orders');
        if(!empty($row)){
            $ecorder_order_bn = $ecorder_orders->dump($row,'order_bn');
            $order_bn  = $ecorder_order_bn['order_bn'];
        }else{
            $order_bn = '---';
        }
        return $order_bn;
    }
}