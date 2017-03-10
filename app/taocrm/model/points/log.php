<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class taocrm_mdl_points_log extends dbeav_model{

    function _filter($filter,$tableAlias=null,$baseWhere=null)
    {
        if(isset($filter['member_id']) && is_string($filter['member_id'])){
            $oMembers = $this->app->model("members");
            $rows = $oMembers->getList('member_id', array('uname'=>$filter['member_id']), 0, -1);
            foreach($rows as $row){
                $memberId[] = $row['member_id'];
            }
            if (empty($memberId)){
                $memberId[] = '0';
            }else{
                //处理合并之前的数据
                $rows = $oMembers->getList('member_id', array('parent_member_id'=>$memberId), 0, -1);
                foreach($rows as $row){
                    $memberId[] = $row['member_id'];
                }
            }
            $where .= ' AND member_id IN ('.implode(',', $memberId).')';
            unset($filter['member_id']);
        }
        
        if(isset($filter['order_id'])){
            $orderObj = app::get('ecorder')->model("orders");
            $rows = $orderObj->getList('order_id', array('order_bn'=>$filter['order_id']), 0, -1);
            foreach($rows as $row){
                $orderId[] = $row['order_id'];
            }
            if (empty($orderId)){
                $orderId[] = '0';
            }
            $where .= ' AND order_id IN ('.implode(',', $orderId).')';
            unset($filter['order_id']);
        }
        
        if(isset($filter['refund_id'])){
            $oRefunds = app::get('ecorder')->model("refunds");
            $rows = $oRefunds->getList('refund_id', array('refund_bn'=>$filter['refund_id']), 0, -1);
            foreach($rows as $row){
                $orderId[] = $row['refund_id'];
            }
            if (empty($orderId)){
                $orderId[] = '0';
            }
            $where .= ' AND refund_id IN ('.implode(',', $orderId).')';
            unset($filter['refund_id']);
        }
        return parent::_filter($filter,$tableAlias,$baseWhere).$where;
    }
    
    
	function export_csv($data){
		foreach ($data['contents'] as $k=>$v) {
			$str = '"36375","33935","1","167523364708058","","体验一店","2012-05-21 14:16","1","","","",""';
			$str1=str_replace("'" , "" , $str);
			//error_log(var_export($arr,true),3,'d:/ddd.log');
			exit;
		
		}
    }
    
}