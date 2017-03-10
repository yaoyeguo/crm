<?php

class taocrm_service_oauth{
    
    const APP_KEY = '12424101';
    const APP_SECRET = '84e680764405335d68a0d10e7365dfc9';
    
    public function get_tb_url(){
        $tb_url = array();
        $tb_url['login'] = 'https://oauth.taobao.com/authorize?response_type=user&client_id='.(self::APP_KEY).'&redirect_uri=http://'.($_SERVER['HTTP_HOST']).'/index.php/taocrm/default/index/app/site';
        $tb_url['logout'] = 'https://oauth.taobao.com/logoff?client_id='.(self::APP_KEY).'&redirect_uri=http://'.($_SERVER['HTTP_HOST']).'/index.php/taocrm/default/index/app/site/?state=logout';
        return $tb_url;
    }
    
    public function get_login_info(){
        
        $top_parameters = $_COOKIE['top_parameters'];
        $top_sign = $_COOKIE['top_sign'];
        if(!$top_parameters || !$top_sign){
            return false;
        }
        $this->chk_sign($top_parameters,$top_sign);
        
        //获取登录信息
        $top_parameters = base64_decode($top_parameters);
        parse_str($top_parameters,$account_info);
        return $account_info;
    }

	public function login_from_tb(){
        
        if(isset($_GET['top_parameters']) && isset($_GET['top_sign'])){
            $top_parameters = urldecode($_GET['top_parameters']);
            $top_sign = urldecode($_GET['top_sign']);   
            
            $this->chk_sign($top_parameters,$top_sign);
            
            //将登录参数保存到cookie
            $arr = array(
                'top_parameters'=>$top_parameters,
                'top_sign'=>$top_sign
            );
            $this->set_cookie($arr,0);
            
            header('location:http://'.($_SERVER['HTTP_HOST']).'/index.php/taocrm/default/index/app/site/');
            die();
        }
    }
    
    public function logout(){
        $arr = array(
            'top_parameters'=>'',
            'top_sign'=>''
        );
        $this->set_cookie($arr,time()-24*3600);
        header('location:http://'.($_SERVER['HTTP_HOST']).'/index.php/taocrm/default/index/app/site/');
        die();
    }
    
    //验证签名是否合法
    public function chk_sign($top_parameters,$top_sign){
        
        $local_sign = $top_parameters.(self::APP_SECRET);
        $local_sign = md5($local_sign,true);
        $local_sign = $local_sign;
        $local_sign = urldecode(base64_encode($local_sign));
        if($top_sign != $local_sign) {
            die('签名错误！请检查访问来源。');
        }
        return true;
    }
    
    public function set_cookie(&$arr,$time){
        foreach($arr as $k=>$v){
            setcookie($k,$v,$time,'/');
        }
    }

}
