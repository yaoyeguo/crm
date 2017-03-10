<?php
class plugins_service_vcard{

    var $sms_page_size = 200; //每次提交的短信数

    // 插件介绍
    public function get_desc()
    {
        $conf_desc = array(
            'title'=>'店铺名片',
            'worker'=>__CLASS__,
            'desc'=>'买家首次购物之后，系统通过短信发送店铺名片信息，用户点击链接将自动下载商家店铺信息到手机通讯录(发送时间：每天8:00-20:00)',
            'icon'=>'vcard.png',
            'price'=>array(0),
            'month'=>array(120),
            'status'=>'active',
            'addons'=>'tags',
            'tags'=>array('姓名','昵称','店铺','店铺名片'),
            'sms_template'=>'亲爱的<{姓名}>,感谢您的支持,点击链接保存我们的联系方式<{店铺名片}>,全心全意为你服务',
            'sort'=>6
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
            'order_type'=>array(
                'label'=>'发送条件',
                'type'=>'select',
                'options' => array(
                    '1'=>'首次下单',
                    '2'=>'首次付款',
                    '3'=>'首次完成',
                ),
                'default' => 1,
            ),
            'send_content'=>array(
                'label'=>'发送内容',
                'type'=>'textarea',
                'options'=>'亲爱的<{姓名}>,感谢您的支持,点击链接保存我们的联系方式<{店铺名片}>,全心全意为你服务 退订回N【<{签名}>】'
            ),
        );
        return $conf_items;
    }
    
