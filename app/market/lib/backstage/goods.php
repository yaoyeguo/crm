<?php
class market_backstage_goods{


    /**
     * 定义API每页获取的订单数
     *
     * @var Integer
     */
    const PAGESIZE = '100';

     
    function fetch($data){
        if($data['node_id']){
            kernel::single('ecgoods_rpc_request_taobao_goods')->downloadByNodeId($data['node_id']);
        }
        
        return array('status'=>'succ');

    }





}

