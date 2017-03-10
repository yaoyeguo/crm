<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class pam_callback{

	function login($params)
    {
        if(isset($_POST['mobile'])) $mobile = $_POST['mobile'];//兼容呼叫中心设置
        
		$auth = pam_auth::instance($params['type']);
		$auth->set_appid($params['appid']);
		if($params['module']){
			if($passport_module = kernel::single($params['module'])){
				if($passport_module instanceof pam_interface_passport){
					$module_uid = $passport_module->login($auth,$auth_data);
					if($module_uid){
						$auth_data['account_type'] = $params['type'];
						$auth->account()->update($params['module'], $module_uid, $auth_data);
					}
					$log = array(
                        'event_time'=>time(),
                        'event_type'=>$auth->type,
                        'event_data'=>base_request::get_remote_addr().':'.$auth_data['log_data'],

					);
					app::get('pam')->model('log')->insert($log);
					if(!$module_uid)$_SESSION['last_error'] = $auth_data['log_data'];
					$_SESSION['type'] = $auth->type;
					$_SESSION['login_time'] = time();
					$params['member_id'] = $_SESSION['account'][$params['type']];
					$params['uname'] = $_POST['uname'];
					foreach(kernel::servicelist('pam_login_listener') as $service)
					{
						$service->listener_login($params);
					}
					if($params['redirect'] && $module_uid){
						$service = kernel::service('callback_infomation');
						if(is_object($service)){
							if(method_exists($service,'get_callback_infomation') && $module_uid){
								$data = $service->get_callback_infomation($module_uid,$params['type']);
								if(!$data) $url = '';
								else $url = '?'.utils::http_build_query($data);
							}

						}
					}

					if($_COOKIE['autologin'] > 0){
						kernel::single('base_session')->set_cookie_expires($_COOKIE['autologin']);
						//如果自动登录，设置cookie过期时间，单位：分
					}

					if($_SESSION['callback'] && !$module_uid){
						$callback_url = urldecode($_SESSION['callback']);
						unset($_SESSION['callback']);
                        if($mobile){
                            if(strstr($callback_url, '#') or strstr($callback_url, '?') or strstr($callback_url, '&')){
                                $callback_url .= "&mobile={$mobile}";
                            }else{
                                $callback_url .= "?mobile={$mobile}";
                            }
                        }
						header('Location:'.$callback_url);
						exit;
					}
					else{
						$url = $params['redirect'] == 'index.php' ? kernel::base_url(true) : base64_decode(urldecode($params['redirect'])). $url;
                        if($mobile){
                            if(strstr($url, '#') or strstr($url, '?') or strstr($url, '&')){
                                $url .= "&mobile={$mobile}";
                            }else{
                                $url .= "?mobile={$mobile}";
                            }
                        }
						header('Location:' . $url);
						exit;
					}

				}
			}else{
				 
			}
		}
	}

}
