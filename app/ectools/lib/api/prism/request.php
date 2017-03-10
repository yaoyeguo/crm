<?php

/*
 * 私有矩阵/共有矩阵接口调用
 *
 * @author liuqi
 * @version 0.1
 */

class ectools_api_prism_request extends ectools_api_abstract {

    //API访问地址
    private $requestUrl = MATRIX_SYNC_URL_M;

    public function get_api($param = array(),$shop_id)
    {
        if(empty($param))
        {
            return false;
        }
        $shop = app::get("ecorder")->model("shop");
        $shop_row = $shop->dump(array('shop_id'=>$shop_id));

        base_kvstore::instance('desktop')->fetch('matrix_switch', $matrix_switch);
        $matrix_switch = json_decode($matrix_switch,true);
        if($matrix_switch['switch'] == 1)
        {
            $url = $matrix_switch['prism_api'];
            $key = $matrix_switch['key'];
            $secret = $matrix_switch['secret'];

            $param['to_node_type']  = $shop_row['shop_type'];
            $param['node_id']       = $param['from_node_id'].'_'.$param['to_node_id'];
            if(isset($param['sign']))  unset($param['sign']);

            require_once('app/ectools/lib/client.php');
            $c = new prism_client($url, $key, $secret);
            //$token = $this->get_token();
            $result = $c->post('/api/matrix/sync', $param);
        }else
        {
            $token = $this->get_token();
            
            $param['sign'] = $this->get_sign($param,$token);
            $http = kernel::single("base_httpclient");
            $result  = $http->post($this->requestUrl,$param);
        }
        return $result;
    }

    private function get_token()
    {
        if(!$this->token)
        {
            $app_id = $this->get_appid();
            $shopnode = app::get($app_id)->getConf('shop_site_node_id');
            $snode = unserialize($shopnode);
            $this->token = $snode['token'];
        }
        return $this->token;
    }

    private function get_appid()
    {
        $app_exclusion = app::get('base')->getConf('system.main_app');
        return $app_exclusion['app_id'];
    }

    private function assemble($params){
        if(!is_array($params))  return null;
		$this->pagedata['is_member_prop'] = false;
        ksort($params,SORT_STRING);
        $sign = '';
        foreach($params AS $key=>$val){
            $sign .= $key . (is_array($val) ? $this->assemble($val) : $val);
        }
        return $sign;
    }

    private function get_sign($params,$token){
        return strtoupper(md5(strtoupper(md5($this->assemble($params))).$token));
    }
}
