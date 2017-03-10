<?php

class taocrm_middleware_activity extends taocrm_middleware_connect{

    function GetMarketActivityInfo($activeId)
    {
        $db = kernel::database();
        $sql = 'select * from sdb_market_active where active_id = '.$activeId;
        $activity = $db->selectrow($sql);
        if(!$activity['shop_id'] && $activity['create_source'] != 'tags'){
            return array('err_msg'=>'活动未指定店铺(shop_id is null)');
        }

        //缓存ID
        $CacheId = $activity['cache_id'];

        //是否是活动人数对照组
        $personAB = $activity['control_group'] == 'no' ? 0 : 1;
        //是否开启短信对照组
        $messageAB = 0 >= $activity['template_id_b'] ? 0 : 1;
        $filter = array();
        $member_list = '';
        if ($activity) {
            if ($activity['member_list'] != '') {
                $member_list = unserialize($activity['member_list']);
                if(strstr($member_list[0],'group_id')){
                    // 1.自定义分组
                    $group_id = str_replace('group_id:','',$member_list[0]);
                    $sql = "SELECT filter FROM sdb_taocrm_member_group WHERE group_id = $group_id";
                    $rs = $db->selectrow($sql);
                    $filter['filter'] = unserialize($rs['filter']);
                    $filter['shop_id'] = $activity['shop_id'];
                    $member_list = '';
                }else{
                    // 2.直接勾选客户
                    if ($member_list) {
                        //                        $market_user_id = kernel::single('desktop_user')->get_id();
                        //                        base_kvstore::instance('analysis')->fetch('filter_member_' . $market_user_id, $membersList);
                        //                        if ($membersList != '') {
                        //                            print_r($membersList);
                        //                            exit;
                        //                            $member_list = explode(',', $membersList);
                        //                            $filter['memberIdStr'] = implode(',', $member_list);
                        //                        }
                        //                        else {
                        //                            $filter['memberIdStr'] = implode(',', $member_list);
                        //                            print_r($filter);
                        //                            exit;
                        //                        }
                    }
                }
            }
            elseif ($activity['filter_sql'] != '') {

            }
            else {
                $filter = unserialize($activity['filter_mem']);
            }
        }
        //发送类型
        $sendType = unserialize($activity['type']);
        if (count($sendType) == 1) {
            $sendTypeSmsOrEmail = $sendType[0];
        }
        elseif (count($sendType) > 1) {
            $sendTypeSmsOrEmail = $sendType[1];
        }
        $filter['personAB'] = $personAB;
        $filter['messageAB'] = $messageAB;
        $filter['smsOrMail'] = $sendTypeSmsOrEmail == 'sms' ? 'sms' : 'Mail';
        $filter['reSendTime'] = time() - 86400;

        //走缓存
        if ($CacheId > 0) {
            if (empty($filter['shop_id'])) {
                $filter['shop_id'] = $activity['shop_id'];
            }
            $filter['cacheId'] = $CacheId;
            $res = $this->TaskInfoByCacheId($filter);
        }elseif($member_list){
                $filter['shopId'] = $activity['shop_id'];
                $filter['memberIdStr'] = implode(',', $member_list);
            $res = $this->TaskInfoByMemberId($filter);
        }else{
            $res = $this->TaskInfo($filter);
            }
        //err_log($res);

        return $res;
    }

