<?php
class taocrm_ctl_admin_member extends desktop_controller{

    //var $workground = 'taocrm.member';
    var $token = 'iloveshopex';
    protected static $middleware = '';
    protected static $shopObj = '';

    public function index()
    {
        $title = '店铺客户列表';
        $actions = '';
        $baseFilter['methodName'] = 'SearchMemberAnalysisByShop';
        $baseFilter['packetName'] = 'ShopMemberAnalysis';
        $actions = $this->_action();
        $this->finder('taocrm_mdl_member_analysis',array(
            'title'=> $title,
            'actions' => $actions,
            'base_filter'=>$baseFilter,
            'orderBy' => '',//去掉默认排序
            //'use_buildin_set_tag'=>true,
            'use_buildin_import'=>false,
            'use_buildin_export'=>false,
            'use_buildin_recycle'=>false,
            //'use_buildin_filter'=>true,//暂时去掉高级筛选功能
            'use_buildin_tagedit'=>true,
            'use_view_tab'=>true,
        ));
    }

    public function _views()
    {
        $baseFilter = array();
        $shopObj = $this->getShopObj();
        $filter = array();
        $shopList = $shopObj->get_shops('no_fx');
        $sub_menu = array();
        $default_shop_id = '';
        foreach($shopList as $shop){        
            if(!$default_shop_id)
                $default_shop_id = $shop['shop_id'];
            $sub_menu[] = array(
                'label' => $shop['name'],
                'filter' => array('shop_id' => $shop['shop_id']),
                'optional' => false,
                'display' => true,
            );
        }

        /**
         *  getDBAllShopInfo 计算每个店铺对应的客户数
         *  返回数据格式：
         *  array('shop_id'=>array('MemberCount'=>123))
         */
        //$result = $this->getDBAllShopInfo();
        $i = 0;
        foreach($sub_menu as $k => $v){
            if (!IS_NULL($v['filter'])){
                $v['filter'] = array_merge($v['filter'], $baseFilter);
            }
            //$count = $result[$v['filter']['shop_id']]['MemberCount'];
            $count = 0;
            $sub_menu[$k]['addon'] = $count;
            if (!isset($_GET['view'])) {
                $this->redirect('index.php?app=taocrm&ctl=admin_member&act=index&shop_id='.$default_shop_id.'&view='.$i++);
                exit;
            }
            $sub_menu[$k]['href'] = 'index.php?app=taocrm&ctl='.$_GET['ctl'].'&act='.$_GET['act'].'&shop_id='.$v['filter']['shop_id'].'&view='. $i++;
        }
        return $sub_menu;
    }

    //客户接待
    public function phone_search()
    {
        $this->page('admin/member/phone_search.html');
    }

    protected function _action()
    {
        $actions =  array();


        array_push(
        $actions,
        array(
                'label'=>'创建短信活动',
                'submit'=>'index.php?app=market&ctl=admin_active_sms&act=create_active&create_source=members&send_method=sms&memlist=1&shop_id= '. trim($_GET['shop_id']),
                'target'=>'dialog::{width:700,height:350,title:\'创建短信活动\'}'
                )
                );

                $a1 = array(
            'label'=>'加入短信黑名单',
            'submit'=>'index.php?app=taocrm&ctl=admin_member&act=sms_blickname',
                );
                $a2 = array(
            'label'=>'加入邮件黑名单',
            'submit'=>'index.php?app=taocrm&ctl=admin_member&act=edm_blickname',
                );
                $a3 = array(
            'label'=>'加入贵宾组',
            'submit'=>'index.php?app=taocrm&ctl=admin_member&act=addvip',
                );
                $a4 = array(
            'label'=>'客户标签',
            'submit'=>'index.php?app=taocrm&ctl=admin_member&act=addTag',
            'target'=>'dialog::{width:650,height:355,title:\'客户标签\'}'
            );
            $a5 = array(
                'label'=>'批量删除',
                'submit'=>'index.php?app=taocrm&ctl=admin_member&act=batch_delete_member',
                'target'=>'dialog::{width:650,height:200,title:\'批量删除\'}'
            );

            array_push($actions, $a1, $a2, $a3, $a4,$a5);
            //array_push($actions, $a1, $a2, $a3);

            return $actions;
    }

    protected function getShopObj()
    {
        if (self::$shopObj == '') {
            self::$shopObj = app::get(ORDER_APP)->model('shop');
        }
        return self::$shopObj;
    }

    public function addTag()
    {
        if(isset($_POST['id']) && $_POST['id']){
            $member_ids = $_POST['id'];
        }elseif(isset($_POST['member_id']) && $_POST['member_id']){
            $member_ids = $_POST['member_id'];
        }else{
            $member_ids = $_GET['id'];
        }

        $oTag= &$this->app->model('member_tag');
        $this->pagedata['taglist'] = $oTag->getTagList();
        $this->pagedata['member_ids'] = implode(',', $member_ids);

        //获取当前客户的标签
        $oTag= &$this->app->model('member_tag');
        $tags = $oTag->getTagsByMember($member_ids);
        $this->pagedata['tags'] = '0,'.implode(',',$tags).',0';

        //var_dump( $this->pagedata['taglist']);exit;
        $this->display('admin/member/tag/add.html');
    }

    public function saveMemberTag()
    {
        $oTag= $this->app->model('member_tag');
        $this->begin();
        $member_ids = trim($_POST['member_ids']);
        $tag_ids = trim($_POST['tag_ids']);
        $old_tag_ids = trim($_POST['old_tag_ids']);
        if($member_ids){
            $member_ids = explode(',', $member_ids);
            $tag_ids ? $tag_ids=explode(',', $tag_ids) : $tag_ids=false;
            $old_tag_ids = explode(',', $old_tag_ids);
            $oTag->saveMemberTag($member_ids, $tag_ids, $old_tag_ids);
            $this->end(true,app::get('taocrm')->_('操作成功'));
        }else{
            $this->end(false,app::get('taocrm')->_('没有选择客户'));
        }
    }

    protected function getShopFullIds()
    {
        $model = $this->getShopObj();
        $shopList = $model->get_shops('no_fx');
        $shops = array();
        foreach ((array)$shopList as $v) {
            $shops[] = $v['shop_id'];
        }
        return $shops;
    }

