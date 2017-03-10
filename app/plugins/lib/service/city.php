<?php
class plugins_service_city{

    var $sms_page_size = 200; //每次提交的短信数

    // 插件介绍
    public function get_desc()
    {
        $conf_desc = array(
            'title'=>'送达城市提醒',
            'worker'=>__CLASS__,
            'desc'=>'【仅限淘宝店铺】订单到达收货城市后发送短信给买家，提醒买家收货。短信发送时间：8:00-19:00',
            'icon'=>'city.png',
            'price'=>array(0),
            'month'=>array(120),
            'status'=>'active',
            'addons'=>'tags',
            'tags'=>array('姓名','昵称','店铺','收货城市','物流公司'),
            'sms_template'=>'亲爱的<{姓名}>，您购买的宝贝已经到达<{收货城市}>，请注意查收，有问题及时联系客服，感谢您的支持~',
            'sort'=>2
        );
        
        return $conf_desc;
    }
    
    // 插件可配置项
    public function get_items($sdf)
    {
        $shops = $this->_get_shop_list();
        foreach($shops as $v){
            $shop_arr[$v['shop_id']] = $v['name'];
        }
        $conf_items = array(
            'shop_id'=>array(
                'label'=>'有效店铺',
                'type'=>'checkbox',
                'options' => $shop_arr,
                'default' => array_keys($shop_arr),
            ),
            'send_content'=>array(
                'label'=>'发送内容',
                'type'=>'textarea',
                'options'=>'亲爱的<{姓名}>，您购买的宝贝已经到达<{收货城市}>，请注意查收，有问题及时联系客服，感谢您的支持~ 退订回N【<{签名}>】'
            ),
        );
        return $conf_items;
    }
    
