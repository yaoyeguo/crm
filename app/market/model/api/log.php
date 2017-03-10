<?php
class market_mdl_api_log extends dbeav_model{
    
    function gen_id(){
        return uniqid();
    }
    
    function _filter($filter,$tableAlias=NULL,$baseWhere=NULL){
        if (isset($filter['params'])){
            $wheresql = " AND `params` LIKE '%".$filter['params']."%'";
            unset($filter['params']);
        }
        return parent::_filter($filter,$tableAlias,$baseWhere).$wheresql;
    }
    
    /*
     * 写日志
     * @param int $log_id 日志id
     * @param string $task_name 操作名称
     * @param string $class 调用这次api请求方法的类
     * @param string $method 调用这次api请求方法的类函数
     * @param array $params 调用这次api请求方法的参数集合
     * @param string $msg 返回信息
     * @param string $addon[marking_value标识值，marking_type标识类型 ]
     * 
     */
    function write_log($log_id,$task_name,$class,$method,$params,$memo='',$api_type='request',$status='running',$msg='',$addon=''){
        $time = time();
        $log_sdf = array(
            'log_id' => $log_id,
            'task_name' => $task_name,
            'status' => $status,
            'worker' => $class.':'.$method,
            'params' => serialize($params),
            'msg' => $msg,
            'api_type' => $api_type,
            'memo' => $memo,
            'createtime' => $time,
            'last_modified' => $time,
        );
        if (is_array($addon)){
            $log_sdf['marking_value'] = $addon['marking_value'];
            $log_sdf['marking_type'] = $addon['marking_type'];
        }
        return $this->save($log_sdf);
    }
    
    function update_log($log_id,$msg=NULL,$status=NULL,$params=NULL,$addon=NULL){
        
        //同步日志状态非success才进行修改
        $api_detail = $this->dump(array('log_id'=>$log_id), 'status');
        if ($api_detail['status'] != 'success'){
            $log_sdf = array(
                'msg' => $msg,
                'status' => $status,
            );
            if(!empty($params)){
                $log_sdf['params'] = serialize($params);
            }
            //错误等级
            if (isset($addon['error_lv']) && !empty($addon['error_lv'])){
                $log_sdf['error_lv'] = $addon['error_lv'];
            }
            $filter = array('log_id'=>$log_id);
            return $this->update($log_sdf, $filter);
        }else{
            return true;
        }
    }
    
    /*
     * 同步重试
     * 有单个重试与批量重试
     * @param array or int $log_id
     * @param string $retry_type 默认为单个重试，btach:为批量重试
     * @param string $isSelectedAll 是否全选
     * @param string $cursor 当前游标，用于循环选中重试
     */
    function retry($log_id='', $retry_type='', $isSelectedAll='', $cursor='0'){

        if ($retry_type=='batch' and ( strstr($log_id,"|") or $isSelectedAll == '_ALL_' ) ){
            //批量重试
            $filter['status'] = 'fail';
            $filter['api_type'] = 'request';

            $limit = 1;
            if ($isSelectedAll != '_ALL_'){
                $log_ids = explode('|',$log_id);
                $filter['log_id'] = $log_ids[$cursor];
                $lim = 0;
            }else{
                $lim = $cursor * $limit;
            }
            $row = $this->getList('*', $filter, $lim, $limit, ' createtime asc ');
            if ($row){
                foreach ($row as $k=>$v){
                    return $this->start_api_retry($v);
                }
            }else{
                return array('task_name'=>'全部批量重试', 'status'=>'complete');
            }
        }else{
            //单个按钮重试
            $row = $this->db->selectrow("SELECT * FROM sdb_market_api_log WHERE log_id='".$log_id."' and status='fail' ");
            return $this->start_api_retry($row);
        }
    }
    
    /*
     * 发起API同步重试
     * @param array $row 发起重试数据
     */
    function start_api_retry($row){
         
        $worker = explode(":",$row['worker']);
        $class = $worker[0];
        $method = $worker[1];
        $params = unserialize($row['params']);
        $log_id = $row['log_id'];
        $queryparams = '';
        $status = 'fail';
        $msg = '手动重试';
        $return = $this->db->exec("UPDATE sdb_market_api_log SET retry=retry+1,last_modified='".time()."',status='sending',msg='".$msg."' WHERE log_id='".$log_id."'");
        if (isset($params[1]['all_list_quantity'])){
            unset($params[1]['all_list_quantity']);
        }
        if($params){
            $eval = "kernel::single('$class')->$method(";
            if(is_array($params)){
                $i = 0;
                foreach($params as $v){
                    $tmp_param[$i] = $v;
                    $tmp_param_string[] = "\$tmp_param[$i]";
                    $i++;
                }
                $eval .= implode(",",$tmp_param_string);
            }else{
                $eval .= $params;
            }
            $eval .= ");";
            eval($eval);
            if ($return) $status = 'succ';
        }
        return array('task_name'=>$row['task_name'], 'status'=>$status);
    }
    
    function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null){
        if(empty($orderType))$orderType = "createtime DESC";
        return parent::getList($cols,$filter,$offset,$limit,$orderType);
    }
}
?>