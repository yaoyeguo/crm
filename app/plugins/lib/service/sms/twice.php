<?php
/**
 * 二次催付插件
 * 注意：本插件不直接运行，由第一次催付插件调用
 */
class plugins_service_sms_twice{

    var $sms_page_size = 200; //每次提交的短信数

    // 运行插件
    public function run_hour($arr_param)
    {
        if(intval(date('H')) <8 or intval(date('H') >20)){
            return false;
        }
        
        $db = kernel::database();
        $oServiceApi = kernel::single('plugins_service_api');
        
        //插件设置
        $params = json_decode($arr_param['params'],1);
        $oLog = app::get('plugins')->model('log');
        $start_time = strtotime(date('Y-m-d'));
        
        $send_time = intval($params['send_time2']);
        if($send_time>0){
            $now_date = strtotime('-'.($send_time+1).' hours', strtotime(date('Y-m-d H:00:00')) );
            $end_date = $now_date + 3600 - 1;
        }else{
            return false;
        }
        
        $shops = $this->_get_shop_list();
        
        //防止重复执行
        $sql = "select * from sdb_plugins_log where plugin_id=".$arr_param['plugin_id']." and run_key='$now_date' and start_time>=".strtotime(date('Y-m-d'))." ";
        $rs = $db->select($sql);
        if($rs) return false;
        
        //获取短信模板内容
        $params['send_content2'] = trim($params['send_content2']);
        
        //需要发送短信的用户:未付款，订单状态为有效
        $mobiles = array();
        $sms = array();
        $rs = array();
        foreach($params['shop_id'] as $v){
            $get_order_params = array(
                'start_created' => date('Y-m-d H:i:s', $now_date),
                'end_created' => date('Y-m-d H:i:s', $end_date),
                'shop_info' => $shops[$v],
                'type' => 'taobao',
            );
            $orders = kernel::single('ecorder_service_orders')->get_orders_by_params($get_order_params);
            if($orders){
                if($rs){
                    $rs = array_merge($rs, $orders);
                }else{
                    $rs = $orders;
                }
            }
        }
        
        if($rs){
            foreach($rs as $v){
                $mobiles[] = $v['receiver_mobile'];
            }
        }

        if($mobiles){
            //短信号码过滤：当天发送过的买家不发
            $today_sent_mobiles = array();
            $sql = "select mobile from sdb_plugins_sms_logs where create_time>=$start_time and worker=".$arr_param['plugin_id']." and mobile in ('".implode("','",$mobiles)."') ";
            $rs_temp = $db->select($sql);
            if($rs_temp){
                foreach($rs_temp as $v){
                    $today_sent_mobiles[$v['mobile']] = 1;
                }
            }
            
            //短信号码过滤：当天有付款订单的买家不发
            $today_paid_mobiles = array();
            $sql = "select ship_mobile from sdb_ecorder_orders where createtime>=$start_time and ship_mobile in ('".implode("','",$mobiles)."') and (pay_status in ('1','2','3','4') or ship_status='1') ";
            $rs_temp = $db->select($sql);
            if($rs_temp){
                foreach($rs_temp as $v){
                    $today_paid_mobiles[$v['ship_mobile']] = 1;
                }
            }
        }

        $mobiles = array();
        foreach($rs as $v){
        
            if(isset($today_sent_mobiles[$v['receiver_mobile']])){
                continue;
            }
            
            if(isset($today_paid_mobiles[$v['receiver_mobile']])){
                continue;
            }
        
            //是否满足最低催付金额
            if(floatval($v['payment']) < floatval($params['min_amount'])){
                continue;
            }
        
            //检测手机号码格式
            if($oServiceApi->mobile_validate($v['receiver_mobile']) == 0){
                continue;
            }
        
            //检测短信黑名单
            if($this->chk_sms_blacklist($v['receiver_mobile'], $oServiceApi) == true){
                continue;
            }
        
            if(
                !in_array($v['receiver_mobile'],$mobiles) 
                && $v['receiver_mobile']!='' 
                && in_array($v['shop_id'],$params['shop_id'])
            ){            
                //跳过没有签名的店铺
                if(!$shops[$v['shop_id']]) continue;
            
                $mobiles[] = $v['receiver_mobile'];
                $sms_content = $params['send_content2'];
                
                //检测是否存在短信签名
                $sms_content = $oServiceApi->sms_validate($sms_content);
                $sms_content = str_replace('<{昵称}>',$v['buyer_nick'],$sms_content);
                $sms_content = str_replace('<{姓名}>',$v['receiver_name'],$sms_content);
                $sms_content = str_replace('<{用户名}>',$v['receiver_name'],$sms_content);
                $sms_content = str_replace('<{店铺}>',$shops[$v['shop_id']]['name'],$sms_content);
                $sms_content = str_replace('<{签名}>',$shops[$v['shop_id']]['sms_sign'],$sms_content);
                $sms[] = array(
                    'phones'=>$v['receiver_mobile'],
                    'content'=>$sms_content
                );
                
                $log_arr = array(
                    'tid' => $v['tid'],
                    'shop_name' => $shops[$v['shop_id']]['name'],
                    'shop_id' => $v['shop_id'],
                    'plugin_name' => $arr_param['plugin_name'],
                    'worker' => $arr_param['plugin_id'],
                    'sms_content' => $sms_content,
                    'mobile' => $v['receiver_mobile'],
                    'status' => '',
                );
                $oServiceApi->save_sms_log($log_arr);
            }
        }
        unset($rs);        
        
        if($sms){
            // 分批次发送短信
            for($i=0;$i<=(sizeof($sms)/$this->sms_page_size);$i++){
                $content = array_slice($sms,($i*$this->sms_page_size),$this->sms_page_size);
                if(!$content) break;
                $content = json_encode($content);
                
                $send_res = $oServiceApi->_send_sms($content,'cf'.$now_date.'b'.$i, '二次催付');
                if($send_res['res'] != 'succ') break;
            }
            
            // 保存插件运行日志
            $arr['plugin_id'] = $arr_param['plugin_id'];
            $arr['plugin_name'] = $arr_param['plugin_name'];
            $arr['worker'] = $arr_param['worker'];
            $arr['run_key'] = $now_date;//唯一识别码，防止重复运行
            $arr['start_time'] = time();
            $arr['desc'] = '发送短信数：'.count($mobiles);
            $arr['status'] = '成功';
            $arr['sms_count'] = count($mobiles);
            if($send_res['res'] != 'succ') {
                $arr['status'] = '失败';
                $arr['desc'] .= '，失败：'.var_export($send_res, true);
            }
            $oLog->save($arr);
        }
        
        // 更新插件最后运行时间
        $sql = "update sdb_plugins_plugins set last_run_time=".time()." where plugin_id=".$arr_param['plugin_id']." ";
        $db->exec($sql);
        
        return true;
    }
    
    // 店铺信息
    private function _get_shop_list()
    {
        return kernel::single('plugins_service_api')->_get_shop_list();
    }
    
    //检查短信黑名单
    private function chk_sms_blacklist($mobile, $oServiceApi)
    {
        if(!isset($this->sms_blacklist)){
            $this->sms_blacklist = $oServiceApi->get_sms_blacklist();
        }
        
        if($this->sms_blacklist && in_array($mobile, $this->sms_blacklist)){
            return true;
        }else{
            return false;
        }
    }
}