    public function index_back()
    {
        $active_id=trim($_GET['active_id']);

        //参加活动的客户数
        if ($_GET[ac_nums]==1){
            $acnumobj=app::get('market')->model('active_assess');
            $assess_data=$acnumobj->dump(array('active_id'=>$active_id));
            $member_ids=unserialize($assess_data['active_members']);
            if ($member_ids){
                $base_filter['member_id'] = $member_ids;
            }
        }

        //活动付款客户数
        if ($_GET[pay_nums]==1){
            $acnumobj=app::get('market')->model('active_assess');
            $reobj=kernel::single("market_finder_active_assess");
            $assess_data=$acnumobj->dump(array('active_id'=>$active_id));
            $member_idss=unserialize($assess_data['active_members']);
            $member_ids=$reobj->re_nums($member_idss,$assess_data['exec_time'],1);
            if ($member_ids){
                $base_filter['member_id'] = $member_ids;
            }
        }

        //活动二次购买
        if($_GET[ac_nums]==2){
            $acnumobj=app::get('market')->model('active_assess');
            $reobj=kernel::single("market_finder_active_assess");
            $assess_data=$acnumobj->dump(array('active_id'=>$active_id));
            $member_idss=unserialize($assess_data['active_members']);
            $member_ids=$reobj->re_nums($member_idss,$assess_data['exec_time']);
            if ($member_ids){
                $base_filter['member_id'] = $member_ids;
            }
        }

        //交易完成的客户数量
        if ($_GET[finish_nums]==1){
            $acnumobj=app::get('market')->model('active_assess');
            $reobj=kernel::single("market_finder_active_assess");
            $assess_data=$acnumobj->dump(array('active_id'=>$active_id));
            $member_idss=unserialize($assess_data['active_members']);
            $member_ids=$reobj->re_nums($member_idss,$assess_data['exec_time'],1,'finish');
            if ($member_ids){
                $base_filter['member_id'] = $member_ids;
            }
        }

        //对照组客户
        if ($_GET[c_nums]==1){
            $acnumobj=app::get('market')->model('active_assess');
            $assess_data=$acnumobj->dump(array('active_id'=>$active_id));
            $member_ids=unserialize($assess_data['con_members']);
            if ($member_ids){
                $base_filter['member_id'] = $member_ids;
            }
        }

        //对照组二次购买
        if ($_GET[c_nums]==2){
            $acnumobj=app::get('market')->model('active_assess');
            $reobj=kernel::single("market_finder_active_assess");
            $assess_data=$acnumobj->dump(array('active_id'=>$active_id));
            $member_idss=unserialize($assess_data['con_members']);
            $member_ids=$reobj->re_nums($member_idss,$assess_data['exec_time']);
            if ($member_ids){
                $base_filter['member_id'] = $member_ids;
            }
        }

        //对照组付款
        if ($_GET[pay_nums]==2){
            $acnumobj=app::get('market')->model('active_assess');
            $reobj=kernel::single("market_finder_active_assess");
            $assess_data=$acnumobj->dump(array('active_id'=>$active_id));
            $member_idss=unserialize($assess_data['con_members']);
            $member_ids=$reobj->re_nums($member_idss,$assess_data['exec_time'],1);
            if ($member_ids){
                $base_filter['member_id'] = $member_ids;
            }
        }

        //交易完成人数
        if ($_GET[finish_nums]==2){
            $acnumobj=app::get('market')->model('active_assess');
            $reobj=kernel::single("market_finder_active_assess");
            $assess_data=$acnumobj->dump(array('active_id'=>$active_id));
            $member_idss=unserialize($assess_data['con_members']);
            $member_ids=$reobj->re_nums($member_idss,$assess_data['exec_time'],1,'finish');
            if ($member_ids){
                $base_filter['member_id'] = $member_ids;
            }
        }

        //自定义标签
        $tagObj = app::get('desktop')->model('tag');
        $tags = $tagObj->getList('tag_id,tag_name',array('app_id'=>'taocrm','tag_type'=>'member_analysis'));
        foreach($tags as $tag){
            $filter = array();
            $href = '';
            $filter['tag'] = $tag['tag_id'];
            $filter = urlencode(serialize($filter));
            $href = 'index.php?app=taocrm&ctl=admin_member&act=index&view='.$_GET['view'].'&filter='.$filter;
            $group[] = array('label'=>$tag['tag_name'],'href'=>$href);
        }

        $shopObj = app::get(ORDER_APP)->model('shop');
        $shopList = $shopObj->getList('shop_id,name');
        foreach((array)$shopList as $v){
            $shops[] = $v['shop_id'];
        }
        $view = (isset($_GET['view']) && intval($_GET['view']) >= 0 ) ? intval($_GET['view']) : 0;
        if ($_GET['view']!=0){
            $view=$view-1;
            $shop_id =$shops[$view];
        }

        //将shop_id转换成view
        if($_GET['shop_id'] && $view==0) {
            $shop_id = $_GET['shop_id'];
            $_GET['view'] = array_search($shop_id,$shops) + 1;
        }

        $actions =  array(
        array(
                'label' => '标签选择',
                'icon' => 'batch.gif',
                'group' => $group
        ),
        );
        if($_GET['view']){
            array_push($actions,array(
                'label'=>'创建短信活动',
                'submit'=>'index.php?app=market&ctl=admin_active&act=create_active&create_source=members&send_method=sms&memlist=1&shop_id= '. $shop_id,
                'target'=>'dialog::{width:700,height:350,title:\'创建短信活动\'}'
                )
                /*
                array(
                'label'=>'创建邮件活动',
                'submit'=>'index.php?app=market&ctl=admin_active&act=create_active&send_method=edm&memlist=1&shop_id= '. $shop_id,
                'target'=>'dialog::{width:700,height:350,title:\'创建邮件活动\'}'
                )*/
            );
        }
        array_push($actions,
        array(
                'label'=>'加入短信黑名单',
                'submit'=>'index.php?app=taocrm&ctl=admin_member&act=sms_blickname',
        ),
        array(
	            'label'=>'加入邮件黑名单',
	            'submit'=>'index.php?app=taocrm&ctl=admin_member&act=edm_blickname',
        ),
        array(
        	'label'=>'加入贵宾组',
            'submit'=>'index.php?app=taocrm&ctl=admin_member&act=addvip',
        ));
        $this->finder('taocrm_mdl_member_analysis',array(
            'title'=>'客户列表',
            'actions' => $actions,
            'base_filter'=>$base_filter,
            'use_buildin_set_tag'=>true,
            'use_buildin_import'=>false,
            'use_buildin_export'=>false,
            'use_buildin_recycle'=>false,
            'use_buildin_filter'=>true,
            'use_buildin_tagedit'=>true,
            'use_view_tab'=>true,
        ));
    }

    function _views_back(){
        $memberObj = $this->app->model('member_analysis');
        $base_filter = array();
        $group_id = intval($_GET['group_id']);
        $shop_id = trim($_GET['shop_id']);
        $active_id=trim($_GET['active_id']);
        $sub_menu[] = array(
            'label'=> '全部',
            'filter'=> array(),
            'optional'=>false,
        );
        if($group_id>0) {
            $member_ids = $this->app->model('member_group')->getMemberList($group_id);
            if($member_ids) {
                $base_filter['member_id'] = $member_ids;
            }else{
                $base_filter['member_id'] = '0';
            }
        }

        //活动客户数
        if ($_GET[ac_nums]==1){
            $acnumobj=app::get('market')->model('active_assess');
            $assess_data=$acnumobj->dump(array('active_id'=>$active_id));
            $member_ids=unserialize($assess_data['active_members']);
            if ($member_ids){
                $base_filter['member_id'] = $member_ids;
            }else {
                $base_filter['member_id'] = '0';
            }
        }
        //判断活动二次购买的客户数
        if($_GET[ac_nums]==2){
            $acnumobj=app::get('market')->model('active_assess');
            $reobj=kernel::single("market_finder_active_assess");
            $assess_data=$acnumobj->dump(array('active_id'=>$active_id));
            $member_idss=unserialize($assess_data['active_members']);
            $member_ids=$reobj->re_nums($member_idss,$assess_data['exec_time']);
            if ($member_ids){
                $base_filter['member_id'] = $member_ids;
            }else {
                $base_filter['member_id'] = '0';
            }
        }
        //活动下单并且付款的用户数
        if ($_GET[pay_nums]==1){
            $acnumobj=app::get('market')->model('active_assess');
            $reobj=kernel::single("market_finder_active_assess");
            $assess_data=$acnumobj->dump(array('active_id'=>$active_id));
            $member_idss=unserialize($assess_data['active_members']);
            $member_ids=$reobj->re_nums($member_idss,$assess_data['exec_time'],1);
            if ($member_ids){
                $base_filter['member_id'] = $member_ids;
            }else {
                $base_filter['member_id'] = '0';
            }
        }
        if ($_GET[finish_nums]==2){
            $acnumobj=app::get('market')->model('active_assess');
            $reobj=kernel::single("market_finder_active_assess");
            $assess_data=$acnumobj->dump(array('active_id'=>$active_id));
            $member_idss=unserialize($assess_data['active_members']);
            $member_ids=$reobj->re_nums($member_idss,$assess_data['exec_time'],1,'finish');
            if ($member_ids){
                $base_filter['member_id'] = $member_ids;
            }else {
                $base_filter['member_id'] = 0;

            }
        }
        //对照组客户数
        if ($_GET[c_nums]==1){
            $acnumobj=app::get('market')->model('active_assess');
            $assess_data=$acnumobj->dump(array('active_id'=>$active_id));
            $member_ids=unserialize($assess_data['con_members']);
            if ($member_ids){
                $base_filter['member_id'] = $member_ids;
            }else {
                $base_filter['member_id'] = '0';
            }
        }

        //对照组二次购买的客户
        if ($_GET[c_nums]==2){
            $acnumobj=app::get('market')->model('active_assess');
            $reobj=kernel::single("market_finder_active_assess");
            $assess_data=$acnumobj->dump(array('active_id'=>$active_id));
            $member_idss=unserialize($assess_data['con_members']);
            $member_ids=$reobj->re_nums($member_idss,$assess_data['exec_time']);
            if ($member_ids){
                $base_filter['member_id'] = $member_ids;
            }else {
                $base_filter['member_id'] = '0';
            }
        }

        //对照组下单并且付款
        if ($_GET[pay_nums]==2){
            $acnumobj=app::get('market')->model('active_assess');
            $reobj=kernel::single("market_finder_active_assess");
            $assess_data=$acnumobj->dump(array('active_id'=>$active_id));
            $member_idss=unserialize($assess_data['con_members']);
            $member_ids=$reobj->re_nums($member_idss,$assess_data['exec_time'],1);
            if ($member_ids){
                $base_filter['member_id'] = $member_ids;
            }else {
                $base_filter['member_id'] = '0';
            }
        }

        if ($_GET[finish_nums]==2){
            $acnumobj=app::get('market')->model('active_assess');
            $reobj=kernel::single("market_finder_active_assess");
            $assess_data=$acnumobj->dump(array('active_id'=>$active_id));
            $member_idss=unserialize($assess_data['con_members']);
            $member_ids=$reobj->re_nums($member_idss,$assess_data['exec_time'],1,'finish');
            if ($member_ids){
                $base_filter['member_id'] = $member_ids;
            }else {
                $base_filter['member_id'] = 0;

            }
        }

        $shopObj = app::get(ORDER_APP)->model('shop');
        $shopList = $shopObj->getList('shop_id,name');
        foreach($shopList as $shop){
            $sub_menu[] = array(
                'label'=>$shop['name'],
                'filter'=>array('shop_id'=>$shop['shop_id']),
                'optional'=>false,
            );
        }
        $i=0;
        //$i = 1;
        $sum = 0;
        foreach($sub_menu as $k=>$v){
            if ($k == 0) continue;
            if (!IS_NULL($v['filter'])){
                $v['filter'] = array_merge($v['filter'],$base_filter);
            }
            $count =$memberObj->count($v['filter']);
            //$sub_menu[$k]['filter'] = $v['filter']?$v['filter']:null;
            //$count = $this->getMemberCount( $sub_menu[$k]['filter']);
            $sub_menu[$k]['addon'] = $count;
            $sub_menu[$k]['href'] = 'index.php?app=taocrm&ctl='.$_GET['ctl'].'&act='.$_GET['act'].'&view='.$i++;
            $sum += $count;
        }

        $sub_menu[0]['filter'] = null;
        $sub_menu[0]['addon'] = $sum;
        $sub_menu[0]['href'] = 'index.php?app=taocrm&ctl='.$_GET['ctl'].'&act='.$_GET['act'].'&view=0';
        return $sub_menu;
    }

