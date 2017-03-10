<?php
class marketcenter_rpc_response_weixin{
    public function msgbackend(){
         
    }
    public function eventbackend(){
        $log = app::get('ecorder')->model('api_log');
        $logTitle = '微信事件';
        $map =  kernel::single('marketcenter_utility_xml')->xml2array($GLOBALS["HTTP_RAW_POST_DATA"]);
        if($map['xml']['Event'] == 'user_get_card') {
            $this->user_get_card($map['xml']);
        }
        else if($map['xml']['Event'] == 'user_consume_card') {
            $this->user_consume_card($map['xml']);
        }
        else{
            $logInfo = '接收参数格式错误:'.var_export($xml, true) . '<BR>' .var_export($GLOBALS["HTTP_RAW_POST_DATA"], true) . '<BR>';
            $log->write_log($log->gen_id(), $logTitle, __CLASS__, __METHOD__, '', '', 'response', 'fail', $logInfo);
        }
        if(empty($map)){
            $logInfo ='传入参数为空';
            $log->write_log($log->gen_id(), $logTitle, __CLASS__, __METHOD__, '', '', 'response', 'fail', $logInfo);
        }
    }
    public function user_get_card($xml){
        $log = app::get('ecorder')->model('api_log');
        $logTitle = '微信领取事件';
        $logInfo = '接收参数:'.var_export($xml, true) . '<BR>';
        $card = app::get('marketcenter')->model('cards');
        $user_get_card = app::get('marketcenter')->model('get_cards');
        if($user_get_card->save($xml)){
            $log->write_log($log->gen_id(), $logTitle, __CLASS__, __METHOD__, '', '', 'response', 'success', $logInfo);
            $result = array('ErrCode' => '1','ErrMsg'=>'success');
        }else{
            $log->write_log($log->gen_id(), $logTitle, __CLASS__, __METHOD__, '', '', 'response', 'fail', $logInfo);
            $result = array('ErrCode' => '0','ErrMsg'=>'sql error');
        }
        return $result;
    }
    public function user_consume_card($xml){
        $log = app::get('ecorder')->model('api_log');
        $logTitle = '微信核销事件';
        $logInfo = '接收参数:'.var_export($xml, true) . '<BR>';
        $user_consume_card = app::get('marketcenter')->model('consume_cards');
        if($user_consume_card->save($xml)){
            $log->write_log($log->gen_id(), $logTitle, __CLASS__, __METHOD__, '', '', 'response', 'success', $logInfo);
            $result = array('ErrCode' => '1','ErrMsg'=>'success');
        }else{
            $log->write_log($log->gen_id(), $logTitle, __CLASS__, __METHOD__, '', '', 'response', 'fail', $logInfo);
            $result = array('ErrCode' => '0','ErrMsg'=>'sql error');
        }
        return $result;
    }
}