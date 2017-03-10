<?php

class plugins_service_api{

    var $plugins =  array('reminder','care','ship','step','check','sender','city','sign','vcard','sms');//全部插件
    
    //每小时运行一次
    public function run_hour()
    {
        $db = kernel::database();
        
        //重置插件购买状态
        kernel::single('taocrm_service_redis')->redis->srem('PLUGINS:DOMAINS',$_SERVER['SERVER_NAME']);
        kernel::single('taocrm_service_redis')->redis->del($_SERVER['SERVER_NAME'].':PLUGINS');
    
        //筛选当前未过期的插件
        $now_time = time();
        $sql = 'select * from sdb_plugins_plugins where end_time>'.$now_time.' ';
        $rs = $db->select($sql);
        if($rs){
            foreach($rs as $v){
                $plugin_list[] = $v['plugin_name'];
                
                $params = json_decode($v['params'],1);
                if($v['worker']=='plugins_service_birthday'){
                    if($params['use_shop']=='1' && ! $params['shop_id']){
                        continue;
                    }
                }elseif( ! $params['shop_id'] ){
                    continue;
                }
                
                kernel::single('taocrm_service_redis')->redis->sadd('PLUGINS:DOMAINS',$_SERVER['SERVER_NAME']);
                kernel::single('taocrm_service_redis')->redis->sadd($_SERVER['SERVER_NAME'].':PLUGINS',$v['plugin_name']);
                
                if(class_exists($v['worker'])){
                    $worker = kernel::single($v['worker']);
                    if(method_exists($worker, 'run_hour')){
                        $worker->run_hour($v);
                    }
                }
            }
            
        }
    }
    
    //执行任务
    public function run_task()
    {
        $db = kernel::database();

        //筛选当前未过期的插件
        $workers_arr = array();
        $now_time = time();
        $sql = 'select * from sdb_plugins_plugins where end_time>'.$now_time.' ';
        $rs = $db->select($sql);
        if($rs){
            foreach($rs as $v){
                $plugin_list[] = $v['plugin_name'];
                
                $params = json_decode($v['params'],1);
                if(!$params['shop_id']){
                    //echo('No shop selected.');
                    continue;
                }

                if(class_exists($v['worker'])){
                    $workers_arr[] = $v['worker'];
                
                    $worker = kernel::single($v['worker']);
                    if(method_exists($worker, 'run_task')){
                        $worker->run_task($v);
                    }
                }
            }
        }
        
        $this->trace_logistics($workers_arr);
    }
    
    //保存订单信息
    public function save_trades(&$order_sdf)
    {
        //检测当前用户是否订购插件
        $db = kernel::database();
    
        //筛选当前未过期的插件
        $now_time = time();
        $sql = 'select * from sdb_plugins_plugins where end_time>'.$now_time.' ';
        $rs = $db->select($sql);
        if($rs){
            foreach($rs as $v){
                $plugin_list[] = $v['plugin_name'];
                
                $params = json_decode($v['params'],1);
                if(!$params['shop_id']){
                    continue;
                }
                
                if(class_exists($v['worker'])){
                    $worker = kernel::single($v['worker']);
                    if(method_exists($worker, 'save_trade_info')){
                        $worker->save_trade_info($order_sdf, $v);
                    }
                }
            }
        }
    }
    
    //记录短信扣费日志
    public function add_sms_pay_log(&$arr)
    {
        $arr['create_time'] = time();
        $oSmsRecord = app::get('market')->model('sms_op_record');
        $oSmsRecord->insert($arr);
    }
    
    //记录短信发送日志
    public function save_sms_log(&$arr)
    {
        $arr['create_time'] = time();
        $arr['create_date'] = date('Y-m-d H:i:s');
        $oSmsLogs = app::get('plugins')->model('sms_logs');
        $oSmsLogs->insert($arr);
    }

    // 发送短信
    public function _send_sms(&$content,$batch_no='',$plugin_name='')
    {
        //短信帐号
        base_kvstore::instance('market')->fetch('account', $account);
        $account = unserialize($account);
        if(!isset($account['entid'])) return false;
    
        $smsAPI = kernel::single('market_service_smsinterface');
        
        //实时发送接口
        $res = $smsAPI->send($content, 'notice');
        $this->global_save_sms_log($content,$batch_no,$plugin_name,$res);
        return $res;
        
        if($res['res'] != 'succ'){
            return false;
        }else{
            return true;
        }
    }
    
