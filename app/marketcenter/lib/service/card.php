<?php
class marketcenter_service_card{

    function __construct($app){
        $this->app = $app;
    }
    function getcolors(){
        $app_exclusion = app::get('base')->getConf('system.main_app');
        $from_node_id = base_shopnode::node_id($app_exclusion['app_id']);
        $token = base_shopnode::get('token',$app_exclusion['app_id']);
        $api_url = 'http://matrix.ecos.shopex.cn/sync';
        $params = array(
            'from_node_id' => $from_node_id,
            'to_node_id' => '1237383836',
            'method' => 'store.card.getcolors',
            'format' => 'json',
            'v' => '2.0',
            'timestamp' => date('Y-m-d H:m:s'),
            );
        $params['sign'] = $this->gen_matrix_sign($params, $token);
        $headers = array('Connection' => 5);
        $core_http = kernel::single('base_httpclient');
        $response = $core_http->post($api_url, $params,$headers);
        $response = json_decode($response,true);
        if($response['rsp'] == 'succ'){
            $response['data'] = json_decode($response['data'],true);
            return $response['data'];
        }else{
            $response['err_msg'] = json_decode($response['data'],true);
            return $response['err_msg'];
        }
    }
    function uploadlogo($data,$node_id){
        $app_exclusion = app::get('base')->getConf('system.main_app');
        $from_node_id = base_shopnode::node_id($app_exclusion['app_id']);
        $node_id = $node_id;
        $token = base_shopnode::get('token',$app_exclusion['app_id']);
        $api_url = 'http://matrix.ecos.shopex.cn/sync';
        $params = array(
            'from_node_id' => $from_node_id,
            'to_node_id' => $node_id,
            'method' => 'store.media.uploadimg',
            'format' => 'json',
            'v' => '2.0',
            'timestamp' => date('Y-m-d H:m:s'),
            );
        $params['buffer'] = $data;
        $params['sign'] = $this->gen_matrix_sign($params, $token);
        $headers = array('Connection' => 5);
        $core_http = kernel::single('base_httpclient');
        $response = $core_http->post($api_url, $params,$headers);
        $response = json_decode($response,true);
        if($response['rsp'] == 'succ'){
            $response['data'] = json_decode($response['data'],true);
            return $response['data'];
        }else{
            $response['err_msg'] = json_decode($response['data'],true);
            return $response['err_msg'];
        }
    }
    function create($data,$node_id){
        $app_exclusion = app::get('base')->getConf('system.main_app');
        $from_node_id = base_shopnode::node_id($app_exclusion['app_id']);
        $token = base_shopnode::get('token',$app_exclusion['app_id']);
        $api_url = 'http://matrix.ecos.shopex.cn/sync';
        $params = array(
            'from_node_id' => $from_node_id,
            'to_node_id' => $node_id,
            'method' => 'store.card.create',
            'format' => 'json',
            'v' => '2.0',
            'timestamp' => date('Y-m-d H:m:s'),
            );
        foreach($data as $key=>$value){
            $params[$key] = $value;
        }
        $params['sign'] = $this->gen_matrix_sign($params, $token);
        $headers = array('Connection' => 5);
        $core_http = kernel::single('base_httpclient');
        $response = $core_http->post($api_url, $params,$headers);
        $response = json_decode($response,true);
        if($response['rsp'] == 'succ'){
            $response['data'] = json_decode($response['data'],true);
            return $response['data'];
        }else{
            $response['err_msg'] = json_decode($response['data'],true);
            return $response['err_msg'];
        }
    }
    function consume($data){
        $shopObj = app::get('ecorder')->model('shop');
        $shopList=$shopObj->getList("name,node_id",array('shop_type'=>'wechat'));
        $app_exclusion = app::get('base')->getConf('system.main_app');
        $from_node_id = base_shopnode::node_id($app_exclusion['app_id']);
        $token = base_shopnode::get('token',$app_exclusion['app_id']);
        $card = app::get('marketcenter')->model('get_cards');
        $rows = $card->getlist('CardId',array('UserCardCode'=>$data['card_code']));
        $api_url = 'http://matrix.ecos.shopex.cn/sync';
        $params = array(
            'from_node_id' => $from_node_id,
            'method' => 'store.card.code.consume',
            'format' => 'json',
            'v' => '2.0',
            'timestamp' => date('Y-m-d H:m:s'),
            );
        $params['card_id'] = $rows[0]['CardId'];
        $params['code'] = $data['card_code'];
        $log = app::get('ecorder')->model('api_log');
        foreach($shopList as $value){
            $params['to_node_id'] = $value['node_id'];
            $params['sign'] = $this->gen_matrix_sign($params, $token);
            $logTitle = '微信核销接口';
            $logInfo = '接收参数:'.var_export($data, true) . '<BR>';
            $logInfo.= '提交矩阵参数:'.var_export($params, true) . '<BR>';
            $headers = array('Connection' => 5);
            $core_http = kernel::single('base_httpclient');
            $response = $core_http->post($api_url, $params,$headers);
            $response = json_decode($response,true);
            $logInfo.= '矩阵返回参数:'.var_export($response, true) . '<BR>';
            if($response['rsp'] == 'succ'){
                $log->write_log($log->gen_id(), $logTitle, __CLASS__, __METHOD__, '', '', 'response', 'success', $logInfo);
                break;
            }else{
                if($response['err_msg'] == 'invalid code, this code has consumed.'){
                    $log->write_log($log->gen_id(), $logTitle, __CLASS__, __METHOD__, '', '', 'response', 'fail', $logInfo);
                    break;
                }
            }
        }
        return $response;
    }
    function gen_matrix_sign($params,$token){
        return strtoupper(md5(strtoupper(md5($this->assemble($params))).$token));
    }
    function assemble($params)
    {
        if(!is_array($params)){
            return null;
        }

        ksort($params,SORT_STRING);
        $sign = '';
        foreach($params AS $key=>$val){
            $sign .= $key . (is_array($val) ? assemble($val) : $val);
        }
        return $sign;
    }
}