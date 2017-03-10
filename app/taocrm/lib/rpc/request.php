<?php
/**
 * RPC请求基类
 * 各个同步点先组织应用级参数，然后统一调用本类的公共方法向框架发起RPC
 * @author shopex.cn
 * @access public
 * @copyright www.shopex.cn 2010
 */
class taocrm_rpc_request {

    /**
     * RPC应用层发起（业务过滤）
     * 此方法控制发起前的过滤（禁止向未绑定的店铺发起），写入日志记录，还可以决定是否队列发起
     * @access public
     * @param string $method RPC远程服务接口名称
     * @param array $params 业务参数
     * @param array $callback 异步返回参数
     * @param string $title 发起的标题
     * @param string $shop_id 前端店铺
     * @param int $time_out 发起超时时间（秒）
     * @param boolean $queue 是否放入队列方式稍后发起，默认为false:非队列 true:队列
     * @param array $addon 附加参数
     * @return boolean
     */
    public function request($method,$params,$callback=array(),$title,$shop_id=NULL,$time_out=1,$queue=false,$addon=''){

        //过滤此次同步前端店铺
        if($node = $this->_check_node($shop_id, $method)){
            $params['to_node_id'] = $node[0]['node_id'];
            $params['node_type'] = $node[0]['node_type'];
        }else{
            return false;
        }
        
        //检查是否过滤指定回写操作
        if ($this->_check_request_config($shop_id, $method)) {
            
            return false;
        }
        
        //生成日志ID号
        $oApi_log = &app::get('taocrm')->model('api_log');
        $log_id = $oApi_log->gen_id();
        
        //设置callback异常返回参数为空时的默认值
        if($callback && $callback['class'] && $callback['method']){
            $rpc_callback = array($callback['class'],$callback['method'],array('log_id'=>$log_id));
        }else{
            $rpc_callback = array('taocrm_rpc_request','callback',array('log_id'=>$log_id));
        }

        if ($queue == true){
            //队列发起（此时不记录同步日志，队列后台执行时再记录）
            $param = array();
            $param['api_title'] = $title;
            $param['params'] = $params;
            $param['method'] = $method;
            $param['rpc_callback'] = $rpc_callback;
            $this->api_queue($method, $param, $addon);
        }else{
            //非队列发起（记录同步日志），并立即发起RPC
            if (!empty($addon) && is_array($addon)){
                $api_params = array_merge($params, $addon);
            }else{
                $api_params = $params;
            }
            $oApi_log->write_log($log_id,$title,'taocrm_rpc_request','rpc_request',array($method, $api_params, $rpc_callback));
            $this->rpc_request($method, $params, $rpc_callback, $time_out);
        }
    }
    
    /**
     * RPC开始请求
     * 业务层数据过滤后，开始向上级框架层发起
     * @access public
     * @param string $method RPC远程服务接口名称
     * @param array $params 业务参数
     * @param array $callback 异步返回
     * @param int $time_out 发起超时时间（秒）
     * @return RPC响应结果
     */
    public function rpc_request($method,$params,$callback,$time_out=5){

        if (isset($params['gzip'])){
            $gzip = $params['gzip'];
        }else{
            $gzip = false;
        }
        $callback_class = $callback[0];
        $callback_method = $callback[1];
        $callback_params = (isset($callback[2])&&$callback[2])?$callback[2]:array();
        if (isset($params[1]['task'])){
            $rpc_id = $params[1]['task'];
        }
        $rst = app::get('taocrm')->matrix()->set_callback($callback_class,$callback_method,$callback_params)
            ->set_timeout($time_out)
            ->call($method,$params,$rpc_id,$gzip);

    }

    /**
     * RPC异步返回数据接收
     * @access public
     * @param object $result 经由框架层处理后的同步结果数据
     * @return 返回业务处理结果
     */
    public function callback($result){

        if (is_object($result)){
            $callback_params = $result->get_callback_params();
            $status = $result->get_status();
            $msg = $result->get_result();
            $data = $result->get_data();
        }else{
            return true;
        }
        
        if($status == 'succ'){
            $api_status = 'success';
        }else{
            $api_status = 'fail';
        }
        $rsp  ='succ';
        if ($status != 'succ' && $status != 'fail' ){
            $msg = 'rsp:'.$status .'res:'. $msg. 'data:'. $data;
            $rsp = 'fail';
        }
        //错误等级
        if (isset($data['error_level']) && !empty($data['error_level'])){
            $addon['error_lv'] = $data['error_level'];
        }
        $log_id = $callback_params['log_id'];
        $oApi_log = &app::get('taocrm')->model('api_log');
        $oApi_log->update_log($log_id, $msg, $api_status, null, $addon);
        $log_detail = $oApi_log->dump($log_id, 'msg_id');
        return array('rsp'=>$rsp, 'res'=>$msg, 'msg_id'=>$log_detail['msg_id']);
    }
    
