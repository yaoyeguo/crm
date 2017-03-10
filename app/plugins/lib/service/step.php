<?php
class plugins_service_step{

    var $sms_page_size = 200; //每次提交的短信数
    var $db_page_size = 1000; //数据库分页，防止超时和内存溢出

    // 插件介绍
    public function get_desc(){
        $conf_desc = array(
            'title' => '双十一万人团提醒催付工具',
            'worker' => 'plugins_service_step',
            'desc' => '双十一万人团购商品，当客户成功下单后第二天进行短信提醒和流程告知。<br/><font color=red><b>由于万人团订单量较大，客户较多，请预先充值足够的短信。</b></font>',
            'icon' => 'step.png',
            'price' => array(200),
            'month' => array(1),
            'status' => 'disabled',
            'addons' => 'tags',
            'max_buy_times' => 1,
            'view' => 'step',
            'dead_line' => '2012-11-12',
        );
        
        return $conf_desc;
    }
    
    // 插件可配置项
    public function get_items($sdf){
        $shop_arr = $this->_get_shop_list();
        $conf_items = array(
            'send_time1'=>array(
                'label'=>'发送时间1',
                'type'=>'select',
                'options'=>array(9=>'09:00',13=>'13:00',15=>'15:00'),
                'default' => 9,
            ),
            'send_time2'=>array(
                'label'=>'发送时间2',
                'type'=>'select',
                'options'=>array(11=>'11:00',14=>'14:00',17=>'17:00'),
                'default' => 14,
            ),
            'send_time3'=>array(
                'label'=>'发送时间3',
                'type'=>'select',
                'options'=>array(9=>'09:00',13=>'13:00',15=>'15:00'),
                'default' => 13,
            ),
            'shop_id'=>array(
                'label'=>'有效店铺',
                'type'=>'checkbox',
                'options' => $shop_arr,
                'default' => array_keys($shop_arr),
            ),
            'send_content1'=>array(
                'label'=>'发送内容1',
                'type'=>'textarea',
                'options'=>'<{用户名}>，亲，感谢您选购<{店铺}>的产品，本次预售产品将在双11当天进行尾款支付，请您事先做好准备，不要忘记！欢迎您再次光临！'
            ),
            'send_content2'=>array(
                'label'=>'发送内容2',
                'type'=>'textarea',
                'options'=>'<{用户名}>，亲，您在<{店铺}>预定的订单：<{订单号}>，明天可以付款了，在双11当天可能会遭遇网银拥堵，建议您在此之前充值到支付宝！欢迎您的光临！'
            ),
            'send_content3'=>array(
                'label'=>'发送内容3',
                'type'=>'textarea',
                'options'=>'<{用户名}>，亲，现在您可以付款了，尾款支付时间为11月11日2:00-24:00，请及时支付！<{店铺}>欢迎您再次光临！'
            ),
        );
        return $conf_items;
    }
    