    protected function getMemberCount($filter)
    {
        $count = 0;
        if ($filter != '') {
            if (isset($filter['shop_id']) && $filter['shop_id']) {
                if (self::$middleware == null) {
                    self::$middleware = kernel::single('taocrm_middleware_connect');
                    //self::$middleware->testAddDBIndex();
                }
                $data = array();
                $data['shop_id'] = $filter['shop_id'];
                unset($filter['shop_id']);
                $data['filter'] = $filter;
                $result = json_decode(self::$middleware->SearchMemberAnalysisList($data), true);
                if ($result) {
                    $count = $result['Count'];
                }
            }

        }
        return $count;
    }

    /**
     * 获得所有店铺订单数量及客户数量
     * Enter description here ...
     */
    protected function getDBAllShopInfo()
    {
        self::$middleware = kernel::single('taocrm_middleware_connect');
        //$data = json_decode(self::$middleware->DBAllShopInfo(), true);
        $filter = array('status'=>1);
        $data = self::$middleware->DBAllShopInfo($filter);
        return $data;
    }

    //加入短信黑名单
    public function sms_blickname()
    {
        $this->begin();
        //        $member_analysisObj = app::get(taocrm)->model('member_analysis');
        //        $memberid_array=$member_analysisObj->getList('member_id',array('id|in'=>$_POST['id']));
        //        $member_id=array();
        //        foreach ($memberid_array as $k=>$v) {
        //            $member_id[]=$v['member_id'];
        //        }
        $memberObj = app::get(taocrm)->model('members');
        //        $filter=array('member_id|in'=>$member_id);
        if($_POST['id']){
            $filter=array('member_id|in'=> $_POST['id']);
        }else{
            $filter=array('member_id|in'=> $_POST['member_id']);
        }
        $rs=$memberObj->update(array('sms_blacklist'=>TRUE), $filter);
        if ($rs){
            $this->end(true,'成功加入短信黑名单');
        }
    }

    //加入邮件黑名单
    public function edm_blickname()
    {
   	    $this->begin();
   	    //   	    $member_analysisObj = app::get(taocrm)->model('member_analysis');
   	    //   	    $memberid_array=$member_analysisObj->getList('member_id',array('id|in'=>$_POST['id']));
   	    //   	    $member_id=array();
   	    //   	    foreach ($memberid_array as $k=>$v) {
   	    //   	        $member_id[]=$v['member_id'];
   	    //   	    }
   	    $memberObj = app::get(taocrm)->model('members');
   	    //   	    $filter=array('member_id|in'=>$member_id);
   	    $filter=array('member_id|in'=>$_POST['id']);
   	    $rs=$memberObj->update(array('edm_blacklist'=>TRUE), $filter);
   	    if ($rs){
   	        $this->end(true,'成功加入邮件黑名单');
   	    }
    }

    //加入vip
    public function addvip(){
        $this->begin();
        $member_analysisObj = app::get(taocrm)->model('member_analysis');
        //        $memberid_array=$member_analysisObj->getList('member_id',array('id|in'=>$_POST['id']));
        //        $member_id=array();
        //        foreach ($memberid_array as $k=>$v) {
        //            $member_id[]=$v['member_id'];
        //        }
        $memberObj = app::get(taocrm)->model('members');
        //        $filter=array('member_id|in'=>$member_id);
        if($_POST['id']){
            $filter=array('member_id|in'=>$_POST['id']);
        }else{
            $filter=array('member_id|in'=>$_POST['member_id']);
        }
        $member_analysisObj->update(array('is_vip'=>TRUE), $filter);
        $rs=$memberObj->update(array('is_vip'=>TRUE), $filter);
        if ($rs){
            $this->end(true,'成功加入贵宾组');
        }
    }

    public function createActive(){
        $newFilter = $_POST;
        unset($newFilter['app']);
        unset($newFilter['ctl']);
        unset($newFilter['act']);
        unset($newFilter['_finder']);
        unset($newFilter['marketable']);
        unset($newFilter['_DTYPE_BOOL']);

        $memberObj = $this->app->model('members');
        $shopObj = app::get(ORDER_APP)->model('shop');

        if($newFilter['member_id'][0] && $newFilter['member_id'][0]>0){
            $memberInfo = $memberObj->dump($newFilter['member_id'][0]);
            $shop_id = $memberInfo['shop_id'];
            $filter['member_id'] = $newFilter['member_id'];
        }elseif($newFilter['isSelectedAll'] && $newFilter['isSelectedAll'] == '_ALL_'){
            if(isset($newFilter['view']) && $newFilter['view']>=0){
                $views = $this->_views();
                $view_filter = (array)$views[$newFilter['view']]['filter'];
                $shop_id = $view_filter['shop_id'];
                $filter['isSelectedAll'] = '_ALL_';
                $filter['shop_id'] = $shop_id;
            }else{
                $shopList = $shopObj->getList('shop_id,name');
                foreach($shopList as $shop){
                    $count = $memberObj->count(array('shop_id'=>$shop['shop_id']));
                    if($count && $count>0){
                        $shop_id = $shop['shop_id'];
                        break;
                    }
                }
                $filter['isSelectedAll'] = '_ALL_';
                $filter['shop_id'] = $shop_id;
            }
        }
        $groupObj = &$this->app->model('message_themes_group');
        $this->pagedata['groupList'] = $groupObj->getList('group_id,group_title');
        $this->pagedata['shop'] = $shopObj->dump($shop_id);
        $this->pagedata['filter'] = htmlspecialchars(serialize($filter));
        $this->display('admin/member/create_active.html');
    }