    function getCacheInfo($cache_id){
        $data['cacheId'] = $cache_id;
        return $this->TaskExsByCacheId($data);
    }
    //分销营销活动信息
    function GetFxMarketActivityInfo($activeId)
    {
        $db = kernel::database();
        $activity = $db->selectrow('select * from sdb_market_fx_activity where activity_id = '.$activeId);
        if(!$activity['shop_id']){
            return 0;
        }
        //缓存ID
        $CacheId = $activity['cache_id'];

        $filter = array();
        $member_list = '';
        if ($activity) {
            if ($activity['member_list'] != '') {
                $member_list = unserialize($activity['member_list']);
                if(strstr($member_list[0],'group_id')){
                    // 1.自定义分组
                    $group_id = str_replace('group_id:','',$member_list[0]);
                    $sql = "SELECT filter FROM sdb_taocrm_fx_member_group WHERE group_id = $group_id";
                    $rs = $db->selectrow($sql);
                    $filter['filter'] = unserialize($rs['filter']);
                    $filter['shop_id'] = $activity['shop_id'];
                    $member_list = '';
                }
            }elseif ($activity['filter_sql'] != '') {

            }else {
                $filter['filter'] = unserialize($activity['filter_mem']);
            }
        }
        //发送类型
        $sendType = unserialize($activity['type']);
        if (count($sendType) == 1) {
            $sendTypeSmsOrEmail = $sendType[0];
        }
        elseif (count($sendType) > 1) {
            $sendTypeSmsOrEmail = $sendType[1];
        }
        $filter['smsOrMail'] = $sendTypeSmsOrEmail == 'sms' ? 'sms' : 'Mail';
        //$filter['reSendTime'] = time() - 86400;

        //走缓存
        if ($CacheId > 0) {
            if (empty($filter['shop_id'])) {
                $filter['shop_id'] = $activity['shop_id'];
            }
            $filter['cacheId'] = $CacheId;
            //return json_decode($this->TaskInfoByCacheId($filter), true);
            return $this->FxTaskInfoByCacheId($filter);
            //return $this->GetCTaskByMemberInfo($filter);
        }
        else {
            if ($member_list) {
                $filter['shopId'] = $activity['shop_id'];
                $filter['memberIdStr'] = implode(',', $member_list);
                return $this->FxTaskInfoByMemberId($filter);
            }
            return $this->FxTaskInfo($filter);
        }
    }


    /**
     * 模拟调试
     */
    public function GetCTaskByMemberInfo($data)
    {
        //调用接口数据
        //return json_decode($this->TaskInfo($filter), true);
        $message = array('Count' => 10, 'Send' => 10, 'UnSend' => 10, 'ReSend' => 0, 'p1' => 10, 'p2' => 5, 'p3' => 0, 'p4' => 0);
        return $message;
    }

    function GetMarketActivityInfo_back($activeId)
    {
        $db = kernel::database();
        $sql = 'select * from sdb_market_active where active_id='.$activeId;
        $activity = $db->selectrow($sql);
        //err_log($activity);
        if(!$activity['shop_id']){
            return array('rsp'=>'fail', 'Error shop_id');
        }

        //ת��filter��ʽ
        $shopId = $activity['shop_id'];
        $activity['filter_mem'] = unserialize($activity['filter_mem']);
        $param = $this->packFilter($shopId,$activity['filter_mem']['filter']);
        //echo '<pre>';var_dump($param);
        $param['activityId'] = $activeId;
        //��������
        $http = new base_httpclient;
        $result  = $http->post(self::GET_ACTIVITY_INFO_URL, $param);
        //err_log($result);
        $result = json_decode($result,true);
        $info = array();
        if($result['rsp'] == 'succ'){
            $info = $result['info'];
        }
        return $result;
    }