    // 运行插件
    public function run_hour($arr_param)
    {
        $oServiceApi = kernel::single('plugins_service_api');
        //插件设置
        $params = json_decode($arr_param['params'],1);
        $end_date = time() + 86400 - (86400*intval($params['days']));
        $start_date = $end_date - (86400*intval($params['days']));
        
        // 定时发送时间，默认为程序运行当天
        //if(intval(date('H')) != intval($params['send_time'])) return false;
        //$plan_sent_time = strtotime(date('Y-m-d').' '.$params['send_time'].':00:00');
        if(intval(date('H')) <8 or intval(date('H') >19)) return false; 
        
        //echo('<pre>');var_dump($params);
        $db = kernel::database();
        // 防止重复执行
        /*
        $sql = "select * from sdb_plugins_log where plugin_id=".$arr_param['plugin_id']." and run_key='$start_date' ";
        $rs = $db->select($sql);
        if($rs) return false;
        */

        $shops = $this->_get_shop_list();
        
        //获取短信模板内容
        $params['send_content'] = intval($params['send_content']);
        if($params['send_content']>0){
            $rs = app::get('market')->model('sms_templates')->dump($params['send_content']);
            if($rs) 
                $params['send_content'] = $rs['content'];
        }
        
        // 需要发送短信的用户
        $mobiles = array();
        $id_arr = array();
        $sms = array();
        $sql = 'select a.tid,a.id,a.buyer_nick,a.ship_name,a.ship_mobile,a.shop_id,a.logi_company,a.logi_no,a.city from sdb_plugins_trades as a 
        where a.logi_status="1" and a.sms_status=0 ';
        $rs = $db->select($sql);
        if(!$rs) $rs = array();
        foreach($rs as $v){            
            $id_arr[] = $v['id'];
            
            //检测手机号码格式
            if($oServiceApi->mobile_validate($v['ship_mobile']) == 0){
                continue;
            }
            
            //检测短信黑名单
            if($this->chk_sms_blacklist($v['ship_mobile'], $oServiceApi) == true){
                continue;
            }
            
            if(!in_array($v['ship_mobile'],$mobiles) && $v['ship_mobile']!='' 
                    && in_array($v['shop_id'],$params['shop_id'])){
                    
                if( ! $v['logi_company']) $v['logi_company']='无需物流';
                if( ! $v['logi_no']) $v['logi_no']='无运单号';
                    
                $mobiles[] = $v['ship_mobile'];
                $sms_content = $params['send_content'];
                
                //跳过没有签名的店铺
                if(!$shops[$v['shop_id']]) continue;
                
                //检测是否存在短信签名
                $sms_content = $oServiceApi->sms_validate($sms_content);
                
                $v['city'] = $this->format_city($v['city']);
                //'姓名','昵称','店铺','收货城市','物流公司'
                $sms_content = str_replace('<{收货城市}>',$v['city'],$sms_content);
                $sms_content = str_replace('<{姓名}>',$v['ship_name'],$sms_content);
                $sms_content = str_replace('<{物流公司}>',$v['logi_company'],$sms_content);
                $sms_content = str_replace('<{物流单号}>',$v['logi_no'],$sms_content);
                $sms_content = str_replace('<{昵称}>',$v['buyer_nick'],$sms_content);
                $sms_content = str_replace('<{店铺}>',$shops[$v['shop_id']]['name'],$sms_content);
                $sms_content = str_replace('<{签名}>',$shops[$v['shop_id']]['sms_sign'],$sms_content);
                $sms[] = array(
                    'phones'=>$v['ship_mobile'],
                    'content'=>$sms_content
                );
                
                $log_arr = array(
                    'tid' => $v['tid'],
                    'shop_name' => $shops[$v['shop_id']]['name'],
                    'shop_id' => $v['shop_id'],
                    'plugin_name' => $arr_param['plugin_name'],
                    'worker' => $arr_param['plugin_id'],
                    'sms_content' => $sms_content,
                    'mobile' => $v['ship_mobile'],
                    'status' => '',
                );
                $oServiceApi->save_sms_log($log_arr);
            }
        }
        unset($rs); 

        // 设置已发送标志，防止重复发短信
        if($id_arr){
            app::get('plugins')->model('trades')->update(
                array(
                    'sms_status'=>1,
                    'sms_time'=>time()
                ),
                array('id'=>$id_arr)
            );
        }

        // 分批次发送短信
        if($sms){
            for($i=0;$i<=(sizeof($sms)/$this->sms_page_size);$i++){
                $content = array_slice($sms,($i*$this->sms_page_size),$this->sms_page_size);
                if(!$content) break;
                $content = json_encode($content);
                //err_log($content);
                $send_res = $oServiceApi->_send_sms($content,'cf'.$start_date.'b'.$i, '送达城市提醒');
                //if(!$send_res) break;
            }
        
        
            // 保存插件运行日志
            $arr['plugin_id'] = $arr_param['plugin_id'];
            $arr['plugin_name'] = $arr_param['plugin_name'];
            $arr['worker'] = $arr_param['worker'];
            $arr['run_key'] = $start_date;//唯一识别码，防止重复运行
            $arr['start_time'] = time();
            //$arr['desc'] = date('Y-m-d H:i:s');
            $arr['desc'] = '发送短信数：'.count($mobiles);
            $arr['status'] = '成功';
            $arr['sms_count'] = count($mobiles);
            if($send_res['res'] != 'succ') {
                $arr['status'] = '失败';
                $arr['desc'] .= '，失败：'.var_export($send_res, true);
            }            
            $oLog = &app::get('plugins')->model('log');
            $oLog->save($arr);
        }
        
        // 更新插件最后运行时间
        $sql = "update sdb_plugins_plugins set last_run_time=".time()." where plugin_id=".$arr_param['plugin_id']." ";
        $db->exec($sql);
        
        return true;
    }
    
