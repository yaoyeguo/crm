<?php

class ecorder_mdl_order_items extends dbeav_model{
    function getItemDetail($bn,$order_id){
         $aGoods = $this->db->select('SELECT i.*,nums-sendnum AS send,sendnum AS resend,p.store FROM sdb_ome_order_items i
            LEFT JOIN sdb_ome_products p ON i.product_id = p.product_id
            WHERE order_id = \''.$order_id.'\' AND i.bn = \''.$bn.'\'');
        return $aGoods[0];
    }

    public function getOrderIdByPbn($product_bn){
        $sql = 'SELECT order_id FROM sdb_ome_order_items WHERE bn like \''.$product_bn.'%\'';
        $rows = $this->db->select($sql);
        return $rows;
    }

    public function getOrderIdByPbarcode($product_barcode){
        $sql = 'SELECT order_id FROM sdb_ome_order_items as I LEFT JOIN '.
            'sdb_ome_products as P ON I.product_id=P.product_id WHERE P.barcode like \''.$product_barcode.'%\'';
        $rows = $this->db->select($sql);
        return $rows;
    }
    /**
     * 通过product_id获得符合条件的冻结库存值
     * @param unknown_type $product_id
     */
    public function getStoreByProductId($product_id,$offset='0',$limit='10'){
    	$sql = "SELECT o.order_bn,oi.sendnum,oi.nums,o.ship_status  
				FROM sdb_ome_order_items as oi,sdb_ome_orders o 
				where o.order_id = oi.order_id 
				and o.status='active' 
				and oi.product_id = $product_id
				and oi.`delete`='false' 
				and o.ship_status in ('0','2')
				and oi.sendnum != oi.nums
				LIMIT {$offset},{$limit}
				";
    	$rows = $this->db->select($sql);
        return $rows;
    }
    /**
     * 获取符合条件的冻结库存的 总数
     */
    public function count_order_id($product_id){
    	$sql = "SELECT count(*) AS count  
                FROM sdb_ome_order_items as oi,sdb_ome_orders o 
                where o.order_id = oi.order_id 
                and o.status='active' 
                and oi.product_id = $product_id
                and oi.`delete`='false' 
                and o.ship_status in ('0','2')
                and oi.sendnum != oi.nums";
    	$rows = $this->db->selectrow($sql);
        return $rows['count'];
    }
}

