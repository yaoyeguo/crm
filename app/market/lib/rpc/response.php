<?php
/**
 * RPC响应基类
 * @author shopex.cn ome
 * @access public
 * @copyright www.shopex.cn 2010
 *
 */
class market_rpc_response {
    
    function __construct(){
        if (defined('DEBUG') && DEBUG == true && function_exists('debug')){
            debug($_POST);
        }
    }

    /**
     * 获取请求方店铺信息
     * 请求方店铺信息过滤，拒绝绑定关系不正确的访问
     * @param string $col 店铺字段
     * @param object $responseObj 框架层对象引用
     */
    function get_shop($col="*", &$responseObj){
        $shopObj = &app::get(ORDER_APP)->model('shop');
        $node_id = base_rpc_service::$node_id;
        $shop = $shopObj->dump(array('node_id'=>$node_id), $col);
        if($shop){
            return $shop;
        }else{
            $responseObj->send_user_error(app::get('base')->_('Can\'t recognize the source'), '');
            return false;
        }
    }
    
    function get_shop_id(&$responseObj){
        $shop = $this->get_shop("shop_id", $responseObj);
        return $shop['shop_id'];
    }
}