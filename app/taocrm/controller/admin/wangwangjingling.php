<?php

class taocrm_ctl_admin_wangwangjingling extends desktop_controller
{
    //var $workground = 'taocrm.wangwangjingling';
    protected static $shopObj = '';
    protected static $middleware = '';
    protected static $wangwangService = '';
    public $pageSize = 20;
    public $defaultColumns = 5;
    
    public function index()
    {
        $shopModel = app::get('ecorder')->model('shop');
        $shopInfo = $shopModel->getList();
        
        $formatShopInfo = array();
        foreach ($shopInfo as $v) {
            $formatShopInfo[$v['shop_id']] = $v;
        }
        $finderShopInfo = array();
        $i = 0;
        foreach ($shopInfo as $v) {
            $finderShopInfo[$i]['shop_id'] = $v['shop_id'];
            $finderShopInfo[$i]['name'] = $formatShopInfo[$v['shop_id']]['name'];
            $i++;
        }
        
        $shop_id = '';
        if (isset($_GET['shop_id']) && $_GET['shop_id']) {
            $shop_id = $_GET['shop_id'];
        }
        else {
            $shop_id = $finderShopInfo[0]['shop_id'];
        }
        
        $type = isset($_GET['type']) ? intval($_GET['type']) : 0;
        
        if ($type == 1) {
            $wangwang_title = '自定义属性客户';
        }elseif ($type == 0) {
            $wangwang_title = '未下单客户';
        }else{
            $wangwang_title = '客户自定义属性';
        }
        
        $page = isset($_GET['page']) ? max (1, $_GET['page']) : 1;
        $pageSize = $this->pageSize;
        $message = '暂无数据';
        if ($shop_id) {
            $service = $this->getWangWangService();
            $field = $service->getTypeTagField($shop_id, $type);
            $searchFileds = $service->getTypeSearch($shop_id, $type, $field);
            $user_id = kernel::single('desktop_user')->get_id();
            $kv = base_kvstore::instance('taocrm');
            $kv->fetch('wangwang_choise_fields_'.$shop_id.'_'.$type.'_'.$user_id, $choiseFields);
            $filedMore = 'false';
            if ($choiseFields) {
                 $tagFileds = unserialize($choiseFields);
                 $tmpSearchFields = array();
                 foreach ($tagFileds as  $tagfield) {
                     $tmpSearchFields[$tagfield] = $searchFileds[$tagfield];
                 }
                 $filedMore = 'true';
                 $searchFileds = $tmpSearchFields;
            }
            elseif (count($searchFileds) > $this->defaultColumns) {
                $tmpSearchFields = array();
                $i = 0;
                foreach ($searchFileds as $k => $v) {
                    if ($i < $this->defaultColumns) {
                        $tmpSearchFields[$k] = $v;
                        $i++;
                    }
                    else {
                        break;
                    }
                }
                $filedMore = 'true';
                $searchFileds = $tmpSearchFields;
            }
            $this->pagedata['wangwang_field_more'] = $filedMore;
            $this->pagedata['wangwang_field'] = $field;
            $this->pagedata['wangwang_search_fields'] = $searchFileds;
            $this->pagedata['wangwang_field_len'] = count($field);
            $this->pagedata['wangwang_field_len_ext'] = count($field) + 1;
            if ($_POST) {
                $result = $service->getTypeTagAllInfoByShopId($shop_id, $type, $page, $pageSize, $field, $_POST['search']);
                $data = $result['data'];
                $count = $result['count'];
                if (empty($data)) {
                    $message = '没有查询到相关记录';
                }
                $this->pagedata['wangwang_search_field'] = $_POST['search'];
            }
            else {
                $result = $service->getTypeTagAllInfoByShopId($shop_id, $type, $page, $pageSize, $field);
                $data = $result['data'];
                $count = $result['count'];
            }
            $link = "index.php?app=taocrm&ctl=admin_wangwangjingling&act=index&shop_id={$shop_id}&type={$type}&page=%d";
            $total_page = ceil($count / $pageSize);
            $pager = $this->app->render()->ui()->pager( array ('current' => $page, 'total' => $total_page, 'link' => $link ));
            $this->pagedata['wangwang_data'] = empty($data) ? '' : $data;
            $this->pagedata['pager'] = $pager;
        }
        $this->pagedata['wangwang_message'] = $message;
        $this->pagedata['type'] = $type;
        $this->pagedata['shop_id'] = $shop_id;
        $this->pagedata['wangwang_title'] = $wangwang_title;
        $this->pagedata['wangwang_shops'] = $finderShopInfo;
        $this->page('admin/wangwangjingling/member_order_list.html');
    }
    
