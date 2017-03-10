<?php
class marketcenter_apiv_apis_response_card{

    public function user_get_card($params,&$service){
        $data = array('event'=>'user_get_card','backend_url'=>$_SERVER["REMOTE_ADDR"].'/ecstore/index.php/shopadmin/#app=marketcenter&ctl=admin_cards&act=user_get_card');
        $weixin_card = kernel::single('marketcenter_service_card');
        $reslut = $weixin_card->getmsg($data);
    }

    public function user_del_card($params, &$service){
        
    }

    public function user_consume_card($params, &$service){
        $data = array('event'=>'user_get_card','backend_url'=>$_SERVER["REMOTE_ADDR"].'/ecstore/index.php/shopadmin/#app=marketcenter&ctl=admin_cards&act=user_consume_card');
        $weixin_card = kernel::single('marketcenter_service_card');
        $reslut = $weixin_card->getmsg($data);
    }
}
