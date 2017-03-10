<?php
class plugins_ctl_admin_market extends desktop_controller{
    var $workground = 'market.manage';

    public function index(){

        //测试代码
        //kernel::single('plugins_service_api')->run_day();
        //$account = serialize(array('entid'=>'311109000446','password'=>'sms2011'));
        //base_kvstore::instance('market')->store('account', $account);

        if(!$_GET['type']){
            echo '参数出错!';exit;
        }
        $plugins = kernel::single('plugins_market')->plugins;
         
        $this->pagedata['type'] = $_GET['type'];
        $this->pagedata['curr_tab'] = isset($_GET['tab']) ? $_GET['tab'] : 0;
        $this->pagedata['plugins'] = $plugins[$_GET['type']]['list'];
        if(empty($this->pagedata['plugins'] )){
            echo '无规则';exit;
        }
        
        base_kvstore::instance('ecorder')->fetch('default_shop_id',$default_shop_id);

        $shopObj = &app::get('ecorder')->model('shop');
        $shopdata=$shopObj->getList("*");
        $this->pagedata['shopList']=$shopdata;//店铺信息
        $this->pagedata['default_shop_id']=$default_shop_id;
        $this->page('admin/market/list.html');
    }

    function getMemberCounts(){
        if(!$_GET['market_id'] || !$_GET['shop_id']){
            echo '参数出错!';exit;
        }

        echo kernel::single('plugins_market')->getMemberCounts($_GET['market_id'],$_GET['shop_id']);
        exit;
    }

    function save_hits(){
        $arr['market_id'] = $_GET['market_id'];
        $arr['shop_id'] = $_GET['shop_id'];
        $arr['type'] = $_GET['type'];
        $arr['created'] = time();
        $arr['shop_name'] = '';
        ($arr['type'] == 'exec') ? $arr['type']='频率':$arr['type']='热度';

        if(!$arr['shop_id'] || !$arr['market_id']) return false;

        //获取店铺名称
        $oShop = &app::get('ecorder')->model('shop');
        $rs = $oShop->dump($arr['shop_id']);
        $arr['shop_name'] = $rs['name'];

        //保存统计信息
        $oHits = $this->app->model('hits');
        $oHits->insert($arr);

        echo('success');
    }

    function legal_notice(){
        $this->pagedata['hide_agree'] = 'none';
        $this->page('admin/market/legal_copy.html');
    }

    function ad(){
        kernel::single('taocrm_service_redis')->redis->INCR('tgcrm:SYS_AD_CLICK');
        $this->singlepage('admin/market/ad.html');
    }
}
