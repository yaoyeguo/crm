<?php
class ecorder_rpc_request_gift extends ecorder_rpc_response{

    private $api_url = MATRIX_SYNC_URL_M;
    private static $shopGiftObj = null;
    private static $http = null;
    private static $token = null;

    /**
     * 获取erp的赠品列表
     */
    function get_gift($shop_id)
    {
        $app_exclusion = app::get('base')->getConf('system.main_app');
        $from_node_id = base_shopnode::node_id($app_exclusion['app_id']);
        
        $param = array(
            'method' => 'store.gift.list.get',
            'from_node_id' => $from_node_id,
            'v' => '1.0',
            'timestamp' => date('Y-m-d H:m:s'),
            'format' => 'json',
            'page' => 1,
            'limit' => 500,
            //'cols' => 'gift_bn,gift_name,gift_num',
        );

        $shopObj =  app::get('ecorder')->model('shop');
        if($shop_id){
            $result = $shopObj->dump(array('shop_id'=>$shop_id));
            if($result['node_id']){
                $param['to_node_id'] = $result['node_id'];
                $this->get_erp_gifts($param,$shop_id);
            }else{
                echo('店铺没有节点号，请重新绑定');
                return false;
            }
        }else{
            $result = $shopObj->getList('node_id,shop_id',array('node_id|noequal'=>''));
            if($result){
                foreach($result as $v){
                    $param['to_node_id'] = $v['node_id'];
                    $this->get_erp_gifts($param,$v['shop_id']);
                }
            }else{
                echo('店铺没有节点号，请重新绑定');
                return false;
            }
        }
    }

    function get_erp_gifts($param,$shop_id)
    {
        if (self::$http == null) {
            self::$http = new base_httpclient();
        }
        
        $log_mdl = app::get('ecorder')->model('api_log');
        $logTitle = 'ERP赠品接口';
        $logInfo = '赠品接口：<BR>';
        $logInfo .= '请求参数 $param 信息：' . var_export($param, true) . '<BR>';
        
        self::$token = base_shopnode::get_token();
        $param['sign'] = $this->sign($param,self::$token);
        
        if($this->api_url == 'MATRIX_SYNC_URL_M'){
            echo('API地址未配置，请检查config文件');
            return false;
        }
        
        $res = self::$http->post($this->api_url,$param);
        $result = json_decode($res,TRUE);
        
        if($result['rsp'] == 'succ'){
            $logInfo .= '返回值为：' . var_export($result, true) . '<BR>';
            $log_mdl->write_log($log_mdl->gen_id(), $logTitle, __CLASS__, __METHOD__, '', '', 'request', 'success', $logInfo);
        
            if(self::$shopGiftObj == null){
                self::$shopGiftObj = app::get('ecorder')->model('shop_gift');
            }
            $data = json_decode($result['data'],true);
                
            if($data && $shop_id){
            
                //$gift_bn = self::$shopGiftObj->getList('gift_bn',array('shop_id'=>$shop_id));
                $gift_bn = self::$shopGiftObj->getList('gift_bn');
                if($gift_bn){
                    foreach($gift_bn as $val){
                        $gift_bns[$val['gift_bn']] = $val['gift_bn'];
                    }
                }
                
                foreach($data as $v){
                    //优先使用成本价
                    if($v['cost']) $v['price'] = $v['cost'];
                
                    if($gift_bns[$v['gift_bn']]){
                        $list = array(
                            'gift_name'=>$v['gift_name'],
                            'gift_num'=>intval($v['gift_num']),
                            'gift_price'=>floatval($v['price']),
                            'update_time'=>time(),
                        );
                        //$status = self::$shopGiftObj->update($list,array('gift_bn'=>$v['gift_bn'],'shop_id'=>$shop_id));
                        $status = self::$shopGiftObj->update($list, array('gift_bn'=>$v['gift_bn']));
                    }else{
                        $data = array(
                            'gift_bn'=>$v['gift_bn'],
                            'gift_name'=>$v['gift_name'],
                            'gift_num'=>intval($v['gift_num']),
                            'gift_price'=>floatval($v['price']),
                            'create_time'=>time(),
                            'update_time'=>time(),
                            'shop_id'=>$shop_id
                        );
                        $status = self::$shopGiftObj->insert($data);
                    }
                    //unset($gift_bns[$v['gift_bn']]);
                }
                
                //自动删除不存在的赠品
                /*
                if($gift_bns){
                    foreach($gift_bns as $v){
                        $status = self::$shopGiftObj->delete(array('shop_id'=>$shop_id,'gift_bn'=>$v));
                    }
                }
                */
            }
            echo($result['rsp']);
        }else{
            $logInfo .= '返回值为：' . var_export($result, true) . '<BR>';
            $log_mdl->write_log($log_mdl->gen_id(), $logTitle, __CLASS__, __METHOD__, '', '', 'request', 'fail', $logInfo);
        
            if($result['err_msg']){
                echo($result['err_msg']);
            }else{
                echo($res);
            }
        }
    }

    private function sign($params,$token='BS-CRM'){
        //return $this->make_sign_matrix($params);
        return strtoupper(md5(strtoupper(md5($this->assemble($params))).$token));
    }

    private function assemble($params)
    {
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

    function make_sign_matrix($params)
    {
        ksort($params);
        $query = '';
        foreach($params as $k=>$v){
            $query .= $k.'='.$v.'&';
        }
         
        return md5(substr($query,0,strlen($query)-1).base_certificate::get('token'));
    }

}