    // 购买插件
    public function plugin_buy()
    {
        $plugin = $this->get_desc();
        $items = $this->get_items();
        $db = kernel::database();
        $oPluginsOrders = &app::get('plugins')->model('orders');
        $oPlugins = &app::get('plugins')->model('plugins');
        
        $arr['worker'] = $plugin['worker'];
        $arr['plugin_name'] = $plugin['title'];
        $arr['amount'] = $plugin['price'][intval($_POST['month'])];
        $arr['amount'] = intval($arr['amount']);
        $arr['price'] = 0;
        $arr['buy_time'] = time();
        $arr['month'] = $plugin['month'][intval($_POST['month'])];
        
        // 扣除费用
        if($arr['amount']>0 && !$this->_set_paid($arr['amount'])){
            echo('付款失败！');
            return false;
        }
        
        $sql = 'select * from sdb_plugins_plugins where worker="'.$arr['worker'].'" ';
        $rs = $db->selectRow($sql);
        if($rs){
            // 续费
            $arr['plugin_id'] = $rs['plugin_id'];
            if($rs['end_time']<time()) $rs['end_time'] = time();
            $arr['end_time'] = strtotime('+'.$arr['month'].' months',$rs['end_time']);
            $oPlugins->save($arr);
        }else{            
            //初始化短信模板分类
            $oTemplateType = &app::get('market')->model('sms_template_type');
            $sms_type_arr = $oTemplateType->dump(array('title'=>'插件模板','is_fixed'=>1));
            if(!isset($sms_type_arr['type_id'])) {
                $sms_type_arr['title'] = '插件模板';
                $sms_type_arr['remark'] = '';
                $sms_type_arr['create_time'] = time();
                $sms_type_arr['is_fixed'] = 1;
                $oTemplateType->insert($sms_type_arr);
            }
            
            //初始化短信模板
            $conf_items = $this->get_items();
            $sms_arr['title'] = $plugin['title'];
            $sms_arr['content'] = $conf_items['send_content']['options'];
            $sms_arr['type_id'] = $sms_type_arr['type_id'];
            $sms_arr['create_time'] = time();
            $sms_arr['is_fixed'] = 1;
            app::get('market')->model('sms_templates')->insert($sms_arr);
            
            //初始化所有的配置参数
            $params = array('send_content'=>$sms_arr['template_id']);
            foreach($items as $k=>$v){
                if(isset($v['default'])){
                    $params[$k] = $v['default'];
                }
            }
            
            // 初次购买
            $arr['end_time'] = strtotime('+'.$arr['month'].' months',time());
            $arr['status'] = 'wait';
            $arr['params'] = json_encode($params);
            
            $oPlugins->save($arr);
        }
        
        kernel::single('taocrm_service_redis')->redis->sadd('PLUGINS:DOMAINS',$_SERVER['SERVER_NAME']);
        kernel::single('taocrm_service_redis')->redis->sadd($_SERVER['SERVER_NAME'].':PLUGINS', __CLASS__);
        
        // 保存购买记录
        $arr['op_user'] = kernel::single('desktop_user')->get_name();
        $oPluginsOrders->insert($arr);
        
        echo('succ'.$arr['plugin_id']);        
        //echo('<p align=center ><br/><br/>首次购买插件，请<a style="color:red" href="index.php?app=plugins&ctl=admin_manage&act=set&p[0]='.$arr['plugin_id'].'&finder_id='.$finder_id.'" target="dialog::{width:680,height:300,title:\'插件设置\'}">点击这里进行配置</a>！</p>');
    }
    
    // 扣除插件费用
    private function _set_paid($amount)
    {
		$plugin = $this->get_desc();
        $msgid = date('ymdHis').rand(111,999);//对账用唯一识别码
        $pluginAPI = kernel::single('plugins_service_api');
        $smsAPI = kernel::single('market_service_smsinterface');
		$res = $smsAPI->payment($msgid,$amount,0, 3, $plugin['title']);
        if($res['res'] != 'succ'){
            echo($res['info'].'　');
            return false;
        }
        
        // 扣费日志
        $arr['action'] = 'deduct';
        $arr['msgid'] = $msgid;
        $arr['nums'] = $amount;
        $arr['remark'] = '购买插件';
        $pluginAPI->add_sms_pay_log($arr);
        
        return true;
    }
    
    // 店铺信息
    private function _get_shop_list()
    {
        return kernel::single('plugins_service_api')->_get_shop_list();
    }
    
