<?php
/**
 * 客户API接口，系统对接用
 */
class taocrm_rpc_response_members extends taocrm_rpc_response
{

    public function add($sdf, &$responseObj)
    {
        //写入API日志
        $log = app::get('ecorder')->model('api_log');
        $logTitle = '客户创建接口[uname：'. $sdf['uname'] .']';
        $logInfo = '客户创建接口：<br/>';
        $logInfo .= '接收参数 $sdf 信息：' . var_export($sdf, true) . '<br/>';
    
        $apiParams = array(
            'uname'=>array('label'=>'客户用户名','required'=>true),
            'uid'=>array('label'=>'外部会员ID','required'=>false),
            'node_id'=>array('label'=>'节点ID','required'=>false),
            'real_name'=>array('label'=>'客户真实姓名','required'=>false),
            'source_terminal'=>array('label'=>'来源终端','required'=>false),
            'zip'=>array('label'=>'邮编','required'=>false),
            'state'=>array('label'=>'省份','required'=>false),
            'city'=>array('label'=>'城市','required'=>false),
            'district'=>array('label'=>'地区','required'=>false),
            'address'=>array('label'=>'详细地址','required'=>false),
            'mobile'=>array('label'=>'手机','required'=>false),
            'email'=>array('label'=>'email','required'=>false),
            'birthday'=>array('label'=>'生日','required'=>false),
            'sex'=>array('label'=>'性别','required'=>false),
            'is_vip'=>array('label'=>'是否贵宾','required'=>false),
            'is_sms_black'=>array('label'=>'短信黑名单','required'=>false),
            'is_email_black'=>array('label'=>'邮件黑名单','required'=>false),
            'alipay'=>array('label'=>'支付宝账号','required'=>false),
            'remark'=>array('label'=>'客户备注','required'=>false),
            'reg_time'=>array('label'=>'注册时间','required'=>false),
            'props'=>array('label'=>'自定义属性','required'=>false),
            'qq'=>array('label'=>'qq','required'=>false),
            'weibo'=>array('label'=>'微博','required'=>false),
            'weixin'=>array('label'=>'微信','required'=>false),
            'wangwang'=>array('label'=>'旺旺','required'=>false),
            'parent_code'=>array('label'=>'推荐人推荐码','required'=>false),
        );
        //$this->checkApiParams($apiParams,$sdf, $responseObj);

        $sdf = kernel::single('ecorder_func')->trim_array($sdf);

        if(base_rpc_service::$node_id){
            $sdf['shop_id'] = $this->get_shop_id($responseObj);
        }else{
            $sdf['shop_id'] = 0;
        }

        $membersObj = kernel::single("taocrm_members");
        $msg = '';
        $memberId = $membersObj->add($sdf,$msg);
        if(!$memberId){
            $log->write_log($log->gen_id(), $logTitle, __CLASS__, __METHOD__, '', '', 'response', 'fail', $logInfo.$msg, array('task_id'=>$sdf['uname']));
            $responseObj->send_user_error(app::get('base')->_($msg));
        }
        
        $log->write_log($log->gen_id(), $logTitle, __CLASS__, __METHOD__, '', '', 'response', 'success', $logInfo.$msg, array('task_id'=>$sdf['uname']));
        return array('member_id'=>$memberId['member_id'],'self_code'=>$memberId['self_code']);
    }

