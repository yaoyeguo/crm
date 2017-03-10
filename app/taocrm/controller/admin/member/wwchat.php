<?php

class taocrm_ctl_admin_member_wwchat extends desktop_controller{

    var $workground = 'taocrm.member';

    public function index()
    {
        $title = '旺旺接待客户列表';
        $actions = '';
        $baseFilter = array();

        $shopObj = app::get(ORDER_APP)->model('shop');
        $shops = array();
        $shopList = $shopObj->getList('shop_id,name');
        foreach((array)$shopList as $v){
            $shops[] = $v['shop_id'];
        }

        $view = (isset($_GET['view'])) ? max(0, intval($_GET['view'])) : 0;
        $baseFilter['shop_id'] = $shops[$view];
        if ($baseFilter['shop_id'] == '') {
            if  (isset($_GET['shop_id'])) {
                $baseFilter['shop_id'] = $_GET['shop_id'];
                unset($_GET['shop_id']);
            }
            else {
                $baseFilter['shop_id'] = $shops[0];
            }
        }

        $this->finder('taocrm_mdl_wangwang_shop_chat_log',array(
            'title'=> $title,
            'base_filter'=>$baseFilter,
        	'orderBy' => 'chat_date desc',
        //'use_buildin_set_tag'=>true,
            'use_buildin_import'=>false,
            'use_buildin_export'=>false,
            'use_buildin_recycle'=>false,
        //暂时去掉高级筛选功能
        //'use_buildin_filter'=>true,
            'use_buildin_tagedit'=>true,
            'use_view_tab'=>true,
        ));
    }

    public function _views()
    {
        $baseFilter = array();
        $shopObj = app::get('ecorder')->model('shop');
        $shopList = $shopObj->getList('shop_id,name');
        $sub_menu = array();
        foreach($shopList as $shop){
            $sub_menu[] = array(
                'label' => $shop['name'],
                'filter' => array('shop_id' => $shop['shop_id']),
                'optional' => false,
            );
        }

        $i = 0;
        $memberObj = &$this->app->model('wangwang_shop_chat_log');
        foreach($sub_menu as $k => $v){
            if (!IS_NULL($v['filter'])){
                $v['filter'] = array_merge($v['filter'], $baseFilter);
            }
            $count = $memberObj->count($v['filter']);
            $sub_menu[$k]['addon'] = $count;
            if (!isset($_GET['view']) && $count > 0) {
                $this->redirect('index.php?app=taocrm&ctl=admin_member_wwchat&act=index&view='. $i++);
                exit;
            }
            $sub_menu[$k]['href'] = 'index.php?app=taocrm&ctl='.$_GET['ctl'].'&act='.$_GET['act'].'&view='.$i++;
            //            $sub_menu[$k]['href'] = 'index.php?app=taocrm&ctl='.$_GET['ctl'].'&act='.$_GET['act'].'&view='.$i++.'&shop_id='.$v['filter']['shop_id'];
        }
        return $sub_menu;
    }
}

