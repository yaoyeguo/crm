<?php
/**
 * 前端店铺退款业务处理
 * @author ome
 * @access public
 * @copyright www.shopex.cn 2010
 *
 */
class ecorder_rpc_response_refund extends ecorder_rpc_response
{

    function add($data, &$responseObj)
    {
        $log = app::get('ecorder')->model('api_log');
        $logTitle = '退款单接口['. $data['tid'] . $data['order_bn'] .']';
        $logInfo = '退款单接口：<BR>';
        $logInfo .= '接收参数 $sdf 信息：' . var_export($data, true) . '<BR>';
        
        $logInfo .= '单据状态为：'.$data['status'].' <BR>';
        $task_id = $data['refund_id'] ? $data['refund_id'] : $data['refund_bn'];
        if($task_id){
            $log->write_log($log->gen_id(), $logTitle, __CLASS__, __METHOD__, '', '', 'response', 'success', $logInfo, array('task_id'=>$task_id));
        }else{
            $log->write_log($log->gen_id(), $logTitle, __CLASS__, __METHOD__, '', '', 'response', 'fail', $logInfo.'<br/>退款单编号不能为空', array('task_id'=>$task_id));
            return false;
        }
        
		$this->_shopInfo = $this->fetchShopInfo();
        /*
		$sdf = array(
            'refund_id' => $data['refund_id'],
            'tid' => $data['tid'],
         	'title' => $data['title'] ? $data['title'] : '',
            'buyer_nick' => $data['buyer_nick'],
            'seller_nick' => $data['seller_nick'],
            'total_fee' => $data['total_fee'],
            'status' => $data['status'],
            'created' => ($data['created']),
            'modified' => ($data['modified']),
            'refund_fee' => $data['refund_fee'],
            'oid' => $data['oid'],
            'good_status' => $data['good_status'],
            'company_name' => $data['company_name'] ? $data['company_name'] : '',
            'sid' => $data['sid'] ? $data['sid'] : '',
            'payment' => $data['payment'] ? $data['payment'] : '',
            'reason' => $data['reason'],
            'desc' => (string)$data['desc'],
            'num' => $data['num'],
            'has_good_return' => $data['has_good_return'],
            'order_status' => $data['order_status'],
            'shop_id' => $this->_shopInfo['shop_id'],
		);
        */

        //转换标准格式sdf
        $sdf = $this->convertSdfParams($data);
		$order_bn = $sdf['tid'];
		$refund_bn = $sdf['refund_id'];
		
		$this->save_tb_refunds($sdf);

		//返回值
		$return_value = array('tid'=>$order_bn,'refund_id'=>$refund_bn);
        return $return_value;
    }

    function save_tb_refunds($sdf)
    {
        $oRefunds = app::get('ecorder')->model('tb_refunds');
        $rs = $oRefunds->dump(array('refund_id'=>$sdf['refund_id']), 'id');
        if($rs){
            $q = $oRefunds->update($sdf, array('id'=>$rs['id']));
            $sdf['id'] = $rs['id'];
        }else{
            $q = $oRefunds->insert($sdf);
            $sdf['id'] = $oRefunds->lastinsertid();
        }

            $this->save_refunds($sdf);

        //退款成功，扣除积分
        if(strtoupper($sdf['status']) == 'SUCCESS'){
            $taocrm_service_member = kernel::single('taocrm_service_member');
            $taocrm_service_member->updateMemberPoints($sdf['id'], 'refunds', $sdf);
        }
            
        return $sdf['refund_id'];
    }