    // 运行插件
    public function run_hour($arr_param){
    
        //判断是否过期
        $conf_desc = $this->get_desc();
        if(time() > strtotime($conf_desc['dead_line'].' 00:00:00')){
            return false;
        }
        
        //插件设置
        $params = json_decode($arr_param['params'],1);
            
        //判断执行第几步提醒
        if(date('Y-m-d') == '2012-11-11'){
            $step = 3;
            if(intval(date('H')) != intval($params['send_time3'])) return false;
        }elseif(date('Y-m-d') == '2012-11-10'){
            if(intval(date('H')) == intval($params['send_time2'])) {
                $step = 2;
            }elseif(intval(date('H')) == intval($params['send_time1'])) {
                $step = 1;
            }else{
                return false;
            }
        }else{
            $step = 1;
            if(intval(date('H')) != intval($params['send_time1'])) return false;
        }      

        $db = kernel::database();
        $oLog = &app::get('plugins')->model('log');
        $now_date = strtotime('-1 days',strtotime(date('Y-m-d')));
        $end_date = $now_date+24*3600;
        $run_key = $now_date.':'.$step;//防止重复运行
        
        // 定时发送时间，默认为程序运行当天
        $plan_sent_time = strtotime(date('Y-m-d').' '.$params['send_time'].':00:00');
        $shops = $this->_get_shop_list();
        
        // 防止重复执行
        $sql = "select * from sdb_plugins_log where plugin_id=".$arr_param['plugin_id']." and run_key='$run_key' ";
        $rs = $db->select($sql);
        if($rs) return false;
        
        //获取短信模板内容
        $send_content = $params['send_content'.$step];
        
        //需要发送短信的用户:未付款，订单状态为有效
        $page = 0;
        $total_mobiles = 0;
        for($page=0;$page<1000;$page++){
            $mobiles = array();
            $sms = array();
            if($step == 1) {
                $sql = 'select b.uname,a.ship_name,a.ship_mobile,a.shop_id,a.order_bn 
                from sdb_ecorder_orders as a
                left join sdb_taocrm_members as b on a.member_id=b.member_id
                where (a.createtime between '.$now_date.' and '.$end_date.')
                and a.trade_type="step" and a.status="active" limit '.($page*$this->db_page_size).','.$this->db_page_size.' ';
            }elseif($step == 2 || $step==3) {
                $sql = 'select b.uname,a.ship_name,a.ship_mobile,a.shop_id,a.order_bn 
                from sdb_ecorder_orders as a
                left join sdb_taocrm_members as b on a.member_id=b.member_id
                where a.trade_type="step" and a.pay_status="0" and a.status="active" limit '.($page*$this->db_page_size).','.$this->db_page_size.' ';
            }
            $rs = $db->select($sql);
            if(!$rs) break;
            foreach($rs as $v){
                //$v['ship_mobile'] = '13524985717';//测试代码
                if($v['ship_mobile']!='' && in_array($v['shop_id'],$params['shop_id'])){
                    if($step == 1 && isset($mobiles[$v['ship_mobile']])) {
                        continue;
                    }
                    $mobiles[$v['ship_mobile']] = 1;
                    $sms_content = $send_content;
                    $sms_content = str_replace('<{用户名}>',$v['uname'],$sms_content);
                    $sms_content = str_replace('<{店铺}>',$shops[$v['shop_id']],$sms_content);
                    $sms_content = str_replace('<{订单号}>',$v['order_bn'],$sms_content);
                    $sms[] = array(
                        'phones'=>$v['ship_mobile'],
                        'content'=>$sms_content
                    );
                }
            }
            unset($rs,$mobiles);
            
            // 分批次发送短信
            $total_mobiles += count($sms);
            for($i=0;$i<=(sizeof($sms)/$this->sms_page_size);$i++){            
            
                $content = array_slice($sms,($i*$this->sms_page_size),$this->sms_page_size);
                if(!$content) break;
                $content = json_encode($content);
                //echo('<pre>');var_dump(($sms));die();
                
                $send_res = $this->_send_sms($content,'cf'.$now_date.'b'.$i);
                if(!$send_res) break;
            }
        }
        
        // 保存插件运行日志
        $arr['plugin_id'] = $arr_param['plugin_id'];
        $arr['plugin_name'] = $arr_param['plugin_name'];
        $arr['worker'] = $arr_param['worker'];
        $arr['run_key'] = $run_key;//唯一识别码，防止重复运行
        $arr['start_time'] = time();
        if($step == 1) $arr['desc'] = '下单提醒';
        if($step == 2) $arr['desc'] = '第一次提醒';
        if($step == 3) $arr['desc'] = '第二次提醒';
        $arr['desc'] .= '，发送短信数：'.$total_mobiles;
        $arr['status'] = '成功';
        if(!$send_res) $arr['status'] = '失败';
        $oLog->save($arr);
        
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
        $arr['price'] = 0;
        $arr['buy_time'] = time();
        $arr['month'] = $plugin['month'][intval($_POST['month'])];
        
        // 扣除费用
        if(!$this->_set_paid($arr['amount'])){
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
            //初始化短信模板
            $conf_items = $this->get_items();
            $sms_arr['content'] = $conf_items['send_content']['options'];
            
            //初始化所有的配置参数
            $params = array('send_content'=>$sms_arr['content']);
            foreach($items as $k=>$v){
                if(isset($v['default'])){
                    $params[$k] = $v['default'];
                }
            }
            $params['send_content1'] = $items['send_content1']['options'];
            $params['send_content2'] = $items['send_content2']['options'];
            $params['send_content3'] = $items['send_content3']['options'];
            
            // 初次购买
            $arr['end_time'] = strtotime('+'.$arr['month'].' months',time());
            $arr['status'] = 'wait';
            $arr['params'] = json_encode($params);
            
            $oPlugins->save($arr);
        }
        
        // 保存购买记录
        $arr['op_user'] = kernel::single('desktop_user')->get_name();
        $oPluginsOrders->insert($arr);
        
        echo('<p align=center ><br/><br/>感谢您的订购！<br/><br/>首次运行插件，请<a style="color:red" href="index.php?app=plugins&ctl=admin_manage&act=set&p[0]='.$arr['plugin_id'].'&finder_id='.$finder_id.'" target="dialog::{width:680,height:300,title:\'插件设置\'}">点击这里进行配置</a>！</p>');
    }
    
    // 发送短信
    private function _send_sms(&$content,$batch_no){
    
        //短信帐号
        base_kvstore::instance('market')->fetch('account', $account);
        $account = unserialize($account);
        if(!isset($account['entid'])) return false;
    
        $smsAPI = kernel::single('market_service_smsinterface');
        
        // 实时发送接口
        $res = $smsAPI->send($content, 'fan-out');
        if($res['res'] != 'succ'){
            return false;
        }else{
            return true;
        }
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
    private function _get_shop_list(){
        $db = kernel::database();
        $sql = 'select shop_id,name from sdb_ecorder_shop
        where active="true" and disabled="false"
        ';
        $rs = $db->select($sql);
        if(!$rs) return false;
        foreach($rs as $v){
            $shops[$v['shop_id']] = $v['name'];
        }
        return $shops;
    }
}
