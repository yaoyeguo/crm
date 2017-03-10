<?php 
require_once(realpath(dirname(__FILE__).'/../../../../').'/config/saasapi.php');
class market_service_deduction{
	
	public function __construct(){
		//$this->startTime = mktime(0, 0, 0, date("m")  , date("d")-1, date("Y"));
		define('SASS_APP_KEY', 'taocrm');
		define('SAAS_SECRE_KEY', '5EB2B5FF9F8DBD6C583281E326F66D9B');
	}
	
	//向saas发送扣费信息
	public function dedu_money_222($day,$domain=FALSE){
    
	    $startTime = strtotime($day.' 00:00:00');
        $endTime =  strtotime($day.' 23:59:59');
		$de_obj=&app::get("market")->model("sms_op_record");
		$sms = &app::get("market")->model("sms");
		//$fliter=array("create_time|between"=>array($startTime,$endTime),'action'=>'deduct');
		$fliter=array("create_time|between"=>array($startTime,$endTime));
        $data=$de_obj->getList("*",$fliter);//扣款记录

        //所有营销超市的活动
        $active_id_arr = array();
        $sql = "select active_id from sdb_market_active where (create_time between $startTime and $endTime) and pay_type='market' ";
        $rs = $sms->db->select($sql);
        foreach($rs as $v){
            $active_id_arr[] = $v['active_id'];
        }

        if($active_id_arr) $fliter['active_id'] = $active_id_arr;
		$arr = $sms->getList("*",$fliter);//短信发送记录
		
		$saasdata=array();
		$s_num = 0;//发送短信条数
		$d_num	= 0;//扣除短信条数
		$f_num = 0;//冻结短信条数
		$uf_num = 0;//解冻短信条数
		
		foreach ($data as $k=>$v){
			if($v['action'] == 'deduct'){
				$d_num += $v['nums'];
			}else if($v['action'] == 'freeze'){
				$f_num += $v['nums'];
			}elseif($v['action'] == 'unfreeze'){
				$uf_num += $v['nums'];
			}
		}
		
		foreach($arr as $k=>$v){
			$s_num += $v['success_num'];
		}
		//deduct_num扣除短信条数 		freeze_nu冻结短信条数   	unfreeze_num解冻短信条数			send_num发送短信条数
		$saasdata['data']=array('deduct_num'=>$d_num,'freeze_num'=>$f_num,'unfreeze_num'=>$uf_num,'send_num'=>$s_num);
		$saasdata['date']=date("Y-m-d",$startTime);
		//$saasdata['servername']="shiyao.crm.taoex.com";//kernel::single('base_request')->get_remote_addr();$domain;//
		$saasdata['servername']=$domain;
		$saasdata['service_code']="taoex-crm";
        
        //营销超市的使用热度
        $sql = "select market_id,count(*) as hits,type from sdb_plugins_hits
        where created between $startTime and $endTime group by market_id,type 
        ";
        $rs = kernel::database()->select($sql);
        if($rs){
            foreach($rs as $v){
                $hits[$v['market_id']][$v['type']] = $v['hits'];
            }
        }
        $saasdata['market_hits']=$hits;
        
       // echo('<pre>');var_dump($saasdata);
        
		$api = new SaasOpenClient();
		$api->appkey=SASS_APP_KEY;
		$api->secretKey = SAAS_SECRE_KEY;
		$api->format = 'json';
		$api->execute('application.storedata',array('service_code'=>'taoex-crm','appdata' => serialize(array($saasdata))));
		
	}
	
