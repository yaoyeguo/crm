<?php
class market_backstage_crontab{

    function analysis($data){
        $day_time = strtotime($data['day']);

        //统计客户店铺每日分析数据
        //kernel::single('ecorder_service_orders')->countBuys(null,$day_time,$data['type']);

        //执行统计每天店铺交易数据
        //kernel::single('taocrm_service_member')->runAnalysisDay($day_time,$data['type']);

        return array('status'=>'succ');
    }

    function day($data){

        //客户清洗
        $db = kernel::database();
        $sql = 'update sdb_taocrm_member_import as a,sdb_taocrm_members as b set a.succ_member_id=b.member_id where a.mobile=b.mobile and a.is_mobile_valid=1';
        $db->exec($sql);

        //如果有未发送的导入客户短信，进行补发
        //$this->afreshSendImport();

        //统计营销效果
        //kernel::single("market_backstage_activity")->assess();
         
        //$s_time = time();


        //统计商品的销售情况
        //kernel::single('ecgoods_service_products')->countProductBuys();


        //$e_time = time();
        //var_dump($e_time-$s_time);exit;
        //echo $e_time-$s_time;exit;

        //生成决策树缓存数据
        //kernel::single('taocrm_analysis_cache')->create_tree_task();

        //短信解冻和扣除
        //kernel::single("market_sms_send")->op_fu();

        //$e_time = time();
        //echo $e_time-$s_time;exit;

        return array('status'=>'succ');
    }

    function clean($data){
        //清理
        kernel::single("market_service_activity")->clean();

        //报表缓存清除
        kernel::single("taocrm_cache_report")->clear();

        return array('status'=>'succ');
    }

    function saas($data){

        //同步到saas中心
        kernel::single('market_service_deduction')->dedu_money($data['day'],$_SERVER['SERVER_NAME']);

        return array('status'=>'succ');
    }

    //private $insert_url = 'http://prism.nirvana.shopex123.com/dcinput/data/insert';
    //private $query_url = 'http://prism.nirvana.shopex123.com/dccrm/customer/queryid_ensure';

    private $insert_url = 'http://openapi.ishopex.cn/api/dcinput/data/insert';
    private $query_url = 'http://openapi.ishopex.cn/api/dccrm/customer/queryid_ensure';
    private $test_query_url = 'http://openapi.ishopex.cn/api/dcinput/data/query';
    private $get_timestamp_url = 'http://openapi.ishopex.cn/api/platform/timestamp';
    private $appkey = 'A06Q4I';
    private $appsecret = 'BMY8BCRPOX812Z5CBCON';