    // 运行插件
    public function run_hour($arr_param)
    {
        //插件设置
        $params = json_decode($arr_param['params'],1);
        
        // 定时发送时间，默认为程序运行当天
        if(intval(date('H')) <8 or intval(date('H') >20)) return false; 
        
        //echo('<pre>');var_dump($params);
        $db = kernel::database();
        $oServiceApi = kernel::single('plugins_service_api');
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
        $begin_time = intval($arr_param['last_run_time']);//开始时间，插件上次执行的时间
        if($begin_time >0 ) {
            $begin_time += 1;
        }else{
            $begin_time = time() - 86400;
        }
        $end_time = time();//结束时间
        $mobiles = array();
        $id_arr = array();
        $sms = array();
        
        if($params['order_type']==1) $fields = 'createtime';
        if($params['order_type']==2) $fields = 'pay_time';
        if($params['order_type']==3) $fields = 'finish_time';
        
        $last_run_time = $begin_time;
        $sql = "select a.member_id,b.uname,a.ship_name,a.ship_mobile,a.shop_id,a.order_bn,a.tostr as shop_name,a.$fields as last_run_time
        from sdb_ecorder_orders as a left join sdb_taocrm_members as b 
         on a.member_id=b.member_id
        where a.$fields between $begin_time and $end_time";
        //die($sql);
        $rs = $db->select($sql);
        if(!$rs) $rs = array();
        foreach($rs as $v){
            $last_run_time = max($last_run_time, $v['last_run_time']);
            
            //检测手机号码格式
            if($oServiceApi->mobile_validate($v['ship_mobile']) == 0){
                continue;
            }
            
            //检测短信黑名单
            if($this->chk_sms_blacklist($v['ship_mobile'], $oServiceApi) == true){
                continue;
            }
            
            if(!in_array($v['ship_mobile'],$mobiles) && $v['ship_mobile']!='' && in_array($v['shop_id'],$params['shop_id'])){
                
                //跳过没有签名的店铺
                if(!$shops[$v['shop_id']]) continue;
                
                if(!$shops[$v['shop_id']]['vcard_url']) continue;
                
                //判断是否首次
                if($this->check_first($v, $params, $db) == false) continue;
                
                $mobiles[] = $v['ship_mobile'];
                $sms_content = $params['send_content'];
                
                //检测是否存在短信签名
                $sms_content = $oServiceApi->sms_validate($sms_content);

                $sms_content = str_replace('<{姓名}>',$v['ship_name'],$sms_content);
                $sms_content = str_replace('<{昵称}>',$v['uname'],$sms_content);
                $sms_content = str_replace('<{店铺}>',$shops[$v['shop_id']]['name'],$sms_content);
                $sms_content = str_replace('<{店铺名片}>', $shops[$v['shop_id']]['vcard_url'].' ',$sms_content);
                $sms_content = str_replace('<{签名}>', $shops[$v['shop_id']]['sms_sign'], $sms_content);
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
        }
        unset($rs);

        // 分批次发送短信
        if($sms){
            for($i=0;$i<=(sizeof($sms)/$this->sms_page_size);$i++){
                $content = array_slice($sms,($i*$this->sms_page_size),$this->sms_page_size);
                if(!$content) break;
                $content = json_encode($content);
                //err_log($content);
                $send_res = $oServiceApi->_send_sms($content,'cf'.$start_date.'b'.$i, '店铺名片');
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
        $sql = "update sdb_plugins_plugins set last_run_time=".$last_run_time." where plugin_id=".$arr_param['plugin_id']." ";
        $db->exec($sql);
        
        return true;
    }
    
    //判断是否首次
    private function check_first(&$v, &$params, &$db)
    {
        $member_id = $v['member_id'];        
        $shop_id = $v['shop_id'];        
        $order_type = $params['order_type'];        
        if(!$member_id or !$order_type){
            return false;
        }
        
        if($order_type==1) $wherestr = " ";
        if($order_type==2) $wherestr = " and pay_status='1' ";
        if($order_type==3) $wherestr = " and status='finish' ";
        
        $sql = "select count(*) as total_orders from sdb_ecorder_orders where member_id=$member_id and shop_id='$shop_id' $wherestr ";
        $rs = $db->selectRow($sql);
        if($rs['total_orders'] == 1){
            $return = true;
        }else{
            $return = false;
        }
        return $return;
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
        //echo('<p align=center ><br/><br/>首次启用插件，请<a style="color:red" href="index.php?app=plugins&ctl=admin_manage&act=set&p[0]='.$arr['plugin_id'].'&finder_id='.$finder_id.'" target="dialog::{width:680,height:300,title:\'插件设置\'}">点击这里进行配置</a>！</p>');
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
        //将店铺信息同步到vcard表
        $this->init_vcard_shops();    
    
        $db = kernel::database();
        $sql = 'select a.shop_id,b.name,a.vcard_id,a.vcard_url,b.config from sdb_ecorder_shop_vcard as a 
            left join sdb_ecorder_shop as b on a.shop_id=b.shop_id
        ';
        $rs = $db->select($sql);
        if(!$rs) return false;
        foreach($rs as $v){
            
            if($v['vcard_id'] == 0){
                $v['name'] .= '(<a target="_blank" href="index.php?app=plugins&ctl=admin_vcard&act=index" style="color:red">名片未设置</a>)';
            }
            
            //处理店铺短信签名
            $config = unserialize($v['config']);
            $v['sms_sign'] = $config['sms_sign'];
            $v['extend_no'] = $config['extend_no'];
            $v['review'] = $config['review'];
            if(!$v['sms_sign']){
                $v['sms_sign'] = $v['name'];
            }
            
            //如果签名未审核，跳过该店铺
            if(!$v['extend_no']){
                continue;
            }
            
            $shops[$v['shop_id']] = $v;
        }
        return $shops;
    }
    
    //将店铺信息同步到vcard表
    public function init_vcard_shops()
    { 
        $oShopVcard = app::get('ecorder')->model('shop_vcard');
        $sql = "select * from sdb_ecorder_shop where shop_id not in (
            select shop_id from sdb_ecorder_shop_vcard
        )";
        $rs = $oShopVcard->db->select($sql);
        if( ! $rs) $rs = array();
        foreach($rs as $v){
            $v['vcard_id'] = 0;
            $v['nick'] = $v['name'];
            $v['address'] = $v['addr'];
            $v['company'] = $v['name'];
            $oShopVcard->save($v);
        }
    }
    
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
