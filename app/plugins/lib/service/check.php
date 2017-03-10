<?php
class plugins_service_check{

    // 插件介绍
    public function get_desc(){
        $conf_desc = array(
            'title'=>'体检行业版',
            'worker'=>'plugins_service_check',
            'desc'=>'体检行业专用插件，具体开通请联系客服。',
            'icon'=>'check.png',
            'price'=>array(999999),
            'month'=>array(24),
        	'max_buy_times' => 1,
            'status'=>'disabled',
            'addons'=>'tags',
        );
        
        return $conf_desc;
    }
    
    // 插件可配置项
    public function get_items($sdf){
        $conf_items = array(
            'keys'=>array(
                'label'=>'密钥',
                'type'=>'text',
            ),
        );
        return $conf_items;
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
            // 初次购买
            $arr['end_time'] = strtotime('+'.$arr['month'].' months',time());
            $arr['status'] = 'wait';
            $arr['params'] = json_encode(array('key'=>'12345678'));
            
            $oPlugins->save($arr);
        }
        
        // 保存购买记录
        $arr['op_user'] = kernel::single('desktop_user')->get_name();
        $oPluginsOrders->insert($arr);
        
        echo('<p align=center ><br/><br/>首次运行插件，请<a style="color:red" href="index.php?app=plugins&ctl=admin_manage&act=set&p[0]='.$arr['plugin_id'].'&finder_id='.$finder_id.'" target="dialog::{width:680,height:300,title:\'插件设置\'}">点击这里进行配置</a>！</p>');
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

        $msgid = date('ymdHis').rand(111,999);//对账用唯一识别码
        $pluginAPI = kernel::single('plugins_service_api');
        $smsAPI = kernel::single('market_service_smsinterface');
        $res = $smsAPI->payment($msgid,$amount);
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
    
}