    public function update($sdf, &$responseObj)
    {
        //写入API日志
        $log = app::get('ecorder')->model('api_log');
        $logTitle = '客户更新接口[uname：'. $sdf['uname'] .']';
        $logInfo = '客户更新接口：<br/>';
        $logInfo .= '接收参数 $sdf 信息：' . var_export($sdf, true) . '<br/>';
        
        $apiParams = array(
            'member_id'=>array('label'=>'客户ID','required'=>true),
            'node_id'=>array('label'=>'节点ID','required'=>false),
            'real_name'=>array('label'=>'客户真实姓名','required'=>false),
            'zip'=>array('label'=>'邮编','required'=>false),
            'state'=>array('label'=>'省份','required'=>false),
            'city'=>array('label'=>'城市','required'=>false),
            'district'=>array('label'=>'地区','required'=>false),
            'address'=>array('label'=>'详细地址','required'=>false),
            'mobile'=>array('label'=>'手机','required'=>false),
            'email'=>array('label'=>'email','required'=>false),
            'birthday'=>array('label'=>'生日','required'=>false),
            'sex'=>array('label'=>'性别','required'=>false),
            'is_vip'=>array('label'=>'是否贵宾','required'=>false),
            'is_sms_black'=>array('label'=>'短信黑名单','required'=>false),
            'is_email_black'=>array('label'=>'邮件黑名单','required'=>false),
            'alipay'=>array('label'=>'支付宝账号','required'=>false),
            'remark'=>array('label'=>'客户备注','required'=>false),
            'props'=>array('label'=>'自定义属性','required'=>false),
        );
        //$this->checkApiParams($apiParams,$sdf, $responseObj);
        
        $sdf = kernel::single('ecorder_func')->trim_array($sdf);
        
        if(!$sdf['member_id'] && $sdf['uid']){
            $sdf['member_id'] = $sdf['uid'];
        }

        if(base_rpc_service::$node_id){
            $shop = $this->get_shop('shop_id',$responseObj);
            $sdf['shop_id'] = $shop['shop_id'];
        }else{
            $sdf['shop_id'] = 0;
        }

        $membersObj = kernel::single("taocrm_members");
        $msg = '';
        $memberId = $membersObj->update($sdf,$msg);
        if(!$memberId){
            $log->write_log($log->gen_id(), $logTitle, __CLASS__, __METHOD__, '', '', 'response', 'fail', $logInfo.$msg, array('task_id'=>$sdf['uname']));          
            $responseObj->send_user_error(app::get('base')->_($msg));
        }
        
        $log->write_log($log->gen_id(), $logTitle, __CLASS__, __METHOD__, '', '', 'response', 'success', $logInfo.$msg, array('task_id'=>$sdf['uname']));
        return array('member_id'=>$memberId);

    }

    public function get($sdf, &$responseObj)
    {
        $apiParams = array(
            'member_id'=>array('label'=>'客户ID','required'=>true),
        );
        $this->checkApiParams($apiParams,$sdf, $responseObj);

        $membersObj=kernel::single("taocrm_members");
        $msg = '';
        $member = $membersObj->get($sdf['member_id'],$msg);
        if(!$member){
            $responseObj->send_user_error(app::get('base')->_($msg));
        }

        return array('member'=>$member);
    }
     
    public function getlist($sdf, &$responseObj)
    {
        $apiParams = array(
            'start_update_date'=>array('label'=>'客户更新开始时间','required'=>false),
            'end_update_date'=>array('label'=>'客户更新结束时间','required'=>false),
            'start_created_date'=>array('label'=>'客户更新时间','required'=>false),
            'end_created_date'=>array('label'=>'客户更新时间','required'=>false),
            'page_size'=>array('label'=>'页码','required'=>true,'max'=>100),
            'page'=>array('label'=>'页数','required'=>true),
        );
        $this->checkApiParams($apiParams,$sdf, $responseObj);

        $membersObj=kernel::single("taocrm_members");
        $msg = '';
        $members = $membersObj->getMembers($sdf,$msg);

        return array('members'=>$members);
    }
    
    //根据手机号查询客户是否存在
    //如果存在，发送验证码
    public function get_mobile($sdf, &$responseObj)
    {
        $send_sms = intval($sdf['send_sms']);
        $err_msg = '';
        if( ! $sdf['mobile']) {
            $err_msg = '手机号码必须填写';
            return array('err_msg' => $err_msg);
        }
        
        $member = array(
            'member_id' => 0,
            'trade_points' => 0,
            'mobile' => $sdf['mobile'],
            'err_msg' => '',
        );
        
        $mdl = app::get('taocrm')->model('member_level');
        $rs = $mdl->getList('level_id,level_name');
        if($rs){
            foreach($rs as $v){
                $level[$v['level_id']] = $v['level_name'];
            }
        }
        
        $mdl_members = app::get('taocrm')->model('members');
        $rs = $mdl_members->dump(array('mobile'=>$sdf['mobile']), 'member_id,level_id,points,email');
        if($rs){
            $member['member_id'] = $rs['member_id'];
            $member['level'] = $level[$rs['level_id']];
            
            //查询客户全局积分
            $mdl_member_points = app::get('taocrm')->model('member_points');
            $rs = $mdl_member_points->dump(array('member_id'=>$member_id), 'sum(points) as points');
            if($rs) $member['trade_points'] = $rs['points'];
            
            $passcode = rand(1111, 9999);
        }
        
        //发送短信验证码
        if(0 && $send_sms==1 && $passcode){
            $mobile = trim($sdf['mobile']);            
            $content = "尊敬的用户，您的验证码为(4位数字):{$passcode}。请在一小时内用此验证码进行验证。";

            //发送验证码
            $market_service_sms = kernel::single('market_service_sms');
            $market_service_sms->send_passcode($mobile, $content, $err_msg);
        }
        
        return array('member'=>$member, 'passcode'=>$passcode, 'err_msg'=>$err_msg);
    }

