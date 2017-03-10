<?php
class taocrm_members {

    function add($sdf, &$msg)
    {
        $sdf = kernel::single('ecorder_func')->trim_array($sdf);
        
        $node_type = 'unknow';//如果有店铺ID，就取节点类型，进入判断会员唯一规则流程，如果没有就取默认
        if($sdf['shop_id']){
            $shop = app::get ( 'ecorder' )->model ( 'shop' )->dump ( $sdf['shop_id'], 'channel_id,node_type,node_id' );
            $node_type = $shop['node_type'];
        }

        if( !$sdf['uname'] && !$sdf['mobile']){
            $msg = '<br/>帐号和手机号不能全部为空';
            return false;
        }

        $filter = array();
        //只要填写第三方会员id，就以第三方会员ID作为唯一标示
        if(isset($sdf['uid']) && !empty($sdf['uid'])){
        	$filter[] = array(
        			'ext_uid' => $sdf ['uid'],
        			'shop_id'=>$sdf['shop_id']
        	);
        }else{
        
	        if($node_type == 'taobao'){
	            $filter[] = array(
	                'uname' => $sdf['uname'],
	                'channel_type' => $node_type,
	            );
	        }else if($node_type == 'ecos.b2c'){
	            if($sdf['uname'] && $sdf['mobile']){
	                $filter[]  = array(
	                    'uname' => $sdf['uname'],
	                    'mobile' => $sdf['mobile']
	                );
	            }
	            if($sdf['uid'] && $sdf['shop_id']){
	                $filter[] = array(
	                'ext_uid' => $sdf ['uid'],
	                    'shop_id'=>$sdf['shop_id']
	                );
	            }
	            if($sdf['uname'] && $sdf['shop_id']){
	                $filter[] = array(
	                    'uname' => $sdf ['uname'],
	                    'shop_id'=>$sdf['shop_id']
	                );
	            }
	        }else{
	        	$filter[] = array(
	        		'mobile' => $sdf ['mobile']
	        	);
	        }
        }

        $filter = kernel::single('ecorder_func')->clear_value($filter);
        $member = kernel::single('taocrm_service_member')->check_repeat_members($filter, 'member_id');
        if($member){
            $msg = '<br/>客户已存在';
            $rec_mod = app::get('taocrm')->model('members_recommend');
            $rec_mod_data = $rec_mod->dump(array('member_id'=>$member['member_id']));
            $member_info = array('member_id'=>$member['member_id'],'self_code'=>$rec_mod_data['self_code']);
            return $member_info;
            //return $member['member_id'];
        }


        $area = $sdf['state'] . '/' . $sdf['city'] . '/' . $sdf['district'];
        kernel::single("ecorder_func")->region_validate($area);
        $area = str_replace('::', '', $area);
        $sdf['area'] = $area;

        if(!empty($sdf['state'])){
            $row = kernel::database()->selectrow('select region_id from sdb_ectools_regions where local_name="'.$sdf['state'].'"');
            $sdf['state'] = $row['region_id'];
        }

        if(!empty($sdf['city'])){
            $row = kernel::database()->selectrow('select region_id from sdb_ectools_regions where local_name="'.$sdf['city'].'"');
            $sdf['city'] = $row['region_id'];
        }

        if(!empty($sdf['district'])){
            $row = kernel::database()->selectrow('select region_id from sdb_ectools_regions where local_name="'.$sdf['district'].'"');
            $sdf['district'] = $row['region_id'];
        }

        if(isset($sdf['sex'])){
            if($sdf['sex'] == 0){
                $sdf['sex']='unkown';
            }else if($sdf['sex'] == 1){
                $sdf['sex']='male';
            }else if($sdf['sex'] == 2){
                $sdf['sex']='female';
            }else{
                $sdf['sex']='unkown';
            }
        }else{
            $sdf['sex']='unkown';
        }

        $member = array(
            'update_time'=>time(),
            'props'=>$sdf['props'],
            'uname'=>$sdf['uname'],
            'ext_uid'=>$sdf['uid'],
            'name'=>$sdf['name'],
            'source_terminal'=>$sdf['source_terminal'],
            'area'=>$sdf['area'],
            'state'=>$sdf['state'],
            'city'=>$sdf['city'],
            'district'=>$sdf['district'],
            'addr'=>$sdf['address'],
            'mobile'=>$sdf['mobile'],
            'tel'=>$sdf['tel'],
            'email'=>$sdf['email'],
            'zip'=>$sdf['zip'],
            'alipay_account'=>$sdf['alipay'],
            'alipay_no'=>$sdf['alipay'],
            'sex'=>$sdf['sex'],
            'birthday'=>strtotime($sdf['birthday']),
            'remark'=>$sdf['remark'],
            'is_vip'=>($sdf['is_vip'] == 1) ? 'true' : 'false',
            'sms_blacklist'=>($sdf['is_sms_black'] == 1) ? 'true' : 'false',
            'edm_blacklist'=>($sdf['is_email_black'] == 1) ? 'true' : 'false',
            'channel_type'=>$node_type,
            'shop_id'=>$sdf['shop_id']
        );
        
        //防止空数据覆盖用户的输入数据
        $member = kernel::single('ecorder_func')->trim_array($member);
        $member = kernel::single('ecorder_func')->clear_value($member);

        //添加初次节点ID
        $member['stand_node_id'] = $shop['node_id'];

        $memberId = $this->addMember($member);
        if(!$memberId){
            $msg = '<br/>创建客户失败';
            return false;
        }

        //更新会员推荐表
        //if($sdf['parent_code']!=''){
            $self_code = $this->updateMembersRecommend($sdf,$memberId);
       // }

        $this->process_member_ext($sdf, $memberId);

        //保存客户自定义属性
        if($sdf['props']!=''){
            $props = json_decode($sdf['props'], true);
            $oMemberProp = app::get('taocrm')->model('member_overall_property');
            foreach($props as $k=>$v){
                if($k && $v){
                    $save = array(
                        'member_id'=>$memberId,
                        'uname'=>$sdf['uname'],
                        'property'=>$k,
                        'value'=>$v,
                    );
                    $oMemberProp->insert($save);
                }
            }
            $msg .= '<br/>自定义属性保存成功';
        }

        //检测注册时间
        if($sdf['reg_time']){
            if(strstr($sdf['reg_time'], '-') or strstr($sdf['reg_time'], '/')){
                $sdf['reg_time'] = strtotime($sdf['reg_time']);
            }else{
                $sdf['reg_time'] = floatval($sdf['reg_time']);
            }
        }
        if(!$sdf['reg_time']){
            $sdf['reg_time'] = time();
        }

        //如果有店铺ID，就创建店铺会员
        if($sdf['shop_id']){
            $memberAnalysis = array(
            'member_id'=>$memberId,
            'shop_id'=>$sdf['shop_id'],
            'district'=>$sdf['district'],
            'channel_id'=>$shop['channel_id'],
            'lv_id'=>$this->getDefaultLv($sdf['shop_id']),
            'is_vip'=>($sdf['is_vip'] == 1) ? 'true' : 'false',
            'update_time'=>time(),
            'f_created'=>$sdf['reg_time'], //时间戳
            );
            $memberAnalysisId = $this->addMemberAnalysis($memberAnalysis);
            if(!$memberAnalysisId){
                $msg .= '<br/>创建店铺客户失败';
                return false;
            }
            
            $msg .= '<br/>创建店铺客户成功';
        }else{
            $msg .= '<br/>店铺ID不存在，未创建店铺客户';
        }

        $member_info = array('member_id'=>$memberId,'self_code'=>$self_code);
        return $member_info;
    }
    //更新会员推荐表
    function updateMembersRecommend($sdf,$memberId){
        $rec_mod = app::get('taocrm')->model('members_recommend');
        $insert_data = array(
            'member_id'=>$memberId,
            'uname'=>$sdf['uname'],
            'name'=>$sdf['name'],
            'mobile'=>$sdf['mobile'],
            'update_time'=>time(),
            'parent_code'=>$sdf['parent_code'],
        );
        $has_code = $rec_mod->dump(array('member_id'=>$memberId));
        if( ! $has_code){
            $recno_mod = app::get('taocrm')->model('members_recommend_no');
            $code = array('id'=>'');
            $recno_mod->insert($code);
            $insert_data['self_code'] = $code['id'] + 1000000000;
            $insert_data['create_time'] = time();
        }
        $rec_mod->save($insert_data);
        //更新推荐关系
        $rec_parent_info = $rec_mod->dump(array('self_code'=>$sdf['parent_code']));
        if($rec_parent_info){
            //“告诉”推荐人，被推荐人了
            $member_rec_d['update_time'] = time();
            $member_rec_d['is_parent'] = 'true';
            $rec_mod->update($member_rec_d,array('self_code'=>$sdf['parent_code']));

            //把没有子推荐的状态改掉
            $is_p = $rec_mod->count(array('parent_code'=>$has_code['parent_code']));
            $member_rec_d2['update_time'] = time();
            $member_rec_d2['is_parent'] = 'false';
            if(!$is_p){
                $rec_mod->update($member_rec_d2,array('self_code'=>$has_code['parent_code']));
            }
        }
        return $insert_data['self_code'];
    }

