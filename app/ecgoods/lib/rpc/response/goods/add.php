<?php
/**
 * 商品接收模块
 * 
 * @author hzjsq@msn.com
 * @version 0.1b
 */
 
class ecgoods_rpc_response_goods_add {
	
  	/**
     * 插件组
     */
    private $_plugins = array('taobao', 'paipai', 'youa');
    
     /**
     * 插件列表
     * @var Array
     */
    static $_plugObjects = array();

    public function __construct()
    {
    }
    
	/**
	 * 增加商品
	 * 
	 * @param mixed $sdf 标准商品结构
	 * @param object $response RPC对像
	 * @return mixed
	 */
	public function add($sdf , &$response)
    {
		$shop_info['shop_type'] = 'erp';

		return kernel::single('ecgoods_rpc_response_goods_add_erp')->add($sdf , $response);
	}

}