    //会员签到接口
    public function update_member_signin($sdf, &$responseObj)
    {
        //写入API日志
        $log = app::get('ecorder')->model('api_log');
        $logTitle = '会员签到接口[member_id：'. $sdf['member_id'] .']';
        $logInfo = '会员签到接口：<br/>';
        $logInfo .= '接收参数 $sdf 信息：' . var_export($sdf, true) . '<br/>';

        $apiParams = array(
            'member_id'=>array('label'=>'会员id','required'=>true),
            'node_id'=>array('label'=>'节点ID','required'=>false),
            'signin_time'=>array('label'=>'签到时间','required'=>true),
        );
        //$this->checkApiParams($apiParams,$sdf, $responseObj);

        $sdf = kernel::single('ecorder_func')->trim_array($sdf);

        $membersObj = kernel::single("taocrm_members");
        $msg = '';
        $memberId = $membersObj->update_member_signin($sdf,$msg);
        if(!$memberId){
            $log->write_log($log->gen_id(), $logTitle, __CLASS__, __METHOD__, '', '', 'response', 'fail', $logInfo.$msg, array('task_id'=>$sdf['member_id']));
            $responseObj->send_user_error(app::get('base')->_($msg));
        }

        $log->write_log($log->gen_id(), $logTitle, __CLASS__, __METHOD__, '', '', 'response', 'success', $logInfo.$msg, array('task_id'=>$sdf['member_id']));
        return array('member_id'=>$memberId);

    }