    public function member()
    {
        $wangwang_title = '自定义属性客户';
        
        $shopModel = app::get('ecorder')->model('shop');
        $shopInfo = $shopModel->getList();
        $formatShopInfo = array();
        foreach ($shopInfo as $v) {
            $formatShopInfo[$v['shop_id']] = $v;
        }
        $finderShopInfo = array();
        $i = 0;
        foreach ($shopInfo as $v) {
            $finderShopInfo[$i]['shop_id'] = $v['shop_id'];
            $finderShopInfo[$i]['name'] = $formatShopInfo[$v['shop_id']]['name'];
            $i++;
        }
        
        $shop_id = '';
        if (isset($_GET['shop_id']) && $_GET['shop_id']) {
            $shop_id = $_GET['shop_id'];
        }
        else {
            $shop_id = $finderShopInfo[0]['shop_id'];
        }
        
        if ($shop_id) {
            $service = $this->getWangWangService();
            $field = $service->getTagField($shop_id);
            $searchFileds = $service->getSearch($shop_id, $field);
            
            $this->pagedata['wangwang_field'] = $field;
            $this->pagedata['wangwang_search_fields'] = $searchFileds;
            $this->pagedata['wangwang_field_len'] = count($field);
            if ($_POST) {
                $data = $service->getTagAllInfoByShopId($shop_id, $field, $_POST['search']);
                $this->pagedata['wangwang_search_field'] = $_POST['search'];
            }
            else {
                $data = $service->getTagAllInfoByShopId($shop_id, $field);
            }
            $this->pagedata['wangwang_data'] = $data;
        }
        
        $this->pagedata['shop_id'] = $shop_id;
        $this->pagedata['wangwang_title'] = $wangwang_title;
        $this->pagedata['wangwang_shops'] = $finderShopInfo;
        $this->page('admin/wangwangjingling/list.html');
//        $actions = array();
//        $baseFilter = array();
//        $shops = $this->getShopFullIds();
//        $view = (isset($_GET['view'])) ? max(0, intval($_GET['view'])) : 0;
//        $baseFilter['shop_id'] = $shops[$view];
//        if ($baseFilter['shop_id'] == '') {
//            if  (isset($_GET['shop_id'])) {
//                $baseFilter['shop_id'] = $_GET['shop_id'];
//                unset($_GET['shop_id']);
//            }
//            else {
//                $baseFilter['shop_id'] = $shops[0];
//            }
//        }
//        $baseFilter['methodName'] = 'SearchMemberAnalysisList';
//        $baseFilter['packetName'] = 'ShopMemberAnalysis';
//        $this->finder('taocrm_mdl_middleware_member_wangwangjingling',array(
//            'title'=> $title,
//            'actions' => $actions,
//            'base_filter'=>$baseFilter,
//            //'use_buildin_set_tag'=>false,
//            'use_buildin_import'=>false,
//            'use_buildin_export'=>false,
//            'use_buildin_recycle'=>false,
//            'use_buildin_filter'=>true,
//            'use_buildin_tagedit'=>true,
//            'use_view_tab'=>true,
//        ));
    }
    
    public function sendinfo()
    {
        if (isset($_GET['shop_id'])) {
            $shopNames = $this->getShopNameId();
            $shop_id = $_GET['shop_id'];
            $shop_name = $shopNames[$shop_id];
            $type = $_GET['type'];
            $search = $_GET['search'];
            $service = $this->getWangWangService();
            $page = 0;
            $pageSize = 0;
            $field = array();
            $result = $service->getTypeTagAllInfoByShopId($shop_id, $type, $page, $pageSize, $field, $search);
            $data = $result['data'];
//            $count = $result['count'];
            $personCount = count($data);
            $memberIds = array();
            foreach ($data as $v) {
                $memberIds[] = $v['member_id'];
            }
//            $queryString = '';
//            foreach ($search as $k => $v) {
//                $queryString .= $k ."=" . $v . "&";
//            }
//            $queryString = trim($queryString, "&");
            $pagedataInfo = array(
                'shop_id' => $shop_id, 
                'shop_name' => $shop_name, 
                'person_count' => $personCount, 
                //'memberids' => $memberIds,
                //'type' => $type,
                //'queryString' => $queryString,
            );
            $user_id = kernel::single('desktop_user')->get_id();
            $kv = base_kvstore::instance('taocrm');
            $kv->store('wangwang_memberids_'.$user_id, $memberIds);
            $this->pagedata['wangwang_info'] = $pagedataInfo;
            $this->pagedata['wangwang_model'] = 'sendinfo';
            $this->display('admin/wangwangjingling/send_info.html');
        }
    }
    