	//向saas发送店铺统计信息
	public function dedu_money($day, $servername='BS-CRM'){
	
		$start_time = strtotime($day.' 00:00:00');
        $end_time = strtotime($day.' 23:59:59');
		$NodeUrl = $_SERVER['SERVER_NAME'];
		
		$db = kernel::database();
		
		$sql = 'select count(*) as total,max(lastlogin) as lastlogin,sum(logincount) as logincount from sdb_desktop_users';
		$rs = $db->selectrow($sql);
		$AccountNum = $rs['total'];
		$logintime = $rs['lastlogin'];
		$LoginTimes = $rs['logincount'];
		if($logintime >= (strtotime(date('Y-m-d 00:00:00')) - 86400)){
			$LoginStatus = 1;
		}else{
			$LoginStatus = 0;
		}
		
		$sql = 'select count(*) as total from sdb_ecorder_shop where node_id<>"" ';
		$rs = $db->selectrow($sql);
		$BindingShopNum = $rs['total'];
		
		$tt_obj = memcache_connect(SERVER_TT_HOST, SERVER_TT_PORT);
		$preFix = md5(md5($NodeUrl) . 'setting/taocrm' . 'shop_site_node_id');
		$data = unserialize(memcache_get($tt_obj, $preFix));
		$NodeId = unserialize($data['value']);
		
		$sql = "select sum(nums) as total from sdb_market_sms_op_record where remark='购买插件' and create_time between {$start_time} and {$end_time} ";
		$rs = $db->selectrow($sql);
		$AppInMoney = floatval($rs['total']);
        
        //插件运行发送的短信数量
        $AppSendMoney = 0;
        $sql = "select `desc` from `sdb_plugins_log` where (start_time between {$start_time} and {$end_time}) and status='成功' ";
		$rs = $db->select($sql);
		if($rs){
            foreach($rs as $v){
                $desc = explode('发送短信数：',$v['desc']);
                $AppSendMoney += intval($desc[1]);
            }
        }
		
		$sql = "select shop_id,name,shop_type,addon from sdb_ecorder_shop";
		$rs = $db->select($sql);
		foreach((array)$rs as $v){
			$addon = unserialize($v['addon']);
            $shop_id = $v['shop_id'];
			$temp_v['ShopIdent'] = $addon['nickname'];
			$temp_v['ShopName'] = $v['name'];
			$temp_v['Shoptype'] = $v['shop_type'];
			$temp_v['Shopindustry'] = '';
            
            $sql = "select sum(success_num) as total from sdb_market_sms where create_time between {$start_time} and {$end_time} and shop_id='{$shop_id}' ";
            $rs = $db->selectrow($sql);
            $NumberCountSMSSend = floatval($rs['total']) + $AppSendMoney;
            $AppSendMoney = 0;
            
            $sql = "select sum(success_num) as total from sdb_market_edm where create_time between {$start_time} and {$end_time} and shop_id='{$shop_id}'";
            $rs = $db->selectrow($sql);
            $NumberCountEDMSend = floatval($rs['total']);
            
            $sql = "select count(*) as total from sdb_taocrm_member_analysis where shop_id='{$shop_id}'";
            $rs = $db->selectrow($sql);
            $Shopmembercount = floatval($rs['total']);
            
			$temp_v['Shopmembercount'] = $Shopmembercount;
			$temp_v['LoginTimes'] = $LoginTimes;
			$temp_v['NumberCountSMSSend'] = $NumberCountSMSSend;
			$temp_v['NumberCountSNSSend'] = '0';
			$temp_v['NumberCountEDMSend'] = $NumberCountEDMSend;
			$temp_v['BuyBackInMoney'] = '0';
			$temp_v['Rules'] = array();
            $shoplist[] = $temp_v;
		}
	
		//节点信息
		$companyinfo['NodeUrl'] = $NodeUrl;
		$companyinfo['NodeID'] = $NodeId['node_id'];
		$companyinfo['BindingShopNum'] = $BindingShopNum;
		$companyinfo['AccountNum'] = $AccountNum;
		$companyinfo['AppInMoney'] = $AppInMoney;
		// $companyinfo['shopIdent'] = $shopIdent;
		// $companyinfo['shopname'] = $shopname;
		// $companyinfo['shoptype'] = $shoptype;
		// $companyinfo['logintime'] = $logintime;
		$companyinfo['shoplist'] = $shoplist;
		
        /*
		$sql = "select sum(active_num) as total from sdb_market_active where pay_type='market' ";
		$rs = $db->selectrow($sql);
		$allsendnum = $rs['total'];
		
		$sql = "select * from sdb_market_active where create_time between ".($start_time - 86400*15)." and {$end_time} ";
		$rs = $db->select($sql);
		if($rs){
			foreach($rs as $v){
				$tmp_v = array();
				$tmp_v['activityname'] = $v['active_name'];
				$tmp_v['activityid'] = $v['active_id'];
				$tmp_v['sendnum'] = $v['active_num'];
				$tmp_v['sendtime'] = $v['exec_time'];
				$tmp_v['endtime'] = $v['end_time'];
				
				$assess = $this->_get_active_assess($v['active_id']);
				if($assess){
					//{s:9:"acmembers";s:1:"2";s:9:"ordernums";i:0;
					//s:8:"apaynums";i:0;s:15:"afinish_members";
					//i:0;s:6:"aratio";i:0;s:11:"asale_money";i:0;}
					$active_members_res = unserialize($assess['active_members_res']);
					$tmp_v['forzenum'] = 0;//冻结数量
					$tmp_v['thawtime'] = 0;//解冻时间
					$tmp_v['BuyBacknum'] = $active_members_res['acmembers'];//回头客数量
					$tmp_v['thawnum'] = 0;//解冻条数
					$tmp_v['deduction'] = 0;//扣除条数 ok
					$tmp_v['backBuyROI'] = round(($active_members_res['acmembers']*100/$v['active_num']),4);//回购比例 ok
				}
				$activity[] = $tmp_v;
				
				if($v['pay_type'] == 'market'){
					$report_filter = json_decode($v['report_filter'],1);
					$market_id = $report_filter['market_id'];
					
					$market_info = kernel::single('plugins_market')->getRule($market_id);
					//var_dump($market_id);
					$hit_times = $this->_get_hit_times($market_id);
					
					$emarket[$market_id]['rules'] = $market_info['title'];//规则名称
					$emarket[$market_id]['hittimes'] = $hit_times;//点击次数
					$emarket[$market_id]['allsendnum'] = 0;//发送数
					$emarket[$market_id]['allbacknum'] = 0;//回头数
					$emarket[$market_id]['parentname'] = $market_info['parent_name'];//父节点
					$emarket[$market_id]['parent_id'] = $market_info['parent_id'];//父节点id
					$emarket[$market_id]['rules_id'] = $market_id;//规则id
					$emarket[$market_id]['rulesmarketingInfo'] = $market_info['desc'];//规则内容
				}
			}
		}
		
		//营销活动统计
		$saasdata['activity'] = $activity;
		
		//营销超市统计
		$saasdata['emarket'] = $emarket;
		
		$sql = "select count(*) as total from sdb_taocrm_member_analysis where f_created between {$start_time} and {$end_time}";
		$rs = $db->selectrow($sql);
		$NumberCountDay = $rs['total'];
		
		$sql = "select sum(success_num) as total from sdb_market_sms where create_time between {$start_time} and {$end_time}";
		$rs = $db->selectrow($sql);
		$NumberCountSMSSend = $rs['total'];
		
		$sql = "select sum(success_num) as total from sdb_market_edm where create_time between {$start_time} and {$end_time}";
		$rs = $db->selectrow($sql);
		$NumberCountEDMSend = $rs['total'];	
        */
		
		//店铺统计
		// $companyinfo['NumberCountDay'] = $NumberCountDay;
		// $companyinfo['NumberCountSMSSend'] = $NumberCountSMSSend;
		// $companyinfo['NumberCountEDMSend'] = $NumberCountEDMSend;
		// $companyinfo['NumberCountSNSSend'] = 0;
		$companyinfo['LoginSatus'] = $LoginStatus;
		$companyinfo['ServiceExpiredTime'] = date('Y-m-d H:i:s',strtotime('+90 days'));
		
		//充值平台
        $ShopexID = '';
        $money = array('sms'=>0,'edm'=>0);
        base_kvstore::instance('market')->fetch('account', $account);
        $sms_acount = unserialize($account);
        if($sms_acount) {
            $ShopexID = $sms_acount['entid'];
            //查询余额
            //$endtime = strtotime($day);
            //$starttime = strtotime('-1 days', $endtime);
            $money = kernel::single('market_service_smsinterface')->get_sms_money($start_time, $end_time); 
        }
        
		$companyinfo['ShopexID'] = $ShopexID;
		$companyinfo['SMSMoney'] = $money['sms'];
		$companyinfo['EDMMoney'] = $money['edm'];
		$companyinfo['SNSMoney'] = 0;
		// $companyinfo['OtherMoney'] = 0;
		// $companyinfo['BuyBackInMoney'] = 0;
		// $companyinfo['servername'] = $_SERVER['SERVER_NAME'];
        
		$saasdata['date'] = $day;
		$saasdata['companyinfo'] = $companyinfo;
    
		//echo('<pre>');var_dump($saasdata);die();
        
        $api_data = array();
        $api_data['sendtype'] = 'json';
        $api_data['service_code'] = 'taoex-crm';
        $api_data['appdata'] = json_encode(array(array(
            'servername' => $servername,
            'date' => date('Y-m-d'),
            'data' => array($saasdata),
            'service_code'=>'taoex-crm'
        )));
        
		$api = new SaasOpenClient();
		$api->appkey=SASS_APP_KEY;
		$api->secretKey = SAAS_SECRE_KEY;
		$api->format = 'json';
		$res = $api->execute('application.storedata',$api_data);
        //var_dump($res);
	}
	
	private function _get_active_assess($active_id){
	
		$db = kernel::database();
		$sql = "select * from sdb_market_active_assess where active_id={$active_id}";
		$rs = $db->selectrow($sql);
		if($rs){
			return $rs;
		}
		return false;
	}
	
	private function _get_hit_times($market_id){
	
		$db = kernel::database();
		$sql = "select count(*) as total from sdb_plugins_hits where market_id={$market_id} and type='频率' ";
		$rs = $db->selectrow($sql);
		if($rs){
			return $rs['total'];
		}
		return 0;
	}
	
}
