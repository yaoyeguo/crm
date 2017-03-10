<?php
class market_backstage_orders{


	/**
	 * 定义API每页获取的订单数
	 *
	 * @var Integer
	 */
	const PAGESIZE = '100';

	/**
	 *
	 * 获取订单然后分页
	 * 	$data = array(
	 'day'=>'2012-06-28',
	 'session'=>'610090621766fa16cbdb26c202e34b68aa9f6d06baf4dcc374544688',
	 'node_id'=>'',
	 'token'=>'',
	 );
	 */
	function fetch($data,$shop_id=''){
		if(empty($data['day'])){
			return array('status'=>'fail','errmsg'=>'param is error');
		}

		$fetchAli = new market_api_taobao_order();
		$fetchAli->setSessionKey($data['session']);
		$start_time = strtotime($data['day'].' 00:00:00');
		$end_time =  strtotime($data['day'].' 23:59:59');
		$result = $fetchAli->getIncrementOrdersByPage($start_time,$end_time,1,1,$shop_id);
		//var_dump($result);exit;
		if(!empty($result)){
			if($result['status'] == 'succ'){
				$pages = ceil($result['totalNum'] / self::PAGESIZE);
				$pages = intval($pages);
				for($page=1;$page<=$pages;$page++){
					//echo $page."\n";
					$data['page'] = $page;
					$data['shop_id'] = $shop_id;
					kernel::single('taocrm_service_queue')->addJob('market_backstage_orders@fetchPage',$data);
				}
			}elseif($result['status'] == 'timeout'){
				return array('status'=>'timeout');
			}
		}

		return array('status'=>'succ');

	}

	/**
	 *
	 * 每页取订单数据
	 * 	$data = array(
	 'page'=>1
	 'day'=>'2012-06-28',
	 'session'=>'610090621766fa16cbdb26c202e34b68aa9f6d06baf4dcc374544688',
	 'node_id'=>'',
	 'token'=>'',
	 );
	 */
	function fetchPage($data){
		//$conn = new Mysql();
		//$conn->open($this->domain);
		if(empty($data['day'])){
			return array('status'=>'fail','errmsg'=>'param is error');
		}

		$fetchAli = new market_api_taobao_order();
		$fetchAli->setSessionKey($data['session']);
		$start_time = strtotime($data['day'].' 00:00:00');
		$end_time =  strtotime($data['day'].' 23:59:59');
		$result = $fetchAli->getIncrementOrdersByPage($start_time,$end_time,$data['page'],self::PAGESIZE,$data['shop_id']);

		if(!empty($result)){
			if($result['status'] == 'succ'){
				foreach($result['orders'] as $tid){
					//echo $tid."\n";
					$data = array('tid'=>$tid,
					'session'=>$data['session'],
					'node_id'=>$data['node_id'],
					//'token'=>$data['token'],
					);
					//var_export($data);exit;
					kernel::single('taocrm_service_queue')->addJob('market_backstage_orders@fetchInfoIntoQueue',$data['shop_id']);
				}
			}elseif($result['status'] == 'timeout'){
				return array('status'=>'timeout');
			}
		}

		return array('status'=>'succ');
	}

	/**
	 *
	 * 获取订单详细信息
	 * 	$data = array(
	 'tid'=>111111
	 'session'=>'610090621766fa16cbdb26c202e34b68aa9f6d06baf4dcc374544688',
	 'node_id'=>'',
	 'token'=>'',
	 );
	 */
	function fetchInfo($data){
		$fetchAli = new market_api_taobao_order();
		$fetchAli->setSessionKey($data['session']);
		$result = $fetchAli->getFullTrade($data['tid'],$data['shop_id']);
		if(!empty($result)){
			if($result['status'] == 'succ'){
				$trade = $result['trade'];
				$trade['method'] = 'ome.order.add';
				$trade['node_id'] = $data['node_id'];
				$trade['app_id'] = 'taobao';
				$trade['date'] = date('Y-m-d H:i:s');
				$trade['sign'] = self::gen_sign($trade,$data['token']);
				$post = array('domain'=>'http://'.$this->domain.'/index.php/api',
						'trade'=>$trade,
				);
				kernel::single('taocrm_service_queue')->addJob('market_backstage_orders@send',$data);

			}elseif($result['status'] == 'timeout'){
				return array('status'=>'timeout');
			}
		}

		return array('status'=>'succ');
	}

