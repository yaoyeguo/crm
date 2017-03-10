<?php
class ecorder_ctl_admin_shop_credit extends desktop_controller{
    var $workground = 'taocrm.shop';

    public function index()
    {
        $memberObj = app::get('taocrm')->model('members');
        $shopObj = $this->app->model('shop');
        $shopList = $shopObj->get_shops('no_fx');
        foreach((array)$shopList as $v){
            $shops[] = $v['shop_id'];
        }
        $view = (isset($_GET['view']) && intval($_GET['view']) >= 0 ) ? intval($_GET['view']) : 0;
        $base_filter = array();
        if ($view == 0) {
            //$base_filter = "shop_id IN ('" . implode("','", $shops) . "')";
            $base_filter = array('shop_id|in' => $shops);
        }
//        exit;
        /*
        $view = (isset($_GET['view']) && intval($_GET['view']) >= 0 ) ? intval($_GET['view']) : 0;
        if($view>0) 
        $base_filter = array('shop_id' => $shopList[$view]['shop_id']);
        */

        $this->finder('ecorder_mdl_shop_credit',array(
            'title'=>'客户积分规则',
            'actions'=>array(
                 array(
                    'label'=>'通用积分规则',
                    'href'=>'index.php?app=ecorder&ctl=admin_shop_credit&act=addnew&shop_id='.$_GET['shop_id'],
                    'target'=>'dialog::{width:680,height:270,title:\'添加积分规则\'}'
                 ),
                array(
                    'label'=>'特殊积分规则',
                    'href'=>'index.php?app=ecorder&ctl=admin_shop_credit&act=addnew_special&shop_id='.$_GET['shop_id'],
                    'target'=>'dialog::{width:680,height:260,title:\'添加特殊积分规则\'}'
                ),
                ),
            'base_filter' => $base_filter,
            'orderBy' => 'create_time DESC',
            //'orderBy' => 'shop_id ASC, create_time DESC',
            ));
    }
    
    function _views()
    {
        $shopLvObj = $this->app->model('shop_credit');
        $base_filter = array();
        $shopObj = app::get(ORDER_APP)->model('shop');
        $shopList = $shopObj->get_shops('no_fx');
        foreach((array)$shopList as $v){
            $shops[] = $v['shop_id'];
        }
        $base_filter = array('shop_id|in' => $shops);
        
        $sub_menu[] = array(
            'label'=>'全部',
            'filter'=>$base_filter,
            'optional'=>false
        );
        foreach($shopList as $shop){
            $sub_menu[] = array(
                'label'=>$shop['name'],
                'filter'=>array('shop_id'=>$shop['shop_id']),
                'optional'=>false
            );
        }

        $i=0;
        foreach($sub_menu as $k=>$v){
            if (!IS_NULL($v['filter'])){
                $v['filter'] = array_merge($v['filter'], $base_filter);
            }
            $sub_menu[$k]['filter'] = $v['filter']?$v['filter']:null;
            $sub_menu[$k]['addon'] = $shopLvObj->count($v['filter']);
            $sub_menu[$k]['href'] = 'index.php?app=ecorder&ctl='.$_GET['ctl'].'&act='.$_GET['act'].'&view='.$i++.'&shop_id='.$v['filter']['shop_id'];
        }
        return $sub_menu;
    }