    //保存全局短信日志
    function global_save_sms_log($content,$batch_no='',$plugin_name='',$res='')
    {
        $sms_list = json_decode($content, true);
        if(!$this->oLog)
            $this->oLog = app::get('taocrm')->model('sms_log');
    
        if($res['res'] != 'succ'){
            $status = 'fail';
            $remark = json_encode($res);
        }else{
            $status = 'succ';
            $remark = '';
        }
    
        foreach($sms_list as $v){
            $log = array(
                'source'=>'plugins_plugins',
                'source_id'=>0,
                'batch_no'=>$batch_no,
                'mobile'=>$v['phones'],
                'content'=>$v['content'],
                'status'=>$status,
                'send_time'=>time(),
                'create_time'=>time(),
                'sms_size'=>ceil(mb_strlen($v['content'],'utf-8')/67),
                'cyear'=>date('Y'),
                'cmonth'=>date('m'),
                'cday'=>date('d'),
                'op_user'=>$plugin_name,
                'ip'=>'',
                'remark'=>$remark,
            );
            $this->oLog->insert($log);
        }
    }
    
    public function get_sms_blacklist()
    {
        $sms_blacklist = array();
        $db = kernel::database();
        $sql = 'select mobile from sdb_taocrm_members where sms_blacklist="true" ';
        $rs = $db->select($sql);
        if($rs){
            foreach($rs as $v){
                $sms_blacklist[] = $v['mobile'];
            }
        }
        return $sms_blacklist;
    }
    
    public function _get_shop_list($shop_type='taobao')
    {
        if($shop_type != 'all'){
            $shop_type = ' and shop_type="'.$shop_type.'" ';
        }else{
            $shop_type = '';
        }
    
        $db = kernel::database();
        $sql = 'select shop_id,name,config,addon from sdb_ecorder_shop
            where active="true" '.$shop_type.'  and disabled="false"
        ';
        $rs = $db->select($sql);
        if(!$rs) return false;
        foreach($rs as $v){
            //处理店铺短信签名
            $addon = unserialize($v['addon']);
            $config = unserialize($v['config']);
            $v['session'] = $addon['session'];
            $v['sms_sign'] = $config['sms_sign'];
            $v['review'] = $config['review'];
            $v['extend_no'] = $config['extend_no'];
            if(!$v['sms_sign']) $v['sms_sign'] = $v['name'];
            
            //如果签名未审核，跳过该店铺
            if(!$v['extend_no']){
                continue;
            }
        
            $shops[$v['shop_id']] = $v;
        }
        return $shops;
    }
    
    public function mobile_validate($mobile)
    {
        return preg_match('/^1\d{10}$/', $mobile);
    }
    
    //检测短信内容是否符合要求
    public function sms_validate($sms_content)
    {
        $unsubscribe_str = '退订回N';
    
        if(preg_match('/【.+】$/', $sms_content) == 0){
            $sms_content = str_replace('【', '', $sms_content);
            $sms_content = str_replace('】', '', $sms_content);
            $sms_content .= '【<{签名}>】';
        }

        $arr = explode('【', $sms_content);
        $pos = count($arr) - 2;
        if(mb_substr($arr[$pos],-4,4,'utf-8') != $unsubscribe_str){
            $arr[$pos] = str_replace($unsubscribe_str, '', $arr[$pos]);
            $arr[$pos] = trim($arr[$pos]);
            $arr[$pos] .= ' '.$unsubscribe_str;
        }
        $sms_content = implode('【', $arr);
 
        return $sms_content;
    }
    
