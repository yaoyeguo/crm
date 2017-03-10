<?php
class taocrm_ctl_admin_app extends desktop_controller{
	
	//private $resp_url = 'http://test_api2.wwgenius.taoex.com/wwgenius_csapi/openapi/';
	private $resp_url = 'http://api.wwgenius.taoex.com/openapi/';
	//private $resp_url = 'http://192.168.61.132/wwgenius_csapi/openapi/';
	var $name = "添加应用";
    var $workground = "exapp";
    
	function index(){
		$actions=array(
            array (
	            'label' => '添加绑定',
	            'href' => 'index.php?app=taocrm&ctl=admin_app&act=addnew',
	            'target' => 'dialog::{title:\'' . '添加绑定' . '\',width:600, height:250}'
            ),
        );
		$params=array(
            'title' =>'绑定旺旺精灵',
			'actions'=>$actions,
		 	'use_buildin_recycle'=>true,
        );
        $this->finder('taocrm_mdl_app',$params);
	}
	
	function set_member_rule()
	{
        $log_mod = $this->app->model('members_rule_log');
        if(!$_POST)
        {
            $sql = "select * from sdb_taocrm_members_rule_log order by create_time desc limit 1;";
            $data = $log_mod->db->select($sql);
            $data = current($data);
            $this->pagedata['info'] = $data;
            $this->page('admin/app/member_rule.html');
        }else
        {
            $this->begin();
            $rule = $_POST['member_rule'];
            $data = array(
                'type' => $rule,
                'create_time' => time(),
            );
            $rs = $log_mod->save($data);
            if($rs)
                $this->end(true,'保存成功');
            else
                $this->end(false,'保存失败');
        }
	}

	function addnew(){
		$app_type = array(
						array('app_type'=>'wwgenius','app_type_name'=>'旺旺精灵'),
					);
		$this->pagedata['type'] = $app_type;
		$this->display('admin/app/add_app.html');
	}
	
	function save(){
		$this->begin("index.php?app=taocrm&ctl=admin_app&act=index");
        $data = $_POST['app'];
		$appObj = $this->app->model('app');
        if($appObj -> save($data)){
        	$this->end(true,'添加成功');
        }else{
            $this->end(false,'添加失败');
        }	
	}
	
	function check_app_name(){
		$appObj = app::get('taocrm')->model('app');
		$arr = $appObj->dump(array('app_name'=>$_POST['app_name']),'id');
		$flag = 0;
		if($arr){
			$flag = 1;
		}
		echo $flag;
	}
	
	function edit_app(){
		$appObj = app::get('taocrm')->model('app');
		$arr = $appObj->dump(array('id'=>$_GET['p'][0]));
		$this->pagedata['data'] = $arr;
		$this->display('admin/app/edit_app.html');
	}
	
	function update_app(){
		$this->begin("index.php?app=taocrm&ctl=admin_app&act=index");
        $appObj = $this->app->model('app');
       	$id = $_POST['app']['id'];
       	$data = $_POST['app'];
       	unset($data['id']);
        if($appObj->update($data,array('id'=>$id))){
        	$this->end(true,'修改成功');
        }else{
            $this->end(false,'修改失败');
        }	
	}
	
	//检查应用名称是否相同
	function check_exist_app(){
		$appObj = app::get('taocrm')->model('app');
		$arr = $appObj->dump(array('app_name'=>$_POST['app_name']),'id');
		$flag = 0;
		if($arr && $arr['id'] != $_POST['id']){
			$flag = 1;
		}
		echo $flag;
	}
	
	function bind(){
		$shopObj = app::get('ecorder')->model('shop');
		$shops = $shopObj -> getList('shop_id,name',array('node_id|noequal'=>'','node_type'=>'taobao'));
		
		$appObj = $this->app->model('app');
		$shop_ids = $appObj->getList('shop_id',array('shop_id|noequal'=>''));

		$this->pagedata['id'] = $_GET['p'][0];
		foreach($shop_ids as $v){
			$shopArr[] = $v['shop_id'];
		}
	
		foreach($shops as $k=>$v){
			if(in_array($v['shop_id'],$shopArr)){
				unset($shops[$k]);
			}
		}
		$this->pagedata['shops'] = $shops;
		$this->display('admin/app/bind.html');
	}
	
