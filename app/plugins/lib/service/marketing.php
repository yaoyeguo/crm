<?php
class plugins_service_marketing{

    var $sms_page_size = 200; //每次提交的短信数

    // 插件介绍
    public function get_desc(){
        $conf_desc = array(
            'title'=>'自动营销',
            'worker'=>'plugins_service_marketing',
            'desc'=>'根据客户的订单状态自动对客户进行营销，可对催付、发货等业务流程补充，也可单独使用。可对付款订单的客户进行关怀',
            'icon'=>'',
            'price'=>array(0),
            'month'=>array(120),
            'status'=>'active',
            'addons'=>'tags',
            'tags'=>array('姓名','店铺'),
            'sms_template'=>'亲爱的<{姓名}>，感谢您的惠顾，如您在购物过程中有疑问，请联系小二。我们将及时为您提供满意的服务！',
            'sort'=>10
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
            'order_status'=>array(
                'label'=>'订单状态',
                'type'=>'select',
                'options'=>array('下单','付款','发货','完成','关闭'),
            ),
            'send_time'=>array(
                'label'=>'发送条件',
                'type'=>'text',
                'prefix'=>'订单状态后',
                'default' => '1',
                'desc' => '小时 (请输入1~99之间的数字)',
                'size' => 2,
            ),
            'shop_id'=>array(
                'label'=>'有效店铺',
                'type'=>'checkbox',
                'options' => $shop_arr,
                'default' => array_keys($shop_arr),
            ),
            'send_content'=>array(
                'label'=>'发送内容',
                'type'=>'textarea',
                'options'=>'亲爱的<{姓名}>，感谢您的惠顾，如您在购物过程中有疑问，请联系小二。我们将及时为您提供满意的服务！'
            ),
        );
        return $conf_items;
    }
    
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
        $today_time = strtotime(date('Y-m-d'));
        
        $send_time = intval($params['send_time']);
        $start_time = strtotime('-'.($send_time+1).' hours', strtotime(date('Y-m-d H:00:00')) );
        $end_time = $start_time + 3600;//每次执行一小时数据
        
        $shops = $this->_get_shop_list($params['shop_id']);
        
        //防止重复执行
        $sql = "select * from sdb_plugins_log where plugin_id=".$arr_param['plugin_id']." and run_key='$start_time' ";
        $rs = $db->select($sql);
        if($rs) return false;
        
        //获取短信模板内容
        $oTemplates = app::get('market')->model('sms_templates');
        $template_id = intval($params['send_content']);
        if($template_id>0){
            $rs = $oTemplates->dump(array('template_id'=>$template_id));
            if($rs) $params['send_content'] = $rs['content'];
        }
        
        //需要发送短信的用户:未付款，订单状态为有效
        $sms = array();
        $rs = array();
        switch($params['order_status'])
        {
            case 0://下单
                $time_type = 'createtime';
                $order_status = array(
                    'status' => 'active',
                    'pay_status' => 0,
                    'ship_status' => 0,
                );
                break;
            case 1://付款
                $time_type = 'pay_time';
                $order_status = array(
                    'status' => 'active',
                    'pay_status' => 1,
                    'ship_status' => 0,
                );
                break;
            case 2://发货
                $time_type = 'delivery_time';
                $order_status = array(
                    'status' => 'active',
                    'pay_status' => 1,
                    'ship_status' => 1,
                );
                break;
            case 3://完成
                $time_type = 'finish_time';
                $order_status = array(
                    'status' => 'finish',
                    'pay_status' => 1,
                    'ship_status' => 1,
                );
                break;
            case 4://关闭
                $time_type = 'finish_time';
                $order_status = array(
                    'status' => 'dead',
                );
                break;
        }
        
        $fields = 'order_bn,ship_mobile,ship_name,shop_id';
        $get_order_params = array(
            'start_time' => date('Y-m-d H:i:s', $start_time),
            'end_time' => date('Y-m-d H:i:s', $end_time),
            'shop_id' => $params['shop_id'],
            'filter' => $order_status,
            'time_type' => $time_type,
            'type' => 'all',
        );
        $rs = kernel::single('ecorder_service_orders')->get_orders_by_params($get_order_params, $fields);
        if(!$rs){
            return false;
        }
        
        $mobiles = array();
        $tids = array();//符合条件的订单号
        foreach($rs as $v){
            //短信号码去重
            if(in_array($v['ship_mobile'],$mobiles)){
                continue;
            }
            if($v['ship_mobile']) $mobiles[] = $v['ship_mobile'];
            if($v['order_bn']) $tids[] = $v['order_bn'];
        }