    /**
     * ִ��Ӫ��
     */
    function ExecMarketActivity($activeId)
    {
        $db = kernel::database();
        $activity = $db->selectrow('select * from sdb_market_active where active_id = '.$activeId);
        if(!$activity['shop_id'] && $activity['create_source']!='tags'){
            return 0;
        }
        //缓存ID
        $CacheId = $activity['cache_id'];

        //是否是活动人数对照组
        $personAB = $activity['control_group'] == 'no' ? 0 : 1;
        //是否开启短信对照组
        $messageAB = 0 >= $activity['template_id_b'] ? 0 : 1;
        //企业帐号密码相关
        $smsInfo = json_decode($activity['active_remark'], true);
        //        print_r($smsInfo);
        //        exit;
        $filter = array();
        $member_list = array();
        //条件过滤器
        if ($activity['filter_mem'] != '') {
            //营销活动
            $queryFilter = unserialize($activity['filter_mem']);
        }
        elseif ($activity['member_list'] != '') {
            $member_list = unserialize($activity['member_list']);
            //自定义客户分组
            if (strstr($member_list[0],'group_id')) {
                $group_id = str_replace('group_id:', '',$member_list[0]);
                $sql = "SELECT filter FROM sdb_taocrm_member_group WHERE group_id = $group_id";
                $rs = $db->selectrow($sql);
                $queryFilter = unserialize($rs['filter']);
                $member_list = '';
            }
            else {
                // 2.直接勾选客户
                if ($member_list == '') {
                    $market_user_id = kernel::single('desktop_user')->get_id();
                    base_kvstore::instance('analysis')->fetch('filter_member_' . $market_user_id, $membersList);
                    if ($membersList) {
                        $member_list = explode(',', $membersList);
                    }
                }
                $filter['memberIdStr'] = implode(',', $member_list);
            }
        }
        //发送类型
        $sendType = unserialize($activity['type']);
        if (count($sendType) == 1) {
            $sendTypeSmsOrEmail = $sendType[0];
        }
        elseif (count($sendType) > 1) {
            $sendTypeSmsOrEmail = $sendType[1];
        }
        //        print_r($activity);
        //        exit;
        
        //优惠劵用
        if(isset($smsInfo['ecstore_coupon_id'])){
            $shopObj = app::get(ORDER_APP)->model('shop');
            $shop = $shopObj->dump($activity['shop_id'],'node_id');
            $app_exclusion = app::get('base')->getConf('system.main_app');
            $app_id = $app_exclusion['app_id'];
            $filter['fromNodeId'] = base_shopnode::node_id($app_id);
            $filter['toNodeId'] = $shop['node_id'];
            $filter['couponId'] = $smsInfo['ecstore_coupon_id'];
            $filter['channelType'] = $smsInfo['channelType'];
            //$filter['couponToken'] = base_certificate::token();
            $filter['couponToken'] = base_shopnode::get_token();
            $filter['logId'] = $smsInfo['coupon_log_id'];
        }else{
            //优惠券ID
            $filter['couponId'] = '';
            //店铺SESSION KEY
            $filter['sessionKey'] = '';

            if ($activity['coupon_id'] > 0) {
                //优惠券SQl
                $sql = "SELECT * FROM `sdb_market_coupons` WHERE `coupon_id` = {$activity['coupon_id']}";
                $couponInfo = $db->selectrow($sql);
                if ($couponInfo['outer_coupon_id']) {
                    $filter['couponId'] = $couponInfo['outer_coupon_id'];
                    $sql = "SELECT * FROM `sdb_ecorder_shop` WHERE `shop_id` = '{$activity['shop_id']}'";
                    $shopInfo = $db->selectrow($sql);
                    if ($shopInfo['addon']) {
                        $shopInfoAddon = unserialize($shopInfo['addon']);
                        $filter['sessionKey'] = $shopInfoAddon['session'];
                    }
                }
            }
        }

        $filter['filter'] = $queryFilter;
        $filter['careateSource'] = $activity['create_source'];
        $filter['opUser'] = $activity['op_user'];
        $filter['ip'] = $activity['ip'];
        $filter['shop_id'] = $activity['shop_id'];
        $filter['shopId'] = $activity['shop_id'];
        $filter['taskId'] = $activity['active_id'];
        $filter['isTiming'] = $activity['is_timing'];
        $filter['planTimestamp'] = $activity['plan_send_time'];
        $filter['personAB'] = $personAB;
        $filter['messageAB'] = $messageAB;
        $filter['tamplateA'] = $activity['template_id'];
        $filter['tamplateB'] = $messageAB == 0 ? -1 : $activity['template_id_b'];
        $filter['entId'] = $smsInfo['entId'];
        $filter['entPwd'] = $smsInfo['entPwd'];
        $filter['license'] = $smsInfo['license'];
        $filter['smsTemplateA'] = $activity['templete'];
        $filter['smsTemplateB'] = $activity['templete_b'];
        $filter['shopName'] = $smsInfo['shopName'];
        $filter['reSendTime'] = time() - 86400;
        $filter['reSend'] = $smsInfo['is_send_salemember'];
        $filter['smsOrMail'] = $sendTypeSmsOrEmail == 'sms' ? 'sms' : 'Mail';
        $filter['mailTitle'] = $activity['templete_title'];
        if ($filter['smsOrMail'] == 'Mail') {
            $filter['mailFrom'] = $this->getEdmAccountFrom($smsInfo);
        }
        else {
            $filter['mailFrom'] = '';
        }

        //是否退订N
        if ($activity['unsubscribe'] == 1) {
            if ($filter['smsTemplateA']) {
                $filter['smsTemplateA'] .= ' 退订回N';
            }
            if ($filter['smsTemplateB']) {
                $filter['smsTemplateB'] .= ' 退订回N';
            }
        }

        //积分兑换完整地址
        $url = kernel::base_url(1);
        $url = $url . '/index.php/taocrm/default/index/app/site';
        //将积分兑换地址变为短地址
        $SinaObj = kernel::single('market_shorturl');
        $shorturl = $SinaObj->shortenSinaUrl($url);
        //短地址替换A
        if ($filter['smsTemplateA']) {
            $msgContent = str_replace(array('<{积分兑换}>'), array($shorturl),$filter['smsTemplateA']);
            $filter['smsTemplateA'] = $msgContent;
        }
        //短地址替换B
        if ($filter['smsTemplateB']) {
            $msgContent = str_replace(array('<{积分兑换}>'), array($shorturl),$filter['smsTemplateB']);
            $filter['smsTemplateB'] = $msgContent;
        }

        //半角"符号进行全角转换
        if ($filter['smsOrMail'] != 'Mail') {
            if ($filter['tamplateA']) {
                $filter['tamplateA'] = $this->formatMarkCovert($filter['tamplateA']);
            }
            if ($filter['smsTemplateA']) {
                $filter['smsTemplateA'] = $this->formatMarkCovert($filter['smsTemplateA']);
            }
            if ($filter['tamplateB']) {
                $filter['tamplateB'] = $this->formatMarkCovert($filter['tamplateB']);
            }
            if ($filter['smsTemplateB']) {
                $filter['smsTemplateB'] = $this->formatMarkCovert($filter['smsTemplateB']);
            }
        }



        $cacheError = '';
        $err_msg = '';
        if ($CacheId > 0) {
            //缓存超时时间
            $setTimeOutCacheTime = $activity['cache_id_create_time'] + 1800;
            if (time() > $setTimeOutCacheTime) {
                $cacheError = 'cahce_error';
            }
            else {
                $filter['cacheId'] = $CacheId;
                $result = $this->createTaskByCahceId($filter, $err_msg);
            }
        }else{
            if ($member_list) {
                if (isset($filter['filter'])) {
                    unset($filter['filter']);
                }
                $result = $this->createTaskByMemberIdList($filter, $err_msg);
            }
            else {
                $result = $this->createTask($filter, $err_msg);
            }
        }
        $data['res'] = 'success';
        if ($cacheError == '') {
            if ($result) {
                //-3 超时
                if ($result == 'true') {
                    $time = time();
                    $sql = "UPDATE `sdb_market_active` SET `is_active` = 'execute' , `exec_time` = {$time} WHERE active_id = {$activeId} and is_active='wait_exec' ";
                    $db->exec($sql);
                }
                else {
                    $data = array('res'=>'fail','msg'=>'发送失败:'.$err_msg);
                }
            }
            else {
                $data = array('res'=>'fail','msg'=>'未知错误:'.$err_msg);
            }
        }
        else {
            $time = time();
            $sql = "UPDATE `sdb_market_active` SET `is_active` = 'dead' , `exec_time` = {$time} WHERE active_id = {$activeId}";
            $db->exec($sql);
            $data = array('res' => 'fail', 'msg' => '活动已经过期，请重新创建活动');
        }
        return $data;
    }

