<?php
class market_ctl_admin_coupon_analysis extends desktop_controller{
    var $workground = 'market.sales';

    public function index(){
        $shopObj = &app::get(ORDER_APP)->model('shop');
        $shopList = $shopObj->getList('shop_id,name');
        foreach((array)$shopList as $v){
            $shops[] = $v['shop_id'];
        }
        $view = (isset($_GET['view']) && intval($_GET['view']) >= 0 ) ? intval($_GET['view']) : 0;
        if ($_GET['view']!=0){
            $view=$view-1;
            $shop_id =$shops[$view];
        }
        
        $actions = array(
            array(
                'label'=>'添加优惠券',
                'href'=>'index.php?app=market&ctl=admin_coupon&act=add',
                'target'=>'_blank'
            ),
        );
        $baseFilter = array();
        if ($view == 0) {
            $baseFilter =   array('shop_id|in' => $shops);
        }
        $this->finder('market_mdl_coupons',array(
            'title'=>'淘宝优惠券',
            //'actions'=>$actions,
            'use_buildin_recycle'=>false,
            //'use_buildin_selectrow'=>false,
            'base_filter' => $baseFilter,
            'finder_cols' => 'shop_id,coupon_name,status,created,end_time,used_num,applied_count,coupon_count,denominations,outer_coupon_id,column_orders,column_amount',
        ));
    }
    
    public function _views(){
        $oRecord = $this->app->model('coupons');

        $sub_menu[] = array(
            'label'=> '全部',
            'filter'=> '',
            'optional'=>false,	
        );
        
        $sub_menu[] = array(
            'label'=> '可兑换',
            'filter'=> array('is_exchange'=>1),
            'optional'=>false,	
        );

        $i=0;
        foreach($sub_menu as $k=>$v){
            $count =$oRecord->count($v['filter']);
            $sub_menu[$k]['filter'] = $v['filter']?$v['filter']:null;
            $sub_menu[$k]['addon'] = $count;
            $sub_menu[$k]['href'] = 'index.php?app=market&ctl=admin_coupon&act=index&view='.$i++;
        }
        return $sub_menu;        
    }

    public function add() {
        $this->_editor();
    }

    public function edit($coupon_id){
        $this->_editor($coupon_id);
    }

    public function _editor($coupon_id=NULL){
        if($coupon_id && $coupon_id>0){
            $couponObj = &$this->app->model('coupons');
            $coupon = $couponObj->dump($coupon_id);
            $this->pagedata['coupon'] = $coupon;
        }else{
            $this->pagedata['coupon'] = array('status'=>1);
            $this->pagedata['user_condition'] = 1;
        }
        $couponType = market_coupon_taobao_type::get_coupon_type();
        $this->pagedata['couponType'] = $couponType;

        $limitCount = market_coupon_taobao_type::get_limit_count();
        $this->pagedata['limitCount'] = $limitCount;

        $shopObj = &app::get(ORDER_APP)->model('shop');
        $shopList = $shopObj->getList('shop_id,name');
        $this->pagedata['shopList'] = $shopList;
        $this->pagedata['finder_id'] = $_GET['_finder']['finder_id'];        

        header("Cache-Control:no-store");
        $this->singlepage('admin/coupon/frame.html');
    }

    public function goOnAdd($shop_id){
        $post = app::get('market')->getConf('goon_add_' . $shop_id);
        if($post){
            $post = unserialize($post);
            $coupon = $post['coupon'];
            $this->pagedata['coupon_name'] = $post['coupon']['coupon_name'];
            $this->pagedata['user_condition'] = $post['user_condition'];
            $this->pagedata['end_time'] = $post['end_time'];
            $this->pagedata['post'] = $post;
        }else{
            $post = array();
            $coupon = array('status'=>1);
            $this->pagedata['user_condition'] = 1;
        }
        $this->pagedata['coupon'] = $coupon;
        $couponType = market_coupon_taobao_type::get_coupon_type();
        $this->pagedata['couponType'] = $couponType;

        $limitCount = market_coupon_taobao_type::get_limit_count();
        $this->pagedata['limitCount'] = $limitCount;

        $shopObj = &app::get(ORDER_APP)->model('shop');
        $shopList = $shopObj->getList('shop_id,name');
        $this->pagedata['shopList'] = $shopList;

        header("Cache-Control:no-store");
        $this->singlepage('admin/coupon/frame.html');
    }