    //更新推荐关系绑定接口
    public function update_recommend($sdf, &$responseObj)
    {
        $apiParams = array(
            'referee_member_id'=>array('label'=>'推荐人会员id','required'=>true),
            'recommended_member_ids'=>array('label'=>'被推荐会员ids','required'=>true)
        );
        $this->checkApiParams($apiParams,$sdf, $responseObj);

        $membersObj=kernel::single("taocrm_members");
        $msg = '';
        $referee_member_id = $membersObj->update_recommend($sdf,$msg);

        return array('referee_member_id'=>$referee_member_id);
    }
    /**
     * @access public
     * @func  update_member_stored_value  更新会员预存款接口
     * @params
     * @return int 客户ID
     * @author lb
     * @time 2015-08-19 16:29
     */
    public function update_member_stored_value($sdf, &$responseObj)
    {
        //写入API日志
        $log = app::get('ecorder')->model('api_log');
        $logTitle = '更新会员预存款接口[member_id：'. $sdf['member_id'] .']';
        $logInfo = '更新会员预存款接口：<br/>';
        $logInfo .= '接收参数 $sdf 信息：' . var_export($sdf, true) . '<br/>';
        $apiParams = array(
            'member_id'=>array('label'=>'客户ID','required'=>true),
            'stored_value'=>array('label'=>'需要更新的预存款值','required'=>true),
            'remark'=>array('label'=>'备注','required'=>false),
            'shop_id'=>array('label'=>'店铺ID','required'=>false),
        );
        //$this->checkApiParams($apiParams,$sdf, $responseObj);
        $sdf = kernel::single('ecorder_func')->trim_array($sdf);
        if(base_rpc_service::$node_id){
            $sdf['shop_id'] = $this->get_shop_id($responseObj);
        }else{
            $sdf['shop_id'] = 0;
        }
        $membersObj = kernel::single("taocrm_members");
        $msg = '';
        $memberId = $membersObj->update_member_stored_value($sdf,$msg);
        if(!$memberId){
            $log->write_log($log->gen_id(), $logTitle, __CLASS__, __METHOD__, '', '', 'response', 'fail', $logInfo.$msg, array('task_id'=>$sdf['member_id']));
            $responseObj->send_user_error(app::get('base')->_($msg));
        }
        $log->write_log($log->gen_id(), $logTitle, __CLASS__, __METHOD__, '', '', 'response', 'success', $logInfo.$msg, array('task_id'=>$sdf['member_id']));
        return array('member_id'=>$memberId['member_id']);
    }
    /**
     * @access public
     * @func  get_member_stored_value获取会员预存款接口
     * @params
     * @return int 客户ID
     * @author lb
     * @time 2015-08-19 16:29
     */
    public function get_member_stored_value($sdf, &$responseObj)
    {
        //写入API日志
        $log = app::get('ecorder')->model('api_log');
        $logTitle = '获取会员预存款接口[member_id：'. $sdf['member_id'] .']';
        $logInfo = '获取会员预存款接口：<br/>';
        $logInfo .= '接收参数 $sdf 信息：' . var_export($sdf, true) . '<br/>';
        $apiParams = array(
            'member_id'=>array('label'=>'会员ID','required'=>true),
            'shop_id'=>array('label'=>'店铺ID','required'=>false),
        );
        //$this->checkApiParams($apiParams,$sdf, $responseObj);
        $sdf = kernel::single('ecorder_func')->trim_array($sdf);
        if(base_rpc_service::$node_id){
            $sdf['shop_id'] = $this->get_shop_id($responseObj);
        }else{
            $sdf['shop_id'] = 0;
        }
        $membersObj = kernel::single("taocrm_members");
        $msg = '';
        $memberId = $membersObj->get_member_stored_value($sdf,$msg);
        if(!$memberId){
            $log->write_log($log->gen_id(), $logTitle, __CLASS__, __METHOD__, '', '', 'response', 'fail', $logInfo.$msg, array('task_id'=>$sdf['member_id']));
            $responseObj->send_user_error(app::get('base')->_($msg));
        }
        $log->write_log($log->gen_id(), $logTitle, __CLASS__, __METHOD__, '', '', 'response', 'success', $logInfo.$msg, array('task_id'=>$sdf['member_id']));
        return array('member_id'=>$memberId['member_id'],'stored_value'=>$memberId['stored_value']);
    }
    /**
     * @access public
     * @func  gget_member_stored_value_log 获取会员预存款更新日志接口
     * @params
     * @return int 客户ID
     * @author lb
     * @time 2015-08-19 16:29
     */
    public function get_member_stored_value_log($sdf, &$responseObj)
    {
        //写入API日志
        $log = app::get('ecorder')->model('api_log');
        $logTitle = '获取会员预存款更新日志接口[member_id：'. $sdf['member_id'] .']';
        $logInfo = '获取会员预存款更新日志接口：<br/>';
        $logInfo .= '接收参数 $sdf 信息：' . var_export($sdf, true) . '<br/>';
        $apiParams = array(
            'member_id'=>array('label'=>'会员ID','required'=>true),
            'shop_id'=>array('label'=>'店铺ID','required'=>false),
        );
        //$this->checkApiParams($apiParams,$sdf, $responseObj);
        $sdf = kernel::single('ecorder_func')->trim_array($sdf);
        if(base_rpc_service::$node_id){
            $sdf['shop_id'] = $this->get_shop_id($responseObj);
        }else{
            $sdf['shop_id'] = 0;
        }
        $membersObj = kernel::single("taocrm_members");
        $msg = '';
        $memberId = $membersObj->get_member_stored_value_log($sdf,$msg);
        if(!$memberId){
            $log->write_log($log->gen_id(), $logTitle, __CLASS__, __METHOD__, '', '', 'response', 'fail', $logInfo.$msg, array('task_id'=>$sdf['member_id']));
            $responseObj->send_user_error(app::get('base')->_($msg));
        }
        $log->write_log($log->gen_id(), $logTitle, __CLASS__, __METHOD__, '', '', 'response', 'success', $logInfo.$msg, array('task_id'=>$sdf['member_id']));
        return array('member_id'=>$memberId['member_id'],'log'=>$memberId['log']);
    }

}