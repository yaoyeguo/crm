<?php
/**
 * 商品接口
 * 
 * @author hzjsq@msn.com
 * @version 0.1b
 */
 
interface ecgoods_rpc_response_goods_add_interface {
	
	/**
	 * 新增一个商品
	 * 
	 * @param void
	 * @return Boolean
	 */
	 public function createProcess($sdf);
	
	/**
	 * 更新一个商品
	 * 
	 * @param void
	 * @return array
	 */
	public function updateProcess($sdf, $goods_id);
    
}