    public function toCreateActive(){
        $this->begin('');
        $data['active_name'] = $_POST['active_name'];
        $data['shop_id'] = $_POST['shop_id'];
        $data['coupon_id'] = $_POST['coupon_id'];
        $data['theme_id'] = $_POST['theme'];
        $data['createtime'] = time();
        $filter = unserialize($_POST['filter']);

        if(!$data['active_name'] || $data['active_name']==''){
            $this->end(false,'活动名称不能为空！');
        }

        if(!$data['coupon_id'] && !$data['theme_id']){
            $this->end(false,'请确定促销方案！');
        }

        $activeObj = app::get('taocrm')->model('active');
        $acMemObj = app::get('taocrm')->model('active_member');
        if($activeObj->save($data)){
            $active_id = $data['active_id'];
            if($filter['isSelectedAll'] && $filter['isSelectedAll'] == '_ALL_'){
                $memberObj = $this->app->model('members');
                $members = $memberObj->getList('member_id',array('shop_id'=>$shop['shop_id']));
                foreach($members as $member){
                    $acMem['active_id'] = $active_id;
                    $acMem['member_id'] = $member['member_id'];
                    $acMemObj->insert($acMem);
                }
            }else{
                foreach($filter['member_id'] as $val){
                    $acMem['active_id'] = $active_id;
                    $acMem['member_id'] = $val;
                    $acMemObj->insert($acMem);
                }
            }
            $this->end(true, '活动创建成功');
        }else{
            $this->end(false, '活动创建失败');
        }
    }

    public function buildCustomerSmsByMember() {
        $newFilter = $_POST;
        unset($newFilter['app']);
        unset($newFilter['ctl']);
        unset($newFilter['act']);
        unset($newFilter['_finder']);
        unset($newFilter['marketable']);
        unset($newFilter['_DTYPE_BOOL']);
        $memberObj = $this->app->model('members');
        $shopObj = app::get(ORDER_APP)->model('shop');

        $filter = array();
        $shop_id = trim($_GET['shop_id']);
        $filter['shop_id'] = $shop_id;

        if ($newFilter['member_id'][0] && $newFilter['member_id'][0]>0) {
            $filter['member_id'] = $newFilter['member_id'];
        }
        elseif ($newFilter['isSelectedAll'] && $newFilter['isSelectedAll'] == '_ALL_') {
            if (isset($_GET['group_id'])) {
                $filter['group_id'] = $_GET['group_id'];
            }
            $filter['isSelectedAll'] = '_ALL_';
        }

        $this->pagedata['shop'] = $shopObj->dump($shop_id);
        $this->pagedata['filter'] = htmlspecialchars(serialize($filter));

        $groupList = $this->app->model('message_themes_group')->getList('*');
        $this->pagedata['groupList'] = $groupList;
        $this->display('admin/member/buildCustomerSms.html');
    }

    public function sendCustomerSms() {
        $data = $_POST;
        $shop = app::get('ecorder')->model('shop')->dump(array('shop_id' => $data['shop_id']));
        $memberObj = $this->app->model('members');
        $memberGroupDataObj = $this->app->model('member_group_data');
        $filter = unserialize($data['filter']);

        if ($filter['isSelectedAll'] && $filter['isSelectedAll'] == '_ALL_') {
            if ($filter['group_id']) {
                $memberGroupData = $memberGroupDataObj->getList('member_id', array('group_id' => $filter['group_id']));
                $memberIds = array();
                foreach ($memberGroupData as $value) {
                    $memberIds[] = $value['member_id'];
                }
                $members = $memberObj->getList('member_id, uname, shop_id, mobile', array('member_id|in' => $memberIds));
            }
            else {
                $members = $memberObj->getList('member_id, uname, shop_id, mobile', array('shop_id' => $shop['shop_id']));
            }
        }
        else {
            $members = $memberObj->getList('member_id, uname, shop_id, mobile', array('member_id|in' => $filter['member_id']));
        }
        $members = $this->_array_unique_fb($members);

        $this->begin();
        $queue = app::get('base')->model('queue');
        $memberGroups = array_chunk($members, 30);
        foreach ($memberGroups as $memberGroup) {
            $msgs = array();
            foreach ($memberGroup as $member) {
                if (!$member['mobile']) {
                    continue;
                }
                $message = $this->_buildMessage($shop, $member, $data['smsContent']);
                $msgs[] = array(
					'phones' => array($member['mobile']),
					'content' => $message
                );
            }
            $queueData = array (
				'queue_title' => '自定义短信',
	            'start_time' => time(),
		            'params' => array(
		            	'msgs' => $msgs
            ),
	            'worker'=>'taocrm_queue.sendSms',
            );

            if (!$queue->insert($queueData)) {
                $this->end(false, '操作失败!');
            }
        }
        $logData = array(
			'type' => '1',
			'shop_id' => $shop['shop_id'],
			'log' => '客户列表处手动发送短信' . serialize($members),
			'optime' => time(),
        );
        $this->app->model('sms_log')->insert($logData);
        $this->end(true, '操作成功!');
    }

    /**
     *
     * 把短信模板转变为具体的短信内容
     * @order array 一条订单
     * @smsTemplate string 一个短信模板
     */
    private function _buildMessage($shop, $member, $smsTemplate) {
        $message = str_replace(array('<{$uname}>', '<{$shop}>'), array($member['uname'], $shop['name']), $smsTemplate);
        return $message;
    }

    public function import()
    {
        //$this->workground = 'taocrm.member';
        /*
         $shopObj = app::get(ORDER_APP)->model('shop');
         $filter = array('disabled'=>'false','shop_type'=>'taobao');
         $shopList = $shopObj->getList('shop_id,name',$filter);
         $this->pagedata['shopList'] = $shopList;
         $this->page('admin/member/member_import.html');
         die();
         */
        $this->pagedata['import_tools_download_url'] = kernel::base_url(true).'/CRMM导入工具.zip?v='.rand(1111,9999);
        //$this->pagedata['import_host']  = 'http://' . $_SERVER['SERVER_NAME'];
        $this->pagedata['import_host']  = kernel::base_url(true);
        $this->pagedata['import_pwd']  = md5($_SERVER['SERVER_NAME'].$this->token);
        $this->pagedata['token']  = base_certificate::get('token');
        $this->page('admin/member/orders_import_tools.html');
    }

    public function order_status($status){
        switch ($status){
            case '交易成功':
                $order_status['is_delivery'] = 'Y';
                $order_status['pay_status'] = 1;
                $order_status['status'] = 'finish';
                $order_status['ship_status'] = 1;
                break;
            case '买家已付款，等待卖家发货':
                $order_status['is_delivery'] = 'N';
                $order_status['pay_status'] = 1;
                $order_status['status'] = 'active';
                $order_status['ship_status'] = 0;
                break;
            case '卖家已发货，等待买家确认':
                $order_status['is_delivery'] = 'Y';
                $order_status['pay_status'] = 1;
                $order_status['status'] = 'active';
                $order_status['ship_status'] = 1;
                break;
            case '交易关闭':
                $order_status['is_delivery'] = 'N';
                $order_status['pay_status'] = 0;
                $order_status['status'] = 'dead';
                $order_status['ship_status'] = 0;
                break;
        }
        return $order_status;
    }

    public function order_detail($file){

        $tmpFileHandle = fopen($file['tmp_name'],"r");

        $i=0;
        while($row = fgetcsv($tmpFileHandle, 1000, ",")){
            foreach( $row as $num => $col ){
                $row[$num] = iconv("GBK","UTF-8",(string)$col);
            }
            $i++;
            if($i == 1){
                if($row[0]=='订单编号' && $row[8]=='订单状态'){
                    continue;
                }else{
                    break;
                }
            }
            $order_status = $this->order_status($row[8]);
            /*
             $order_objects[$row[0]][] = array (
             'obj_type' => 'goods',
             'name' => $row[1],
             'weight' => 0,
             'price' => $row[2],
             'order_items' => array (
             0 => array (
             'status' => 'active',
             'name' => $row[1],
             'bn' => $row[9],
             'product_attr' => $row[5],
             'item_type' => 'product',
             'amount' => $row[2],
             'cost' => $row[2],
             'shop_goods_id' => '0',
             'sendnum' => ($order_status['ship_status']==1)?$row[3]:0,
             'score' => '',
             'quantity' => $row[3],
             'price' => $row[2],
             'shop_product_id' => '0',
             'delete' => 'false',
             'product_id' => '0',
             ),
             ),
             'amount' => $row[2],
             'score' => '',
             'shop_goods_id' => '0',
             'obj_alias' => '商品',
             'bn' => $row[9],
             'quantity' => $row[3],
             );
             */
            if(stristr($row[0],'+')) return false;//防止科学计数法
            $order_objects[$row[0]][] = array(
                'oid' => $row[0],
                'obj_type' => 'goods',
                'obj_alias' => '商品',
                'shop_goods_id' => '0',
                'bn' => $row[9],
                'name' => $row[1],
                'price' => $row[2],
                'quantity' => $row[3],
                'amount' => $row[2]*$row[3],
                'weight' => '',
                'score' => '',
                'order_items' => array(array(
                    'shop_product_id' => '0',
                    'shop_goods_id' => '0',
                    'item_type' => 'product',
                    'bn' => $row[9],
                    'name' => $row[1],
                    'product_attr' => $row[5],
                    'cost' => $row[2],
                    'quantity' => $row[3],
                    'sendnum' => ($order_status['ship_status']==1)?$row[3]:0,
                    'amount' => $row[2],
                    'price' => $row[2],
                    'weight' => '',
                    'status' => 'active',
                    'score' => 0,
                    'create_time' => time(),
            ))
            );
            unset($row);
        }
        fclose($tmpFileHandle);
        return $order_objects;
    }