    public function addnew($rule_id=null){
    	$select_sign=array(
    		 	'unlimited' => '无限制',
                'gthan' => '大于',
                'sthan' => '小于',
                'equal' => '等于',
                'gethan' => '大于等于',
        		'sethan' => '小于等于',
    			'between' => '介于'
    	);
        $this->pagedata['select_sign'] = $select_sign;
        $aLv['shop_id'] = $_GET['shop_id'];
        $aLv['count_type'] = 'payed';
        $this->pagedata['levelSwitch']= $this->app->getConf('site.level_switch');
        $this->pagedata['credit'] = $aLv;

        if($rule_id!=null){
            $shopLvObj = $this->app->model('shop_credit');
            $aLv = $shopLvObj->dump($rule_id); 
//            echo "<pre>";
//            print_r($aLv);
            //$aLv['default_lv_options'] = array('1'=>'是','0'=>'否');
            $this->pagedata['credit'] = $aLv;
        }
        
        $rs = $this->app->model('shop')->get_shops('no_fx');
        if($rs) {
            foreach($rs as $v) {
                $shops[$v['shop_id']] = $v['name'];
            }
        }
        $this->pagedata['shops'] = $shops;
        
        $this->display('admin/shop/credit.html');
    }

    function save(){
        $this->begin();
        $lvData = $_POST;
    	$lvData['create_time'] = time();
        if(!$lvData['start_time'] || !$lvData['end_time']) {
            $lvData['start_time'] = 0;
            $lvData['end_time'] = 0;
        }else{
            $lvData['start_time'] = strtotime($lvData['start_time']);
            $lvData['end_time'] = strtotime($lvData['end_time']);
        }
         $lvData['amount_symbol'] = $_POST['filter']['total_amount']['sign'];
         unset($lvData['filter']);
         unset($lvData['_DTYPE_DATE']);
        $shopLvObj = $this->app->model('shop_credit');
        if($shopLvObj->save($lvData)){
            $this->end(true,'保存成功'); 
        }else{
            $this->end(false,'保存失败');
        }
    }

