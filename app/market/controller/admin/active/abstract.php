<?php

abstract class market_ctl_admin_active_abstract extends desktop_controller{

    //优惠券
    public function coupon_send()
    {
        $active_id=$_GET[p][0];
        $active_obj = app::get('market')->model('coupons');
        $active_data = $active_obj->dump(array('active_id'=>$active_id),'shop_id,coupon_name,coupon_count,used_num');
        echo(json_encode($active_data));
    }
    
    //获取客户分组
    public function getmember_group()
    {
        $active_obj = app::get('market')->model('active');
        $membergroup_obj = app::get('taocrm')->model('member_group');
        $active_id=$_GET['p'][0];
        $shop_id=$active_obj->dump(array('active_id'=>$active_id),'shop_id,filter_mem');
        $filter_array=unserialize($shop_id['filter_mem']);
        $group_data=$membergroup_obj->getList("group_id,group_name",array('shop_id'=>$shop_id['shop_id']));
        foreach ($group_data as $k=>$v){
            $group_data[$k]['group_selected']=!empty($filter_array['group_id'])?intval($filter_array['group_id']) : 0;
        }
        echo(json_encode($group_data));
    }
    
    protected function _init_config_arr()
    {
        //地区列表
        $rs = app::get('ectools')->model('regions')->getList('region_id,local_name,group_name',array('region_grade'=>1,'region_id|sthan'=>3266));
        if($rs){
            foreach($rs as $v){
                if(!$v['group_name']) $v['group_name'] = '其它';
                $regions[$v['group_name']][$v['region_id']] = $v['local_name'];
            }
        }
        $this->pagedata['regions'] = $regions;

        $taobaolv = array(
            'c'=>'普通客户',
            'asso_vip'=>'荣誉客户',
            'vip1'=>'vip1',
            'vip2'=>'vip2',
            'vip3'=>'vip3',
            'vip4'=>'vip4',
            'vip5'=>'vip5',
            'vip6'=>'vip6'
        );
        
        $select_sign = array(
            'nequal' => '等于',
            'sthan' => '小于等于',
            'bthan' => '大于等于',
            'between' => '介于'
        );
        
        $select_sign_time = array(
            'than' => '晚于',
            'lthan' => '早于',
            'nequal' => '等于',
            'between' => '介于'
        );
        
        $select_date = array(
            '7' => '最近一周',
            '30' => '最近一个月',          
            '60' => '最近二个月',          
            '90' => '最近三个月',          
            '180' => '最近半年',
            '360' => '最近一年',
        );
        
        for($i=0;$i<24;$i++){
            $select_hour[$i] = $i.':00';
        }

        $this->pagedata['taobaolv'] = $taobaolv;
        $this->pagedata['select_sign'] = $select_sign;
        $this->pagedata['select_sign_time'] = $select_sign_time;
        $this->pagedata['select_date'] = $select_date;
        $this->pagedata['select_hour'] = $select_hour;
    }
    
    //客户的函数
    public function select_member_data()
    {
        $memberanaly_obj = app::get('taocrm')->model('member_analysis_day');
        $couponsobj=app::get('market')->model('coupons');//优惠券
        $active_obj = app::get('market')->model('active');
        $members_group_obj = app::get('taocrm')->model('member_group');
        $members_obj = app::get('taocrm')->model('members');
        $filter=array('active_id'=>$_GET[p][0]);

        if ($_POST['uptag']=='uptag') {
            $filter=array('active_id'=>$_GET[p][0]);
            $dump_data=$active_obj->dump($filter);
            $dump_data['create_time']=date('Y-m-d',$dump_data['create_time']);
            $dump_data['end_time']=date('Y-m-d',$dump_data['end_time']);
            $dump_data['filter_mem'] = unserialize($dump_data['filter_mem']);

            echo(json_encode($dump_data));

        }else{
            $active_id=$_GET[p][0];
            $data=$active_obj->dump(array('active_id'=>$active_id));
            $shop_id=trim($data['shop_id']);
            $filter=array('shop_id'=>$shop_id);
            $str_post=serialize($_POST);//保存活动对应的筛选条件
            
            if(isset($_POST['chk_goods_id']) && $_POST['chk_goods_id']==2){
                if(isset($_POST['filter']['good_name']) && $_POST['filter']['good_name']){

                    $good_name_sign = $_POST['filter']['good_name_sign'];
                    $good_name = trim($_POST['filter']['good_name']);
                    $good_name2 = trim($_POST['filter']['good_name2']);

                    if($good_name_sign != 'or') $good_name_sign='and';

                    $str_post['filter']['goods_id'] = array();

                    $sql = "select goods_id from sdb_ecgoods_shop_goods where  (name like '%$good_name%' ";
                    if($good_name2)
                        $sql .= " $good_name_sign name like '%$good_name2%' ";
                    $sql .= ')';
                    $goods_id_list = kernel::database()->select($sql);
                    foreach($goods_id_list as $v){
                        $str_post['filter']['goods_id'][] = $v['goods_id'];
                    }
                    $str_post['filter']['goods_id'] = array_unique($str_post['filter']['goods_id']);
                }
            }
            
            $rs=$active_obj->update(
            array('filter_mem'=>$str_post,'is_active'=>'sel_template'),
            array('active_id'=>$_GET[p][0])
            );
            if (!empty($data['coupon_id'])){
                $coupononedata=$couponsobj->dump(array('coupon_id'=>$data['coupon_id']),'coupon_name,coupon_id');
            }else{
                $coupononedata = array('coupon_name'=>'','coupon_id'=>0);
            }
            echo json_encode($coupononedata);
        }
    }
    