    function getDefaultLv($shop_id)
    {
        $db = kernel::database();
        $row = $db->selectRow('select lv_id from sdb_ecorder_shop_lv where shop_id="'.$shop_id.'" and is_default="1" and is_active="1"');

        return intval($row['lv_id']);
    }
     
    function addMember($member)
    {
        $db = kernel::database();
        $member['create_time'] = time();
        $db->insert('sdb_taocrm_members',$member);

        return $db->lastinsertid();
    }

    function addMemberAnalysis($memberAnalysis)
    {
        $db = kernel::database();

        $db->insert('sdb_taocrm_member_analysis',$memberAnalysis);

        return $db->lastinsertid();
    }

    function getMember($uname,$mobile){

        return kernel::database()->selectRow('select member_id from sdb_taocrm_members where uname="'.$uname.'" and mobile="'.$mobile.'"');
    }

    function getMemberById($member_id,$cols='*'){

        return kernel::database()->selectRow('select '.$cols.' from sdb_taocrm_members where member_id='.$member_id);
    }

    function update($sdf, &$msg)
    {
        $sdf = kernel::single('ecorder_func')->trim_array($sdf);
    
        $memberInfo = $this->getMemberById($sdf['member_id'],'member_id,uname');
        if(!$memberInfo){
            $msg = '<br/>客户不存在';
            return false;
        }

        $area = $sdf['state'] . '/' . $sdf['city'] . '/' . $sdf['district'];
        kernel::single("ecorder_func")->region_validate($area);
        $area = str_replace('::', '', $area);
        $sdf['area'] = $area;

        if(!empty($sdf['state'])){
            $row = kernel::database()->selectrow('select region_id from sdb_ectools_regions where local_name="'.$sdf['state'].'"');
            $sdf['state'] = $row['region_id'];
        }

        if(!empty($sdf['city'])){
            $row = kernel::database()->selectrow('select region_id from sdb_ectools_regions where local_name="'.$sdf['city'].'"');
            $sdf['city'] = $row['region_id'];
        }

        if(!empty($sdf['district'])){
            $row = kernel::database()->selectrow('select region_id from sdb_ectools_regions where local_name="'.$sdf['district'].'"');
            $sdf['district'] = $row['region_id'];
        }

        if(isset($sdf['sex'])){
            if($sdf['sex'] == 0){
                $sdf['sex']='unkown';
            }else if($sdf['sex'] == 1){
                $sdf['sex']='male';
            }else if($sdf['sex'] == 2){
                $sdf['sex']='female';
            }else{
                $sdf['sex']='unkown';
            }
        }else{
            $sdf['sex']='unkown';
        }

        $member = array(
            'update_time'=>time(),
            'props'=>$sdf['props'],
            'name'=>$sdf['real_name'],
            'area'=>$sdf['area'],
            'state'=>$sdf['state'],
            'city'=>$sdf['city'],
            'district'=>$sdf['district'],
            'addr'=>$sdf['address'],
            'mobile'=>$sdf['mobile'],
            'tel'=>$sdf['tel'],
            'email'=>$sdf['email'],
            'zip'=>$sdf['zip'],
            'alipay_account'=>$sdf['alipay'],
            'alipay_no'=>$sdf['alipay'],
            'sex'=>$sdf['sex'],
            'birthday'=>strtotime($sdf['birthday']),
            'remark'=>$sdf['remark'],
            'is_vip'=>($sdf['is_vip'] == 1) ? 'true' : 'false',
            'sms_blacklist'=>($sdf['is_sms_black'] == 1) ? 'true' : 'false',
            'edm_blacklist'=>($sdf['is_email_black'] == 1) ? 'true' : 'false',
        );
        
        //防止空数据覆盖用户的输入数据
        $member = kernel::single('ecorder_func')->trim_array($member);
        $member = kernel::single('ecorder_func')->clear_value($member);

        //更新用户名
        if($sdf['uname']){
            $member['uname'] = $sdf['uname'];
            $memberInfo['uname'] = $sdf['uname'];
        }

        $memberId = $this->updateMember($sdf['member_id'],$member);
        if(!$memberId){
            $msg = '<br/>更新客户失败';
            return false;
        }

        $this->process_member_ext($sdf, $memberId);

        //保存客户自定义属性
        if($sdf['props']!=''){
            $props = json_decode($sdf['props'], true);
            $oMemberProp = app::get('taocrm')->model('member_overall_property');
            $oMemberProp->delete(array('member_id'=>$sdf['member_id']));
            foreach($props as $k=>$v){
                if($k && $v){
                    $save = array(
                        'member_id'=>$sdf['member_id'],
                        'uname'=>$memberInfo['uname'],
                        'property'=>$k,
                        'value'=>$v,
                    );
                    $oMemberProp->insert($save);
                }
            }
        }

        if($sdf['shop_id']){
            $memberAnalysis = array(
            'district'=>$sdf['district'],
            'is_vip'=>($sdf['is_vip'] == 1) ? 'true' : 'false',
            'update_time'=>time(),
            );
            $memberAnalysisId = $this->updateMemberAnalysis($sdf['shop_id'],$sdf['member_id'],$memberAnalysis);
            if(!$memberAnalysisId){
                $msg .= '<br/>更新店铺客户失败';
                return false;
            }
        }

        return $memberId;
    }

