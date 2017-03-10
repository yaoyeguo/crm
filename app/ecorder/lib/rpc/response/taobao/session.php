<?php
/**
 * 淘宝登录处理
 * @author ecorder
 * @access public
 * @copyright www.shopex.cn 2010
 *
 */
class ecorder_rpc_response_taobao_session extends ecorder_rpc_response
{

    /**
     * 淘宝登录状态更新
     * @param bool $status 状态，true:正常  false:失败
     * @param string $node_id 节点ID
     * @return null
     */
    function status($session_sdf){

        //状态，true:正常  false:失败
        $status = $session_sdf['status'];
        //淘宝session
        $session = $session_sdf['session'];
        //昵称：淘宝帐号
        $nickname = $session_sdf['nickname'];
        $node_id = base_rpc_service::$node_id;

        // 更新addon字段
        $shopObj = &app::get('ecorder')->model('shop');
        $addon = array('session'=>$session, 'nickname'=>$nickname);
        $data = array('addon'=>$addon);
        $filter = array('node_id'=>$node_id);
        $shopObj->update($data, $filter);

        // 更新KVSTORE登录状态
        app::get('ecorder')->setConf('taobao_session_'.$node_id, $status);
    }


}
?>