    /**
     * RPC同步返回数据接收
     * @access public
     * @param json array $res RPC响应结果
     * @param array $params 同步日志ID
     */
    public function response_log($res, $params){

        $response = json_decode($res, true);
        if (!is_array($response)){
            $response = array(
                'rsp' => 'running',
                'res' => $res,
            );
        }
        $status = $response['rsp'];
        $result = $response['res'];

        if($status == 'running'){
            $api_status = 'running';
        }elseif ($result == 'rx002'){
            //将解除绑定的重试设置为成功
            $api_status = 'success';
        }else{
            $api_status = 'fail';
        }
        
        $log_id = $params['log_id'];
        $oApi_log = &app::get('taocrm')->model('api_log');

        //更新日志数据
        $oApi_log->update_log($log_id, $result, $api_status);
        
        if ($response['msg_id']){
            //更新日志msg_id及在应用级参数中记录task
            $log_info = $oApi_log->dump($log_id);
            $log_params = unserialize($log_info['params']);
            $rpc_key = $params['rpc_key'];
            $log_params[1]['task'] = $rpc_key;
            $update_data = array(
                'msg_id' => $response['msg_id'],
                'params' => serialize($log_params),
            );
            $update_filter = array('log_id'=>$log_id);
            $oApi_log->update($update_data, $update_filter);
        }
    }
    
    
    /**
     * 店铺绑定关系过滤
     * 检查店铺（shop_id为空时标识所有店铺）是否可访问远端API接口服务，并返回可用的node_id
     * @access private
     * @param string $shop_id 店铺标识ID
     * @param string $method RPC远程调用接口名称
     * @return boolean
     */
    private function _check_node($shop_id,$method){
        
        $node = $this->_get_node($shop_id);

        if($node){
            $request_whitelist = kernel::single('taocrm_rpc_request_whitelist');
            $t_node = $node;
            foreach($t_node as $k=>$v){
                $res = $request_whitelist->check_node($v['node_type'],$method);
                if(!$res){
                    unset($node[$k]);
                }
            } 
            if($node){
                return $node; 
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
    
    /**
     * 检查指定店铺及回调方法是否被禁止
     * 
     * @param String $shop_id 店铺ID
     * @param $method 调用方法
     * @return boolean true 禁止 false 允许
     */
    private function _check_request_config($shop_id, $method) {
        
        $method = strtolower($method);
        if ($method == 'store.items.quantity.list.update') {
            
            $request_auto_stock = app::get('taocrm')->getConf('request_auto_stock_' . $shop_id);

            //如无设置,缺省置为 true
            if (empty($request_auto_stock)) {
                $request_auto_stock = 'true';
                app::get('taocrm')->setConf('request_auto_stock_' . $shop_id, 'true');
            }
            
            //如已经关闭了库存回写功能，返回 true
            if ($request_auto_stock == 'false') {
                
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * 通过shop_id获取结点信息
     * @access private
     * @param $shop_id
     * @return array 店铺绑定的节点数据
     */
    private function _get_node($shop_id){
        
        $shopObj = &app::get(ORDER_APP)->model('shop');
        $node = array();
        if(empty($shop_id)){
            
            $shop_info = $shopObj->getList('node_id,node_type', '', 0, -1);
            if($shop_info){
                foreach($shop_info as $v){
                    if ($v['node_id']){
                        $node[] = array(
                            'node_id' => $v['node_id'],
                            'node_type' => $v['node_type'],
                        );
                    }
                }
            }
        }else{

            $shop_info = $shopObj->dump($shop_id,'node_id,node_type');
            if ($shop_info['node_id']){
                $node[] = array(
                    'node_id' => $shop_info['node_id'],
                    'node_type' => $shop_info['node_type']
                );
            }
        }
        
        return $node;
    }
    
    /**
     * RPC同步日志队列
     * @access public
     * @param string $queue_title 队列标题
     * @param array $queue_params 队列参数
     * @param array $addon 附加参数
     * 
     */
    public function api_queue($queue_title,$queue_params,$addon=''){
        
        $oQueue = &app::get('base')->model('queue');
        $queueData = array(
                'queue_title'=>$queue_title,
                'start_time'=>time(),
                'params'=>array(
                    'sdfdata'=>$queue_params,
                    'addon' => $addon,
                ),
                'status' => 'hibernate',
                'worker'=> __CLASS__.'.run',
       );
       $oQueue->save($queueData);
    }
    
    /**
     * 执行API同步日志队列
     * @param $cursor_id
     * @param $params
     */
    function run(&$cursor_id,$params){

        $oApi_log = &app::get('taocrm')->model('api_log');
        
        if (!is_array($params)){
            $params = unserialize($params);
        }        
        $Sdf = $params['sdfdata'];
        $addon = $params['addon'];
        $title = $Sdf['api_title'];
        $method = $Sdf['method'];
        $params = $Sdf['params'];
        $rpc_callback = $Sdf['rpc_callback'];
        //附加参数
        if (!empty($addon) && is_array($addon)){
            $api_params = array_merge($params, $addon);
        }else{
            $api_params = $params;
        }
        $log_id = $rpc_callback[2]['log_id'];
        $oApi_log->write_log($log_id,$title,'taocrm_rpc_request','rpc_request',array($method,$api_params,$rpc_callback));
        kernel::single('taocrm_rpc_request')->rpc_request($method,$params,$rpc_callback);

    }
    
    
    /**
     * 
     * @param $url
     * @param $params
     * @param $time_out
     * 
     * @return String
     */
    function direct_request($url,$params,$time_out=5){
        $headers = array(
            'Connection'=>$time_out,
        );
        $core_http = kernel::single('base_httpclient');
        $res = $core_http->post($url,$params,$headers);
        $res = 'direct_request:' . $res;
        kernel::log($res);
        $res2 = 'direct_request content:' . $url."\n".json_encode($params);
        kernel::log($res2);
        
        return $res;
    }
    
   

   /**
    * 返回验证字符串
    * 
    * @param $params
    * 
    * @return String
    */
   function make_sign($post_params){
       ksort($post_params);
       $str = '';
       foreach($post_params as $key=>$value){
            $str.=$value;
       }
      
       return md5($str.base_certificate::get('token'));
   }
   
   function make_sign_matrix($params){
       ksort($params);
       $query = '';
       foreach($params as $k=>$v){
           $query .= $k.'='.$v.'&';
       }
     
       return md5(substr($query,0,strlen($query)-1).base_certificate::get('token'));
   }
}