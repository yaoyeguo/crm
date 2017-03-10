<?php

class taocrm_rpc_response_orders extends taocrm_rpc_response
{

    public function search($sdf, &$responseObj){
        $apiParams = array(
            'member_id'=>array('label'=>'客户ID','required'=>true),
            'shop_id'=>array('label'=>'店铺ID','required'=>false),
            'page_size'=>array('label'=>'page_size','required'=>true),
            'page'=>array('label'=>'page','required'=>true),
        );
        $this->checkApiParams($apiParams,$sdf, $responseObj);

        $ordersOjb=kernel::single("taocrm_member_orders");
        $msg = '';
        $ordersList = $ordersOjb->getOrdersList($sdf['shop_id'],$sdf['member_id'],$sdf['page_size'],$sdf['page'],$msg);

        return array('orders'=>$ordersList['orders'],'total_result'=>$ordersList['totalResult']);
    }
     
    //物流查询
    public function trades($sdf, &$responseObj){
        //参数验证
        $apiParams = array(
            'tid'=>array('label'=>'物流单号','required'=>true),
            'ship_mobile'=>array('label'=>'收货人手机号','required'=>true)
        );
        $this->checkApiParams($apiParams,$sdf, $responseObj);
        $ordersOjb=kernel::single("taocrm_member_orders");
        $msg = '';
        $tradesinfo = $ordersOjb->getTradesInfo($sdf['tid'],$sdf['ship_mobile']);
        return array('trades'=>$tradesinfo['trades']);
    }

    //单个订单查询
    public function single($sdf, &$responseObj){
        //参数验证
        $apiParams = array(
            'order_id'=>array('label'=>'订单ID','required'=>true),
            'ship_mobile'=>array('label'=>'手机号码','required'=>true),
        );
        $this->checkApiParams($apiParams,$sdf, $responseObj);

        $ordersOjb=kernel::single("taocrm_member_orders");
        $msg = '';
        $orderinfo = $ordersOjb->get_single_order($sdf['order_id'],$sdf['ship_mobile']);
        
        return array('order'=>$orderinfo['order_info']);

    }

    //处理用户微信升级功能 操作kv
    public function handlekv($sdf, &$responseObj){ 
        // 1 微信升级 2 会员包升级
        if($sdf['call_back'] == 0){
            if($sdf['upgrade_type'] == 2){
                base_kvstore::instance('market')->fetch('wx_info', $wx_info);
                //备份数据
                $wx_info = json_decode($wx_info,true);
                base_kvstore::instance('market')->store('wx_info_backups', json_encode($wx_info));
                $real_end_time = strtotime($sdf['upgrade_end_data']);
                if(empty($wx_info)){
                    $wx_info = array(
                        'is_trial'      => '1',
                        'version'       => '3',
                        'start_time'    => time(),
                        'end_date'      => $real_end_time
                    );
                }
                else{
                    if($wx_info['version'] == 2){
                        $wx_info = array(
                            'is_trial'      => '1',
                            'version'       => '3',
                            'start_time'    => time(),
                            'end_date'      => $real_end_time
                        );
                    }
                    if($wx_info['version'] == 1 || $wx_info['version'] == 3){

                        if($wx_info['end_date']>time()){
                            $add_time = $wx_info['end_date'] - time();
                            $real_end_time = $real_end_time + $add_time;
                        }
                        $wx_info = array(
                            'is_trial'      => '1',
                            'version'       => '3',
                            'start_time'    => time(),
                            'end_date'      => $real_end_time
                        );
                    }
                }
                base_kvstore::instance('market')->store('wx_info',json_encode($wx_info));
            }
            elseif($sdf['upgrade_type'] == 1){
                $real_end_time = app::get('taocrm')->getConf('system.limit.member');
                app::get('taocrm')->setConf('system.limit.member_backups',$real_end_time);
                $real_end_time+=$sdf['upgrade_end_data'];
                $data = array(
                    'member_nums' => $real_end_time
                );
                $set_version = new  base_system();
                $set_version->setVersion($data);
            }
        }
        else
        {
            if($sdf['upgrade_type'] == 1){
                $real_end_time = app::get('taocrm')->getConf('system.limit.member_backups');
                $real_end_time = app::get('taocrm')->setConf('system.limit.member',$real_end_time);
                $data = array(
                    'member_nums' => $real_end_time
                );
                $set_version = new  base_system();
                $set_version->setVersion($data);
            }
            elseif($sdf['upgrade_type'] == 2){
                base_kvstore::instance('market')->fetch('wx_info_backups', $wx_info);
                $wx_info = json_decode($wx_info,true);
                $real_end_time = $wx_info['end_date'];
                base_kvstore::instance('market')->store('wx_info', $wx_info);
            }
        }

        return $real_end_time;
    }  
}