    public function toImport(){
        $this->begin('index.php?app=taocrm&ctl=admin_member&act=import');
        @set_time_limit(600);
        @ini_set('memory_limit','128M');
        $memberObj=&$this->app->model('members');
        $orderObj = app::get(ORDER_APP)->model('orders');
        $queueObj = app::get('base')->model('queue');
        $shop_id = $_POST['shop_id'];
        if(!$shop_id || $shop_id==''){
            $this->end(false, '请选择所属店铺！');
        }

        if(substr($_FILES['order_list']['name'],-4)!='.csv' || substr($_FILES['detail_list']['name'],-4)!='.csv'){
            $this->end(false, '订单CSV和宝贝CSV都必须上传！');
        }

        $order_objects = $this->order_detail($_FILES['detail_list']);


        if(!$order_objects || $order_objects == ''){
            $this->end(false, '宝贝CSV文件内容有误！');
        }

        if(!$_FILES['order_list']['tmp_name']) $this->end(false, '文件上传失败，请检查大小是否超过限制！');
        $tmpFileHandle = fopen($_FILES['order_list']['tmp_name'],"r");
        $i=0;
        while($row = fgetcsv($tmpFileHandle, 1000, ",")){
            foreach( $row as $num => $col ){
                $row[$num] = iconv("GBK","UTF-8",(string)$col);
            }
            $i++;
            if($i == 1){
                if($row[0]=='订单编号' && $row[10]=='订单状态'){
                    continue;
                }else{
                    $this->end(false, '订单CSV文件内容有误');
                    break;
                }
            }

            if(stristr($row[0],'+')) {
                //防止科学计数法
                $this->end(false, '订单编号有误');
                break;
            }

            $order_status = $this->order_status($row[10]);
            $consignee = explode(" ", $row[13]);
            $zip = substr($row[13],strpos($row[13],'(')+1,6);
            $addr = substr($consignee[3],0,strpos($consignee[3],'('));
            $tel = substr($row[15],1);
            $mobile = substr($row[16],1);

            $orderData[] = array(
                'order_source' => 'taobao',
                'order_bn' => $row[0],
                'memeber_id' => $row[26],
                'status' => $order_status['status'],
                'pay_status' => $order_status['pay_status'],
                'ship_status' => $order_status['ship_status'],
                'is_delivery' => $order_status['is_delivery'],
                'shipping' => json_encode(array(
                    'shipping_name' => $row[22],
                    'is_cod' => 'false',
                    'is_protect' => 'false',
                    'cost_shipping' => $row[4],
                    'cost_protect' => 0,
                    'shipping_id' => '',
            )),
                'member_info' => json_encode(array(
                    'uname' => $row[1],
                    'name' => $row[12],
                    'area_state' => $consignee[0],
                    'area_city' => $consignee[1],
                    'area_district' => $consignee[2],
                    'alipay_no' => $row[2],
                    'addr' => $consignee[1].$consignee[2].$addr,
                    'mobile' => $mobile,
                    'tel' => $tel,
                    'email' => $row[2],
                    'zip' => $zip
            )),
                'payinfo' => json_encode(array(
                    'pay_name' => '支付宝',
                    'cost_payment' => 0.0
            )),
                'weight' => '',
                'title' => $row[19],
                'itemnum' => '',
                'modified' => strtotime($row[17]),
                'createtime' => strtotime($row[17]),
                'ip' => '',
                'consignee' => json_encode(array(
                    'name' => $row[12],
                    'area_state' => $consignee[0],
                    'area_city' => $consignee[1],
                    'area_district' => $consignee[2],
                    'addr' => $consignee[1].$consignee[2].$addr,
                    'zip' => $zip,
                    'telephone' => $tel,
                    'email' => $row[2],
                    'r_time' => '',
                    'mobile' => $mobile
            )),
                'payment_detail' => json_encode(array(
                    'pay_account' => $row[2],
                    'currency' => 'CNY',
                    'paymethod' => '支付宝',
                    'pay_time' => strtotime($row[18]),
                    'trade_no' => $row[2]
            )),
                'pmt_detail' => '',
                'cost_item' => $row[3],
                'is_tax' => 'false',
                'cost_tax' => '0.00',
                'tax_title' => $row[30],
                'currency' => 'CNY',
                'cur_rate' => 1.0,
                'score_u' => $row[5],
                'scort_g' => $row[7],
                'discount' => $row[8] - $row[6],
                'pmt_goods' => '0',
                'pmt_order' => '0',
                'total_amount' => $row[8],
                'cut_amount' => $row[6],
                'payed' => $row[8],
                'custom_mark' => $row[11],
                'mark_text' => '',
                'mark_type' => '',
                'tax_no' => '',
                'order_limit_time' => '',
                'coupons_name' => '',
                'order_objects' => json_encode($order_objects[$row[0]]),
                'shop_id' => $shop_id,
                'shop_type' => 'taobao',
                'source' => 'manual',
            );
            if(count($orderData) == 200){
                $newFileName = 'taocrm_orders_'.$_FILES['order_list']['name'].$i.'-'.time();
                $this->importQueue($newFileName,$orderData);
                unset($orderData,$newFileName);
            }
            unset($row,$member,$member_info);
        }
        fclose($tmpFileHandle);
        if($orderData){
            $newFileName = 'taocrm_orders_'.$_FILES['order_list']['name'].'-'.time();
            $this->importQueue($newFileName,$orderData);

            unset($orderData,$newFileName);
        }
        $queueObj->flush();
        $this->end(true, '导入成功');
    }

    public function importQueue($fileName,$data){
        if($data){
            $queueObj = app::get('base')->model('queue');
            base_kvstore::instance('taocrm_orders')->store($fileName,serialize($data));
            $queueData = array(
                'queue_title'=>'历史订单CSV导入',
                'start_time'=>time(),
                'params'=>array(
                    'app' => 'taocrm',
                    'mdl' => 'orders',
                    'file_name' => $fileName
            ),
                'worker'=>'taocrm_order_import.run',
            );
            $queueObj->save($queueData);
            return true;
        }else{
            return false;
        }
    }



    protected function _array_unique_fb($array2D){
        foreach ($array2D as $v){
            $temp[] = serialize($v);
        }

        $temp = array_unique($temp);
        $uniqueArr = array();
        foreach ($temp as $k => $v) {
            $uniqueArr[$k] = unserialize($v);
        }
        return $uniqueArr;
    }

    public function add_member()
    {
        $shopObj = app::get(ORDER_APP)->model('shop');
        if ($_POST){
            $member_data=array();
            $member_data=$_POST;
            $member_data['sex']=$_POST['gender'];
            unset(
                $member_data['_DTYPE_DATE'],
                $member_data['_DTYPE_BOOL'],
                $member_data['gender']
            );
            $rs=$this->save_member($member_data);
            exit;
        }
        $rs = app::get('ecorder')->model('shop')->getList('shop_id,name');
        if($rs) {
            foreach($rs as $v) {
                if($v['name']) $shops[$v['shop_id']] = $v['name'];
            }
        }
        $this->pagedata['from'] = $_GET['from'];
        $this->pagedata['shops'] = $shops;
        $this->pagedata['shop'] = $shopObj->dump($shop_id);
        $this->page('admin/member/add.html');
    }

