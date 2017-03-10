<?php
/**
 * 微信注册服务同步请求
 * @author ome
 * @access public
 * @copyright www.shopex.cn 2010
 *
 */
class ecorder_rpc_request_weixin_menu extends ecorder_rpc_request_weixin_backend {

    /**
     *  创建菜单
     *  https://git.ishopex.cn/xushuai/crm-wechat-data/blob/master/docs/weixin_menu/create.md
     */
    function create($params)
    {
        $data['method'] = 'store.menu.create';
        $data['button'] = $params['button'];
        $data['to_node_id'] = $params['node_id'];
        return $this->wx_api_request($data);
    }

    //删除菜单
    function delete($params)
    {
        $data['method'] = 'store.menu.delete';
        $data['to_node_id'] = $params['node_id'];
        return $this->wx_api_request($data);
    }
    
}