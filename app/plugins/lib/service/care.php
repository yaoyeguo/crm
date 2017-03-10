<?php
class plugins_service_care{

    var $sms_page_size = 200; //每次提交的短信数

    // 插件介绍
    public function get_desc(){
        $conf_desc = array(
            'title'=>'客户关怀',
            'worker'=>'plugins_service_care',
            'desc'=>'帮助提高买家的忠诚度，对确认收货三天后的买家，发送客户关怀短信。(8:00-19:00)',
            'icon'=>'care.png',
            'price'=>array(0),
            'month'=>array(120),
            'status'=>'active',
            'addons'=>'tags',
            'tags'=>array('姓名','昵称','店铺'),
            'sms_template'=>'亲爱的<{姓名}>，您购买的宝贝还满意吗？有任何意见和建议请告诉我们哦，我们会做得更好~',
            'sort'=>5
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
            'buy_times'=>array(
                'label'=>'发送条件',
                'type'=>'select',
                'options'=>array(2=>'每次购买'),
                'default' => 2,
            ),
            'days'=>array(
                'label'=>'确认收货后',
                'type'=>'text',
                'options'=>array(1=>'1天',2=>'2天',3=>'3天'),
                'default' => 1,
                'size' => 2,
                'desc' => '天 (请输入大于等于1的数字)',
            ),
            'send_time'=>array(
                'label'=>'发送时间',
                'type'=>'text',
                'options'=>array(10=>'10:00',13=>'13:00',15=>'15:00'),
                'default' => 13,
                'size' => 4,
                'desc' => ':00 (请输入8~19之间的数字)',
            ),
            'shop_id'=>array(
                'label'=>'有效店铺',
                'type'=>'checkbox',
                'options'=> $shop_arr,
                'default' => array_keys($shop_arr),
            ),
            'send_content'=>array(
                'label'=>'发送内容',
                'type'=>'textarea',
                'options' => '亲爱的<{姓名}>，您购买的宝贝还满意吗？有任何意见和建议请告诉我们哦，我们会做得更好~ 退订回N【<{签名}>】',
            ),
        );
        return $conf_items;
    }
    
    // 运行插件
    public function run_hour($arr_param)
    {
        //插件设置
        $oServiceApi = kernel::single('plugins_service_api');
        $params = json_decode($arr_param['params'],1);
        $params['days'] = intval($params['days']);
        $params['send_time'] = intval($params['send_time']);
        if($params['days'] == 0){
            $params['days'] = 1;
        }
        if($params['send_time'] <8 or $params['send_time']>19){
            return false;
        }
        
        $db = kernel::database();
        $oLog = &app::get('plugins')->model('log');
        $now_date = strtotime('-'.$params['days'].' days',strtotime(date('Y-m-d')));
        $end_date = $now_date+24*3600;
        
        // 定时发送时间，默认为程序运行当天
        if(intval(date('H')) != intval($params['send_time'])) return false;
        $plan_sent_time = strtotime(date('Y-m-d').' '.$params['send_time'].':00:00');
        $shops = $this->_get_shop_list();
        
        // 防止重复执行，每天只运行一次
        $sql = "select * from sdb_plugins_log where plugin_id=".$arr_param['plugin_id']." and run_key='$now_date' ";
        $rs = $db->select($sql);
        if($rs) return false;
        
        //获取短信模板内容
        $params['send_content'] = intval($params['send_content']);
        if($params['send_content']>0){
            $rs = app::get('market')->model('sms_templates')->dump($params['send_content']);
            if($rs) 
                $params['send_content'] = $rs['content'];
        }
        
        //需要发送短信的用户:已付款，订单状态为已完成
        $mobiles = array();
        $sms = array();
        $sql = 'select b.uname,a.ship_name,a.ship_mobile,a.shop_id,a.order_bn as tid from sdb_ecorder_orders as a
                left join sdb_taocrm_members as b on a.member_id=b.member_id
                where (a.f_modified between '.$now_date.' and '.$end_date.')
                and a.pay_status="1" and a.status="finish"
            ';
        $rs = $db->select($sql);
        if(!$rs) $rs = array();

        $tids = array();
        foreach($rs as $v){
            //订单号去重
            $tids[] = $v['tid'];
        }

        if($tids){
            //短信号码过滤：同订单发送过的买家不发
            $order_sent_mobiles = array();
            $sql = "select mobile from sdb_plugins_sms_logs where tid in ('".implode("','",$tids)."') and worker=".$arr_param['plugin_id'];
            $rs_temp = $db->select($sql);
            if($rs_temp){
                foreach($rs_temp as $v){
                    $order_sent_mobiles[$v['mobile']] = 1;
                }
            }
        }

        foreach($rs as $v){
        
            if(isset($order_sent_mobiles[$v['ship_mobile']])){
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
            
            if(!in_array($v['ship_mobile'],$mobiles) && $v['ship_mobile']!='' &&
            in_array($v['shop_id'],$params['shop_id'])){
                $mobiles[] = $v['ship_mobile'];
                $sms_content = $params['send_content'];
                
                //跳过没有签名的店铺
                if(!$shops[$v['shop_id']]) continue;
                
                //检测是否存在短信签名
                $sms_content = $oServiceApi->sms_validate($sms_content);
                
                $sms_content = str_replace('<{昵称}>',$v['uname'],$sms_content);
                $sms_content = str_replace('<{姓名}>',$v['ship_name'],$sms_content);
                $sms_content = str_replace('<{用户名}>',$v['ship_name'],$sms_content);
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
        
        if($sms){
            // 分批次发送短信
            for($i=0;$i<=(sizeof($sms)/$this->sms_page_size);$i++){            
            
                $content = array_slice($sms,($i*$this->sms_page_size),$this->sms_page_size);
                if(!$content) break;
                $content = json_encode($content);
                
                $send_res = $oServiceApi->_send_sms($content,'gh'.$now_date.'b'.$i, '客户关怀');
                if($send_res['res'] != 'succ') break;
            }
            
            // 保存插件运行日志
            $arr['plugin_id'] = $arr_param['plugin_id'];
            $arr['plugin_name'] = $arr_param['plugin_name'];
            $arr['worker'] = $arr_param['worker'];
            $arr['run_key'] = $now_date;//唯一识别码，防止重复运行
            $arr['start_time'] = time();
            //$arr['desc'] = date('Y-m-d H:i:s',$plan_sent_time);
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
    
    // 购买插件
    public function plugin_buy(){
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
            
            //初次购买
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
    
    //扣除插件费用
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
    private function _get_shop_list()
    {
        return kernel::single('plugins_service_api')->_get_shop_list('all');
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