    public function toAdd() {
        $this->begin('index.php?app=market&ctl=admin_coupon');

        //记录添加的值，授权回来以后可以恢复表单
        app::get('market')->setConf('goon_add_' . $_POST['coupon']['shop_id'],serialize($_POST));

        $coupon = $_POST['coupon'];
        $coupon['start_time'] = time();
        $coupon['source'] = 'local';
        $coupon['end_time'] = strtotime($_POST['end_time'].' '.$_POST['_DTIME_']['H']['end_time'].':'.$_POST['_DTIME_']['M']['end_time'].':00');

        if(!$coupon['denominations'] || $coupon['denominations']<=0){
            $this->end(false,'抵扣金额不能为空！');
        }
        
        if(!empty($coupon['conditions']) && !preg_match ('/^[0-9]+$/', $coupon['conditions'])){
            $this->end(false,'订单限额请输入整数！');
        }
        
        if($coupon['end_time'] <= $coupon['start_time']){
            $this->end(false,'优惠券结束时间应晚于当前时间！');
        }

        if($coupon['end_time'] >= ($coupon['start_time']+15552000) ){
            $this->end(false,'优惠券结束时间不能大于6个月！');
        }

        if(!$coupon['coupon_count']){
            $this->end(false,'总领用量不能为空！');
        }

        //无限
        if($_POST['user_condition'] == 1){
            $coupon['conditions'] = 0;
        }else{
            if($coupon['conditions'] <= $coupon['denominations']){
                $this->end(false,'使用条件必须大于面额!');
            }
        }

        $shopObj = &app::get(ORDER_APP)->model('shop');
        $shop = $shopObj->dump($coupon['shop_id']);
        /*if($coupon['conditions'] && $coupon['conditions']>0){
            $coupon['coupon_name'] = $shop['name'].'满'.$coupon['conditions'].'元减'.$coupon['denominations'].'元优惠券('.date("Y-m-d H:i",$coupon['end_time']).')';
        }else{
            $coupon['coupon_name'] = $shop['name'].$coupon['denominations'].'元优惠券('.date("Y-m-d H:i",$coupon['end_time']).')';
        }*/

        //error_log(var_export($coupon,true),3,__FILE__.".log");
        $activity = array(
            'active_name' => $_POST['active_name'],
            'shop_id' => $coupon['shop_id'],
            'type' => 'coupon',
            'is_active' => 'sel_member',
            'end_time' => $coupon['end_time'],
        );
        //if( ($active_id = kernel::single('market_service_activity')->saveActivity($activity)) ){
            //$coupon['active_id'] = $active_id;
            $coupon['active_id'] = 0;
            if( ($coupon_id = kernel::single('market_service_coupon')->saveCoupon($coupon)) ){
                if(!kernel::single('market_service_coupon')->requestCoupon($coupon_id,$msg)){
                    $this->_applyAuth($msg,$coupon['shop_id']);
                    $this->end(false,'请求优惠卷接口失败:'.$msg);
                }

                if(!kernel::single('market_service_activity')->requestActivity($coupon_id,$msg)){
                    $this->_applyAuth($msg,$coupon['shop_id']);
                    $this->end(false,'请求优惠卷活动接口失败:'.$msg);
                }

                $this->end(true,'操作成功');
            }else{
                $this->end(true,'保存优惠卷失败');
            }
        /*}else{
            $this->end(false,'保存活动失败');
        }*/
    }

    private function _applyAuth(& $msg,$shopId,$jumpto = 'coupon_goOnAdd'){
        if(strstr($msg, 'Insufficient session permissions')){
            $db = kernel::database();
            $db->rollback();
            echo '<a href="index.php?app=ecorder&ctl=admin_shop&act=applyAuth&p[0]='.$shopId.'&p[1]='.$jumpto.'">点击链接,你需要登录淘宝帐号进行授权</a>';
            exit;
        }else if(strstr($msg, 'isv.error-unauthorized')){
            $db = kernel::database();
            $db->rollback();
            echo '卖家没有订购优惠券的服务,<a href="http://fuwu.taobao.com/serv/detail.htm?service_id=6831" target="_self">点击购买</a>';
            exit;
        }else if(strstr($msg, 'session no exist') || strstr($msg, 'Missing session')){
            $msg = '授权不存在,请在系统设置->店铺管理里登录淘宝';
        }
    }