    //内存接口，删除活动
    function delete_active($active_id)
    {
        if(!$active_id) return false;
        
        $err_msg = '';
        $filter['filter'] = $queryFilter;
        $filter['taskId'] = $active_id;
        $filter['quartzAction'] = 'delete';
        $filter['isTiming'] = 1;
        $filter['planTimestamp'] = 1;
        
        $db = kernel::database();
        $sql = 'select cache_id,member_list,is_timing,plan_send_time from sdb_market_active where active_id = '.$active_id;
        $rs_active = $db->selectrow($sql);
        
        if($rs_active['is_timing'] == 0) {
            return false;
        }
        
        if($rs_active['cache_id'] > 0) {
            $result = $this->createTaskByCahceId($filter, $err_msg);
        }elseif($rs_active['member_list']!=''){
            $result = $this->createTaskByMemberIdList($filter, $err_msg);
        }else{
            $result = $this->createTask($filter, $err_msg);
        }
    }

    /**
     * ִ	分销营销活动短信发送
     */
    function FxExecMarketActivity($activeId)
    {
        $db = kernel::database();
        $activity = $db->selectrow('select * from sdb_market_fx_activity where activity_id = '.$activeId);
        if(!$activity['shop_id'] && $activity['create_source']!='tags'){
            return 0;
        }
        //缓存ID
        $CacheId = $activity['cache_id'];

        //企业帐号密码相关
        $smsInfo = json_decode($activity['active_remark'], true);
        //        print_r($smsInfo);
        //        exit;
        $filter = array();
        $member_list = array();
        //条件过滤器
        if ($activity['filter_mem'] != '') {
            //营销活动
            $queryFilter = unserialize($activity['filter_mem']);
        }elseif ($activity['member_list'] != '') {
            $member_list = unserialize($activity['member_list']);
            //自定义客户分组
            if (strstr($member_list[0],'group_id')) {
                $group_id = str_replace('group_id:', '',$member_list[0]);
                $sql = "SELECT filter FROM sdb_taocrm_fx_member_group WHERE group_id = $group_id";
                $rs = $db->selectrow($sql);
                $queryFilter = unserialize($rs['filter']);
                $member_list = '';
            }
            else {
                // 2.直接勾选客户
                if ($member_list == '') {
                    $market_user_id = kernel::single('desktop_user')->get_id();
                    base_kvstore::instance('analysis')->fetch('filter_member_' . $market_user_id, $membersList);
                    if ($membersList) {
                        $member_list = explode(',', $membersList);
                    }
                }
                $filter['memberIdStr'] = implode(',', $member_list);
            }
        }
        //发送类型
        $sendType = unserialize($activity['type']);
        if (count($sendType) == 1) {
            $sendTypeSmsOrEmail = $sendType[0];
        }
        elseif (count($sendType) > 1) {
            $sendTypeSmsOrEmail = $sendType[1];
        }
        //        print_r($activity);
        //        exit;
        $filter['filter'] = $queryFilter;
        $filter['shop_id'] = $activity['shop_id'];
        $filter['shopId'] = $activity['shop_id'];
        $filter['taskId'] = $activity['activity_id'];
        $filter['tamplate'] = $activity['template_id'];
        $filter['entId'] = $smsInfo['entId'];
        $filter['entPwd'] = $smsInfo['entPwd'];
        $filter['license'] = $smsInfo['license'];
        $filter['smsTemplate'] = $activity['templete'];
        $filter['shopName'] = $smsInfo['shopName'];
        $filter['reSend'] = $smsInfo['is_send_salemember'];
        $filter['mailTitle'] = $activity['templete_title'];
        $filter['opUser'] = $activity['op_user'];
        $filter['ip'] = $activity['ip'];


        //是否退订N
        if ($activity['unsubscribe'] == 1) {
            if ($filter['smsTemplate']) {
                $filter['smsTemplate'] .= ' 退订回N';
            }
        }

        //积分兑换完整地址
        $url = kernel::base_url(1);
        $url = $url . '/index.php/taocrm/default/index/app/site';
        //将积分兑换地址变为短地址
        $SinaObj = kernel::single('market_shorturl');
        $shorturl = $SinaObj->shortenSinaUrl($url);

        //短地址替换
        if ($filter['smsTemplate']) {
            $msgContent = str_replace(array('<{积分兑换}>'), array($shorturl),$filter['smsTemplate']);
            $filter['smsTemplate'] = $msgContent;
        }


        //半角"符号进行全角转换
        if ($filter['smsOrMail'] != 'Mail') {
            if ($filter['tamplate']) {
                $filter['tamplate'] = $this->formatMarkCovert($filter['tamplate']);
            }
            if ($filter['smsTemplate']) {
                $filter['smsTemplate'] = $this->formatMarkCovert($filter['smsTemplate']);
            }
             
        }
         
        $cacheError = '';
        if ($CacheId > 0) {
            //缓存超时时间
            $setTimeOutCacheTime = $activity['cache_id_create_time'] + 1800;
            if (time() > $setTimeOutCacheTime) {
                $cacheError = 'cahce_error';
            }
            else {
                $filter['cacheId'] = $CacheId;
                $result = $this->FxcreateTaskByCahceId($filter);
            }

        }
        else {
            if ($member_list) {
                if (isset($filter['filter'])) {
                    unset($filter['filter']);
                }
                $result = $this->FxcreateTaskByMemberIdList($filter);
            }
            else {
                //                print_r($filter);
                //                exit;
                $result = $this->FxcreateTask($filter);
            }
        }
        $data['res'] = 'success';
        if ($cacheError == '') {
            if ($result) {
                //-3 超时
                if ($result == 'true') {
                    $time = time();
                    $sql = "UPDATE `sdb_market_fx_activity` SET `is_active` = 'execute' , `exec_time` = {$time} WHERE activity_id = {$activeId} and is_active='wait_exec' ";
                    $db->exec($sql);
                }
                else {
                    $data = array('res'=>'fail','msg'=>'发送失败');
                }
            }
            else {
                $data = array('res'=>'fail','msg'=>'未知错误');
            }
        }
        else {
            $time = time();
            $sql = "UPDATE `sdb_market_fx_activity` SET `is_active` = 'dead' , `exec_time` = {$time} WHERE activity_id = {$activeId}";
            $db->exec($sql);
            $data = array('res' => 'fail', 'msg' => '活动已经过期，请重新创建活动');
        }
        return $data;
    }

