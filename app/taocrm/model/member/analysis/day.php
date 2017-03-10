<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class taocrm_mdl_member_analysis_day extends dbeav_model{

	public function get_all_sales_data($filter) {
        if($filter){
            $start_time = strtotime($filter['start_time']);
            $end_time = strtotime($filter['end_time']);
            $where .= " AND c_time > $start_time ";
            $where .= " AND c_time < $end_time ";
            $where .= ' AND shop_id = "'.$filter['shop_id'].'" ';
            $count_by = 'c_'.$filter['count_by'];
        }
        $sql = "select 
            $count_by as date,
            sum(total_orders) as total_orders,
            sum(total_members) as total_members,
            sum(total_amount) as total_amount,
            sum(buy_products) as buy_products,
            sum(finish_orders) as finish_orders,
            sum(succ_members) as succ_members,
            sum(finish_total_amount) as finish_total_amount,
            sum(unpay_orders) as unpay_orders,
            sum(unpay_amount) as unpay_amount,
            sum(refund_orders) as refund_orders,
            sum(refund_amount) as refund_amount
        from sdb_taocrm_member_analysis_day 
        where 1=1 $where
        group by $count_by
        ";
        $rs = $this->db->select($sql);
        foreach($rs as $v){
            $analysis_data[] = $v;
        }
        
        $data['sales_data'] = $sales_data;
        $data['analysis_data'] = $analysis_data;
        return $data;
    }
    
    public function get_order_status($filter) {
        if($filter){
            $start_time = strtotime($filter['start_time']);
            $end_time = strtotime($filter['end_time']);
            $where .= " AND c_time > $start_time ";
            $where .= " AND c_time < $end_time ";
            $where .= ' AND shop_id = "'.$filter['shop_id'].'" ';
            $count_by = 'c_'.$filter['count_by'];
        }
        
        $sql = "select 
            $count_by as date,
            sum(total_orders) as total_orders,
            sum(total_members) as total_members,
            sum(total_amount) as total_amount,
            sum(buy_products) as buy_products,
            sum(finish_orders) as finish_orders,
            sum(succ_members) as succ_members,
            sum(finish_total_amount) as finish_total_amount,
            sum(unpay_orders) as unpay_orders,
            sum(unpay_amount) as unpay_amount,
            sum(refund_orders) as refund_orders,
            sum(refund_amount) as refund_amount,
            sum(failed_orders) as failed_orders,
            sum(failed_amount) as failed_amount,
            sum(failed_members) as failed_members
        from sdb_taocrm_member_analysis_day 
        where 1=1 $where
        group by $count_by
        ";
        $rs = $this->db->select($sql);
        foreach($rs as $v){
            $analysis_data[] = $v;
        }
        
        $data['sales_data'] = $sales_data;
        $data['analysis_data'] = $analysis_data;
        return $analysis_data;
    }

}