    /**
     * 订单客户控制器
     */
    public function _views_back()
    {
        $baseFilter = array();
        $shopObj = $this->getShopObj();
        $shopList = $shopObj->getList('shop_id,name');
        $sub_menu = array();
        foreach($shopList as $shop){
            $sub_menu[] = array(
                'label' => $shop['name'],
                'filter' => array('shop_id' => $shop['shop_id']),
                'optional' => false,
            );
        }
        $result = $this->getDBAllShopInfo();
        $i = 0;
        foreach($sub_menu as $k => $v){
            if (!IS_NULL($v['filter'])){
                $v['filter'] = array_merge($v['filter'], $baseFilter);
            }
            $count = $result[$v['filter']['shop_id']]['MemberCount'];
            if ($count > 0) {
                $bind = $this->isBind($v['filter']['shop_id']);
                if ($bind == false) {
                    $count = 0;
                }
            }
            $sub_menu[$k]['addon'] = $count;
            if (!isset($_GET['view']) && $count > 0) {
                $this->redirect('index.php?app=taocrm&ctl=admin_wangwangjingling&act=index&view='. $i++);
                exit;
            }
            $sub_menu[$k]['href'] = 'index.php?app=taocrm&ctl='.$_GET['ctl'].'&act='.$_GET['act'].'&view='.$i++;
        }
        return $sub_menu;
    }
    
    /**
     * 更多字段选择
     */
    public function morefields()
    {
        $shop_id = $_REQUEST['shop_id'];
        $type = $_REQUEST['type'];
        $user_id = kernel::single('desktop_user')->get_id();
        $kv = base_kvstore::instance('taocrm');
        $kv->fetch('wangwang_choise_fields_'.$shop_id.'_'.$type.'_'.$user_id, $choiseFields);
        $this->pagedata['tagFiledsSign'] = 'false';
        if ($choiseFields) {
            $tagFileds = unserialize($choiseFields);;
            $this->pagedata['tagfileds'] = $tagFileds;
            $this->pagedata['tagFiledsSign'] = 'true';
        }
        $service = $this->getWangWangService();
        $fields = $service->getTypeTagField($shop_id, $type);
        $fields = $service->getTagFields($fields);
        if ($_POST) {
            $this->begin('index.php?app=taocrm&ctl=admin_wangwangjingling&act=index&type='.$type.'&shop_id='.$shop_id);
            if (isset($_POST['checkbox'])) {
                if (count($_POST['checkbox']) > $this->defaultColumns) {
                    $this->end(false, '最多能选择' . $this->defaultColumns . '个过滤字段');
                }
                else {
                    $kv->store('wangwang_choise_fields_'.$shop_id.'_'.$type.'_'.$user_id, serialize($_POST['checkbox']));
                }
                
            }
            $this->end(true, '保存成功');
        }
        $this->pagedata['tdNums'] = 5;
        $this->pagedata['shop_id'] = $shop_id;
        $this->pagedata['type'] = $type;
        $this->pagedata['choice_fields'] = $fields;
        $this->display('admin/wangwangjingling/morefields.html');
    }
    
    //查看是否绑定店铺到旺旺精灵
    private function isBind($shop_id)
    {
        $wangwangService = $this->getWangwangService();
        return $wangwangService->isBind($shop_id);
    }
    
    private function getWangWangService()
    {
        if (self::$wangwangService == '') {
            self::$wangwangService = kernel::single('taocrm_wangwangjingling_service');
        }
        return self::$wangwangService;
    } 
    
    /**
     * 获得所有店铺订单数量及客户数量
     * Enter description here ...
     */
    private function getDBAllShopInfo()
    {
        self::$middleware = kernel::single('taocrm_middleware_connect');
        $data = json_decode(self::$middleware->DBAllShopInfo(), true);
        return $data;
    }
    
    private function getShopObj()
    {
        if (self::$shopObj == '') {
            self::$shopObj = &app::get(ORDER_APP)->model('shop');
        }
        return self::$shopObj;
    }
    
    private function getShopFullIds()
    {
        $model = $this->getShopObj();
        $shopList = $model->getList('shop_id,name');
        $shops = array();
        foreach ((array)$shopList as $v) {
            $shops[] = $v['shop_id'];
        }
        return $shops;
    }
    
    private function getShopNameId()
    {
        $model = $this->getShopObj();
        $shopList = $model->getList('shop_id,name');
        $shops = array();
        foreach ((array)$shopList as $v) {
            $shops[$v['shop_id']] = $v['name'];
        }
        return $shops;
    }
}