    protected function getEdmAccountFrom($data)
    {
        if ($data['account']['edm_email']) {
            $edmEmail = $data['account']['edm_email'];
            $contact = $data['shopName'];
            $from = $edmEmail . ':' . $contact;
            //            $contact = $data['account']['contact'];
            //            if (trim($contact) == '??') {
            //                $from = $edmEmail . ':' . $edmEmail;
            //            }
            //            else {
            //                $from = $edmEmail . ':' . $contact;
            //            }

        }
        else {
            base_kvstore::instance('market')->fetch('account',$edms);
            if (unserialize($edms)) {
                $edmEmail = $edms['edm_email'];
                $contact = $edms['contact'];
                if ($edmEmail) {
                    $edmEmail = 'admin@shopex.cn';
                }

                if (empty($contact)) {
                    $contact = $edmEmail;
                }
                $contact = $edmEmail;

            }
            else {
                $contact = $edmEmail = 'admin@shopex.cn';
            }

            $from = $edmEmail . ':' . $contact;
        }
        return $from;
    }

    public function ExecMarketActivity_back($activeId)
    {
        $db = kernel::database();
        $activity = $db->selectrow('select * from sdb_market_active where active_id = '.$activeId);
        //var_dump($shop_id);exit;
        if(!$activity['shop_id']){
            return 0;
        }

        $shopId = $activity['shop_id'];
        $param = $this->packFilter($shopId,$activity['filter_mem']);
        $http = new base_httpclient;
        $param['activityId'] = $activeId;
        //echo '<pre>';var_export($filter);exit;
        $result  = $http->post(self::EXEC_ACTIVITY_URL, $param);
        var_dump($result);exit;
        $result = json_decode($result,true);
        $info = array();
        if($result['rsp'] == 'succ'){
            $info = $result['info'];
        }
        return $info;
    }

    /**
     * 符号进行转换
     */
    public function formatMarkCovert($string)
    {
        if (strlen($string) <= 0) {
            return '';
        }
        //对"进行全角转换
        $local = 1;
        $newString = '';
        for ($i = 0; $i < strlen($string); $i++) {
            $char = $string[$i];
            if ($string[$i] == '"') {
                if ($local % 2 == 1) {
                    $char = '“';
                }
                else {
                    $char = '”';
                }
                $local++;
            }
            $newString .= $char;
        }
        return $newString;
    }

}