    //优惠券的
    public function coupons_selected()
    {
        $couponsobj=app::get('market')->model('coupons');//优惠券
        $active_obj = app::get('market')->model('active');
        $active_id=$_GET[p][0];
        $shop_id=$active_obj->dump(array('active_id'=>$active_id),'shop_id');
        $shop_id=trim($shop_id['shop_id']);
        $filter=array('shop_id'=>$shop_id);
        $couponslist=$couponsobj->getList("coupon_id,coupon_name",$filter);
        if (empty($couponslist)){
            $tsts=array();
            $tsts[0]['active_id']=$_GET[p][0];
            $tsts[0]['cou_tag']=true;
            echo json_encode($tsts);
        }else{
            foreach ($couponslist as $k=>$v){
                $couponslist[$k]['active_id']=$_GET[p][0];
                $couponslist[$k]['cou_tag']=false;
            }
            echo json_encode($couponslist);
        }
    }
    
    //step2 评估客户数量
    function assess()
    {
        $shop_id = &$_POST['shop_id'];
        $filter = &$_POST['filter'];
        $oMiddlewareMember = kernel::single('taocrm_middleware_member');
        $res = $oMiddlewareMember->SearchMemberAnalysisCount($shop_id, $filter);
        echo($res);
        die();
        
        $oMemberGroup = app::get('taocrm')->model('member_group');
        $oMemberAnalysis = app::get('taocrm')->model('member_analysis');
        //转换过滤条件
        $filter = $oMemberGroup->buildFilter($_POST['filter'],$_POST['shop_id']);
        if(strstr($filter,'select')){
            $db = kernel::database();
            $count = $db->selectRow($filter);
            echo $count['_count'];
        }else{
            $count = $oMemberAnalysis->count($filter);
            echo $count;
        }
        die();
    }
    
    //作废活动
    function invalid_active($active_id)
    {
        $this->pagedata['active_id']=$active_id;
        $this->display('admin/active/invalid.html');
    }
    
    //重复营销
    public function repeat_active($active_id)
    {
        $activeObj = $this->app->model('active');
        $activeInfo = $activeObj->dump(array("active_id" => $active_id));
        $afterTime = 86400 * 15;
        $pageParams['create_time'] = strtotime(date("Y-m-d 00:00:00"));
        $pageParams['end_time'] = $pageParams['create_time'] + $afterTime;
        $pageParams['type'] = $activeInfo['type'];
        $pageParams['coupon_id'] = $activeInfo['coupon_id'];
        $is_active = 'sel_template';
        $pageParams['is_active'] = $is_active;
        $subArray = array('create_time', 'end_time', 'is_active', 'active_id', 'total_num', 'valid_num');
        foreach ($activeInfo as $k => $v) {
            if (in_array($k, $subArray)) {
                continue;
            }
            if ($k == 'active_name') {
                $pageParams[$k] = $v . '(重复营销)';
            }else {
                $pageParams[$k] = $v;
            }
        }
        //如果是放大镜营销
        if (isset($pageParams['cache_id']) && $pageParams['cache_id'] > 0) {
            $sql = "SELECT `member_id` FROM `sdb_market_active_member` WHERE `active_id` = {$activeInfo['active_id']}";
            $memberInfo = $activeObj->db->select($sql);
            $memberList = array();
            foreach ($memberInfo as $v) {
                $memberList[] = $v['member_id'];
            }
            $pageParams['member_list'] = serialize($memberList);
            unset($pageParams['cache_id']);
            unset($pageParams['cache_id_create_time']);
        }
        $active_id = $activeObj->insert($pageParams);
        $_GET['p'][0] = $active_id;
        $_GET['p'][1] = $is_active;
        $this->editer_data();
    }
    
    //删除营销活动
    function invalid()
    {
        $this->begin();
        if($_POST['invalid_name']=='on'){
            $active_id = floatval($_POST['active_id_name']);
            
            //调用java接口删除活动
            kernel::single('taocrm_middleware_activity')->delete_active($active_id);
            
            $active_obj = app::get('market')->model('active');
            $rec = $active_obj->update(
                array('is_active'=>'dead'),
                array('active_id'=>$active_id)
            );
            
            $this->end();
        }else {
            $this->end();
        }
    }
    