	//绑定检查
	function bind_check(){
		$shopObj = app::get('ecorder')->model('shop');
		$shops = $shopObj -> dump(array('shop_id'=>$_POST['shop_id']),'addon,name');
		//$data = json_decode($shops['addon'],true);	
		$data = $shops['addon'];
		$flag = 0;
		
		if(!$data['nickname']){
			//未登录淘宝
			$flag = 1;
		}else{
			$method = 'crm.bind';
			$nickname = $data['nickname'];
			$result = $this->response($method,$nickname);
		    if($result['error_response']['code'] == 'no-order'){
		    	//未订购旺旺精灵
		    	$flag = 2;
		    }else if($result['crm.bind']['success']){
		    	$appObj = $this->app->model('app');
		    	if($appObj->update(array('status'=>1,'shop_id'=>$_POST['shop_id'],'seller_nick'=>$nickname),array('id'=>$_POST['id']))){
		    		//绑定成功
		    		$flag = 3;
		    	}else{
		    		//绑定失败
		    		$flag = 4;
		    	}   
		    }else{
		    	//绑定失败
		    	$flag = 4;
		    }
		}
		echo $flag;
	}
	
	function unbind($id){
    	$this->pagedata['id']=$_GET['p'][0];
        $this->page('admin/app/unbind.html');
    }
    
    //解除绑定
 	function invalid(){
 		
 		$appObj = $this->app->model('app');
        $shops = $appObj -> dump(array('id'=>$_POST['id']),'shop_id');
            
        $shopObj = app::get('ecorder')->model('shop');
		$shops = $shopObj -> dump(array('shop_id'=>$shops['shop_id']),'addon,name');
	
        $data = json_decode($shops['addon'],true);	
		$data = $shops['addon'];
        $method = 'crm.unbind';
		$nickname = $data['nickname'];
		$result = $this->response($method,$nickname);
		$flag = 0;
		if($result['crm.unbind']['success']){
			if($appObj->update(array('status'=>0,'shop_id'=>'','seller_nick'=>''),array('id'=>$_POST['id']))){
				//解绑成功
				$flag = 1;
		    }else{
		    	//解绑失败
		    	$flag = 2;
		    }
		}else{
			//解绑失败
			$flag = 2;
		}
		echo $flag;
    }
    
	function login(){
		
		$shopObj = app::get('ecorder')->model('shop');
		$shop = $shopObj -> dump(array('shop_id'=>$_GET['shop_id']),'*');
		/*
        $shop_type = $shop['shop_type'];
        $node_id = $shop['node_id'];
        $shop_id = $shop['shop_id'];
        $taobao_session = app::get('ecorder')->getConf('taobao_session_'.$node_id);
        $taobao_session  =  $taobao_session ? $taobao_session : 'false';
        $certi_id = base_certificate::get('certificate_id');
        $url = OPENID_URL."?open=taobao&certi_id=".$certi_id."&node_id=".$node_id."&refertype=ecos.taobukpi&callback_url=http://".$_SERVER['HTTP_HOST'].kernel::base_url()."/index.php/api";
        */
        
		$shop_type = ecorder_shop_type::get_shop_type();
        $url = "";
        $shoptype = $shop['node_type'];
        $node_id = $shop['node_id'];
        $taobao_session = &app::get('ecorder')->getConf('taobao_session_'.$node_id);
        $taobao_session = strval($taobao_session);
        $certi_id = base_certificate::get('certificate_id');
        $url = OPENID_URL."?open=taobao&certi_id=".$certi_id."&node_id=".$node_id."&refertype=ecos.taocrm&callback_url=http://".$_SERVER['HTTP_HOST'].kernel::base_url()."/index.php/api";
        
        $this->pagedata['url'] = $url;
       	$this->display('admin/app/login_taobao.html');
          
	}
	
	function bind_fail(){
		$this->display('admin/app/bind_fail.html');
	}
	
	private function gen_sign($data){
        $str = $this->assemble($data) . $data['app_secret'];
        return strtoupper(md5($str));
    }
    
    private function assemble($params){
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
    
    //请求
    private function response($method,$nickname){
    	$params['app_key'] = 'crm';
	    $params['method'] = $method;
	    $params['timestamp'] = time();
	    $params['domain'] = $_SERVER['SERVER_NAME'];
	    $params['seller_nick'] = $nickname;
	    $params['app_secret'] = strtoupper(md5($params['app_key'].'^_^'.'iloveshopex'));
	    $params['sign'] = $this->gen_sign($params);
	    $http = new base_httpclient();
	    $result = $http->post($this->resp_url,$params);
	    $result = json_decode($result,true);
	    return $result;
    }
    
    //清理系统证书
    function clear_certi()
    {
        $this->begin();
        if(@copy(ROOT_DIR.'/config/certi.php', ROOT_DIR.'/config/certi_'.date('Ymd_His').'.php')){
            if(@unlink(ROOT_DIR.'/config/certi.php')){
                //删除节点信息
                $app_exclusion = app::get('base')->getConf('system.main_app');
                if($app_exclusion['app_id']){
                    app::get($app_exclusion['app_id'])->setConf('shop_site_node_id', '');
                }
                $this->end(true, '清除成功，请重新登录系统。');
            }else{
                $this->end(false, '清除证书失败，请检查config目录权限。');
            }
        }else{
            $this->end(false, '没有找到证书文件。');
        }
    }
}
