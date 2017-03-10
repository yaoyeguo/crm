<?php
class ecorder_mdl_api_log extends dbeav_model{
    
    function gen_id(){
        return uniqid();
    }
    
    //物理删除指定天数前的日志，默认保留最近7天
    function clear_old_logs($days=7)
    {
        $days = intval($days);
        if(!$days) $days = 7;
        $begin_time = strtotime("-$days days");
        $sql = "delete from sdb_ecorder_api_log where createtime<$begin_time ";
        $this->db->exec($sql);
        
        //清空表
        $sql = "truncate sdb_ecorder_api_log ";
        //$this->db->exec($sql);
        
        //创建索引
        $sql = "ALTER TABLE sdb_ecorder_api_log ADD INDEX ind_createtime (createtime) ";
        //$this->db->exec($sql);
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
    function write_log($log_id,$task_name,$class,$method,$params,$memo='',$api_type='request',$status='running',$msg='',$addon='')
    {
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
        
        if(is_array($addon) && $addon){
            $log_sdf = array_merge($log_sdf, $addon);
        }
        
        return $this->save($log_sdf);
    }
    
    function update_log($log_id,$msg='',$status='success',$params=NULL){
                
        $msg_data = json_decode($msg,true);
        $log_sdf = array(
            'msg' => $msg,
            'status' => $status,
        );
        if(!is_null($params)){
            $log_sdf['params'] = serialize($params);
        }
        $filter = array('log_id'=>$log_id);
        $this->update($log_sdf, $filter);
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

            if ($isSelectedAll != '_ALL_'){
                $filter['log_id'] = explode('|',$log_id);
            }

            $row = $this->getList('*', $filter, 0, 1, ' createtime asc ');
            if ($row){
                foreach ($row as $k=>$v){
                    return $this->start_api_retry($v);
                    break;
                }
            }else{
                return array('task_name'=>'全部批量重试', 'status'=>'complete');
            }
        }else{
            //单个按钮重试
            $row = $this->db->selectrow("SELECT * FROM sdb_ome_api_log WHERE log_id='".$log_id."' and status='fail' ");
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
        $return = $this->db->exec("UPDATE sdb_ome_api_log SET retry=retry+1,last_modified='".time()."',status='sending',msg='".$msg."' WHERE log_id='".$log_id."'");
        
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