    /**
     *
     * 没有电话并且旺旺或者短信账号都不统计
     *
     * @param array $data
     */
    function prism($data){
        $date = $data['day'];
        //$date = '2012-11-10';
        $action = 'post';
        $endtime = strtotime($date.' 23:59:59');
        $starttime = strtotime($date.' 00:00:00');
        $http = new base_httpclient;
        $db = kernel::database();

        //插件运行发送的短信数量
        $AppSendMoney = 0;
        $sql = "select `desc` from `sdb_plugins_log` where (start_time between {$starttime} and {$endtime}) and status='成功' ";
        $rs = $db->select($sql);
        if($rs){
            foreach($rs as $v){
                $desc = explode('发送短信数：',$v['desc']);
                $AppSendMoney += intval($desc[1]);
            }
        }

        $sql = 'select node_id,shop_id,addon,mobile,tel,config from sdb_ecorder_shop where node_id<>"" and node_id is not null';
        $shops = $db->select($sql);
        foreach($shops as $shop){
            $shop_id = $shop['shop_id'];
            //echo $shop_id."\n";

            //获取customerinfo_id
            $addon = unserialize($shop['addon']);
            base_kvstore::instance('taocrm')->fetch('prism_customer_'.$shop['node_id'], $customerId);
            //$customerId = 0;
            if(!$customerId){
                $customerinfo = array();
                $customer_query_params = array();
                if($shop['mobile']){
                    $customerinfo['contact_phone'] = $shop['mobile'];
                }else if($shop['tel']){
                    $customerinfo['contact_phone'] = $shop['tel'];
                }
                if($addon['nickname']){
                    $customerinfo['contact_wangwang'] = $addon['nickname'];
                }
                //没有电话或者旺旺都不统计
                if(!isset($customerinfo['contact_phone']) && !isset($customerinfo['contact_wangwang'])){
                    continue;
                }
                //var_dump($customerinfo);continue;
                $customer_query_params['customerinfo'] = json_encode($customerinfo);
                $query_url = $this->makeSign($action,$this->query_url,$customer_query_params);
                //echo $query_url."\n";exit;
                $result = $http->post($query_url,$customer_query_params);
                //var_dump($result);exit;
                $result = json_decode($result,true);
                //var_dump($result);echo "\n";continue;
                if($result['resp'] == 'succ'){
                    $customerId = $result['result']['customerinfo_id'];
                    base_kvstore::instance('taocrm')->store('prism_customer_'.$shop['node_id'], $customerId);
                }else{
                    continue;
                }
            }
            //echo $customerId;exit;

            $key = array('requiredproductid'=>16,'requiredcustomerid'=>$customerId,'requireddate'=>$starttime);
            base_kvstore::instance('market')->fetch('account', $arr);
            $sms_account = unserialize($arr);
             
            /*$sms_account = array (
             'entid' => '191202214604',
             'password' => 'yixiusp6666',
             'email' => 'zzyxsm@126.com',
             'status' => 1,
             );
             base_kvstore::instance('market')->store('account', serialize($sms_account));*/

            //充值平台
            $ShopexID = '';
            $money = array('sms'=>0,'edm'=>0);
            //var_dump($sms_account);exit;
            if($sms_account) {
                $ShopexID = $sms_account['entid'];
                //查询余额
                $money = kernel::single('market_service_smsinterface')->get_sms_money($starttime, $endtime);
                //var_dump($money);exit;
            }else{
                continue;
            }

            $sql = "select count(*) as total from sdb_taocrm_member_analysis where shop_id='{$shop_id}'";
            $rs = $db->selectrow($sql);
            $Shopmembercount = floatval($rs['total']);

            $sql = "select sum(success_num) as total from sdb_market_sms where create_time between {$starttime} and {$endtime} and shop_id='{$shop_id}' ";
            $rs = $db->selectrow($sql);
            $NumberCountSMSSend = floatval($rs['total']) + $AppSendMoney;
            $AppSendMoney = 0;

            $sql = "select sum(success_num) as total from sdb_market_edm where create_time between {$starttime} and {$endtime} and shop_id='{$shop_id}'";
            $rs = $db->selectrow($sql);
            $NumberCountEDMSend = floatval($rs['total']);

            $data = array(
                'contact_product'=>16,
                'shopexid'=>$ShopexID,
                'wangwang'=>$addon['nickname'],
                'mobile'=>$shop['mobile'],
                'bindingshopnumber'=>count($shops),
                'accountmember'=>$Shopmembercount,
                'smssendtimes'=>$NumberCountSMSSend,
                'smsrechargeamount'=>$money['sms'],
                'edmsendtimes'=>$NumberCountEDMSend,
                'edmrechargeamount'=>$money['edm'],
            	'storeid'=>$shop['node_id'],
            );
            $params = array('key'=>$key,'data'=>$data);
            $params = json_encode($params);
            $arr = array('json'=>$params);
            //var_export($params);
            $insert_url = $this->makeSign($action,$this->insert_url,$arr);
            $params = 'json='.$params;
            $result = $http->post($insert_url,$params);
            $result = json_decode($result,true);
            //var_dump($result);exit;
            if($result['resp'] != 'succ'){
                ilog('prism|'.$result['msg']);
                echo "fail\n";
            }

            //var_dump($result);echo "\n";exit;
        }
    }

    private function getTimeStamp(){
        $query_url = $this->get_timestamp_url;
        $http = new base_httpclient;
        $result = $http->get($query_url);
        return $result;
    }

