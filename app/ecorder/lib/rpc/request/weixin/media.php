<?php
/**
 * 微信注册服务同步请求
 * @author ome
 * @access public
 * @copyright www.shopex.cn 2010
 *
 */
class ecorder_rpc_request_weixin_media extends ecorder_rpc_request_weixin_backend {

    //上传图片素材
    function upload($params)
    {
        $data['method'] = 'store.media.upload';
        $data['to_node_id'] = $params['node_id'];
        $data['type'] = $params['type'];
        $data['filename'] = $params['type'].'.jpg';
        $data['media'] = base64_encode(file_get_contents($params['media']));
        return $this->wx_api_request($data);
    }
    
    //上传图片，测试代码
    function uploadimg($params)
    {
        $data['method'] = 'store.media.uploadimg';
        $data['to_node_id'] = $params['node_id'];
        $data['buffer'] = base64_encode(file_get_contents($params['media']));
        return $this->wx_api_request($data);
    }

    //上传图文消息
    function uploadnews($params)
    {
        $data['method'] = 'store.media.uploadnews';
        $data['to_node_id'] = $params['node_id'];
        $data['articles'] = $params['articles'];
        return $this->wx_api_request($data);
    }
    
}