<?php
class ecorder_finder_shop{

    var $detail_basic = "前端店铺详情";
    function detail_basic($shop_id){
        $render = app::get('ecorder')->render();
        $oShop = app::get('ecorder')->model("shop");
        $shop = $oShop->dump($shop_id);
        $shop_type = ecorder_shop_type::get_shop_type();
        $reset_login = "";
        $shoptype = $shop['node_type'];
        $node_id = $shop['node_id'];
        $shop['config'] = unserialize($shop['config']);
        
        $taobao_session = app::get('ecorder')->getConf('taobao_session_'.$node_id);
        $taobao_session = strval($taobao_session);
        $certi_id = base_certificate::get('certificate_id');
        $url = OPENID_URL."?open=taobao&certi_id=".$certi_id."&node_id=".$node_id."&refertype=ecos.taocrm&callback_url=http://".$_SERVER['HTTP_HOST'].kernel::base_url()."/index.php/api";
        if ($shoptype=='taobao' and $node_id and ($taobao_session=='true' or $taobao_session==1)){
            $reset_login = '<a href="'.$url.'" target="_blank"><b>登录授权</b></a>';
        }
        
        $render->pagedata['reset_login'] = $reset_login;
        $render->pagedata['shop']=$shop;
        $render->pagedata['shop_type'] = $shop_type;
        return $render->fetch("admin/system/terminal_detail.html");
    }

    var $detail_analysis = "统计数据";
    function detail_analysis($shop_id){
    
        //重新校对店铺统计数据
        $data = kernel::single('taocrm_service_shop')->countShopBuys($shop_id);
    
        $render = app::get('ecorder')->render();
        $oShopAnalysis = app::get('ecorder')->model("shop_analysis");
        $analysis = $oShopAnalysis->dump($shop_id);
        //付款订单数据
        $analysis['pay_amount'] = $data['pay_amount'] ? $data['pay_amount'] : 0;
        $analysis['pay_orders'] = $data['pay_orders'] ? $data['pay_orders'] : 0;
        $render->pagedata['analysis'] = $analysis;
        return $render->fetch("admin/shop/analysis_detail.html");
    }
    
    var $addon_cols = "shop_id,shop_type,node_id,name";
    var $column_edit = "操作";
    var $column_edit_width = "180";
    var $column_edit_order = COLUMN_IN_HEAD;
    function column_edit($row){
        $finder_id = $_GET['_finder']['finder_id'];
        $shop_type = $row[$this->col_prefix.'shop_type'];
        $node_id = $row[$this->col_prefix.'node_id'];
        $shop_id = $row[$this->col_prefix.'shop_id'];

        //$button1 = '<a href="index.php?app=ecorder&ctl=admin_shop&act=editterminal&p[0]='.$shop_id.'&finder_id='.$finder_id.'" target="_blank">编辑</a>';
        
        $button1 = '<a href="index.php?app=ecorder&ctl=admin_shop&act=editterminal&p[0]='.$shop_id.'&finder_id='.$finder_id.'" target="dialog::{width:700,height:380,title:\'编辑店铺\'}">编辑</a>';
        
        $taobao_session = app::get('ecorder')->getConf('taobao_session_'.$node_id);
        $taobao_session  =  $taobao_session ? $taobao_session : 'false';
        $certi_id = base_certificate::get('certificate_id');
        $api_url = kernel::base_url(true).kernel::url_prefix().'/api';
        $url = OPENID_URL."?open=taobao&certi_id=".$certi_id."&node_id=".$node_id."&refertype=ecos.taocrm&callback_url=".$api_url;
        if ($shop_type=='taobao' and $node_id and ($taobao_session=='false' or $taobao_session=='False' or $taobao_session==false) ){
            $button2 = ' | <a href="'.$url.'" target="_blank">登录授权</a>';
        }
        $callback_url = urlencode(kernel::openapi_url('openapi.ome.shop','shop_callback',array('shop_id'=>$shop_id)));

        $app_exclusion = app::get('base')->getConf('system.main_app');
        $app_id = $app_exclusion['app_id'];
        $api_url = urlencode("http://".$_SERVER['HTTP_HOST'].kernel::base_url()."/index.php/api");
        if (!$node_id) $button3 = ' | <a  target="dialog::{onClose:function(){window.location.reload();},width:700,height:380,title:\'绑定店铺\'}"  href="index.php?app=ecorder&ctl=admin_shop&act=apply_bindrelation&p[0]='.$app_id.'&p[1]='.$callback_url.'&p[2]='.$api_url.'">申请绑定</a>';
        //else $button3 = ' | <a href="index.php?#ctl=shoprelation&act=index&p[0]=accept&p[1]='.$app_id.'&p[2]='.$callback_url.'">解除绑定</a>';
        else $button3 = ' | 已绑定';
        return $button1.$button3.$button2;
    }
    
    var $column_member_prop = "客户自定义属性";
    var $column_member_prop_width = 100;
    var $column_member_prop_order = 10;
    function column_member_prop($row){
        $finder_id = $_GET['_finder']['finder_id'];
        $shop_type = $row[$this->col_prefix.'shop_type'];
        $node_id = $row[$this->col_prefix.'node_id'];
        $shop_id = $row[$this->col_prefix.'shop_id'];
        
        $button1 = '<a href="index.php?app=ecorder&ctl=admin_shop&act=member_prop_edit&p[0]='.$shop_id.'&finder_id='.$finder_id.'" target="dialog::{width:700,height:380,title:\'自定义属性\'}">自定义属性</a>';
        
        return $button1;
    }
}
