<?php
/**
 * RPC响应基类
 * @author shopex.cn ome
 * @access public
 * @copyright www.shopex.cn 2010
 *
 */
require_once(realpath(dirname(__FILE__).'/../../../../').'/config/saasapi.php');

class plugins_rpc_response {
    
	private $appkey = 'taocrm';
    private $secretKey = '5EB2B5FF9F8DBD6C583281E326F66D9B';
    
    function __construct(){
        if (defined('DEBUG') && DEBUG == true && function_exists('debug')){
            debug($_POST);
        }
    }

    /**
     * 获取请求方店铺信息
     * 请求方店铺信息过滤，拒绝绑定关系不正确的访问
     * @param string $col 店铺字段
     * @param object $responseObj 框架层对象引用
     */
    function get_shop($col="*", &$responseObj){
        
        $shopObj = &app::get('ecorder')->model('shop');
        $node_id = base_rpc_service::$node_id;
        $shop = $shopObj->dump(array('node_id'=>$node_id), $col);
        //暂时解node_id的问题.
        //$shop = $shopObj->getList($col,'',0,-1);
        if($shop){
            return $shop;
        }else{
            $responseObj->send_user_error(app::get('base')->_('Can\'t recognize the source'), '');
            return false;
        }
    }
    
    function get_shop_id(&$responseObj){
        $shop = $this->get_shop("shop_id", $responseObj);
        return $shop['shop_id'];
    }
    
    
    //签名验证
    function get_sign($params,$rpc_type){
    	if($rpc_type == 'check'){
   			return strtoupper(md5(strtoupper(md5($this->assemble($params))).$this->sign_key($rpc_type)));
    	}else{
    		return strtoupper(md5(strtoupper(md5($this->assemble($params))).$this->sign_nick_key($rpc_type)));
    	}
 	}
 	
	function assemble($params){
		if(!is_array($params))  return null;
		ksort($params,SORT_STRING);
        $sign = '';
		foreach($params AS $key=>$val){
            $sign .= $key . (is_array($val) ? $this->assemble($val) : $val);
        }
		return $sign;
	}
	
	function sign_key($rpc_type){
		$oPlugins = &app::get('plugins')->model('plugins');
		$data = $oPlugins->getList('params',array('worker'=>'plugins_service_check'),0,1);
		$keys = $data[0]['params'];
		$key = json_decode($keys,true);
		return $key['keys'];
	}

	function sign_nick_key($rpc_type){
		return ;
		$oPlugins = &app::get('plugins')->model('plugins');
		$data = $oPlugins->getList('params',array('worker'=>'plugins_service_genius'),0,1);
		$keys = $data[0]['params'];
		$key = json_decode($keys,true);
		return $key['keys'];
	}
    
    //检查权限
    function check_authority(){	
    	//检查签名是否正确
    	$params = json_decode($_POST['params'],true);
    	$type = 'check';
    	$sign = $this->get_sign($params,$type);
  		if($sign !== $_POST['sign']){
  			echo  json_encode(array('res'=>'','rsp'=>'fail','data'=>array('msg'=>'Invalid signature.')));
            exit();
  		}
    	//检查是否购买过此权限插件
        $oPlugins = app::get('plugins')->model('plugins');
        $rs = $oPlugins->getList('*',array('worker' => 'plugins_service_check'),0,1);
    	if(empty($rs)){
            echo  json_encode(array('res'=>'','rsp'=>'fail','data'=>array('msg'=>'Don\'t buy this plugin.')));
            exit();
        }

        //检查有效期
        /*
       	$end_time = $rs[0]['end_time'];
       	if($end_time < time()){
       		echo json_encode(array('res'=>'','rsp'=>'fail','data'=>array('msg'=>'This plugin is expired.')));
       		exit();
       	}
        */
       	
    	//检查店铺节点是否为空
    	if(empty($_POST['node_id'])){
			echo  json_encode(array('res' => '','rsp' => 'fail','data' =>array('msg'=>'Node id is empty.')));
			exit();
		}
    }
    
    
	//旺旺精灵插件权限检测
    function check_nick_authority(){
    	/*
    	//检查签名是否正确
    	$sign = $_POST['sign'];
    	unset($_POST['sign']);
    	$type = 'wangwang';
    	$gen_sign = $this->get_sign($_POST,$type);
  		if($sign !== $gen_sign){
  			echo  json_encode(array('res'=>'fail','data'=>'Invalid signature.'));
            exit();
  		}
    	//检查是否购买过此权限插件
        $oPlugins = app::get('plugins')->model('plugins');
        $rs = $oPlugins->getList('*',array('worker' => 'plugins_service_genius'),0,1);
    	if(empty($rs)){
            echo  json_encode(array('res'=>'fail','data'=>'Don\'t buy this plugin.'));
            exit();
        }

        //检查有效期
       	$end_time = $rs[0]['end_time'];
       	if($end_time < time()){
       		echo json_encode(array('res'=>'fail','data'=>'This plugin is expired.'));
       		exit();
       	}
       	*/
    	
    	//$host = '61897483.taojixiao.taoex.com';
		$host = $_SERVER['SERVER_NAME'];
    	$info = $this->getInfoByHost($host);
    	if($info->status != 'HOST_STATUS_ACTIVE'){
    		echo  json_encode(array('res'=>'fail','msg'=>'您订购的CRM应用不可用','data'=>''));
    		exit();
    	}
    	$sign = $_POST['sign'];
    	unset($_POST['sign']);
    	$type = 'wangwang';
    	$gen_sign = $this->get_sign($_POST,$type);
    	//$gen_sign = $this->gen_sign($_POST);
    	if($sign !== $gen_sign){
    		echo  json_encode(array('res'=>'fail','msg'=>'签名错误','data'=>''));
            exit();
    	}
    }
    
    
	function getInfoByHost($host) {
		
		$api = new SaasOpenClient();
		$api->appkey = $this->appkey;
		$api->secretKey = $this->secretKey;
		$api->format = 'json';
	
		$params = array('server_name' => $host);
		$result = $api->execute('host.getinfo_byservername', $params);
		unset($api);
		if ($result->success == 'true') {
			if ($result->data == 'QUEUE_END') {
				return null;
			} else {
				return $result->data;
			}
		} else {
			return null;
		}
	}
	
}