    protected function convertSdfParams($data)
    {
        $data['status'] = strtolower($data['status']);
        switch($data['status']){
            case 'closed': $data['status']='CLOSED';break;
            case 'success': $data['status']='SUCCESS';break;
            case 'seller_refuse_buyer': $data['status']='SELLER_REFUSE_BUYER';break;
            case 'wait_buyer_return_goods': $data['status']='WAIT_BUYER_RETURN_GOODS';break;
            case 'wait_seller_agree': $data['status']='WAIT_SELLER_AGREE';break;
            
            case 'succ': $data['status']='SUCCESS';break;
            case '3': $data['status']='SUCCESS';break;
            case '0': $data['status']='WAIT_SELLER_AGREE';break;
            case '1': $data['status']='WAIT_BUYER_RETURN_GOODS';break;
            default : $data['status']='UNKOWN';
        }
        
        //获取会员手机号
        $data['member_id'] = 0;
        if($data['tid']){
            $sql = "select a.order_id,a.member_id,a.ship_name,a.ship_mobile,b.uname 
                    from sdb_ecorder_orders as a 
                    left join sdb_taocrm_members as b on a.member_id=b.member_id 
                    where a.order_bn='".$data['tid']."' ";
            $rs = kernel::database()->selectrow($sql);
            if($rs){
                $order_id = $rs['order_id'];
                $data['member_id'] = $rs['member_id'];
                $data['mobile'] = $rs['ship_mobile'];
                $data['buyer_nick'] = $rs['uname'] ? $rs['uname'] : $rs['ship_name'];
            }
        }
        
        if($data['buyer_nick'] && $data['member_id']==0){
            $sql = "select member_id,mobile from sdb_taocrm_members where uname='".$data['buyer_nick']."' ";
            $rs = kernel::database()->selectrow($sql);
            if($rs){
                $data['member_id'] = $rs['member_id'];
                $data['mobile'] = $rs['mobile'];
            }
        }
        
        //解析退款商品
        if(isset($data['refund_item_list'])){
            $refund_item_list = json_decode($data['refund_item_list'], true);
            if($refund_item_list['return_item']){
                $return_item = $refund_item_list['return_item'][0];
                $data['oid'] = $return_item['oid'];
                $data['num'] = $return_item['num'];
                $data['title'] = $return_item['title'] ? $return_item['title'] : $return_item['outer_id'];
            }
        }
        
        //从子订单查询商品明细
        if(!$data['title'] && $order_id && $data['oid']){
            $sql = "select name,nums from sdb_ecorder_order_items where order_id=$order_id and oid='".$data['oid']."' ";
            $rs = kernel::database()->selectrow($sql);
            if($rs){
                $data['num'] = $rs['nums'];
                $data['title'] = $rs['name'];
            }
        }
        
        $ecorder_func = kernel::single('ecorder_func');
        $data['created'] = $ecorder_func->date2time($data['created']);
        $data['modified'] = $ecorder_func->date2time($data['modified']);
        $data['date'] = $ecorder_func->date2time($data['date']);
        $data['t_ready'] = $ecorder_func->date2time($data['t_ready']);
        
        if(!$data['desc'] or $data['desc']=='None') $data['desc'] = $data['reason'];
        
        $sdf = array(
            'refund_id' => $data['refund_id'] ? $data['refund_id'] : $data['refund_bn'],
            'tid' => $data['tid'] ? $data['tid'] : $data['order_bn'],
            'title' => $data['title'],
            'buyer_nick' => $data['buyer_nick'] ? $data['buyer_nick'] : $data['buyer_id'],
            'seller_nick' => $data['seller_nick'],
            'total_fee' => $data['total_fee'] ? $data['total_fee'] : $data['cur_money'],
            'created' => $data['created'] ? $data['created'] : $data['t_ready'],
            'modified' => $data['modified'] ? $data['modified'] : time(),
            'down_time' => date('Y-m-d H:i:s'),
            'refund_fee' => $data['refund_fee'] ? $data['refund_fee'] : $data['money'],
            'oid' => $data['oid'],
            'company_name' => $data['company_name'],
            'sid' => $data['sid'],
            'member_id' => $data['member_id'],
            'payment' => $data['cur_money'] ? floatval($data['cur_money']) : floatval($data['payment']),
            'reason' => $data['reason'],
            'desc' => $data['desc'] ? (string)$data['desc'] : (string)$data['memo'],
            'num' => $data['num'],
            'has_good_return' => $data['has_good_return'],
            'shop_id' => $this->_shopInfo['shop_id'],
            'status' => $data['status'],
            'mobile' => $data['mobile'],
            //'good_status' => $data['good_status'] ? $data['good_status'] : 'BUYER_RETURNED_GOODS',
            //'order_status' => $data['order_status'] ? $data['order_status'] : 'TRADE_CLOSED',
        );

        return $sdf;
    }
		