	function fetchInfoIntoQueue($data){
		$fetchAli = new market_api_taobao_order();
		$fetchAli->setSessionKey($data['session']);
		$result = $fetchAli->getFullTrade($data['tid'],$data['shop_id']);

		if(!empty($result)){
			if($result['status'] == 'succ'){
				$order = array('order' => $result['trade'], 'nodeId' => $data['node_id'], 'host'=>$_SERVER['SERVER_NAME']);
				kernel::single('taocrm_service_redis')->redis->RPUSH('tgcrm:SYS_ORDER_QUEUE',serialize($order));

			}elseif($result['status'] == 'timeout'){
				return array('status'=>'timeout');
			}
		}

		return array('status'=>'succ');
	}

	/**
	 *
	 * 发送订单到应用
	 *
	 * array (
	 'domain' => 'http://374544688.taojixiao.taoex.com/index.php/api',
	 'trade' =>
	 array (
	 'order_source' => 'taobao',
	 'order_bn' => '190010363029323',
	 'memeber_id' => '福禄网游数卡专营店',
	 'status' => 'finish',
	 'pay_status' => 1,
	 'ship_status' => 1,
	 'is_delivery' => '',
	 'shipping' => '{"shipping_name":null,"cost_shipping":"0.00","is_protect":"","cost_protect":0,"is_cod":"false"}',
	 'member_info' => '{"uname":"\\u5fc3\\u4e3a\\u4e4b\\u6012\\u653e","name":"","area_state":"","area_city":"","area_district":"","alipay_no":"398698300@qq.com","addr":"","mobile":"","tel":"","email":"","zip":""}',
	 'payinfo' => '{"pay_name":"\\u652f\\u4ed8\\u5b9d","cost_payment":0}',
	 'weight' => '',
	 'title' => '福禄网游数卡专营店',
	 'itemnum' => '1',
	 'modified' => 1340899196,
	 'createtime' => 1340898895,
	 'ip' => '',
	 'consignee' => '{"name":"\\u6768\\u6714","area_state":"","area_city":"","area_district":"","addr":"398698300","zip":"000000","telephone":"","email":"","r_time":"","mobile":""}',
	 'payment_detail' => '{"pay_account":"398698300@qq.com","currency":"CNY","paymethod":"\\u652f\\u4ed8\\u5b9d","pay_time":1340898940,"trade_no":"2012062839201155"}',
	 'pmt_detail' => '[{"pmt_amount":"","pmt_describe":""}]',
	 'cost_item' => '106.80',
	 'is_tax' => 'false',
	 'cost_tax' => '0.00',
	 'tax_title' => '',
	 'currency' => 'CNY',
	 'cur_rate' => 1,
	 'score_u' => '0',
	 'scort_g' => '10',
	 'discount' => 0,
	 'pmt_goods' => '0',
	 'pmt_order' => 0,
	 'total_amount' => '106.80',
	 'cut_amount' => '106.80',
	 'payed' => '106.80',
	 'custom_mark' => '',
	 'mark_text' => '',
	 'mark_type' => '0',
	 'tax_no' => '',
	 'order_limit_time' => false,
	 'coupons_name' => '',
	 'order_objects' => '[{"oid":"190010363029323","obj_type":"goods","obj_alias":"\\u5546\\u54c1","shop_goods_id":"5287917632","bn":"","name":"QQ\\u4f1a\\u5458\\u4e00\\u5e74QQ\\u4f1a\\u5458\\u5305\\u5e74QQ\\u4f1a\\u545812\\u4e2a\\u6708\\u5e74\\u8d3915\\u70b9\\u6210\\u957f\\u53ef\\u67e5\\u65f6\\u95f4\\u81ea\\u52a8\\u5145\\u503c","price":"106.80","quantity":"1","amount":"106.80","weight":"","score":"","order_items":[{"shop_product_id":"","shop_goods_id":"5287917632","item_type":"product","bn":"","name":"QQ\\u4f1a\\u5458\\u4e00\\u5e74QQ\\u4f1a\\u5458\\u5305\\u5e74QQ\\u4f1a\\u545812\\u4e2a\\u6708\\u5e74\\u8d3915\\u70b9\\u6210\\u957f\\u53ef\\u67e5\\u65f6\\u95f4\\u81ea\\u52a8\\u5145\\u503c","product_attr":"","cost":"106.80","quantity":"1","sendnum":0,"amount":"106.80","price":"106.80","weight":"","status":"active","score":0,"create_time":1340898895}]}]',
	 'method' => 'ome.order.add',
	 'node_id' => '1390350139',
	 'app_id' => 'taobao',
	 'date' => '2012-07-04 11:40:46',
	 'sign' => 'C78D60C024E7F8C6D0E18FE0EAB0F7D3',
	 ),
	 )
	 */
	function send($data){
		// echo '<pre>';var_export($data);exit;
		if(!empty($data)){
			$res = @get_http($data['domain'],$data['trade'],8);
			$result = json_decode($res,true);
			if($result['rsp'] == 'fail'){
				return array('status'=>'fail','errmsg'=>$res);
			}elseif(empty($res)){
				return array('status'=>'timeout','errmsg'=>$res);
			}
		}

		return array('status'=>'succ');
	}



