<?php
/**
 * RPC请求基类
 * 各个同步点先组织应用级参数，然后统一调用本类的公共方法向框架发起RPC
 * @author shopex.cn
 * @access public
 * @copyright www.shopex.cn 2010
 */
class ecorder_rpc_request {

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
     * @return boolean
     */
    public function request($method,$params,$callback=array(),$title,$shop_id=NULL,$time_out=1,$queue=false){

        //过滤此次同步前端店铺
        if($node = $this->_check_node($shop_id, $method)){
            $params['to_node_id'] = $node[0]['node_id'];
            $params['node_type'] = $node[0]['node_type'];
        }else{
            return false;
        }
        
        //生成日志ID号
        $oApi_log = &app::get('ome')->model('api_log');
        $log_id = $oApi_log->gen_id();
       
        //设置callback异常返回参数为空时的默认值
        if($callback && $callback['class'] && $callback['method']){
            $rpc_callback = array($callback['class'],$callback['method'],array('log_id'=>$log_id));
        }else{
            $rpc_callback = array('ome_rpc_request','callback',array('log_id'=>$log_id));
        }

        if ($queue == true){
            //队列发起（此时不记录同步日志，队列后台执行时再记录）
            $param = array();
            $param['api_title'] = $title;
            $param['params'] = $params;
            $param['method'] = $method;
            $param['rpc_callback'] = $rpc_callback;
            $this->api_queue($method, $param);
        }else{
            //非队列发起（记录同步日志），并立即发起RPC
            $oApi_log->write_log($log_id,$title,'ome_rpc_request','rpc_request',array($method, $params, $rpc_callback));
            $this->rpc_request($method, $params, $rpc_callback, $time_out);
        }

        //新增修改
        return $log_id;
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

        $callback_class = $callback[0];
        $callback_method = $callback[1];
        $callback_params = (isset($callback[2])&&$callback[2])?$callback[2]:array();
        $rst = app::get('ome')->matrix()->set_callback($callback_class,$callback_method,$callback_params)
            ->set_timeout($time_out)
            ->call($method,$params);

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
            $result = $result->get_result();
        }else{
            return true;
        }
        
        if($status == 'succ'){
            $api_status = 'success';
        }else{
            $api_status = 'fail';
        }
        if ($api_status == 'fail' && !$result){
            $result = ome_api_func::license_error_code('re001', true);
        }
        $rsp  ='succ';
        if ($status != 'succ' && $status != 'fail' ){
            $result = $status . ome_api_func::license_error_code('re001', true);
            $rsp = 'fail';
        }
        
        $log_id = $callback_params['log_id'];
        $oApi_log = &app::get('ome')->model('api_log');
        $oApi_log->update_log($log_id, $result, $api_status);
        
        $log_detail = $oApi_log->dump($log_id, 'msg_id');

        //增加返回 log_id
        return array('rsp'=>$rsp,'res'=>$result,'msg_id'=>$log_detail['msg_id'], 'log_id' => $log_id);
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
                'rsp' => 'fail',
                'res' => $res,
            );
        }
        $status = $response['rsp'];
        $result = $response['res'];

        if($status == 'running'){
            $api_status = 'running';
        }elseif ($result == 'rx002'){
            //将解除绑定的重试设置为成功
            $api_status = 'succ';
        }else{
            $api_status = 'fail';
        }
        
        $log_id = $params['log_id'];
        $oApi_log = &app::get('ome')->model('api_log');

        //更新日志数据
        $oApi_log->update_log($log_id, $result, $api_status);
        
        if ($response['msg_id']){
            //更新日志msg_id
            $update_data = array('msg_id'=>$response['msg_id']);
            $update_filter = array('log_id'=>$params['log_id']);
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
            $request_whitelist = kernel::single('ome_rpc_request_whitelist');
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
     * 通过shop_id获取结点信息
     * @access private
     * @param $shop_id
     * @return array 店铺绑定的节点数据
     */
    private function _get_node($shop_id){
        
        $shopObj = &app::get('ome')->model('shop');
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
     * 
     */
    public function api_queue($queue_title,$queue_params){
        
        $oQueue = &app::get('base')->model('queue');
        $queueData = array(
                'queue_title'=>$queue_title,
                'start_time'=>time(),
                'params'=>array(
                    'sdfdata'=>$queue_params,
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

        $oApi_log = &app::get('ome')->model('api_log');
        
        $Sdf = $params['sdfdata'];
        $title = $Sdf['api_title'];
        $method = $Sdf['method'];
        $params = $Sdf['params'];
        $rpc_callback = $Sdf['rpc_callback'];
        
        $log_id = $rpc_callback[2]['log_id'];
        $oApi_log->write_log($log_id,$title,'ome_rpc_request','rpc_request',array($method,$params,$rpc_callback));
        kernel::single('ome_rpc_request')->rpc_request($method,$params,$rpc_callback);

    }
}