    // 保存客户信息
    public function save_member(&$arr)
    {
        if($_POST['from']=='caselog'){
            $url = 'index.php?app=taocrm&ctl=admin_member_caselog&act=add';
        }else{
            $url = 'index.php?app=taocrm&ctl=admin_member&act=index&shop_id='.$arr['shop_id'].'';
        }

        $this->begin($url);
        $oMembers = $this->app->model('members');
        $rs = $oMembers->getList('member_id',array('uname'=>$arr['uname']));
        if($rs){
            $this->end(false,'客户名重复');
        }
        $member_id = kernel::single('taocrm_service_member')->saveMember($arr['shop_id'],$arr);

        if($arr['shop_id']){
            $data['member_id'] = $member_id;
            $data['shop_id'] = $arr['shop_id'];
            $data['update_time'] = time();
            $oMemberAnalysis = $this->app->model('member_analysis');
            $oMemberAnalysis->save($data);
        }
        $this->end(true,'保存成功');
    }

    public function getOrderInfo($shop_id,$order_id,$page)
    {
        $pagelimit = 20;
        $page = $page ? $page : 1;
        if(isset($_GET['order_bn'])){
            $rs_order = app::get('ecorder')->model('orders')->dump(array('order_bn'=>$_GET['order_bn']),'order_id,shop_id,ship_name,ship_mobile,createtime,order_bn,pay_time,delivery_time,finish_time,f_modified,total_amount,payed,cost_freight');
            if($rs_order){
                $order_id = $rs_order['order_id'];
                $shop_id = $rs_order['shop_id'];
            }
        }        
        $orderItems = app::get('ecorder')->model('order_items')->getPager(array('order_id'=>$order_id),'name,price,nums,amount,evaluation,bn,`delete`',$pagelimit * ($page - 1), $pagelimit);

        $trade_rates = array('good'=>'好评','bad'=>'差评','neutral'=>'中评','unkown'=>'-');
        foreach($orderItems['data'] as $k=>$v){
            $orderItems['data'][$k]['evaluation'] = $trade_rates[$v['evaluation']];
            $orderItems['data'][$k]['pmt_amount'] = ($v['price'] * $v['nums']) -  $v['amount'];
        }

        $count = $orderItems ['count'];
        $total_page = ceil ( $count / $pagelimit );
        $pager = $this->ui ()->pager ( array ('current' => $page, 'total' => $total_page, 'link' => 'index.php?app=taocrm&ctl=admin_member&act=getOrderInfo&p[0]='.$shop_id.'&p[1]='.$order_id.'&p[2]=%d' ) );
        $this->pagedata['pager'] = $pager;

        $this->pagedata['order'] = $rs_order;
        $this->pagedata['orderItems'] = $orderItems['data'];
        $this->display('admin/member/order_info.html');
    }

    //物流信息
    public function getLogisticsInfo(){
        $logi_obj = app::get('ecorder')->model('logi_info');
        $logi_data = $logi_obj->dump(array('order_id'=>trim($_GET['order_id'])));
        $this->pagedata['logi_info'] = $logi_data;
        $this->display('admin/member/logistics_info.html');
    }

    //根据手机号获取客户信息
    public function getInfoByPhone(){
        $mobile = trim($_POST['number_id']);
        $type = $_POST['type'];

        //客户信息
        $memberObj = $this->app->model('members');
        if($type == 'mobile')
        $mobiles = $memberObj->getList('uname,name,member_id,sex,mobile',array('mobile'=>$mobile));
        else
        $mobiles = $memberObj->getList('uname,name,member_id,sex,mobile',array('tel|has'=>$mobile));

        if(!$mobiles){
            $data['rsp'] = 'fail';
        }else{
            foreach($mobiles as $v){
                $member_id[] = $v['member_id'];
                $memberData[$v['member_id']] = $v;
            }
            //订单信息
            $analysisObj = $this->app->model('member_analysis');
            //$analysisArr = $analysisObj->getList('member_id,total_orders,total_amount,shop_id,total_per_amount,refund_orders,refund_amount,finish_orders',array('member_id|in'=>$member_id));
			$sql = "select shop_id,member_id,count(order_id) as total_orders,sum(total_amount) as total_amount,sum(if(pay_status='5',total_amount,0)) as refund_amount,
					sum(if(pay_status='5',1,0)) as refund_orders,sum(if(status='finish',1,0)) as finish_orders from sdb_ecorder_orders
					where member_id in(".implode(',',$member_id).") group by member_id";
            $analysisArr = $analysisObj->db->select($sql);

            foreach($analysisArr as $v){
            	$v['total_per_amount'] = round($v['total_amount']/$v['total_orders']);
                $analysisData[$v['member_id']] = $v;
            }

            //商品信息
            $orderObj = app::get('ecorder')->model('orders');
            $orders = $orderObj->getList('order_id,member_id',array('member_id|in'=>$member_id));
            $mOrders = array();
            foreach($orders as $v){
                $mOrders[$v['member_id']][] = $v['order_id'];
            }
            $itemObj = app::get('ecorder')->model('order_items');
            $goodsArr = array();
            foreach($mOrders as $k=>$v){
                $order_ids = implode(',',$v);
                $goods=$itemObj->db->select("select distinct name from  sdb_ecorder_order_items where order_id in ({$order_ids}) limit 0,3");
                foreach($goods as $val){
                    $arr[] = $val['name'];
                }

                $goodStr = implode('<br />',$arr);
                unset($arr);
                $goodsArr[$k]['goods_name']= $goodStr;
            }

            //组合数据
            $default_value = array(
                'total_orders' => 0,
                'total_amount' => 0,
                'total_per_amount' => 0,
                'refund_orders' => 0,
                'refund_amount' => 0,
                'finish_orders' => 0,
            );
            foreach($memberData as $k=>$v){
                $analysis = $analysisData[$k] ? $analysisData[$k] : $default_value;
                $goodsData = $goodsArr[$k] ? $goodsArr[$k] : array('goods_name'=>'未购买过商品');
                $data['member'][] = array_merge($v,$analysis,$goodsData);
            }

            $data['rsp'] = 'succ';
        }
        echo json_encode($data);
    }

    //根据订单号获取客户信息
   	public function getInfoByOrder(){

   	    $order_bn = trim($_POST['number_id']);
   	    $orderObj = app::get('ecorder')->model('orders');
   	    $order = $orderObj->dump(array('order_bn'=>$order_bn),'member_id,order_id');
   	    if($order){
   	        $member_id = $order['member_id'];

   	        //客户信息
   	        $memberObj = $this->app->model('members');
   	        $member = $memberObj->getList('uname,name,sex,mobile',array('member_id'=>$member_id));
   	        /*
   	         //手机号归属地
   	         if($member[0]['mobile']){
   	         //$area = $this->getPhoneArea($member[0]['mobile']);
   	         }else{
   	         $area['rsp'] = 'fail';
   	         }
   	         $data['area'] = $area;
   	         */
   	        //客户统计信息
   	        $analysisObj = $this->app->model('member_analysis');
   	        //$analysisArr = $analysisObj->getList('member_id,total_orders,total_amount,shop_id,total_per_amount,refund_orders,refund_amount,finish_orders',array('member_id'=>$member_id));
   	        $sql = "select shop_id,member_id,count(order_id) as total_orders,sum(total_amount) as total_amount,sum(if(pay_status='5',total_amount,0)) as refund_amount,
					sum(if(pay_status='5',1,0)) as refund_orders,sum(if(status='finish',1,0)) as finish_orders from sdb_ecorder_orders
					where member_id =$member_id";
            $analysisArr = $analysisObj->db->select($sql);

            foreach($analysisArr as $k=>$v){
            	$v['total_per_amount'] = round($v['total_amount']/$v['total_orders']);
                $analysisArr[$k] = $v;
            }

   	        //商品信息
   	        $itemObj = app::get('ecorder')->model('order_items');
   	        $goods=$itemObj->db->select("select distinct name from  sdb_ecorder_order_items where order_id =".$order['order_id']." limit 0,3");
   	        foreach($goods as $val){
   	            $arr[] = $val['name'];
   	        }
   	        $goodStr = implode('<br />',$arr);
   	        $goodsArr['goods_name'] = $goodStr;
   	        //组合数据
   	        $memberData = $member[0] ? $member[0] : array();
   	        $analysisData = $analysisArr[0] ? $analysisArr[0] : array();
   	        $goodsData = $goodsArr ? $goodsArr : array();
   	        $memberArr = array_merge($member[0],$analysisArr[0],$goodsArr);
   	        $data['member'][] = $memberArr;
   	        $data['rsp'] = 'succ';
   	    }else{
   	        $data['rsp'] = 'fail';
   	        //$data['area']['rsp'] = 'fail';
   	    }
   	    echo json_encode($data);
   	}

