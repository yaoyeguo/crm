<?php
class market_api_func{
    
    /**
     * 错误代码关系表
     * @access public
     * @param string $code 编码
     * @param bool $log_id 日志主键ID
     * @param bool $node_type 店铺类型
     * @return 提示信息
     */
    public function api_code2msg($code=null,$log_id='',$node_type=''){
        
        if (empty($log_id) && empty($node_type)) return $code;
        if (empty($code)) return null;
        $msg = '';
        $api_lang = require_once 'lang.php';
        $oApi_log = &app::get('market')->model('api_log');
        if (empty($node_type)){
            if ($log_id){
                $log_info = $oApi_log->dump($log_id, 'params');
                if ($log_info){
                    $log_params = unserialize($log_info['params']);
                    if (is_array($log_params)){
                        $node_type = $log_params[1]['node_type'];
                    }
                }
            }
        }
        if ($node_type){
            $msg = $api_lang[$node_type][$code];
        }
        if (!$msg){
            $msg = $api_lang['public'][$code];
        }
        if (!empty($msg)){
            return $msg;
        }else{
            return $code;
        }
    }
    
}