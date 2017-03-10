<?php
class market_service_tempinterface{

	private $api_url = 'http://mb.shopex.cn/openapi/';
	
    //模板堂验证
	public function check($arr) {
        $param = array(
        	'method' => 'edm.checklogin',
            'shopexid' => $arr['entid'],
			'pwd' => $arr['password'],
            'url' => $_SERVER['SERVER_NAME']
        );
        $http = new base_httpclient;
		$param['sign'] = $this->sign($param);
		$result  = $http->post($this->api_url.'edm/checklogin',$param);
		return json_decode($result,TRUE);
	}
	
	//写入模板
	public function write_template(){
		
		$sign = $_POST['sign'];
		unset($_POST['sign']);
		$param = $_POST;
		$gen_sign = $this->sign($param);
		if($sign !== $gen_sign){
			echo json_encode(array('res' => 'fail','code'=> '','msg' => 'sign error'));
			exit();
		}
		
		$edm_class = app::get('market')->model('edm_tclass');
		$templateObj = app::get('market')->model('edm_templates');

	 	$type_id = $edm_class -> getList('*',array('mbt_type_id'=>$param['mbt_type_id']));
		$theme_id = $templateObj -> getList('*',array('mbt_theme_id'=>$param['mbt_theme_id']));
		$data = array(
			'title'=>$param['title'],
			'remark'=>$param['remark'],
			'mbt_type_id'=>$param['mbt_type_id']
		);
		$arr = array(
			'theme_title'=>$param['theme_title'],
			'theme_content'=>$param['theme_content'],
			'mbt_theme_id'=>$param['mbt_theme_id']
		);
		
		$flag1 = false;
		$flag2 = false;
		if($type_id){
			$filter = array('mbt_type_id'=>$param['mbt_type_id']);
			$res1 = $edm_class -> update($data,$filter);
			if($res1){
				$flag1 = true;
			}
			if($theme_id){
				$arr['type_id'] = $type_id[0]['type_id'];
				$theme_filter = array('mbt_theme_id'=>$param['mbt_theme_id']);
				$res2 = $result = $templateObj -> update($arr,$theme_filter);
			}else{
				$arr['type_id'] = $type_id[0]['type_id'];
				$arr['create_time'] = time();
				$res2 = $templateObj -> save($arr);
			}
			if($res2){
				$flag2 = true;
			}
		}else{
			$data['create_time'] = time();
			$res1 = $edm_class -> save($data);
			if($res1){
				$flag1 = true;
			}
			if($theme_id){
				$theme_filter = array('mbt_theme_id'=>$param['mbt_theme_id']);
				$arr['type_id'] = $data['type_id'];
				$res2 = $templateObj -> update($arr,$theme_filter);
			}else{
				$arr['type_id'] = $data['type_id'];
				$arr['create_time'] = time();
				$res2 = $templateObj -> save($arr);
			}
			if($res2){
				$flag2 = true;
			}
		}

		//写入模板与分类成功
		if($flag1 && $flag2){
			echo json_encode(array('res' => 'succ','msg'=> '','info' => 'success'));
			exit();
		}else{//写入模板与分类失败
			echo json_encode(array('res' => 'fail','code'=> '','msg' => 'Install fail'));
			exit();
		}
		
	}
	
	private function sign($arrData,$token="mbt-edm") {
  		// todo 这里的token是和license相关的token
	  	return strtoupper(md5(strtoupper(md5($this->assemble($arrData))).$token));
	}
	
	private function assemble($params) {
	  	if(!is_array($params))  return null;
	  	ksort($params, SORT_STRING);
	  	$sign = '';
	  	foreach($params AS $key=>$val){
	  		if(is_null($val))   continue;
	  		if(is_bool($val))   $val = ($val) ? 1 : 0;
	  		$sign .= $key . (is_array($val) ? $this->assemble($val) : $val);
	  	}
	  	return $sign;
	}
}