        if($mobiles){
            $today_sent_mobiles = array();
            $today_paid_mobiles = array();
            
            //手机号排除：发送过的订单不发
            $sql = "select mobile from sdb_plugins_sms_logs where worker=".$arr_param['plugin_id']." and tid in ('".implode("','",$tids)."') ";
            $rs_temp = $db->select($sql);
            if($rs_temp){
                foreach($rs_temp as $v){
                    $today_sent_mobiles[$v['mobile']] = 1;
                }
            }
            
            //短信号码过滤：当天发送过的买家不发
            $sql = "select mobile from sdb_plugins_sms_logs where create_time>=$today_time and worker=".$arr_param['plugin_id']." and mobile in ('".implode("','",$mobiles)."') ";
            $rs_temp = $db->select($sql);
            if($rs_temp){
                foreach($rs_temp as $v){
                    $today_sent_mobiles[$v['mobile']] = 1;
                }
            }
            
            if($params['order_status'] == 0){ 
                //短信号码过滤：当天有付款订单的买家不发
                $sql = "select ship_mobile from sdb_ecorder_orders where createtime>=$today_time and ship_mobile in ('".implode("','",$mobiles)."') and (pay_status in ('1','2','3','4') or ship_status='1') ";
                $rs_temp = $db->select($sql);
                if($rs_temp){
                    foreach($rs_temp as $v){
                        $today_paid_mobiles[$v['ship_mobile']] = 1;
                    }
                }
            }
        }
        
        $mobiles = array();
        foreach($rs as $v){
        
            //短信号码去重
            if(in_array($v['ship_mobile'],$mobiles)){
                continue;
            }
            
            if(isset($today_sent_mobiles[$v['ship_mobile']])){
                continue;
            }
            
            if(isset($today_paid_mobiles[$v['ship_mobile']])){
                continue;
            }
            
            //检测手机号码格式
            if($oServiceApi->mobile_validate($v['ship_mobile']) == 0){
                continue;
            }
        
            //检测短信黑名单
            if($this->chk_sms_blacklist($v['ship_mobile'], $oServiceApi) == true){
                continue;
            }
            
            $mobiles[] = $v['ship_mobile'];
            $sms_content = $params['send_content'];
            //跳过没有签名的店铺
            if(!$shops[$v['shop_id']]) continue;
            
            //检测是否存在短信签名
            $sms_content = $oServiceApi->sms_validate($sms_content);
            $sms_content = str_replace('<{昵称}>',$v['ship_name'],$sms_content);
            $sms_content = str_replace('<{姓名}>',$v['ship_name'],$sms_content);
            $sms_content = str_replace('<{用户名}>',$v['ship_name'],$sms_content);
            $sms_content = str_replace('<{店铺}>',$shops[$v['shop_id']]['name'],$sms_content);
            $sms_content = str_replace('<{签名}>',$shops[$v['shop_id']]['sms_sign'],$sms_content);
            $sms[] = array(
                'phones'=>$v['ship_mobile'],
                'content'=>$sms_content
            );
            
            $log_arr = array(
                'tid' => $v['order_bn'],
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
        $rs = null;
        
        if($sms){
            // 分批次发送短信
            for($i=0;$i<=(sizeof($sms)/$this->sms_page_size);$i++){
                $content = array_slice($sms,($i*$this->sms_page_size),$this->sms_page_size);
                if(!$content) break;
                $content = json_encode($content);
                
                $send_res = $oServiceApi->_send_sms($content,'cf'.$start_time.'b'.$i, $arr_param['plugin_name']);
                if($send_res['res'] != 'succ') break;
            }
            
            // 保存插件运行日志
            $arr['plugin_id'] = $arr_param['plugin_id'];
            $arr['plugin_name'] = $arr_param['plugin_name'];
            $arr['worker'] = $arr_param['worker'];
            $arr['run_key'] = $start_time;//唯一识别码，防止重复运行
            $arr['start_time'] = time();
            $arr['desc'] = '发送短信数：'.count($mobiles);
            $arr['status'] = '成功';
            $arr['sms_count'] = count($mobiles);
            if($send_res['res'] != 'succ') {
                $arr['status'] = '失败';
                $arr['desc'] .= '，失败：'.var_export($send_res, true);
            }
            $oLog = app::get('plugins')->model('log');
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
        $oPluginsOrders = app::get('plugins')->model('orders');
        $oPlugins = app::get('plugins')->model('plugins');
        
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
            $oTemplateType = app::get('market')->model('sms_template_type');
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
    }
    
    // 扣除插件费用
    private function _set_paid($amount){

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
    private function _get_shop_list($ids='')
    {
        if($ids)
        {
            foreach($ids as $id)
            {
                $ids_str .= ",'".$id."'";
            }
            $ids_str = substr($ids_str,1);
        }
        return kernel::single('plugins_service_api')->_get_shop_list($ids_str);
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
