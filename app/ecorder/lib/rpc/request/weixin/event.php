<?php
/**
 * 微信注册服务同步请求
 * @author ome
 * @access public
 * @copyright www.shopex.cn 2010
 *
 */
class ecorder_rpc_request_weixin_event extends ecorder_rpc_request_weixin_backend {

    //事件注册
    function eventbackend_set($data, $node_id)
    {
        $data['method'] = 'store.eventbackend.set';
        $data['to_node_id'] = $node_id;
        return $this->wx_api_request($data);
    }

    //已经注册的事件
    function eventbackend_list($node_id)
    {
        $data['method'] = 'store.eventbackend.list';
        $data['to_node_id'] = $node_id;
        return $this->wx_api_request($data);
    }

    //删除注册的事件
    function eventbackend_delete($node_id)
    {
        $data['method'] = 'store.eventbackend.delete';
        $data['to_node_id'] = $node_id;
        $data['id'] = 0;
        return $this->wx_api_request($data);
    }
    
}