    public function getShopLv($shop_id){
        $shopLvObj = &$this->app->model('shop_lv');
        $member_level = $shopLvObj->getList('shop_lv_id,name',array('shop_id'=>$shop_id));
        if($member_level){
            $this->pagedata['member_level'] = $member_level;
            echo $this->fetch('admin/shop/shop_lv.html');
        }else{
            echo '<span class="red">此店铺没有添加会员等级</span>';
        }
    }
    public function credit_exchange(){
        if(isset($_POST['consume_points_itc']) || isset($_POST['interaction_points_itc'])){
            $url = 'index.php?app=ecorder&ctl=admin_shop_credit&act=credit_exchange';
            $this->begin($url);
            $save_data = $_POST;
            $save_data['create_time'] = time();//创建时间
            $user_id = kernel::single('desktop_user')->get_id();
            $user_name = kernel::single('desktop_user')->get_name();
            $save_data['op_user_id'] = $user_id;
            $save_data['op_name'] = $user_name;
            $creditExchangeObj = $this->app->model('credit_exchange');
            $result = $creditExchangeObj->insert($save_data);
            $this->end(true,'保存成功');
        }
        $creditExchangeObj = $this->app->model('credit_exchange');
        $res = $creditExchangeObj->getList('*',array(),0,-1,'create_time desc');
        //print_r($res);
        if(!empty($res)){
            $this->pagedata['data'] = $res[0];
        }
       // $this->page("admin/shop/exchange.html");
        $title = '积分兑换';
        $baseFilter = array();
        $extra_view = array('ecorder'=>'admin/shop/exchange.html');
        $this->finder('ecorder_mdl_credit_exchange',array(
            'title'=> $title,
            'base_filter'=>$baseFilter,
          //  'actions'=>$actions,
            'top_extra_view' => $extra_view,
            //'use_buildin_set_tag'=>true,
            'use_buildin_selectrow'=>false,
            'use_buildin_import'=>false,
            'use_buildin_export'=>false,
            'use_buildin_recycle'=>false,
            'use_buildin_tagedit'=>false,
            'orderBy' => 'create_time DESC',
            'use_view_tab'=>false,
        ));
    }
    //积分设置
    public function set_logs()
    {
        if($_POST){
            $url = 'index.php?app=ecorder&ctl=admin_shop_credit&act=set_logs';
            $this->begin($url);

            $set_type = $_POST['set_type'];
            $arr = array(
                'set_type' => $set_type=='include' ? 'include' : 'exclude',
                'op_user' => kernel::single('desktop_user')->get_name(),
                'create_time' => time(),
            );
            $this->app->model('point_set_logs')->insert($arr);

            $this->end(true,'保存成功');
        }

        //以最后一次设定的模式为准
        //默认为叠加 exclude
        $set_type = 'exclude';
        $rs = $this->app->model('point_set_logs')->getList('set_type', '', 0, 1, 'id DESC');
        if($rs){
            $set_type = $rs[0]['set_type'];
        }
        $this->pagedata['set_type'] = $set_type;

        $extra_view = array('ecorder'=>'admin/shop/point_set.html');

        $actions = array();
        $base_filter = array();
        $this->finder('ecorder_mdl_point_set_logs',array(
            'title'=>'积分设置',
            'actions'=>$actions,
            'base_filter'=>$base_filter,
            'orderBy' => 'id DESC',
            'use_buildin_recycle' => false,
            'use_buildin_filter' => false,
            'use_view_tab' => false,
            'top_extra_view' => $extra_view,
        ));
    }
    //添加特殊积分规则
    public function addnew_special($rule_id=null){
        $point_times=array(
            '1' => '1.5',
            '2' => '2',
            '3' => '3',
            '4' => '5'
        );
        $this->pagedata['point_times'] = $point_times;
        $birthday_type = array(1=>'当天',2=>'当月');
        $this->pagedata['birthday_type'] = $birthday_type;
        $aLv['shop_id'] = $_GET['shop_id'];
        $this->pagedata['credit'] = $aLv;

        if($rule_id!=null){
            $shopLvObj = $this->app->model('shop_credit');
            $aLv = $shopLvObj->dump($rule_id);
            $aLv['time_from'] = $aLv['time_from'] == '0000-00-00' ? '' : $aLv['time_from'];
            $aLv['time_to'] = $aLv['time_to'] == '0000-00-00' ? '' : $aLv['time_to'];
            $this->pagedata['credit'] = $aLv;
            //送积分规则
            $special_point_rule = explode(',',$aLv['special_point_rule']);
            foreach($special_point_rule as $key=>$value){
                if($value == '1'){
                    $rule['activity'] = $value;
                }elseif($value == '2'){
                    $rule['birthday'] = $value;
                }
            }
            $this->pagedata['rule'] = $rule;
        }

        $rs = $this->app->model('shop')->getList('shop_id,name');
        if($rs) {
            foreach($rs as $v) {
                $shops[$v['shop_id']] = $v['name'];
            }
        }
        $this->pagedata['shops'] = $shops;

        $this->display('admin/shop/credit_special.html');
    }
    function special_save(){
        $this->begin();
        $lvData = $_POST;
        $lvData['create_time'] = time();
        if(!empty($_POST['point_rule'])){
            $lvData['special_point_rule'] = implode(',',$_POST['point_rule']);
        }
        unset($lvData['point_rule']);
        $shopLvObj = $this->app->model('shop_credit');
        if($shopLvObj->save($lvData)){
            $this->end(true,'保存成功');
        }else{
            $this->end(false,'保存失败');
        }
    }
    //积分计算规则
    public function computation_rule(){
        if($_POST){
            $this->begin('index.php?app=ecorder&ctl=admin_shop_credit&act=computation_rule');
            base_kvstore::instance('ecorder')->store('point_computation_rule', $_POST['shops']);
            $this->end(true, app::get('ecorder')->_('保存成功！'));
        }
        base_kvstore::instance('ecorder')->fetch('point_computation_rule', $point_computation_rule);

        $rs = $this->app->model('shop')->getList('shop_id,name');
        foreach($rs as $key => $value){
            if(in_array($value['shop_id'],$point_computation_rule)){
                $rs[$key]['check'] = true;
            }
        }
        $this->pagedata['shops'] = $rs;
        $this->page('admin/shop/computation_rule.html');
    }
}
