<?php
/**
 * 前端店铺订单数据业务处理
 * @author ome
 * @access public
 * @copyright www.shopex.cn 2010
 *
 */

class plugins_rpc_response_order extends plugins_rpc_response {
	
	function __construct($app){
        parent::check_authority();
    }
	
	//获取订单号信息
    public function getList(){
    	$data = $_POST;
		$sDate = strtotime($data['start_time']);
		$eDate = strtotime($data['end_time']);
		$page = $data['page'];
		$page_size = $data['page_size'];
		$node_id = $data['node_id'];
		$session = $this->get_session($node_id);
    	$orderObj = new plugins_api_taobao_order();
    	$orderObj->setSessionKey($session);
    	$orders = $orderObj->getIncrementOrdersByPage($sDate, $eDate,$page,$page_size,$shop_id);
    	if($orders['status'] == 'fail'){
    		echo  json_encode(array('res' => '','rsp' => 'fail','data' => $orders));
    	}else{
    		unset($orders['status']);
    		echo json_encode(array('res' => '','rsp' => 'succ','data'=> $orders));
    	}
    	exit();
    }
    
    //获取订单详情
    public function getInfo(){
    	$data = $_POST;
    	$node_id = $_POST['node_id'];
		$orderObj = new plugins_api_taobao_order();
		$session = $this->get_session($node_id);
    	$orderObj->setSessionKey($session);
		$tid = $data['tid'];
    	$orderOme = $orderObj->getFullTrade($tid);
    	if($orderOme['status'] == 'fail'){
    		echo json_encode(array('res' => '','rsp' => 'fail','data' => $orderOme));
    	}else{
    		echo json_encode(array('res' => '','rsp' => 'succ','data' => $orderOme['trade']));
    	}
    	exit();
    }
    
    //虚拟发货
    public function ship(){
        return false;//疑似废弃，看有没有客户报错吧……
    	$data = $_POST;
    	$tid = $data['tid'];
    	$node_id = $data['node_id'];
    	$orderObj = new plugins_api_taobao_order();
		$session = $this->get_session($node_id);
		$orderObj->setSessionKey($session);
		
        $shop_id = '';//因为函数功能不确定有没有地方在用，这里先初始化变量
		$orderOme = $orderObj->sendGoods($tid,$shop_id);
		if($orderOme['status'] == 'fail'){
    		echo json_encode(array('res' => '','rsp' => 'fail','data' => $orderOme));
    	}else{
    		echo json_encode(array('res' => '','rsp' => 'succ','data' => array('tid'=>$tid)));
    	}
    	exit();
    }
    
    //退款信息
    public function refundList(){
    	$data = $_POST;
    	$sDate = strtotime($data['start_time']);
		$eDate = strtotime($data['end_time']);
		$page = $data['page'];
		$page_size = $data['page_size'];
		$status = $data['status'];
		if(empty($sDate) || empty($eDate) || empty($page) || empty($page_size)){
			echo  json_encode(array('res'=>'','rsp'=>'fail','data'=>array('msg'=>'Params is empty.')));
            exit();
		}
		
		if($sDate > $eDate){
			echo  json_encode(array('res'=>'','rsp'=>'fail','data'=>array('msg'=>'start time is larger than end time.')));
            exit();
		}
		$buyer_nick = $data['buyer_nick'];
		$node_id = $data['node_id'];
		$session = $this->get_session($node_id);
    	$orderObj = new plugins_api_taobao_order();
    	$orderObj->setSessionKey($session);
    	$orders = $orderObj->getRefundOrdersByPage($sDate, $eDate,$buyer_nick,$status,$page,$page_size,$shop_id);
    	if($orders['status'] == 'fail'){
    		echo json_encode(array('res' => '','rsp' => 'fail','data' => $orders['msg']));
    	}else{
    		unset($orders['status']);
    		echo json_encode(array('res' => '','rsp' => 'succ','data' => $orders));
    	}
    	exit();
    }
    
	private function get_session($node_id) {
    
        $db = kernel::database();
        $rs = $db->select("SELECT addon FROM sdb_ecorder_shop WHERE node_id='$node_id' AND  disabled='false' LIMIT 1");
        if(!$rs) return false;
        $data = unserialize($rs[0]['addon']);
        return $data['session'];
    }
}