    //物流流转信息
    public function trace_logistics($plugin_ids)
    {
        if(!$plugin_ids) return false;
        
        //仅在开启签收或送达城市的时候执行
        $logi_status = '';
        if(in_array('plugins_service_city',$plugin_ids)){
            $logi_status = '"0"';
        }
        if(in_array('plugins_service_sign',$plugin_ids)){
            $logi_status = '"0","1"';
        }
        if($logi_status == '') return false;
    
        $min_delivery_time = time() - 86400*6;//发货后六天内的订单
        $max_update_time = time() - 60*30;//30分钟内不重复更新
        $db = kernel::database();
        $sql = 'select id,tid,seller_nick,city,sms_status,logi_status,sign_time,logi_company from sdb_plugins_trades where delivery_time>'.$min_delivery_time.' and logi_status in ('.$logi_status.') and update_time<'.$max_update_time.' and ship_status=1 and order_status="active" order by id desc limit 500';
        $rs = $db->select($sql);
        if(!$rs) return false;
        
        $model_trades = app::get('plugins')->model('trades');
        $orderObj = new ectools_api_taobao_order();
        foreach($rs as $v){
            if($v['logi_company']=='其他'){
                $model_trades->update(
                    array(
                        'logi_status'=>'9',
                        'transit_step_info'=>'其他物流公司'
                    ),
                    array(
                        'id'=>$v['id']
                    )
                );
                continue;
            }
        
            //淘宝接口 taobao.logistics.trace.search 物流流转信息查询 
            //（备注：使用线下发货（offline.send）的运单，不支持运单状态的实时跟踪，
            //只要一发货，状态就会变为对方已签收，该字段仅对线上发货（online.send）
            //的运单有效。） 
            $transit_step_info = $orderObj->getLogisticsTrace($v['seller_nick'], $v['tid'] ,$v['shop_id']);
            if( ! $transit_step_info) {
                //屏蔽出错的订单
                continue;
            }
            
            $city = $this->format_city($v['city']);

            $sign_time = $v['sign_time'];
            $logi_status = $v['logi_status'];
            $sms_status = $v['sms_status'];
            foreach($transit_step_info as $vv){
                $status_desc = $vv['status_desc'];
                if(!$status_desc) continue;
                if(
                    (strstr($status_desc,'到达目的地')) or 
                    
                    (strstr($status_desc,'正在派件')) or 
                    
                    (strstr($status_desc,'派件扫描')) or 
                    
                    (strstr($status_desc,'派送中')) or 
                    
                    (strstr($status_desc,'派件中')) or 
                    
                    (strstr($status_desc,'已收入') && strstr($status_desc,$city) && $v['logi_company']=='圆通速递') or 
                    
                    (strstr($status_desc,'快件到达') && strstr($status_desc,$city) && $v['logi_company']=='中通快递') or 
                    
                    (strstr($status_desc,'到达[') && strstr($status_desc,$city) && ( strpos($status_desc,'到达[') < strpos($status_desc,$city)) ) or 
                    
                    (strstr($status_desc,'从站点出发') && strstr($status_desc,$city) && ( strpos($status_desc,'从站点出发') > strpos($status_desc,$city)) ) or 
                    
                    (strstr($status_desc,'到达处理中心') && strstr($status_desc,$city) && ( strpos($status_desc,'到达处理中心') > strpos($status_desc,$city)) ) or 
                    
                    (strstr($status_desc,'已收入') && strstr($status_desc,$city) && strpos($status_desc,'已收入') < strpos($status_desc,$city)) or 
                    
                    (strstr($status_desc,'已到达') && strstr($status_desc,$city) && strpos($status_desc,'已到达') < strpos($status_desc,$city)) or 
                    
                    (strstr($status_desc,'分拨中心发出') && strstr($status_desc,$city) && strpos($status_desc,'分拨中心发出') > strpos($status_desc,$city)) or 
                    
                    (strstr($status_desc,'分拨中心进行') && strstr($status_desc,$city) && strpos($status_desc,'分拨中心进行') > strpos($status_desc,$city)) or 
                    
                    (strstr($status_desc,'快件在') && strstr($status_desc,$city) && strpos($status_desc,'快件在') < strpos($status_desc,$city)) or 
                    
                    (strstr($status_desc,'离开处理中心') && strstr($status_desc,$city) && strpos($status_desc,'离开处理中心') > strpos($status_desc,$city))
                ){
                    if($v['logi_status']=='0'){
                        $sms_status = 0;
                    }
                    $logi_status = '1';
                    $plugin_id = 'plugins_service_city';
                }
                
                if(
                    (strstr($status_desc,'妥投') && !strstr($status_desc,'未妥投')) or 
                    (strstr($status_desc,'签收')) or 
                    (strstr($status_desc,'派送成功')) or 
                    (strstr($status_desc,'代收')) 
                ){
                    $sign_time = strtotime($vv['status_time']);
                    $logi_status = '2';
                    $sms_status = 0;
                    $plugin_id = 'plugins_service_sign';
                    break;
                }
            }
            
            $transit_step_info = json_encode($transit_step_info);
            $id = $v['id'];
            
            $logi_status = intval($logi_status);
            $save_arr = array(
                'update_time' => time(),
                'plugin_id' => $plugin_id,
                'sign_time' => $sign_time,
                'sms_status' => $sms_status,
                'logi_status' => (string)$logi_status,
                'transit_step_info' => $transit_step_info
            );
            $model_trades->update($save_arr,array('id'=>$id));
        }
    }
    
    public function format_city($city)
    {
        $city = str_replace('市','',$city);
        $city = str_replace('回族自治州','',$city);
        $city = str_replace('朝鲜族自治州','',$city);
        $city = str_replace('地区','',$city);
        $city = str_replace('林区','',$city);
        $city = str_replace('傣族自治州','',$city);
        $city = str_replace('黎族自治县','',$city);
        $city = str_replace('藏族自治州','',$city);
        if(strstr($city, '恩施')) $city='恩施';
        if(strstr($city, '湘西')) $city='湘西';
        if(strstr($city, '黔东')) $city='黔东';
        if(strstr($city, '黔南')) $city='黔南';
        if(strstr($city, '黔西')) $city='黔西';
        //$city = mb_substr($city, 0, 2, 'utf-8');
        return $city;
    }
}