    //保存到crm原来的退款表
    public function save_refunds($data)
    {
        $order_id = 0;
        $oOrders = app::get('ecorder')->model('orders');
        $rs_order = $oOrders->dump(array('order_bn'=>$data['tid']));
        if($rs_order){
            $order_id = $rs_order['order_id'];
            $oOrders->update(
                array('pay_status'=>'5'),
                array('order_id'=>$order_id)
            );
        }

        $sdf = array(
            'refund_bn' => $data['refund_id'],
            'account' => $data['seller_nick'],
            'pay_account' => $data['buyer_nick'],
            'currency' => 'RMB',
            'money' => $data['refund_fee'],
            'paycost' => 0,
            'cur_money' => $data['payment'],
            'pay_type' => 'online',
            'payment' => 0,
            'paymethod' => '支付宝',
            'download_time' => $data['created'],
            'status' => 'succ',
            'trade_no' => $data['tid'],
            'order_id' => $order_id,
            'shop_id' => $this->_shopInfo['shop_id'],
        );

        $oRefunds = app::get('ecorder')->model('refunds');
        $rs = $oRefunds->dump(array('refund_bn'=>$sdf['refund_bn']));
        if($rs){
            $oRefunds->update($sdf, array('refund_bn'=>$sdf['refund_bn']));
        }else{
            $oRefunds->insert($sdf);
        }
    }

    protected function fetchShopInfo()
    {
        $node_id = base_rpc_service::$node_id;
        $oShop = app::get('ecorder')->model('shop');
        $shop_info = $oShop->dump(array('node_id' => $node_id), '*');
        return $shop_info;
	}