    public function requestCoupon($couponId){
        if(!$couponId){
            $couponId = app::get('market')->getConf('request_coupon_id');
        }
        if(!$couponId){
            echo '参数错误，请关闭重新发起!';
            exit;
        }

        if(!$_POST['flag']){
            header("Cache-Control:no-store");
            $this->pagedata['couponId'] = $couponId;
            $this->pagedata['flag'] = 1;
            $this->pagedata['act'] = 'requestCoupon';
            $this->singlepage('admin/coupon/again_request.html');
        }else{
            $this->begin('index.php?app=market&ctl=admin_coupon');
            if(kernel::single('market_service_coupon')->requestCoupon($couponId,$msg)){
                echo '请求优惠卷接口成功';
                exit;
            }else{
                app::get('market')->setConf('request_coupon_id',$couponId);
                $coupon = $this->app->model('coupons')->dump($couponId,'shop_id');
                $this->_applyAuth($msg,$coupon['shop_id'],'coupon_requestCoupon');
                echo '请求优惠卷接口失败('.$msg.')';
                exit;
            }
        }
    }

    public function requestActivity($couponId){
        if(!$couponId){
            $couponId = app::get('market')->getConf('request_coupon_id');
        }
        if(!$couponId){
            echo '参数错误，请关闭重新发起!';
            exit;
        }
        if(!$_POST['flag']){
            header("Cache-Control:no-store");
            $this->pagedata['couponId'] = $couponId;
            $this->pagedata['flag'] = 1;
            $this->pagedata['act'] = 'requestActivity';
            $this->singlepage('admin/coupon/again_request.html');
        }else{
            $this->begin('index.php?app=market&ctl=admin_coupon');
            if(kernel::single('market_service_activity')->requestActivity($couponId,$msg)){
                echo '请求活动接口成功';
                exit;
            }else{
                app::get('market')->setConf('request_coupon_id',$couponId);
                $coupon = $this->app->model('coupons')->dump($couponId,'shop_id');
                $this->_applyAuth($msg,$coupon['shop_id'],'coupon_requestActivity');
                echo '请求活动接口失败('.$msg.')';
                exit;
            }
        }
    }

    public function getShopCoupon($shop_id){
        $couponObj = &$this->app->model('coupons');
        $coupons = $couponObj->getList('coupon_id,coupon_name',array('status'=>1,'shop_id'=>$shop_id,'end_time|than'=>time()));
        if($coupons){
            $this->pagedata['coupons'] = $coupons;
            echo $this->fetch('admin/coupon/shop_coupon.html');
        }else{
            echo '<span class="red">此店铺没有有效的优惠券</span>';
        }
    }

    public function test_add(){
        $couponObj = &app::get('market')->model('coupons');
        $coupon = $couponObj->dump(1);
        $obj = kernel::single('market_rpc_request_coupon');
        if($obj->add($coupon)){
            echo "成功";
        }else{
            echo "失败";
        }
    }

    public function test_sent(){
        $coupon_id = 1;
        $member_id = 168;
        $sentData['sent_id'] = 1;
        $sentData['coupon_id'] = $coupon_id;
        $sentData['member_id'] = $member_id;
        $sentObj = &app::get('market')->model('coupon_sent');
        $sentObj->save($sentData);

        $obj = kernel::single('market_rpc_request_coupon');
        if($obj->send($coupon_id,$member_id)){
            echo "成功";
        }else{
            echo "失败";
        }
    }

    public function test(){
        $coupon_id = 1;
        $member_id = 168;
        $obj = kernel::single('market_rpc_request_coupon');
        if($obj->getDetail($coupon_id,$member_id)){
            echo "成功";
        }else{
            echo "失败";
        }
    }
    
    
    
}