    function updateMember($member_id,$member){
        $db = kernel::database();

        if($db->update('sdb_taocrm_members',$member,'member_id='.$member_id)){
            return $member_id;
        }else{
            return false;
        }
    }

    function updateMemberAnalysis($shop_id,$member_id,$memberAnalysis){
        $db = kernel::database();

        if($db->update('sdb_taocrm_member_analysis',$memberAnalysis,'shop_id="'.$shop_id.'" and member_id='.$member_id)){
            return $member_id;
        }else{
            return false;
        }
    }

    function get($member_id,& $msg){
        $member = $this->getMemberById($member_id,'member_id,uname,name,source_terminal,state,city,district,addr,mobile,tel,email,zip,alipay_account as alipay,birthday,sex,remark,is_vip,sms_blacklist as is_sms_black,edm_blacklist as is_email_black');
        if(!$member){
            $msg = '客户不存在';
            return false;
        }

        //推荐码
        $mRObj = app::get('taocrm')->model('members_recommend');
        $mRData = $mRObj->dump(array('member_id'=>$member_id));
        $member['self_code'] = $mRData['self_code'];

        $arr = array('unkown'=>0,'female'=>2,'male'=>1);
        $member['sex'] = $arr[$member['sex']];
        $member['is_vip'] = ($member['is_vip'] == 'false') ? 0 : 1;
        $member['is_sms_black'] = ($member['is_sms_black'] == 'false') ? 0 : 1;
        $member['is_email_black'] = ($member['is_email_black'] == 'false') ? 0 : 1;

        if(!empty($member['state'])){
            $row = kernel::database()->selectrow('select local_name from sdb_ectools_regions where region_id="'.$member['state'].'"');
            $member['state'] = $row['local_name'];
        }

        if(!empty($member['city'])){
            $row = kernel::database()->selectrow('select local_name from sdb_ectools_regions where region_id="'.$member['city'].'"');
            $member['city'] = $row['local_name'];
        }

        if(!empty($member['district'])){
            $row = kernel::database()->selectrow('select local_name from sdb_ectools_regions where region_id="'.$member['district'].'"');
            $member['district'] = $row['local_name'];
        }

        return $member;
    }

