<?php

class market_ctl_admin_bind extends desktop_controller{

    var $pagelimit = 10;
    var $is_debug = false;
    /*
     * 绑定
     */
    public function bind(){

        base_kvstore::instance('steps')->fetch('steps', $steps);  //检测是否绑定短信
        $this->pagedata['steps'] = unserialize($steps);
        $steps=array();
        $db = kernel::database();
        $row = $db->select('select shop_id,shop_bn,name,node_id from sdb_ecorder_shop');
        $steps['shop_checked'] = 0;//默认值申请绑定
        $steps['shop_bind'] = 0;
        $shop_id = 0;
        if($row){
            $steps['shop_bind'] = 1;
            foreach($row as $k=>$v){
                if($v['node_id']){
                    $steps['shop_checked'] = 1;
                }
                $shop_id = $v['shop_id'];
            }
        }

        base_kvstore::instance('market')->fetch('account', $account);  //检测是否绑定短信
        $account = unserialize($account);
        if($account){
            $steps['sms_bind'] = 1;
        }else{
            $steps['sms_bind'] = 0;
        }
        if($account){ //检查是否购买过短信
            $smsinfo = kernel::single('market_edm_utils');
            $info = $smsinfo->get_sms_buy_info($account);
            if($info['res']=='succ'){
                $steps['sms_buy'] = 1;
            }else{
                $steps['sms_buy'] = 0;
            }
        }else{
            $steps['sms_buy'] = 0;
        }
        $this->pagedata['steps']=$steps;
        $this->pagedata['base_url'] = kernel::base_url(1);


        $callback_url = urlencode(kernel::openapi_url('openapi.ome.shop','shop_callback',array('shop_id'=>$shop_id)));
        $app_exclusion = app::get('base')->getConf('system.main_app');
        $app_id = $app_exclusion['app_id'];
        $api_url = urlencode("http://".$_SERVER['HTTP_HOST'].kernel::base_url()."/index.php/api");
        $this->pagedata['bind_url'] = 'index.php?app=ecorder&ctl=admin_shop&act=apply_bindrelation&p[0]='.$app_id.'&p[1]='.$callback_url.'&p[2]='.$api_url;

        base_kvstore::instance('steps')->store('asteps', serialize($steps));  //储存缓存
        $this->display('admin/bind/create_bind.html');
    }

    /*
     * 申请绑定店铺
     */
    public function apply_bindrelation(){
        //查询未绑定的账号
        $app_exclusion = app::get('base')->getConf('system.main_app');
        $app_id = $app_exclusion['app_id'];  //获得app_id
        $db = kernel::database();
        $row = $db->selectrow('select shop_id,shop_bn,name,node_id from sdb_ecorder_shop where node_id=""');
        if($row){
            $shop_id = $row['shop_id'];
        }
        $callback_url = urlencode(kernel::openapi_url('openapi.ome.shop','shop_callback',array('shop_id'=>$shop_id)));
        $api_url = kernel::base_url(true).kernel::url_prefix().'/api';
        $url = kernel::base_url(true).kernel::url_prefix();
        $forword_url = $url.'?app=ecorder&ctl=admin_shop&act=apply_bindrelation&p[0]='.$app_id.'&p[1]='.$callback_url.'&p[2]='.$api_url;
        header("Location: ".$forword_url);
    }

    /*
     * 账号绑定
     */
    public function sms_bind(){

        $this->singlepage('admin/bind/sms_bind.html');
    }

    /*
     *短信购买
     */
    public function sms_buy(){
        base_kvstore::instance('market')->fetch('account', $account);
        if (unserialize($account)) {
            // 免登
            $param = unserialize($account);
            $url = market_sms_utils::get_book_url($param);
            $this->pagedata['frameurl'] = $url;
            $this->singlepage('admin/bind/sms_buy.html');
        }
    }

}