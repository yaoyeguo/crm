<?php

class ecorder_mdl_orders_manual extends dbeav_model {

    /*
    public function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderby=null)
    {
        $manual_orders = parent::getList($cols, $filter, $offset, $limit, $orderby);
        $mdl_orders = app::get('ecorder')->model('orders');
        
        return $manual_orders;
    }
    */
    
    public function modifier_order_bn($row)
    {
        return $row.' <a target="dialog::{width:700,height:355,title:\'订单明细\'}" href="index.php?app=taocrm&ctl=admin_member&act=getOrderInfo&order_bn='.$row.'">查看</a>';
    }
    
}