    function getMembers($sdf){
        $arrWhere = array(1);
        if(!empty($sdf['start_update_date'])){
            $arrWhere[] = 'update_time >='.strtotime($sdf['start_update_date']);
        }elseif(!empty($sdf['end_update_date'])){
            $arrWhere[] = 'update_time <='.strtotime($sdf['end_update_date']);
        }

        if(!empty($sdf['start_created_date'])){
            $arrWhere[] = 'create_time >='.strtotime($sdf['start_created_date']);
        }elseif(!empty($sdf['end_created_date'])){
            $arrWhere[] = 'create_time <='.strtotime($sdf['end_created_date']);
        }

        $sdf['page']  =  $sdf['page']  - 1;
        $sql = 'select member_id from sdb_taocrm_members where '.implode(' and ', $arrWhere) .' limit '.($sdf['page'] * $sdf['page_size']).','.$sdf['page_size'];
        $mids = kernel::database()->select($sql);
        $msg = '';
        $members = array();
        foreach($mids as $mid){
            $member = $this->get($mid['member_id'],$msg);
            if($member){
                $members[] = $member;
            }
        }

        return $members;
    }
    
    //会员扩展属性
    public function process_member_ext($member_info, $member_id)
    {
        if($member_info['birthday'] && $member_id){
            $member_info['birthday'] = substr($member_info['birthday'],0,10);
            $member_info['birthday'] = 
                str_replace(array('.','/'),'-',$member_info['birthday']);
            list($member_info['b_year'], $member_info['b_month'], $member_info['b_day']) = 
                explode('-', $member_info['birthday']);
        
            $ext_info = array();
            $ext_info['member_id'] = $member_id;
            $ext_info['b_year'] = $member_info['b_year'];
            $ext_info['b_month'] = $member_info['b_month'];
            $ext_info['b_day'] = $member_info['b_day'];
            kernel::single('taocrm_service_member')->save_member_ext($ext_info);
        }
    }

