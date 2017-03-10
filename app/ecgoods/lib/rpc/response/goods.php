<?php

/**
 * 前端店铺商品数据业务处理
 * @author ome
 * @access public
 * @copyright www.shopex.cn 2010
 *
 */
 
class ecgoods_rpc_response_goods extends ecgoods_rpc_response {

	/**
     * 商品创建
	 * @access public
	 * @param Array $sdf 商品标准结构的数据
	 * @param Object $responseObj 框架API接口实例化对象
	 * @return array('order_id'=>'商品主键ID')
     */
    public function add($sdf, &$responseObj)
    {
		$goodsObj = new ecgoods_rpc_response_goods_add();
		return $goodsObj->add($sdf, $responseObj);
	}
	
}
