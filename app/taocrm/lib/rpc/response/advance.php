<?php

//对接ecstore预存款
class taocrm_rpc_response_advance extends taocrm_rpc_response
{
    //余额变动
    public function add($sdf, &$rpc)
    {
        $sdf['explode_money'] = -1 * floatval($sdf['explode_money']);
        $sdf['import_money'] = floatval($sdf['import_money']);
        $sdf['member_id'] = floatval($sdf['member_id']);
        $sdf['remark'] = mb_substr(trim($sdf['message']),0,50,'utf-8');
        $sdf['trade_no'] = trim($sdf['order_id']);
        $sdf['payment_no'] = trim($sdf['payment_id']);
        $sdf['sn'] = trim($sdf['sn']);
        $sdf['sn'] = 'EC'.date('ymdHis').rand(111,999);
        
        //转换预存款
        $sdf['stored_value'] = $sdf['explode_money'] + $sdf['import_money'];
        
        //调用java预存款接口
        $res = kernel::single('taocrm_members')->update_member_stored_value($sdf, $err_msg);
        if(!$res && $err_msg){
            $rpc->send_user_error('0', $err_msg);
        }elseif($res['errmsg']){
            $rpc->send_user_error($res['errcode'], $res['errmsg']);
        }else{
            return $res['taocrm.stored.update'];
        }
    }
    
    //获取余额
    public function get_balance($sdf, &$rpc)
    {
        $sdf['member_id'] = floatval($sdf['member_id']);
        $res = kernel::single('taocrm_members')->get_member_stored_value($sdf, $err_msg);
        if(!$res && $err_msg){
            $rpc->send_user_error('0', $err_msg);
        }elseif($res['errmsg']){
            $rpc->send_user_error($res['errcode'], $res['errmsg']);
        }else{
            return $res['taocrm.stored.get'];
        }
    }
    
    //获取储值流水
    /*
        'change_amount' => 111,
        'before_change_amount' => 110,
        'op_user' => '',
        'value_time' => 1444981145,
        'remark' => '0',
        'uname' => 'faa',
        'after_change_amount' => 221,
        'log_id' => 48,
        'mobile' => '188178002003',
        'member_id' => 2187053,
    */
    public function get_log($sdf, &$rpc)
    {
        $sdf['member_id'] = floatval($sdf['member_id']);
        $sdf['page'] = floatval($sdf['page']);
        $sdf['page_size'] = floatval($sdf['page_size']);
        
        $res = kernel::single('taocrm_members')->get_member_stored_value_log($sdf, $err_msg);
        if(!$res && $err_msg){
            $rpc->send_user_error('0', $err_msg);
        }elseif($res['errmsg']){
            $rpc->send_user_error($res['errcode'], $res['errmsg']);
        }else{
            $advance_log = array();
            $total_result = $res['taocrm.storedlog.get']['total_result'];
            foreach($res['taocrm.storedlog.get']['logList'] as $v){
                $advance_log[] = array(
                    'explode_money' => ($v['change_amount']<0 ? abs($v['change_amount']) : 0),
                    'import_money' => ($v['change_amount']>=0 ? $v['change_amount'] : 0),
                    'money' => abs($v['change_amount']),
                    'member_advance' => $v['after_change_amount'],
                    'member_id' => $v['member_id'],
                    'message' => $v['remark'],
                    'order_id' => $v['trade_no'],
                    'payment_id' => $v['payment_no'],
                    'mtime' => $v['value_time'],
                );
            }
            return array('advance_log'=>$advance_log, 'total_result'=>$total_result);
        }
    }
    
    //会员储值账户余额初始化
    public function init($sdf, &$rpc)
    {
        $sdf['member_id'] = floatval($sdf['member_id']);
        $advance_log = json_decode($sdf['advance_log'], true);
        if( ! $advance_log){
            $rpc->send_user_error('储值记录格式错误');
        }
        
        $err_msg_arr = array();
        foreach($advance_log as $v){
            $sdf['explode_money'] = -1 * floatval($v['explode_money']);
            $sdf['import_money'] = floatval($v['import_money']);
            $sdf['remark'] = mb_substr(trim($v['message']),0,50,'utf-8');
            $sdf['trade_no'] = trim($v['order_id']);
            $sdf['payment_no'] = trim($v['payment_id']);
            
            //转换预存款
            $sdf['stored_value'] = $sdf['explode_money'] + $sdf['import_money'];
            
            //调用java预存款接口
            $res = kernel::single('taocrm_members')->update_member_stored_value($sdf, $err_msg);
            if(!$res && $err_msg){
                $err_msg_arr[] = $sdf['member_id'].'更新失败：'.$err_msg;
            }else{
                
            }
        }
        
        if($err_msg){
            $rpc->send_user_error('0', $err_msg);
        }else{
            return $res;
        }
    }
    
    //查询shop_id
    private function set_shop_id(&$sdf)
    {
        $shopObj = app::get('ecorder')->model('shop');
        $rs_shop = $shopObj->dump(array('node_id'=>base_rpc_service::$node_id),'shop_id,node_type');
        $sdf['shop_id'] = $rs_shop['shop_id'];
    }
     
}