<?php
class market_backstage_member{


    /**
     * 定义API每页获取的订单数
     *
     * @var Integer
     */
    const PAGESIZE = '100';

    /**
     *
     * 获取订单然后分页
     * 	$data = array(
     'day'=>'2012-06-28',
     'session'=>'610090621766fa16cbdb26c202e34b68aa9f6d06baf4dcc374544688',
     'node_id'=>'',
     'token'=>'',
     );
     */
    function fetch($data,$shop_id=''){
        if(empty($data['day'])){
            return array('status'=>'fail','errmsg'=>'param is error');
        }

        $fetchAli = new market_api_taobao_order();
        $fetchAli->setSessionKey($data['session']);
        $start_time = strtotime($data['day'].' 00:00:00');
        $end_time =  strtotime($data['day'].' 23:59:59');
        $result = $fetchAli->getIncrementOrdersByPage($start_time,$end_time,1,1,$shop_id);
        //var_dump($result);exit;
        if(!empty($result)){
            if($result['status'] == 'succ'){
                $pages = ceil($result['totalNum'] / self::PAGESIZE);
                $pages = intval($pages);
                for($page=1;$page<=$pages;$page++){
                    //echo $page."\n";
                    $data['page'] = $page;
                    $data['shop_id'] = $shop_id;
                    kernel::single('taocrm_service_queue')->addJob('market_backstage_orders@fetchPage',$data['shop_id']);
                }
            }elseif($result['status'] == 'timeout'){
                return array('status'=>'timeout');
            }
        }

        return array('status'=>'succ');

    }





}