    private function makeSign($action, $url, $data=array()){
        $parse_url = parse_url($url);
        $path = $parse_url['path'];
        $get_data['sign_method'] = 'md5';
        $get_data['sign_time'] =  $this->getTimeStamp();
        $get_data['client_id'] = $this->appkey;
        $post_data = array();
        if(strtolower($action)=='get') {
            $get_data = array_merge($data, $get_data);
        } else {
            $post_data = $data;
        }
        $get_data = $this->ksort($get_data);
        $post_data = $this->ksort($post_data);

        $get_params = rawurlencode(urldecode(http_build_query($get_data)));
        $post_params = rawurlencode(urldecode(http_build_query($post_data)));
        //$header_params = rawurlencode(urldecode(http_build_query($header_data)));
        $header_params = null;
        $path = rawurlencode('/'.ltrim($path, '/'));
        $orgsign = "{$this->appsecret}&".strtoupper($action)."&{$path}&{$header_params}&{$get_params}&{$post_params}&{$this->appsecret}";

        $sign = strtoupper(md5($orgsign));

        $get_data['sign'] = $sign;
        $str = http_build_query($get_data);
        $url = "".$url."?".$str;
        return $url;
        // return $get_data;
    }

    //参数封装排序
    private function ksort($data){
        ksort($data);
        foreach($data as $key => &$val){
            if (is_array($val)) {
                $val = $this->ksort($val);
            }
        }
        return $data;
    }

    function prismQuery($data){
        $date = $data['day'];
        $date = '2012-11-10';
        $action = 'post';
        $endtime = strtotime($date.' 23:59:59');
        $starttime = strtotime($date.' 00:00:00');
        $http = new base_httpclient;
        $db = kernel::database();

        $sql = 'select node_id,shop_id,addon,mobile,tel,config from sdb_ecorder_shop where node_id<>"" and node_id is not null';
        $shops = $db->select($sql);
        foreach($shops as $shop){
            $shop_id = $shop['shop_id'];
            //echo $shop_id."\n";

            //获取customerinfo_id
            $addon = unserialize($shop['addon']);
            base_kvstore::instance('taocrm')->fetch('prism_customer_'.$shop['node_id'], $customerId);
            //echo $customerId."\n";continue;
            if(!$customerId){
                $customerinfo = array();
                $customer_query_params = array();
                if($shop['mobile']){
                    $customerinfo['contact_phone'] = $shop['mobile'];
                }else if($shop['tel']){
                    $customerinfo['contact_phone'] = $shop['tel'];
                }
                if($addon['nickname']){
                    $customerinfo['contact_wangwang'] = $addon['nickname'];
                }
                //没有电话或者旺旺都不统计
                if(!isset($customerinfo['contact_phone']) && !isset($customerinfo['contact_wangwang'])){
                    continue;
                }
                $customer_query_params['customerinfo'] = json_encode($customerinfo);
                $query_url = $this->makeSign($action,$this->query_url,$customer_query_params);
                //echo $query_url."\n";exit;
                $result = $http->post($query_url,$customer_query_params);
                $result = json_decode($result,true);
                //var_dump($result);echo "\n";
                if($result['resp'] == 'succ'){
                    $customerId = $result['result']['customerinfo_id'];
                    base_kvstore::instance('taocrm')->store('prism_customer_'.$shop['node_id'], $customerId);
                }else{
                    continue;
                }
            }

            $key = array('requiredproductid'=>16,'requiredcustomerid'=>$customerId,'requireddate'=>$starttime);
            base_kvstore::instance('market')->fetch('account', $arr);
            $sms_account = unserialize($arr);
             
            $params = array('key'=>$key);
            $params = json_encode($params);
            $arr = array('json'=>$params);
             
            //var_export($params);
            $test_query_url = $this->makeSign($action,$this->test_query_url,$arr);
            $params = 'json='.($params);
            $result = $http->post($test_query_url,$params);
            $result = json_decode($result,true);

            if($result['resp'] != 'succ'){
                ilog('prism|'.$result['msg']);
                echo "fail\n";
            }

            //var_dump($result);echo "\n";exit;
        }
    }

    function afreshSendImport(){
        $db = kernel::database();
        $afresh_send_time = time()- (3600 * 3);
        $rows = $db->select('select sms_id,batch_id from sdb_taocrm_member_import_sms where send_status="sending" and last_send_time<'.$afresh_send_time);
        if($rows){
            foreach($rows as $row){
                kernel::single("market_backstage_import")->afreshSendImport($row['batch_id'],$row['sms_id']);
            }
            ilog('afreshSendImport:'.count($rows));
        }
    }

}