    //条款条件
    function legal_copy(){
        $op_id = kernel::single('desktop_user')->get_id();
        base_kvstore::instance('market')->fetch('legal_copy_info_'.$op_id,$legal_copy);
        $data = unserialize($legal_copy);
        $this->pagedata['data'] = $data['stat'];
        $this->display('admin/active/legal_copy.html');
    }

    //发送提醒
    function legal_notice(){
        $data = $_GET;
        $this->pagedata['active_id'] = $data['active_id'];
        $this->display('admin/active/legal_notice.html');
    }

    //保存条款条件同意状态
    function legal_store(){
        $data = $_POST;
        $op_id = kernel::single('desktop_user')->get_id();
        $data = serialize($data);
        base_kvstore::instance('market')->store('legal_copy_info_'.$op_id,$data);
         
    }

    //保存发送提醒同意状态
    function legal_save(){
        $data = $_POST;
        $active_id = $data['active_id'];
        unset($data['active_id']);
        $data = serialize($data);
        base_kvstore::instance('market')->store('legal_copy_info_'.$active_id,$data);
    }

    //判断是否已同意条款条件及发送提醒
    function get_legal(){
        $flag = 0;
        $active_id = $_POST['active_id'];
        $systemType = kernel::single('taocrm_system')->getSystemType();
        $system_type = $systemType['system_type'];
        $system_type = 2;
        if($system_type == 2){
             
            base_kvstore::instance('market')->fetch('legal_copy_info_'.$active_id,$legal_copy);
            $legal_copy = unserialize($legal_copy);
             
            $op_id = kernel::single('desktop_user')->get_id();
            //base_kvstore::instance('market')->store('legal_copy_info_'.$op_id,'');
            base_kvstore::instance('market')->fetch('legal_copy_info_'.$op_id,$data);
            $data = unserialize($data);
            if($data['stat'] == 'agree'){
                if($legal_copy['status'] != 'agree'){
                    $flag = 1;
                }
            }else{
                $flag = 2;
            }
        }
        echo $flag;
    }
    
    //客户等级
    function member_lv()
    {
        $active_obj = app::get('market')->model('active');
        $memberlv_obj = app::get('ecorder')->model('shop_lv');
        $shop_id=$active_obj->dump(array('active_id'=>$_GET['p'][0]),'shop_id,filter_mem');
        $lv_data=$memberlv_obj->getList("lv_id,name",array('shop_id'=>$shop_id['shop_id']));
        $filter_array=array();
        $filter_array=unserialize($shop_id['filter_mem']);
        foreach ($lv_data as $k=>$v){
            $lv_data[$k]['seletag']=$filter_array['lv_id'];
        }
        echo json_encode($lv_data);
    }
    
    function member_group($group_id)
    {
        $mgroup_data = app::get('taocrm')->model('member_group');
        $fiter=array('group_id'=>$group_id);
        $group_data=$mgroup_data->getList("*",$fiter,0,-1);
        return $group_data;
    }
    
    //商品选择
    function product_select(){
        $active_obj = app::get('market')->model('active');
        $productlist=$active_obj->dump(array('active_id'=>$_GET['p'][0]),'filter_mem');
        $productlist=unserialize($productlist['filter_mem']);
        $list=$productlist['product'];
        echo json_encode($list);
    }

    //地区选择
    function area_select(){
        $active_obj = app::get('market')->model('active');
        $productlist=$active_obj->dump(array('active_id'=>$_GET['p'][0]),'filter_mem');
        $productlist=unserialize($productlist['filter_mem']);
        $list=$productlist['area'];
        echo json_encode($list);
    }

    //店铺类型
    function shop_type()
    {
        $shop_id=$_GET['shop_id'];
        $shopObj = app::get('ecorder')->model('shop');
        $shop_type=$shopObj->dump(array('shop_id'=>$shop_id),'node_type');
        echo $shop_type['node_type'];
    }
    
    //判断活动是否来自营销超市
    public function market_active($active_id)
    {
        $active_obj = app::get('market')->model('active');
        $filter=array('active_id'=>$active_id);
        $tag=$active_obj->dump($filter);
        if($tag['pay_type'] == 'market'){
            $active_obj->delete($filter);
        }
    }
    
    function checkCoupon($shop_id)
    {
        $result = array('res'=>'succ');
        $shopInfo = app::get('ecorder')->model('shop')->dump(array('shop_id'=>$shop_id),'*');
        if($shopInfo['node_type'] != 'taobao' || empty($shopInfo['addon'])){
            $result = array(
                'res'=>'fail',
                'msg'=>'请重新绑定'.$shopInfo['name'].',非淘宝店铺不能发送优惠券'
                );
        }elseif(empty($shopInfo['addon']['session'])){
            $result = array(
                'res'=>'fail',
                'msg'=>$shopInfo['name'].'登录失效，请到店铺管理内重新登录'
                );
        }

        return $result;
    }
}
