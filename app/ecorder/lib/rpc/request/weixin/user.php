<?php
/**
 * 微信服务
 * @author ome
 * @access public
 * @copyright www.shopex.cn 2010
 *
 */
class ecorder_rpc_request_weixin_user extends ecorder_rpc_request_weixin_backend {

    //微信关注者列表
    function user_list($params)
    {
        $data['method'] = 'store.user.get';
        if($params['next_openid'])
            $data['next_openid'] = $params['next_openid'];
        $data['to_node_id'] = $params['node_id'];
        return $this->wx_api_request($data);
    }

    //微信用户详细信息
    function user_info($params)
    {
        $data['method'] = 'store.user.info';
        $data['openid'] = $params['openid'];
        $data['to_node_id'] = $params['node_id'];
        $data['lang'] = $params['lang'] ? $params['lang'] : 'zh_CN';
        return $this->wx_api_request($data);
    }
    
}