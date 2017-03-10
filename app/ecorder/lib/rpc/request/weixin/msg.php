<?php
/**
 * 微信注册服务同步请求
 * @author ome
 * @access public
 * @copyright www.shopex.cn 2010
 *
 */
class ecorder_rpc_request_weixin_msg extends ecorder_rpc_request_weixin_backend {

    //关键词响应
    function msgbackend_set($node_id)
    {
        $backend_url = kernel::single('market_service_weixin')->get_wx_openapi();
        
        $data['method'] = 'store.msgbackend.set';
        $data['to_node_id'] = $node_id;
        $data['key_value'] = '';
        $data['backend_url'] = $backend_url;
        return $this->wx_api_request($data);
    }

    //已经注册的关键词列表
    function msgbackend_list($node_id)
    {
        $data['method'] = 'store.msgbackend.list';
        $data['to_node_id'] = $node_id;
        return $this->wx_api_request($data);
    }

    //删除关键词响应
    function msgbackend_delete($node_id)
    {
        $data['method'] = 'store.msgbackend.delete';
        $data['to_node_id'] = $node_id;
        return $this->wx_api_request($data);
    }
    
    /** 
     *  群发给分组
     *  图文消息为mpnews，文本消息为text，语音为voice，音乐为music，
     *  图片为image，视频为video，卡券为wxcard
     */
    function mass_sendall($params)
    {
        if($params['is_to_all'] != 'true' && !$params['group_id']){
            die('Group_id is required!');
        }
        
        if($params['msgtype'] == 'text' && !$params['content']){
            die('Content is required!');
        }
    
        $data['method'] = 'store.message.mass.sendall';
        $data['is_to_all'] = $params['is_to_all'];
        $data['group_id'] = $params['group_id'];
        $data['msgtype'] = $params['msgtype'];
        $data['content'] = $params['content'];
        $data['to_node_id'] = $params['node_id'];
        return $this->wx_api_request($data);
    }
    
    //群发给OpenID
    function mass_send($params)
    {
        $data['method'] = 'store.message.mass.send';
        $data['touser'] = $params['touser'];
        $data['content'] = $params['content'];
        $data['msgtype'] = $params['msgtype'];
        $data['to_node_id'] = $params['node_id'];
        return $this->wx_api_request($data);
    }
    
}