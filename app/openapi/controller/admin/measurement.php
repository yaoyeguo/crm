<?php
class openapi_ctl_admin_measurement extends desktop_controller{
	
	public function test(){
		$conf = openapi_conf::getMethods();
		//$paramsObj = new openapi_api_params_v1_ome_order;
        $paramsObj = '';
        $methods = array_keys($conf);

        $taocrm_arr = array('point','pointlog','posorder','members','orders','member_benefits');
        $ome = array('refund');

		foreach($methods as $v){
            switch ($v){
                case in_array($v,$taocrm_arr) :
                    $methods = get_class_methods('openapi_api_function_v1_taocrm_'.$v);
                    $paramsObj = kernel::single('openapi_api_params_v1_taocrm_'.$v);
                break;
                case in_array($v,$ome):
                    $methods = get_class_methods('openapi_api_function_v1_ome_'.$v);
                    $paramsObj = kernel::single('openapi_api_params_v1_ome_'.$v);
                break;
            }
			foreach ($methods as $method){

				if( $paramsObj->getAppParams($method) ){
                    switch ($v){
                        case in_array($v,$taocrm_arr) :
                            $list[] ='taocrm.'.$v.'.'.$method;
                            break;
                        case in_array($v,$ome) :
                            $list[] = 'ome.'.$v.'.'.$method;
                            break;
                    }
				}
			}
		}
		$this->pagedata['list'] = $list;
		$this->display('admin/test/test.html');
	}
	
	public function ajaxResult(){
		if(!$_POST['apiName']) return;
		$info = explode('.', $_POST['apiName']);
        $title = $info[0];
        $class = $info[1];
		$function = $info[2];
        $obj = kernel::single('openapi_api_params_v1_'.$title.'_'.$class);
		$list = $obj->getAppParams($function);
        $required_parameter = $list['content']['cols'];
		$description = $obj->description($function);
        $this->pagedata['required_parameter'] = $required_parameter;
        $this->pagedata['list'] = $list;
		$this->pagedata['post'] = $_POST;
		$this->pagedata['description'] = $description;
		$this->display('admin/test/apiForm.html');
	}
	
	public function result(){
		$url = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME'].'/openapi/rpc/service/';
        $token = $_POST['token'];
        $method = $_POST['method1'];
        $flag = $_POST['flag'];
        unset($_POST['token']);
        unset($_POST['method1']);
        unset($_POST['data_format']);
        unset($_POST['flag']);
        if($_POST['order_items']){
            $_POST['order_items'] = json_decode($_POST['order_items'],true);
        }
        $params['ver'] = 1;
        $params['method'] = $method;
        $params['charset'] = 'utf-8';
        $params['content'] = json_encode($_POST);
        $params['flag'] = $flag;
        $params['page_no'] = 1;
		$params['page_size'] = 100;
		$sign = $this->gen_sign($params ,$token);
		$params['sign'] = $sign;
		$http = kernel::single('base_httpclient');
		$response = $http->post($url,$params );
		echo $response;
	}
	
	private function gen_sign($params,$token){
	
		if(!$token){
			return false;
		}
		return strtoupper(md5(strtoupper(md5($this->assemble($params))).$token));
	}
	
	private function assemble($params)
	{
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