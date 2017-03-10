<?php

class ecorder_mdl_refund_apply extends dbeav_model{
	var $has_many = array(
	   'delivery' => 'delivery'
	);
    var $pay_type = array (
        'online' => '在线支付',
        'offline' => '线下支付',
        'deposit' => '预存款支付',
      );
      
    function _filter($filter,$tableAlias=null,$baseWhere=null){
        if(isset($filter['order_bn'])){
            $orderObj = &$this->app->model("orders");
            $rows = $orderObj->getList('order_id', array('order_bn'=>$filter['order_bn']), 0, -1);
            foreach($rows as $row){
                $orderId[] = $row['order_id'];
            }
            if (empty($orderId)){
                $orderId[] = '0';
            }
            $where .= ' AND order_id IN ('.implode(',', $orderId).')';
            unset($filter['order_bn']);
        }
        return parent::_filter($filter,$tableAlias,$baseWhere).$where;
    }

    function refund_apply_detail($refapply_id){
    	$refapply_detail = $this->dump($refapply_id);
        
        if ($refapply_detail['payment']){
    	    $sql = "SELECT custom_name FROM sdb_ome_payment_cfg WHERE id=".$refapply_detail['payment'];
    	    $payment_cfg = $this->db->selectrow($sql);
            $refapply_detail['payment_name'] = $payment_cfg['custom_name'];
        }else {
            $refapply_detail['payment_name'] = '';
        }
    	
    	$refapply_detail['type'] = $this->pay_type[$refapply_detail['pay_type']];
    	return $refapply_detail;
    }
    
    /* create_refund_apply 添加申请退款单
     * @param sdf $sdf
     * @return sdf
     */
    function create_refund_apply(&$sdf){
        $this->save($sdf);
    }

    function save(&$refund_data,$mustUpdate=NULL){
    	return parent::save($refund_data,$mustUpdate,true);
    }
    
    /**
     * 快捷搜索
     */
    function searchOptions(){
        $options = array(
            'order_bn' => '订单号'
        );
        return $options;
    }
}
?>