    // 保存需要发送短信的订单信息
    public function save_trade_info(&$order_sdf, &$params)
    {    
        //err_log($order_sdf);
        
        //只支持淘宝订单
        if($order_sdf['shop_type'] != 'taobao') return false;
    
        //检测订单是否符合发送条件
        if(intval($order_sdf['ship_status']) != 1) return false;
        
        //发货时间不为0
        if( ! $order_sdf['delivery_time']) return false;
        
        if(floatval($order_sdf['delivery_time']) < strtotime('-2 days')){
            return false;
        }
        
        //已完成的订单不处理
        //if($order_sdf['status'] == 'finish') return false;
        
        //物流公司不为空
        if( ! $order_sdf['logistics_code']) return false;
        
        $consignee_area = explode(':',$order_sdf['consignee']['area']);
        $consignee_area = explode('/',$consignee_area[1]);
        
        //将订单数据保存到待发送的数据库
        $plugin_id = __CLASS__;
        $trades = array(
            'tid'=>$order_sdf['order_bn'],
            'ship_name'=>$order_sdf['member_info']['name'],
            'ship_mobile'=>$order_sdf['consignee']['mobile'],
            'buyer_nick'=>$order_sdf['member_info']['uname'],
            //'shop_name'=>'',
            'shop_id'=>$order_sdf['shop_id'],
            'shop_name'=>$order_sdf['memeber_id'],
            'order_status'=>$order_sdf['status'],
            'ship_status'=> intval($order_sdf['ship_status']),
            'delivery_time'=>$order_sdf['delivery_time'],
            'logi_company'=>$order_sdf['logistics_company'],
            'logi_no'=>$order_sdf['logistics_code'],
            'seller_nick'=>$order_sdf['memeber_id'],
            'province'=>$consignee_area[0],
            'city'=>$consignee_area[1],
            'update_time'=>time(),
            //'sms_time'=>time(),
        );
        
        //拿收货人姓名当作客户姓名
        if( ! $trades['buyer_nick']){
            $trades['buyer_nick'] = $trades['ship_name'];
        }
        
        $filter = array(
            'tid'=>$order_sdf['order_bn']
        );
        $oTrades = app::get('plugins')->model('trades');
        $sql = 'select id from sdb_plugins_trades where tid="'.$order_sdf['order_bn'].'" ';
        $rs = kernel::database()->selectRow($sql);
        if( ! $rs){
            $trades['logi_status'] = '0';
            $trades['sms_status'] = 0;
            $trades['member_id'] = 0;
            $trades['sign_time'] = 0;
            $trades['create_time'] = time();
            $trades['plugin_id'] = $plugin_id;
            $oTrades->insert($trades);
        }else{
            $oTrades->update($trades, $filter);
        }
    }
    
    //实时任务:抓取物流信息
    public function run_task()
    {
        return true;
    }
    
    //物流流转信息
    public function trace_logistics()
    {
        //如果订购了签收插件，不再执行到达城市抓取
    
        $plugin_id = __CLASS__;
        $db = &kernel::database();
        $sql = 'select id,tid,seller_nick,city,sms_status,logi_status,sign_time from sdb_plugins_trades where logi_status="0" and update_time<'.(time() - 1800).' and order_status="active" and ship_status=1 limit 500';
        $rs = $db->select($sql);
        if(!$rs) return false;
        
        $orderObj = new ectools_api_taobao_order();
        foreach($rs as $v){
            //淘宝接口 taobao.logistics.trace.search 物流流转信息查询 
            //（备注：使用线下发货（offline.send）的运单，不支持运单状态的实时跟踪，
            //只要一发货，状态就会变为对方已签收，该字段仅对线上发货（online.send）
            //的运单有效。） 
            $transit_step_info = $orderObj->getLogisticsTrace($v['seller_nick'], $v['tid'] ,$v['shop_id']);
            if( ! $transit_step_info) {
                //屏蔽出错的订单
                $sql = "update sdb_plugins_trades set logi_status='9' where id=".$v['id'];
                $db->exec($sql);
                continue;
            }
            //err_log($transit_step_info);
            
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
                    $logi_status = '1';
                    $sms_status = 0;
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
            
            $save_arr = array(
                'update_time' => time(),
                'sms_status'=>$sms_status,
                'plugin_id'=>$plugin_id,
                'sign_time'=>$sign_time,
                'logi_status'=>$logi_status,
                'transit_step_info'=>$transit_step_info
            );
            app::get('plugins')->model('trades')->update($save_arr,array('id'=>$id));
        }
    }
    
    public function format_city($city){
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
    
    private function chk_sms_blacklist($mobile, $oServiceApi){
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