	/**
	 * 添加退款单
	 * @access public
	 * @param array $refund_sdf 退款单数据
	 * @param object $responseObj 框架API接口实例化对象
	 * @return array 退款单主键ID array('refund_id'=>'退款单主键ID')
	 */
	function add_back($refund_sdf, &$responseObj){
		$shop_id = $this->get_shop_id($responseObj);
		$status = $refund_sdf['status'];
		$refund_money = floatval($refund_sdf['money']);
		$refund_bn = $refund_sdf['refund_bn'];
		$refund_type = $refund_sdf['refund_type'];
		$order_bn = $refund_sdf['order_bn'];
		$refundObj = &app::get('ecorder')->model('refunds');
		//$refund_applyObj = &app::get('ecorder')->model('refund_apply');

		//返回值
		$return_value = array('tid'=>$order_bn,'refund_id'=>$refund_bn);

		//状态值判断
		if ($status==''){
			$responseObj->send_user_error(app::get('base')->_('Status field value is not correct'), $return_value);
		}
		//退款金额判断
		if ($refund_money<=0){
			$responseObj->send_user_error(app::get('base')->_('Money field value is not correct'), $return_value);
		}
		//判断退款单是否已经存在
		if($refundObj->dump(array('refund_bn'=>$refund_sdf['refund_bn'],'shop_id'=>$shop_id))){

			return $return_value;
		}

		if ($refund_bn!='' and $order_bn!=''){

			$shop_id = $this->get_shop_id($responseObj);

			$orderObj = &app::get('ecorder')->model('orders');
			$shopObj = &app::get('ecorder')->model('shop');
			$oApi_log = &app::get('ecorder')->model('api_log');
			$order_detail = $orderObj->dump(array('shop_id'=>$shop_id,'order_bn'=>$order_bn), 'pay_status,status,process_status,order_id,payed,cost_payment');
			//判断订单是否已经存在
			if(!$order_detail){
				$responseObj->send_user_error(app::get('base')->_('order('.$order_bn.') is no exist'), $return_value);
			}

			$shop_detail = $shopObj->dump($shop_id, 'name');
			$shop_name = $shop_detail['name'];

			$payed_money = floatval($order_detail['payed']) - floatval($order_detail['cost_payment']);
			$msg = '接受参数:<BR>'.var_export($refund_sdf,true).'<BR>';

			//退款金额>已支付金额：返回错误消息
			if ($refund_money > $payed_money){
				//日志记录
				$api_filter = array('marking_value'=>$refund_bn,'marking_type'=>'refund_money');
				$api_detail = $oApi_log->dump($api_filter, 'log_id');
				if (empty($api_detail['log_id'])){
					$msg .= $refund_bn.'退款金额>已支付金额';
					$log_title = '店铺('.$shop_name.')添加退款单,'.$msg;
					$addon = $api_filter;
					$log_id = $oApi_log->gen_id();
					$oApi_log->write_log($log_id,$log_title,__CLASS__,__FUNCTION__,'','','response','fail',$msg,$addon);
				}
				$responseObj->send_user_error(app::get('base')->_('refund amount is greater than the amount paid'), $return_value);
			}
			//判断订单是否已经支付
			if (!in_array($order_detail['pay_status'],array('1','3','4'))){

				//日志记录
				$api_filter = array('marking_value'=>$refund_bn,'marking_type'=>'refund_payment');
				$api_detail = $oApi_log->dump($api_filter, 'log_id');
				if (empty($api_detail['log_id'])){
					$order_status = kernel::single('ome_order_status')->pay_status($order_detail['pay_status']);
					$msg .= $order_detail['order_bn'].$order_status.',无法退款';
					$log_title = '店铺('.$shop_name.')添加退款单,';
					$addon = $api_filter;
					$log_id = $oApi_log->gen_id();
					$oApi_log->write_log($log_id,$log_title,__CLASS__,__FUNCTION__,'','','response','fail',$msg,$addon);
				}
				$responseObj->send_user_error(app::get('base')->_('Order pay '.$order_detail['pay_status'].',can not refund'), $return_value);
			}
			//判断订单状态是否为活动订单
			if ($order_detail['status']!='active'){
				//日志记录
				$api_filter = array('marking_value'=>$refund_bn,'marking_type'=>'refund_status');
				$api_detail = $oApi_log->dump($api_filter, 'log_id');
				if (empty($api_detail['log_id'])){
					$msg .= $order_detail['order_bn'].'不是活动订单,无法退款';
					$log_title = '店铺('.$shop_name.')添加退款单,';
					$addon = $api_filter;
					$log_id = $oApi_log->gen_id();
					$oApi_log->write_log($log_id,$log_title,__CLASS__,__FUNCTION__,'','','response','fail',$msg,$addon);
				}
				$responseObj->send_user_error(app::get('base')->_('Order status is not active，can not refund'), $return_value);
			}
			//判断订单确认状态
			if ($order_detail['process_status']=='cancel'){
				//日志记录
				$api_filter = array('marking_value'=>$refund_bn,'marking_type'=>'refund_process_status');
				$api_detail = $oApi_log->dump($api_filter, 'log_id');
				if (empty($api_detail['log_id'])){
					$msg .= $order_detail['order_bn'].'确认状态取消,无法退款';
					$log_title = '店铺('.$shop_name.')添加退款单,';
					$addon = $api_filter;
					$log_id = $oApi_log->gen_id();
					$oApi_log->write_log($log_id,$log_title,__CLASS__,__FUNCTION__,'','','response','fail',$msg,$addon);
				}
				$responseObj->send_user_error(app::get('base')->_('Order is cancel,can not refund'), $return_value);
			}

			$order_id = $order_detail['order_id'];
			$refund_sdf['t_ready'] = kernel::single('ecorder_func')->date2time($refund_sdf['t_ready']);
			$refund_sdf['t_sent'] = kernel::single('ecorder_func')->date2time($refund_sdf['t_sent']);
			$refund_sdf['t_received'] = kernel::single('ecorder_func')->date2time($refund_sdf['t_received']);

			if ($status=="succ" || $refund_type == 'refund'){//退款成功

				$sdf = array(
                    'refund_bn' => $refund_bn,
                    'shop_id' => $shop_id,
                    'order_id' => $order_id,
                    'account' => $refund_sdf['account'],
                    'bank' => $refund_sdf['bank'],
                    'pay_account' => $refund_sdf['pay_account'],
                    'currency' => $refund_sdf['currency'],
                    'money' => $refund_money?$refund_money:'0',
                    'paycost' => $refund_sdf['paycost'],
                    'cur_money' => $refund_sdf['cur_money']?$refund_sdf['cur_money']:'0',
                    'pay_type' => $refund_sdf['pay_type']?$refund_sdf['pay_type']:'online',
                    'paymethod' => $refund_sdf['paymethod'],
                    't_ready' => $refund_sdf['t_ready'],
                    't_sent' => $refund_sdf['t_sent'],
                    't_received' => $refund_sdf['t_received'],
                    'status' => $status,
                    'memo' => $refund_sdf['memo'],
                    'trade_no' => $refund_sdf['trade_no']
				);
				$refundObj->create_refunds($sdf);
				$this->_updateOrder($order_id,$shop_id,$refund_money);
			}elseif ($refund_type == 'apply'){
				//申请退款
				/*$addon = serialize(array('refund_bn'=>$refund_bn));
				//判断申请退款单是否已经存在
				$apply_list = $refund_applyObj->getList('addon', array('order_id'=>$order_id), 0, -1);
				if ($apply_list) {
				foreach ($apply_list as $addon){
				$addon = unserialize($addon['addon']);
				$apply_refund_bn = $addon['refund_bn'];
				if ($apply_refund_bn == $refund_bn){
				return $return_value;
				}
				}
				}

				$sdf = array
				(
				'order_id' => $order_id,
				'pay_type' => $refund_sdf['pay_type']?$refund_sdf['pay_type']:'online',
				'account' => $refund_sdf['account'],
				'bank' => $refund_sdf['bank'],
				'pay_account' => $refund_sdf['pay_account'],
				'money' => $refund_money?$refund_money:'0',
				'refunded' => '0',
				'memo' => $refund_sdf['memo'],
				'create_time' => $refund_sdf['t_ready'],
				'status' => $status,
				'shop_id' => $shop_id,
				'addon' => $addon,
				);
				$refund_applyObj->create_refund_apply($sdf);*/
			}
			return $return_value;
		}else{
			$responseObj->send_user_error(app::get('base')->_('Refund_bn and Order_bn can not be empty'), $return_value);
		}
	}