	/**
	 *
	 * 获取订单然后分页
	 * 	$data = array(
	 'day'=>'2012-06-28',
	 'session'=>'610090621766fa16cbdb26c202e34b68aa9f6d06baf4dcc374544688',
	 'node_id'=>'',
	 'token'=>'',
	 );
	 */
	function fetchOrders($data,$shop_id=''){
		if(empty($data['day'])){
			return array('status'=>'fail','errmsg'=>'param is error');
		}

		$fetchAli = new market_api_taobao_order();
		$fetchAli->setSessionKey($data['session']);
		$start_time = strtotime($data['day'].' 00:00:00');
		$end_time =  strtotime($data['day'].' 23:59:59');
		$result = $fetchAli->getOrdersByPage($start_time,$end_time,1,1,$shop_id);
		//var_dump($result);exit;
		if(!empty($result)){
			if($result['status'] == 'succ'){
				$pages = ceil($result['totalNum'] / self::PAGESIZE);
				$pages = intval($pages);
				for($page=1;$page<=$pages;$page++){
					//echo $page."\n";
					$data['page'] = $page;
					$data['shop_id'] = $shop_id;
					kernel::single('taocrm_service_queue')->addJob('market_backstage_orders@fetchOrdersPage',$data);
				}
			}elseif($result['status'] == 'timeout'){
				return array('status'=>'timeout');
			}
		}

		return array('status'=>'succ');

	}

	/**
	 *
	 * 每页取订单数据
	 * 	$data = array(
	 'page'=>1
	 'day'=>'2012-06-28',
	 'session'=>'610090621766fa16cbdb26c202e34b68aa9f6d06baf4dcc374544688',
	 'node_id'=>'',
	 'token'=>'',
	 );
	 */
	function fetchOrdersPage($data){
		//$conn = new Mysql();
		//$conn->open($this->domain);
		if(empty($data['day'])){
			return array('status'=>'fail','errmsg'=>'param is error');
		}

		$fetchAli = new market_api_taobao_order();
		$fetchAli->setSessionKey($data['session']);
		$start_time = strtotime($data['day'].' 00:00:00');
		$end_time =  strtotime($data['day'].' 23:59:59');
		$result = $fetchAli->getOrdersByPage($start_time,$end_time,$data['page'],self::PAGESIZE,$data['shop_id']);

		if(!empty($result)){
			if($result['status'] == 'succ'){
				foreach($result['orders'] as $tid){
					//echo $tid."\n";
					$data = array('tid'=>$tid,
					'session'=>$data['session'],
					'node_id'=>$data['node_id'],
					'token'=>$data['token'],
					);
					kernel::single('taocrm_service_queue')->addJob('market_backstage_orders@fetchInfoIntoQueue',$data['shop_id']);
						
				}
			}elseif($result['status'] == 'timeout'){
				return array('status'=>'timeout');
			}
		}

		return array('status'=>'succ');
	}







}