    //订单详细信息
    public function getAnalysisData(){

        $data['memberId'] = $_GET['member_id'];
        $data['shopId'] = $_GET['shop_id'];
        $analysis = kernel::single('taocrm_middleware_connect')->AnalysisByMemberId($data);
        $analysis['UnpayPerAmount'] = round($analysis['UnpayAmount'] / $analysis['UnpayOrders']);
        $analysisObj = $this->app->model('member_analysis');
        $points = $analysisObj->dump(array('member_id'=>$data['memberId']),'points');

        $analysis['Points'] = $points['points'];
        $analysis['MinCreateTime'] = $analysis['MinCreateTime'] ? date('Y-m-d H:i:s',$analysis['MinCreateTime']) : '-';
        $analysis['MaxCreateTime'] = $analysis['MaxCreateTime'] ? date('Y-m-d H:i:s',$analysis['MaxCreateTime']) : '-';

        $this->pagedata['analysis'] = $analysis;
        $this->display('admin/member/search_analysis.html');
    }

    //查询手机号归属
    public function getPhoneArea(){
        $mobile = trim($_POST['phone_number']);
        //手机号归属信息
        $url = "http://life.tenpay.com/cgi-bin/mobile/MobileQueryAttribution.cgi?";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "chgmobile={$mobile}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $area = curl_exec($ch);
        curl_close($ch);
        $area=mb_convert_encoding($area, "UTF-8", "gb2312");
        $area = $this->xml2array($area);
        if($area['root']['retmsg'] == 'OK'){
            //手机所在省份
            $result['province'] = $area['root']['province'];
            //手机所在城市
            $result['city'] = $area['root']['city'];
            //手机号类型  （移动、联通、电信）
            $result['type'] = $area['root']['supplier'];
            $result['rsp'] = 'succ';
        }else{
            $result['rsp'] = 'fail';
        }

        echo json_encode($result);
    }

    //XML格式转化成数组格式
    public function xml2array($xml) {
        ini_set('pcre.backtrack_limit',-1);
        $reg = "/<(\\w+)[^>]*?>([\\x00-\\xFF]*?)<\\/\\1>/";
        if (preg_match_all($reg, $xml, $matches)) {
            $count = count($matches[0]);
            $arr = array();

            for ($i = 0; $i < $count; $i++) {
                $key = $matches[1][$i];
                $val = $this->xml2array($matches[2][$i]);

                if (array_key_exists($key, $arr)) {
                    if (is_array($arr[$key])) {
                        if (!array_key_exists(0, $arr[$key])) {
                            $arr[$key] = array($arr[$key]);
                        }
                    } else {
                        $arr[$key] = array($arr[$key]);
                    }
                    $arr[$key][] = $val;
                } else {
                    $arr[$key] = $val;
                }
            }

            return $arr;
        } else {
            return $xml;
        }
    }

    function exportIndex(){
        $this->deleteExpireExport();

        $title = '客户导出列表';
        $baseFilter = array();
        $actions = array();
        $this->finder('taocrm_mdl_member_export',array(
            'title'=> $title,
            'actions' => $actions,
            'base_filter'=>$baseFilter,
        	'orderBy' => 'create_time desc',
        //'use_buildin_set_tag'=>true,
            'use_buildin_import'=>false,
            'use_buildin_export'=>false,
            'use_buildin_recycle'=>false,
            'use_buildin_filter'=>false,
            'use_buildin_tagedit'=>false,
            'use_view_tab'=>false,
        ));
    }

    function deleteExpireExport(){
        $obj = app::get('taocrm')->model('member_export');
        $obj->deleteExpireExport();
    }

    function download($exportId){

        $this->pagedata['check_export'] = $this->checkIsExport();

        $obj = app::get('taocrm')->model('member_export');

        if($exportId){
            $export = $obj->get($exportId,'export_id,total_num,export_param,export_status,create_time,finish_time');
            //var_dump($business);exit;
        }
        if(!$export){echo '记录不存在,无法导出';exit;}

        $this->pagedata['export']  = $export;
        $this->display('admin/member/export/download.html');
    }

