<?php

class taocrm_rpc_response_account extends taocrm_rpc_response
{

    var $token = 'iloveshopex';
    var $version = '3.1';
    public function login($sdf, &$responseObj){
        $type = 'shopadmin';
        if(!isset($sdf['version']) || $sdf['version'] != $this->version){
            $responseObj->send_user_error(app::get('base')->_('版本不一致，请重新下载'));
        }

        $isLogin = false;
        if($sdf['uname'] == 'import'){
            $importMd5 = md5($_SERVER['SERVER_NAME'].$this->token);
            if($sdf['password']  == $importMd5){
                $isLogin = true;
            }
        }else{
            $rows = app::get('pam')->model('account')->getList('*',array(
        'login_name'=>$sdf['uname'],
        'login_password'=>pam_encrypt::get_encrypted_password($sdf['password'],$type),
        'account_type' => $type,
        'disabled' => 'false',
            ),0,1);
            if($rows){
                $isLogin = true;
            }
        }

        if($isLogin){
            base_kvstore::instance('taocrm')->fetch('ext_auth_secret_list',$secret_key_list);
            if($secret_key_list){
                $secret_key_list = json_decode($secret_key_list,true);
                if(!$secret_key_list)$secret_key_list = array();
            }else{
                $secret_key_list = array();
            }

            $time = time();
            foreach($secret_key_list as $k=>$val){
                if($val['expire'] < $time){
                    base_kvstore::instance('taocrm')->real_delete($val['secret_key']);
                    unset($secret_key_list[$k]);
                }
            }
             
            $secret_key = md5($sdf['uname'].$this->token.$time);
            $data = array(
                'secret_key'=>$secret_key,
                'expire'=>$time + (7 * 86400),
                'crm_ver'=>kernel::single('taocrm_system')->get_version_code()
            );
            base_kvstore::instance('taocrm')->store('ext_auth_secret_'.$secret_key,json_encode($data));

            $secret_key_list[] = $data;
            base_kvstore::instance('taocrm')->store('ext_auth_secret_list',json_encode($secret_key_list));

            return $data;
        }else{
            $responseObj->send_user_error(app::get('base')->_('Invalid have username and password'));
        }
    }

    public function init($sdf, &$responseObj){

        base_kvstore::instance('taocrm')->fetch('ext_auth_secret_'.$sdf['secret_key'],$data);
        if($data){
            $data = json_decode($data,true);
            if($data['expire'] >= time()){
                $shop_list =  kernel::database()->select('select shop_id,node_id,name,shop_type from sdb_ecorder_shop where node_id !="" and shop_type!="" ');
                $token = base_shopnode::get_token();
                return array('shop_list'=>$shop_list,'token'=>$token);
            }else{
                $responseObj->send_user_error(app::get('base')->_('Has expired'));
            }

        }else{
            $responseObj->send_user_error(app::get('base')->_('Please sign in'));
        }
    }

    public function analysis($sdf, &$responseObj){
         
        if(!$sdf['days'] || !$sdf['secret_key']){
            $responseObj->send_user_error(app::get('base')->_('Params is failure'));
            return false;
        }

        kernel::single('taocrm_service_queue')->setType('waiting');
        base_kvstore::instance('taocrm')->fetch('ext_auth_secret_'.$sdf['secret_key'],$data);
        if($data){
            $data = json_decode($data,true);
            if($data['expire'] >= time()){
                $days = explode(";", $sdf['days']);
                $host = null;
                foreach($days as $v){
                    $date = array('day'=>$v,'type'=>'created');
                    kernel::single('taocrm_service_queue')->addJob('market_backstage_crontab@analysis',$date,$host);
                }
                	
            }else{
                $responseObj->send_user_error(app::get('base')->_('Has expired'));
                return false;
            }

        }else{
            $responseObj->send_user_error(app::get('base')->_('Please sign in'));
            return false;
        }
    }
     
}