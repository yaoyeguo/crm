<?php
/**
 * 订单接收结口
 * 
 * @author hzjsq@msn.com
 * @version 0.1b
 */
 
interface ecorder_rpc_response_order_add_interface {
	
	/**
	 * 检查一个订单是否要保存
	 * 
	 * @param void
	 * @return Boolean
	 */
	 public function acceptCreateOrder();
	
	/**
	 * 更新一个订单
	 * 
	 * @param void
	 * @return array
	 */
	public function updateProcess();
}