	/**
	 * 更新退款单状态
	 * @access public
	 * @param array $status_sdf 退款单状态数据
	 * @param object $responseObj 框架API接口实例化对象
	 */
    function status_update($status_sdf, &$responseObj)
    { 
		$status = $status_sdf['status'];
		$refund_bn = $status_sdf['refund_bn'];
		$order_bn = $status_sdf['order_bn'];

		//返回值
		$return_value = array('tid'=>$order_bn,'refund_id'=>$refund_bn);

		//状态值判断
		if ($status==''){
			$responseObj->send_user_error(app::get('base')->_('Status field value is not correct'), $return_value);
		}
		if ($refund_bn!='' and $order_bn!=''){

			$shop_id = $this->get_shop_id($responseObj);
			$orderObj = &app::get('ecorder')->model('orders');
			$refundObj = &app::get('ecorder')->model('refunds');
			$order_detail = $orderObj->dump(array('shop_id'=>$shop_id,'order_bn'=>$order_bn), 'order_id');
			$refund_detail = $refundObj->dump(array('refund_bn'=>$refund_bn,'shop_id'=>$shop_id));

			$order_id = $order_detail['order_id'];
			if ($status=="succ"){//已支付

				//更新退款单状态
				$filter = array('refund_bn'=>$refund_bn,'shop_id'=>$shop_id);
				$data = array('status'=>$status);
				$refundObj->update($data, $filter);
				//更新订单状态
				$this->_updateOrder($order_id,$shop_id,$refund_detail['money']);
			}

			return $return_value;

		}else{
			$responseObj->send_user_error(app::get('base')->_('Refund_bn and Order_bn can not be empty'), $return_value);
		}
	}

	/**
	 * 更新订单状态及金额
	 * @access private
	 * @param string order_id
	 * @param string shop_id
	 * @param money refund_money
	 */
    private function _updateOrder($order_id, $shop_id, $refund_money)
    {
		$orderObj = &app::get('ecorder')->model('orders');
		$order_detail = $orderObj->dump(array('shop_id'=>$shop_id,'order_id'=>$order_id), 'payed,cost_payment');

		$payed_money = $order_detail['payed'] - $order_detail['cost_payment'];
			
		//支付状态：退款金额与已支付金额比较
		if ($refund_money==$payed_money){
			$pay_status = '5';#全部退款
		}else{
			$pay_status = '4';#部分退款
		}
		$payed = $payed_money - $refund_money;
		$filter = array("order_id"=>$order_id);
		$data = array("pay_status"=>$pay_status,"payed"=>$payed);
		$orderObj->update($data, $filter);

	}
}