    function checkIsExport(){
        base_kvstore::instance('market')->fetch('account', $account);
        $account = unserialize($account);
        $result = array('res'=>'succ');
        if(empty($account)){
            $result = array('res'=>'fail','msg'=>'请先绑定短信账号');
        }else{
            $smsInfo=$this->getSmsCount();
            if($smsInfo['smscount'] == -1) {
                $result = array('res'=>'fail','msg'=>'您的短信账号出现异常，请检查配置信息');
            }else if($smsInfo['overcount'] < 1) {
                $result = array('res'=>'fail','msg'=>'ShopEx CRM系统中客户资料的下载，涉及您的数据安全，需要您的手机验证后方可下载。<br />
由于你的短信账户余额不足，无法发送短信验证到您的手机，暂无法下载客户资料，请先充值短信金额。<br />
<a href="index.php?app=market&ctl=admin_sms_account&act=index" target="_blank">立即充值</a>');
            }
        }

        return $result;
    }

    public function send_passcode()
    {
        $result = $this->checkIsExport();
        if($result['res'] == 'succ'){
            $mobile = $_POST['mobile'];
            if( !$mobile ) {
                $result = array('res'=>'fail','msg'=>'请输入手机号码');
            }elseif( !preg_match("/^(1)\d{10}$/",$mobile) ) {
                $result = array('res'=>'fail','msg'=>'手机号码格式错误，请重新输入。');
            }else{
                $kvstore = base_kvstore::instance('taocrm');
                $kvstore->fetch('member_export_passcode', $kv_passcode);
                if($kv_passcode) {
                    $kv_passcode = json_decode($kv_passcode, 1);
                }

                if(isset($kv_passcode['create_time']) && $mobile==$kv_passcode['mobile'] && (time() - $kv_passcode['create_time'] < 600)){
                    $result = array('res'=>'fail','msg'=>'10分钟内只能申请一次验证码，请稍后再试。');
                }else{
                    //获取有效的短信签名
                    $rs_shop = app::get('ecorder')->model('shop')->getList('config');
                    foreach((array)$rs_shop as $v){
                        $config = unserialize($v['config']);
                        if($config['extend_no']){
                            $sms_sign = $config['sms_sign'];
                            break;
                        }
                    }
                    if(!$sms_sign){
                        $result = array('res'=>'fail','msg'=>'请先设置短信签名。');
                        echo json_encode($result);
                        exit;
                    }   
                
                    $passcode = rand(1000,9999).date('s');
                    $sms_content = "尊敬的用户，以下是您的Shopex CRM验证码（6位数字）:{$passcode}。请在一小时内用此验证码进行验证。【{$sms_sign}】";
                    $sms = array();
                    $sms[] = array(
                        'phones' => $mobile,
                        'content'=>$sms_content
                    );
                    $type='fan-out';
                    $data = kernel::single('market_service_smsinterface')->send_self_passcode(json_encode($sms),$type);
                    if($data['res'] == 'succ'){
                        $create_time = time();
                        $kv_passcode = array(
        				'mobile' => $mobile,
        				'passcode' => $passcode,
        				'create_time' => $create_time,
                        );
                        $kvstore->store('member_export_passcode', json_encode($kv_passcode));
                    }else{
                        if($data['info'] == 'account balance not enough'){
                            $result = array('res'=>'fail','msg'=>'发送验证码失败,余额不足');
                        }elseif($data['info'] == 'qianming is error or exists'){
                            $result = array('res'=>'fail','msg'=>'短信签名错误，请检查签名设置');
                        }else{
                            $kv_passcode = array(
                                'mobile' => $mobile,
                                'passcode' => $passcode
                            );
                            $kvstore->store('member_export_passcode', json_encode($kv_passcode));
                            $result = array('res'=>'fail','msg'=>'发送验证码失败:'.json_encode($data));
                        }
                    }
                }
            }
        }

        echo json_encode($result);
    }

    //核对验证码
    public function check_passcode()
    {

        $mobile = $_POST['mobile'];
        $passcode = $_POST['passcode'];

        $kvstore = base_kvstore::instance('taocrm');
        $kvstore->fetch('member_export_passcode', $kv_passcode);
        //var_dump($kv_passcode);exit;
        $result = array('res'=>'succ');

        if($kv_passcode) {
            $kv_passcode = json_decode($kv_passcode, 1);
            if($kv_passcode['mobile'] != $mobile){
                $result = array('res'=>'fail','msg'=>'手机号码不匹配:'.$kv_passcode['mobile']);
            }
            if($kv_passcode['passcode'] != $passcode){
                $result = array('res'=>'fail','msg'=>'验证码错误，请重新输入。');
            }
            // echo time() - $kv_passcode['create_time'];exit;
            if(isset($kv_passcode['create_time']) && (time() - $kv_passcode['create_time'] > 3600)){
                $result = array('res'=>'fail','msg'=>'验证码过期，请重新申请。');
            }
        }else{
            $result = array('res'=>'fail','msg'=>'请先点击获取验证码。');
        }

        echo json_encode($result);
        exit;
    }

    public function getSmsCount()
    {
        $send=kernel::single('market_service_smsinterface');
        $send_info = $send->get_usersms_info();//get_usersms_info

        if ($send_info['res']=='succ'){
            $month_residual=$send_info['info']['month_residual']; //短信总条数 all_residual
            $blocknums=intval($send_info['info']['block_num']);//冻结短信条数
        }else{
            $month_residual=-1; //当前可用的短信数
            $blocknums=-1; //冻结短信条数
        }

        //entId,entPwd,license
        $infoarray=array(
                'smscount'=>$month_residual,
                'blocknum'=>$blocknums,
                'overcount'=>$month_residual- $blocknums,
                'entId'=>$send_info['entId'],
                'entPwd'=>$send_info['entPwd'],
                'license'=>$send_info['license'],
        );

        return $infoarray;
    }

    public function toDownload(){
        $export_id = $_POST['export_id'];
        $obj = app::get('taocrm')->model('member_export');
        $export = $obj->get($export_id);
        if(empty($export)){
            header("Content-type: text/html; charset=utf-8");
            echo '客户数据不存在';
            exit;
        }

        $objExportLog = app::get('taocrm')->model('member_export_log');
        $objExportLog->addLog($export_id);

        header("Content-Type: text/csv");
        $filename = 'customer_export('.date('Y-m-d').').csv';
        $encoded_filename = urlencode($filename);
        $encoded_filename = str_replace("+", "%20", $encoded_filename);

        header('Content-Disposition: attachment; filename*="gb2312\'\'' . $filename . '"');

        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');

        echo $export['export_content'];
        exit;
    }

    public function invalid()
    {
        //        if(!isset($_GET['view'])){
        //            $this->redirect('index.php?app=taocrm&ctl=admin_member&act=index&view=1');
        //            exit;
        //        }

        $title = '无效客户列表';
        $actions = '';
        $baseFilter = array();
        $shops = $this->getShopFullIds();
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
        //        if ($baseFilter['shop_id'] == '') {
        //            $baseFilter['shop_id'] = trim($_GET['shop_id']);
        //        }
        //$baseFilter['methodName'] = 'SearchMemberAnalysisList';
        $baseFilter['methodName'] = 'SearchInvalidMemberAnalysisByShop';
        $baseFilter['packetName'] = 'ShopMemberAnalysis';
        $actions = $this->_action();
        $this->finder('taocrm_mdl_middleware_member_analysis',array(
            'title'=> $title,
            'actions' => $actions,
            'base_filter'=>$baseFilter,
        //去掉默认排序
        	'orderBy' => '',
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
    //删除客户
    function delete_member(){
        $this->pagedata['member_id']=$_GET['member_id'];
        $this->pagedata['tagInfo']=$_GET['tagInfo'];
        $this->pagedata['shop_id']=$_GET['shop_id'];
        $membersObj = $this->app->model('members');
        $data = $membersObj->getlist('parent_member_id,is_merger,uname',array('member_id'=>trim($_GET['member_id'])));
        //print_r($data);
        if($data[0]['is_merger']){
            $data_merger = $membersObj->getlist('uname',array('member_id'=>$data[0]['parent_member_id']));
            $this->pagedata['member_data']=array('uname'=>$data[0]['uname'],'is_merger'=>$data[0]['is_merger'],'parent_uname'=>$data_merger[0]['uname']);
        }
        $data_a = $this->app->model('member_analysis')->getlist('points,total_orders',array('member_id'=>trim($_GET['member_id']),'shop_id'=>$_GET['shop_id']));
        $data_a[0]['uname'] = $data[0]['uname'];
        $this->pagedata['data']=$data_a[0];
        $this->page('admin/delete_member.html');
    }
    function to_delete_member(){
        $this->begin('index.php?app=taocrm&ctl=admin_member&act=index');
        if($_POST['invalid_name']=='on'){
            //判断该用户是否有删除权限
            $user = kernel::single('desktop_user');
            $user_id = $user->get_id();
            $is_super = $user->is_super();
            $users = app::get('desktop')->model('users');
            $sdf_users = $users->dump($user_id);
            if(!$is_super && !$sdf_users['customer_delete']){
                $this->end(false,'抱歉，您没有删除客户的权限！');
            }

            $membersObj = $this->app->model('member_analysis');
            $data = $membersObj->getlist('points,total_orders,member_id as _0_member_id',array('member_id'=>trim($_POST['member_id']),'shop_id'=>$_POST['shop_id']));
            if(!empty($data)){
                if($data[0]['points']){
                    $this->end(false,'您好，该会员有积分，不支持删除！');
                }
                if($data[0]['total_orders']){
                    $this->end(false,'您好，该会员有订单，不支持删除！');
                }
                if($_POST['tagInfo']){
                    $this->end(false,'您好，该会员有标签，不支持删除！');
                }
                $res = $membersObj->delete(array('member_id'=>trim($_POST['member_id']),'shop_id'=>$_POST['shop_id']));
                if($res){
                    $this->end(true,'删除成功');
                }else{
                    $this->end(false,'店铺客户表删除失败！');
                }
            }else{
                $this->end(false,'此客户不存在！');
            }
        }else{
            $this->end(true);
        }
    }
    
    //批量删除客户
    function batch_delete_member()
    {
        $member_id_arr = $_POST['id'];
        $member_ids = implode(',',$member_id_arr);

        $this->pagedata['member_ids'] = $member_ids;
        $this->pagedata['member_count'] = count($member_id_arr);
        $this->page('admin/batch_delete_member.html');
    }
    
    function to_batch_delete_member()
    {
        $this->begin();
        if($_POST['invalid_name']=='on'){
            //判断该用户是否有删除权限
            $user = kernel::single('desktop_user');
            $user_id = $user->get_id();
            $is_super = $user->is_super();
            $users = app::get('desktop')->model('users');
            $sdf_users = $users->dump($user_id);
            if(!$is_super && !$sdf_users['customer_delete']){
                $this->end(false,'抱歉，您没有删除客户的权限！');
            }

            $member_id = explode(',',$_POST['member_id']);

            $membersObj = $this->app->model('member_analysis');
            $data = $membersObj->getlist('member_id,points,total_orders',array('member_id'=>$member_id));
            if(!empty($data)){
                $succ_num = 0;
                $fail_num = 0;
                foreach($data as $key => $value){
                    $members_tag = $this->app->model('member_to_tag');
                    $data_tag = $members_tag->getlist('tag_id',array('member_id'=>$value['member_id']));
                    if($value['points'] || $value['total_orders'] || !empty($data_tag)){
                        $fail_num++;
                    }else{
                        $res = $membersObj->delete(array('member_id'=>$value['member_id']));
                        if($res){
                            $succ_num++;
                        }else{
                            $fail_num++;
                        }
                    }
                }
                if($succ_num || $fail_num){
                    $msg = '操作完成，';
                    if($succ_num){
                        $msg .= '成功删除了 '.$succ_num.' 个客户资料。';
                    }
                    if($fail_num){
                        $msg .= '有 '.$fail_num.' 个客户有订单（积分、标签），不支持删除。';
                    }

                    $this->end(true,$msg);
                }else{
                    $this->end(false,'店铺客户表删除失败！');
                }
            }else{
                $this->end(false,'此店铺客户不存在！');
            }
        }else{
            $this->end(true);
        }
}

}
