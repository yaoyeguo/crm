<?php
class plugins_service_birthday{

    var $sms_page_size = 200; //每次提交的短信数

    // 插件介绍
    public function get_desc(){
        $conf_desc = array(
            'title'=> '生日营销',
            'worker'=> __CLASS__,
            'desc'=>'对即将过生日的用户进行生日营销',
            'icon'=>'birthday.png',
        	'max_buy_times' => 1,
            'status'=>'active',
            'addons'=>'tags',
            'tags'=>array('姓名','昵称','店铺'),
            'sms_template'=>'亲爱的<{姓名}>，这是您的生日提醒',
            'sort'=>7,
            'month'=>array(99),
            'view'=>'birthday'
        );
        
        return $conf_desc;
    }
    
    // 插件可配置项
    public function get_items($sdf){
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
            'day' => array(
                'options1' => array('当','前'),
                'options2' => array(1,2,3,4,5,6,7),
                'options3' => range(8,20),
                'send_content' => '亲爱的<{姓名}>,您的生日即将到来，祝您生日快乐',
            ),
            'week' => array(
                'options1' => array('星期一','星期二','星期三','星期四','星期五','星期六','星期日'),
                'options2' => array('本周','下周'),
                'options3' => range(8,20),
                'send_content' => '亲爱的<{姓名}>,您的生日即将到来，祝您生日快乐',
            ),
            'month' => array(
                'options1' => range(1,31),
                'options2' => array('本月','下个月'),
                'options3' => range(8,20),
                'send_content' => '亲爱的<{姓名}>,您的生日即将到来，祝您生日快乐',
            ),
        );
        return $conf_items;
    }
    
    // 店铺信息
    private function _get_shop_list($ids)
    {
        foreach($ids as $id)
        {
            $ids_str .= ",'".$id."'";
        }
        $ids_str = substr($ids_str,1);
        return kernel::single('plugins_service_api')->_get_shop_list($ids_str);
    }
    
    // 购买插件
    public function plugin_buy()
    {
        $plugin = $this->get_desc();
        $db = kernel::database();
        $oPluginsOrders = app::get('plugins')->model('orders');
        $oPlugins = app::get('plugins')->model('plugins');
        
        $arr['worker'] = $plugin['worker'];
        $arr['plugin_name'] = $plugin['title'];
        $arr['price'] = 0;
        $arr['buy_time'] = time();
        $arr['month'] = $plugin['month'][intval($_POST['month'])];
        $sql = 'select * from sdb_plugins_plugins where worker="'.$arr['worker'].'" ';
        $rs = $db->selectRow($sql);
        if($rs){
            // 重新开启
            $arr['plugin_id'] = $rs['plugin_id'];
            if($rs['end_time']<time()) $rs['end_time'] = time();
            $arr['end_time'] = strtotime('+'.$arr['month'].' months',$rs['end_time']);
        }else{            
            // 初次购买
            $arr['end_time'] = strtotime('+'.$arr['month'].' months',time());
            $arr['status'] = 'wait';
        }
            
        $oPlugins->save($arr);
        
        // 保存购买记录
        $arr['op_user'] = kernel::single('desktop_user')->get_name();
        $oPluginsOrders->insert($arr);
        
        echo('succ'.$arr['plugin_id']);
    }
    
    public function run_hour($arr_param)
    {
        if( date('G')<8 or date('G')>20 ){
            return false;
        }
        
        $plugin = $this->get_desc();
        $oServiceApi = kernel::single('plugins_service_api');
        //插件设置
        $params = json_decode($arr_param['params'],1);
        $conf = $params[$params['type']];
        //$conf['shop_ids'] = $params['shop_id'];
        $conf['plugin_id'] = $arr_param['plugin_id'];
        $conf['send_time'] = intval($conf['select3']) + 8;

        //非选定时间不发送
        if($conf['send_time'] != date('G',time())){
            return false;
        }
            
        switch($params['type'])
        {
            case 'day':
                $rs = $this->_run_day_fun($conf);
                break;
            case 'week':
                $rs = $this->_run_week_fun($conf);
                break;
            case 'month':
                $rs = $this->_run_month_fun($conf);
                break;
        }
        if(!$rs) return false;
        $shops = $this->_get_shop_list($params['shop_id']);
        $mdl_member_analysis = app::get('taocrm')->model('member_analysis');
        $member_obj = app::get('taocrm')->model('members');
        $member_list = $member_obj->getList('name,mobile,member_id',array('member_id|in'=>$rs));
        foreach($shops as $shop)
        {
            $sms_content = $oServiceApi->sms_validate($conf['send_content']);
            $sms_content = str_replace('<{店铺}>',$shop['name'],$sms_content);
            $sms_content = str_replace('<{签名}>',$shop['sms_sign'],$sms_content);
            foreach($member_list as $member) 
            {
                //是否在此店铺中购买物品
                $arr = array(
                    'shop_id'=>$shop['shop_id'],
                    'member_id'=>$member['member_id']
                );
                $is_member = $mdl_member_analysis->dump($arr, 'id');
                if( ! $is_member){
                    continue;
                }

                if(in_array($member['mobile'],$mobile_list)) continue;
                $mobile_list[] = $member['mobile'];
                
                //检测手机号码格式
                if($oServiceApi->mobile_validate($member['mobile']) == 0){
                    continue;
                }
            
                //检测短信黑名单
                if($this->chk_sms_blacklist($member['mobile'], $oServiceApi) == true){
                    continue;
                }

                $send_content = str_replace('<{姓名}>',$member['name'],$sms_content);

                $sms[] = array(
                    'phones' => $member['mobile'],
                    'content' => $send_content
                );
                
                $log_arr = array(
                    'shop_name' => $shop['name'],
                    'shop_id' => $shop['shop_id'],
                    'plugin_name' => $arr_param['plugin_name'],
                    'worker' => $arr_param['plugin_id'],
                    'sms_content' => $send_content,
                    'mobile' => $member['mobile'],
                    'status' => '',
                );
                $oServiceApi->save_sms_log($log_arr);
            }
        }
        
        if($sms){
                    // 分批次发送短信
                    for($i=0;$i<=(count($sms)/$this->sms_page_size);$i++)
                    {
                        $content = array_slice($sms,($i*$this->sms_page_size),$this->sms_page_size);
                        if(!$content) 
                            break;
                        $content = json_encode($content);
                        
                        $send_res = $oServiceApi->_send_sms($content,'cf'.$now_date.'b'.$i, $arr_param['plugin_name']);
                        if($send_res['res'] != 'succ') 
                            break;
                    }
                    
                    // 保存插件运行日志
                    $arr['plugin_id'] = $arr_param['plugin_id'];
                    $arr['plugin_name'] = $arr_param['plugin_name'];
                    $arr['worker'] = $arr_param['worker'];
                    $arr['start_time'] = time();
                    $arr['desc'] = '发送短信数：'.count($mobile_list);
                    $arr['status'] = '成功';
                    $arr['sms_count'] = count($mobile_list);
                    if($send_res['res'] != 'succ') {
                        $arr['status'] = '失败';
                        $arr['desc'] .= '，失败：'.var_export($send_res, true);
                    }
                    $arr['run_key'] = $this->_log_key;//唯一识别码，防止重复运行

                    $oLog = app::get('plugins')->model('log');
                    $oLog->save($arr);
                }

            // 更新插件最后运行时间
            $sql = "update sdb_plugins_plugins set last_run_time=".time()." where plugin_id=".$arr_param['plugin_id']." ";
            $db = kernel::database();
            $db->exec($sql);
            
            return true;
            }

    private function _run_day_fun($param)
    {
        //参数还原
        if($param['select1'] == '0'){
            $param['last_day'] = 0;
        }else{
            $param['last_day'] = intval($param['select2']) + 1;
        }
        unset($param['select1'],$param['select2'],$param['select3']);

        //获取指定的日期
        $birth_month = date('n',strtotime("+{$param['last_day']} day"));
        $birth_day = date('d',strtotime("+{$param['last_day']} day"));
        //判断是否执行过
        $birthday = $birth_month.$birth_day;
        $this->_log_key = date('Y',time()).'_day_'.$birthday;
        $log = $this->_get_plugins_run_log($param['plugin_id'],$this->_log_key);
        if(!empty($log)){
            return false;
        }
        
        $filter = array('b_month'=>$birth_month, 'b_day'=>$birth_day);
        $member_ext_obj = app::get('taocrm')->model('member_ext');
        $member_list = $member_ext_obj->getList('member_id', $filter);
        $member_ids = array();
        foreach($member_list as $item){
            $member_ids[] = $item['member_id'];
        }
        return $member_ids;
    }

    private function _run_week_fun($param)
    {
        //参数还原
        $param['week'] = intval($param['select1']) + 1;
        $param['select'] = $param['select2'] == '1' ? 'next' : 'now';
        unset($param['select1'],$param['select2'],$param['select3']);

        $today = date('N',time());
        if($today != $param['week'])
        {
            return false;
        }
        $next_monday= strtotime("next monday"); 
        $day_7 = 7 * 24 * 60 * 60 -1;
        //获取指定的日期
        if($param['select'] == 'next')
        {
            $birth_month_s = date('n',$next_monday);
            $birth_month_e = date('n',$next_monday + $day_7);
            $birth_day_s = date('d',$next_monday);
            $birth_day_e = date('d',$next_monday + $day_7);
        }elseif($param['select'] == 'now')
        {
            $birth_month_s = date('n',$next_monday - $day_7);
            $birth_month_e = date('n',$next_monday - 1);
            $birth_day_s = date('d',$next_monday - $day_7);
            $birth_day_e = date('d',$next_monday - 1);
        }
        //判断是否执行过
        $this->_log_key = date('Ymd',time()).'_week';
        $log = $this->_get_plugins_run_log($param['plugin_id'],$this->_log_key);
        if(!empty($log)){
            return false;
        }
        
        $member_ext_obj = app::get('taocrm')->model('member_ext');
        $member_list = $member_ext_obj->getList('member_id',array('b_month|bthan'=>$birth_month_s,'b_day|bthan'=>$birth_day_s,'b_month|sthan'=>$birth_month_e,'b_day|sthan'=>$birth_day_e));
        foreach($member_list as $item)
        {
            $member_ids[] = $item['member_id'];
        }
        return $member_ids;
    }

    private function _run_month_fun($param)
    {
        //参数还原
        $param['month_day'] = intval($param['select1']) + 1;
        $param['select'] = $param['select2'] == '1' ? 'next' : 'now';
        unset($param['select1'],$param['select2'],$param['select3']);

        $today = date('j',time());
        
        if($today != $param['month_day'])
        {
            return false;
        }
        //获取指定的日期
        if($param['select'] == 'next')
        {
            $month = date('n',strtotime("next month"));
        }elseif($param['select'] == 'now')
        {
            $month = date('n',time());
        }
        
        //判断是否执行过
        $this->_log_key = date('Ymd',time()).'_month';
        $log = $this->_get_plugins_run_log($param['plugin_id'],$this->_log_key);
        if(!empty($log)){
            return false;
        }
        
        $member_ext_obj = app::get('taocrm')->model('member_ext');
        $member_list = $member_ext_obj->getList('member_id',array('b_month'=>$month));
        foreach($member_list as $item)
        {
            $member_ids[] = $item['member_id'];
        }
        return $member_ids;
    }

    //查询执行日志
    private function _get_plugins_run_log($plugin_id,$key = '')
    {
        $db = kernel::database();
        $sql = "select * from sdb_plugins_log where plugin_id = ".$plugin_id;
        if(!empty($key))
        {
            $sql .= " and run_key='$key' ";
        }
        $rs = $db->select($sql);
        return $rs;
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
