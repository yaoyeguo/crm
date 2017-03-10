<?php
class ecorder_ctl_admin_debug extends desktop_controller{

    public function index()
    {        
        $sign = strtoupper(md5($_SERVER['SERVER_NAME']));
        $url_prefix = kernel::base_url(true);;
        $url_prefix .= '/script/saas_update_script/update_taoguan_app.php';
        $url_prefix .= '?sign='.$sign;
        $cmd_url = array(
            'kvrecovery' => $url_prefix.'&cmd=kvrecovery',
            'update' => $url_prefix.'&cmd=update',
        );
        
        if($_POST['version_code']){
            $this->begin('index.php?app=ecorder&ctl=admin_debug');
            kernel::single('taocrm_system')->set_version_code($_POST['version_code']);
            $this->end(true, 'success');
        }
    
        $this->pagedata['cmd_url'] = $cmd_url;
        $this->page('admin/debug.html');
    }
    
    public function get_ec_version()
    {
        $api_url = MATRIX_SYNC_URL_M;//内网
        $headers = array('Connection' => 20);
        $core_http = kernel::single('base_httpclient');

        $app_exclusion = app::get('base')->getConf('system.main_app');
        $params['app_id'] = 'ecos.taocrm';#写死app_id
        $params['from_node_id'] = '1531333636';//base_shopnode::node_id($app_exclusion['app_id']);
        $params['to_node_id'] = '1437333036';//$data['node_id'];
        $params['method'] = 'store.sysinfo.version';
        $token = '7da23d54b6e2f1a099197e3e83a00ba527ce8fcfd210a705d3418ebca75c87e5';
        $params['sign'] = $this->sign($params, $token);
        $resp = $core_http->post($api_url, $params,$headers);
        $data = json_decode($resp,true);
        vdump($params);
        vdump($data);
    }
    
    public function get_tb_refunds()
    {
        echo('start');
        $res = kernel::single('ecorder_rpc_request_taobao_refunds')->download();
        var_dump($res);
        echo('finish');
    }
    
    public function reg()
    {
        $q = kernel::single('base_certificate')->register();
        if(!$q) {
            echo('申请证书失败<br/>');
            var_dump($q);
        }else{
            $app_exclusion = app::get('base')->getConf('system.main_app');
            $certi = base_certificate::get('certificate_id');
            $node_id = base_shopnode::node_id($app_exclusion['app_id']);
            echo('证书：'.$certi.'&nbsp;&nbsp;节点：'.$node_id);
        }
    }
    
    private function sign($params,$token='BS-CRM'){
        //return $this->make_sign_matrix($params);
		return strtoupper(md5(strtoupper(md5($this->assemble($params))).$token));
	}
	
	private function assemble($params){
		if(!is_array($params)){
			return null;
		}
	
		ksort($params,SORT_STRING);
		$sign = '';
		foreach($params AS $key=>$val){
			$sign .= $key . (is_array($val) ? $this->assemble($val) : $val);
		}
		return $sign;
	}
    
    function make_sign_matrix($params){
        ksort($params);
        $query = '';
        foreach($params as $k=>$v){
            $query .= $k.'='.$v.'&';
        }
         
        return md5(substr($query,0,strlen($query)-1).base_certificate::get('token'));
    }
}