    //会员签到
    public function update_member_signin($sdf, &$msg)
    {
        $sdf = kernel::single('ecorder_func')->trim_array($sdf);

        $memberInfo = $this->getMemberById($sdf['member_id'],'member_id,uname');
        if(!$memberInfo){
            $msg = '客户不存在';
            return false;
        }

        $sIObj = app::get('market')->model('wx_sign_in_log');
        //获取微信会员表数据
        $objWxmember = app::get('market')->model('wx_member');
        $wxMember = $objWxmember->dump(array('member_id'=>$sdf['member_id']));
        if(empty($wxMember)){
            $fromusername = null;
        }else{
            $fromusername = $wxMember['FromUserName'];
        }
        //查询是否签到过
        $re_bool =  $objWxmember->checkRegistPoint_new($fromusername,date('Y-m-d',$sdf['signin_time']),$sdf['member_id']);
        if(!$re_bool){
            $msg = '今天签到过！';
            return false;
        }

        $insert_data = array('fromusername'=>$fromusername,'member_id'=>$sdf['member_id'],'create_time'=>$sdf['signin_time']);
        $sIObj->saveSignInLog($insert_data);

        return $sdf['member_id'];
    }

    //更新推荐关系接口
    function update_recommend($sdf, &$msg)
    {
        $rec_mod = app::get('taocrm')->model('members_recommend');
        $referee_data = $rec_mod->dump(array('member_id'=>$sdf['referee_member_id']));
        $recommended_member_ids = json_decode($sdf['recommended_member_ids'],true);
        $re = $rec_mod->update(array('parent_code'=>$referee_data['self_code']),array('member_id'=>$recommended_member_ids));
        if($re){
            return $sdf['referee_member_id'];
        }else{
            return false;
        }
    }

