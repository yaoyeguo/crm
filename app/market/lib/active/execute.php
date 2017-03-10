<?php 
class market_active_execute{
	 function run(&$cursor_id,$params){
	 	//error_log(var_export($params,true),3,'d:/dongxiaoran.txt');
	 	$active_id=$params['active_id'];
		$members_obj = &app::get('taocrm')->model('members');
		$active_obj = &app::get('market')->model('active');
		$templates_obj = &app::get('market')->model('sms_templates');
        $active = $active_obj->dump($active_id);
        $memdata = $members_obj->getList("member_id");
        $template_data=$templates_obj->dump($active['template_id']);
        $sms_content=$template_data['content'];
		//shop信息
		$shopConditions = array('shop_id' => $active['shop_id']);
		$shopRs = &app::get('ecorder')->model('shop')->dump($shopConditions);
		$shopName = $shopRs['name'];
		//短信模板信息
		$templateConditions = array('template_id' => $active['template_id']);
		$templateRs = $templates_obj->dump($templateConditions);
		$contentTemplate = $templateRs['content'];
		$contentTemplate = str_replace(array('<{店铺}>'), array($shopName), $contentTemplate);
		//短信发送的参数
		$paramss = array();
		$paramss['certi_app'] = 'sms.send';//证书方法名
		$paramss['entId']     = '10017';//企业账号id
		$paramss['entPwd']    = md5('10017$ShopEXUser');
		$paramss['source']    = '988516';//94402
		$paramss['license']   = '34788000';
		//$paramss['use_reply'] = '1'; #要求短信回复
		$paramss['test'] = '1';
		$paramss['version']  = '1.0';
		$paramss['format']   = 'json';
		#$paramss['sendType'] = 'notice';
		$paramss['sendType']  = 'fan-out';
		$paramss['timestamp'] = getTime();
		#生成ac
		$token = 'dae26069127c37b587b629eb01354b84';
		$paramss['certi_ac'] = get_sign($paramss,$token);
		$snoopy = new Snoopy();
		$commit_url = 'http://api.sms.shopex.cn';
		$msgContents = array();
		$i=0;
		foreach ($memdata as $value) {
			$memberConditions = array('member_id' => $value['member_id']);
			$member = $members_obj->dump($memberConditions);
			$msgContent = array(
				'phones' => $member['contact']['phone']['mobile'],
				'content' => str_replace('<{用户名}>', $member['account']['uname'], $contentTemplate),
			);
			//更新发送状态
			$msgContents[] = $msgContent;
			$i++;
			if ($i==500){
				$content=json_encode($msgContents);
				$paramss['contents']  = $content ;
				$snoopy->submit($commit_url,$paramss);
				unset($msgContents);
			    $result = json_decode($snoopy->results,true);
			    if(empty($result)){
			       	 echo $snoopy->results;
			    }else{
			       	 return false;
			   	}
					
			}
		}
	 }
	function getTime (){
	    	$token = 'dae26069127c37b587b629eb01354b84';
	        $token = 'SMS_TIME';
	        $substr['certi_app']   = 'sms.servertime';
	        $substr['version']  = '1.0';
	        $substr['format']   = 'json';
	        $substr['certi_ac'] = get_sign( $substr, $token );
	        $httpd = new Snoopy;
	        $httpd->submit('http://webapi.sms.shopex.cn', $substr);
	        $result = $httpd->results;
	        $res = json_decode( $result );
	        $arr = array();
	        foreach ( $res as $k => $v ) {
	            $arr[$k] = $v;
	        }
	        return $arr['info'];
	}
    
	function get_sign($params,$token='41445bc7c3f8b4bebff8c00c1020ee74c2f5f2e1693081c256587b02d19ee53e'){
	    return strtolower(md5(assemble($params).strtolower(md5($token)) ));
	}

	function assemble($params){
	    if(!is_array($params))  return null;
		    ksort($params,SORT_STRING);
		    $sign = '';
		    foreach($params AS $key=>$val){
		        if($key!= 'certi_ac'){
		            $sign .= (is_array($val) ? assemble($val) : $val);
		        }
		    }
		    return $sign;
	}
}