    /**
     * @access public
     * @func  update_member_stored_value更新会员预存款接口
     * @params
     * @return int 客户ID
     * @author lb
     * @time 2015-08-19 16:29
     */
    public function update_member_stored_value($sdf, &$msg)
    {
        $sdf = kernel::single('ecorder_func')->trim_array($sdf);
        $memberInfo = $this->getMemberById($sdf['member_id'],'member_id,uname,mobile');
        if(!$memberInfo){
            $msg = '客户不存在';
            return false;
        }

        $params['method'] = 'taocrm.stored.update';
        $params['member_id'] = $sdf['member_id'];
        $params['shop_id'] = $sdf['shop_id'];
        $params['stored_value'] = $sdf['stored_value'];
        $params['trade_no'] = $sdf['trade_no'];
        $params['payment_no'] = $sdf['payment_no'];
        $params['sn'] = $sdf['sn'];
        $params['remark'] = $sdf['remark'];
        $op_user = kernel::single('desktop_user')->get_name();
        $params['op_user'] = $op_user;
        //$objMembers = app::get('taocrm')->model('members');
        //$member_info = $objMembers->getList('mobile,uname',array('member_id'=>$sdf['member_id']));
        $params['uname'] =  $sdf['uname'] ? $sdf['uname'] : $memberInfo['uname'] ;
        $params['mobile'] =  $sdf['mobile'] ? $sdf['mobile'] : $memberInfo['mobile'] ;
        $taocrm_middleware_javahttp = kernel::single('taocrm_middleware_javahttp');
        $result = $taocrm_middleware_javahttp->exec($params);
        return $result;
    }

    /**
     * @access public
     * @func  get_member_stored_value获取会员预存款接口
     * @params
     * @return int 客户ID
     * @author lb
     * @time 2015-08-19 16:29
     */
    public function get_member_stored_value($sdf, &$msg)
    {
        $sdf = kernel::single('ecorder_func')->trim_array($sdf);
        $memberInfo = $this->getMemberById($sdf['member_id'],'member_id,uname');
        if(!$memberInfo){
            $msg = '客户不存在';
            return false;
        }
        $params['method'] = 'taocrm.stored.get';
        $params['member_id'] = $sdf['member_id'];
        $taocrm_middleware_javahttp = kernel::single('taocrm_middleware_javahttp');
        $result = $taocrm_middleware_javahttp->exec($params);
        return $result;
    }

    /**
     * @access public
     * @func get_member_stored_value_log 获取会员预存款日志接口
     * @params
     * @return int 客户ID
     * @author lb
     * @time 2015-08-19 16:29
     */
    public function get_member_stored_value_log($sdf, &$msg)
    {
        $sdf = kernel::single('ecorder_func')->trim_array($sdf);
        $memberInfo = $this->getMemberById($sdf['member_id'],'member_id,uname');
        if(!$memberInfo){
            $msg = '客户不存在';
            return false;
        }
        $params['method'] = 'taocrm.storedlog.get';
        $params['member_id'] = $sdf['member_id'];
        $params['shop_id'] = $sdf['shop_id'];
        $params['page'] = ($sdf['page'] ? $sdf['page'] : 1);
        $params['page_size'] = ($sdf['page_size'] ? $sdf['page_size'] : 50);
        $taocrm_middleware_javahttp = kernel::single('taocrm_middleware_javahttp');
        $result = $taocrm_middleware_javahttp->exec($params);
        return $result;
    }

    /**
     * @access public
     * @func return_rebate 一键返利/返积分
     * @params
     * @return int 客户ID
     * @author lb
     * @time 2015-08-19 16:29
     */
    public function return_rebate($sdf, &$msg)
    {
        $sdf = kernel::single('ecorder_func')->trim_array($sdf);
        if(!$sdf['rebate_ids'] || !$sdf['rebate_type']){
            $msg = '返利周期不存在或者返利类型不能为空';
            return false;
        }
        $params['method'] = 'taocrm.rebate.return';
        $params['rebate_ids'] = $sdf['rebate_ids'];
        $params['rebate_type'] = $sdf['rebate_type'];
        $op_user = kernel::single('desktop_user')->get_name();
        $params['op_user'] = $op_user;
        $taocrm_middleware_javahttp = kernel::single('taocrm_middleware_javahttp');
        $result = $taocrm_middleware_javahttp->exec($params);
        return $result;
    }

}