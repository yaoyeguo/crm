<?php
/**
 * 前端店铺绑定关系处理
 * @author ome
 * @access public
 * @copyright www.shopex.cn 2010
 *
 */
class ecorder_rpc_response_shop extends ecorder_rpc_response
{
    //添加店铺
    public function add($sdf, &$responseObj){
        //var_dump($sdf);
        $oShop = app::get('ecorder')->model('shop');
        $rs = $oShop->dump(array('shop_bn'=>$sdf['shop_bn']));
        if($rs){
            echo(json_encode(array(
                'shop_id'=>$rs['shop_id'],
                'node_id'=>$rs['node_id']
            )));
            exit;
        }else{
            $sdf['shop_id'] = md5($sdf['shop_bn']);
            $sdf['node_id'] = date('YmdHis');
            if(isset($sdf['ecshop_url'])){
                $sdf['config'] = serialize(array('ecshop_url'=>$sdf['ecshop_url']));
                unset($sdf['ecshop_url']);
            }
            $oShop->insert($sdf);
            return array(
                'shop_id'=>$sdf['shop_id'],
                'node_id'=>$sdf['node_id']
            );
        }
    }

    /**
     * 更新绑定或解除店铺的状态信息
     * 当前端店铺申请绑定或解除关系时，矩阵中心会调用此方法，以更新绑定店铺的绑定状态
     * @access public
     * @param $result 中心返回的店铺数据
     * @method POST
     */
    function shop_callback($result)
    {
        $nodes = $_POST;
        $nodes['shop_id'] = $result['shop_id'];
        
        //接口日志
        $this->write_log($nodes);

        $shop_id = $result['shop_id'];
        $status = $nodes['status'];
        $node_id = $nodes['node_id'];
        $node_type = $nodes['node_type'];
        $nickname = $nodes['nickname'];
        $session = $nodes['session'][0];
        $subbiztype = $nodes['subbiztype'];

        $shopObj = app::get('ecorder')->model('shop');
        $shopdetail = $shopObj->dump(array('node_id'=>$node_id), 'node_id,config,addon,shop_id');

        if($node_id && $status=='bind'){
            //防止config里的短信签名等设置丢失
            $shop_config = array();
            if($shopdetail['shop_config']){
                $shop_config = unserialize($shopdetail['shop_config']);
            }
            if($nodes['shop_url']) $shop_config['url'] = $nodes['shop_url'];
            if($nodes['nickname']) $shop_config['account'] = $nodes['nickname'];

            $shop_arr = array();
            $shop_arr['shop_id'] = $shop_id;
            $shop_arr['shop_type'] = $nodes['node_type'];
            $shop_arr['active'] = 'true';
            $shop_arr['disabled'] = 'false';
            $shop_arr['config'] = serialize($shop_config);
            $shop_arr['node_id'] = $nodes['node_id'];
            $shop_arr['node_type'] = $nodes['node_type'];
            
            if($nodes['node_id'])
                $shop_arr['shop_bn'] = $nodes['node_id'];
                
            if($nodes['shop_title'])
                $shop_arr['name'] = $nodes['shop_title'];

            $shopObj->save($shop_arr);
            $shop_id = $shop_arr['shop_id'];

            base_kvstore::instance('newbie')->store('bind_shop','succ');
            
            //如果节点类型是微信，就去注册事件类型
            if($node_type == 'wechat'){
                $this->reg_weixin_service($node_id);
            }
        }

        $filter = array('shop_id'=>$shop_id);

        if ($status=='bind' and !$shopdetail['node_id']){
            if ($node_id){
                $data = array('subbiztype'=>$subbiztype,'node_id'=>$node_id,'node_type'=>$node_type,'shop_type'=>$node_type);
                $shopObj->update($data, $filter);

                base_rpc_service::$node_id = $node_id;
                $session_sdf = array('status'=>'true','session'=>$session,'nickname'=>$nickname);
                kernel::single('ecorder_rpc_response_taobao_session')->status($session_sdf);

                if($node_type == 'taobao' && $subbiztype == 'zx'){
                    kernel::single('taocrm_service_queue')->setType('waiting');//进入补单队列
                    $this->downloadGoods($_SERVER['SERVER_NAME'],$node_id);
                    $this->downloadOrder($_SERVER['SERVER_NAME'],$session,$node_id);
                }
                die('1');
            }
        }elseif ($status=='unbind'){

            base_kvstore::instance('newbie')->store('bind_shop','');

            app::get('ecorder')->setConf('taobao_session_'.$node_id, 'false');
            $data = array('node_id'=>'');
            $shopObj->update($data, $filter);
            die('1');
        }
        die('0');
    }

    function shop_auth_callback($result)
    {
        $shop_id = $result['shop_id'];
        app::get('ecorder')->setConf('taobao_auth_session_highrisk_'.$shop_id, time());
        if($result['jumpto']){
            switch($result['jumpto']){
                case 'coupon_goOnAdd':
                    $jumpto = 'http://'.$_SERVER['SERVER_NAME'].'/index.php?app=market&ctl=admin_coupon&act=goOnAdd&p[0]='.$shop_id;
                    break;
                case 'coupon_requestCoupon':
                    $jumpto = 'http://'.$_SERVER['SERVER_NAME'].'/index.php?app=market&ctl=admin_coupon&act=requestCoupon';
                    break;
                case 'requestActivity':
                    $jumpto = 'http://'.$_SERVER['SERVER_NAME'].'/index.php?app=market&ctl=admin_coupon&act=requestActivity';
                    break;
                default:
                    $jumpto = '';
                    break;
            }
        }
        if($jumpto){
            header('Location: '.$jumpto);
        }else{
            echo '授权成功!请重新发起请求!';
        }
        exit;
        //die('正在跳转或者你也可以点击<a href="'.$jumpto.'">这里</a>');
    }

    //下载订单
    function downloadOrder($host,$session,$node_id)
    {
        $date_from = date('Y-m-d',strtotime('-3 month'));
        $date_to =  date('Y-m-d');
        $start_time = strtotime($date_from .' 00:00:00');
        $end_time = strtotime($date_to.' 23:59:59');
        $days = $end_time-$start_time;
        if($days > 0){
            $days = round($days/86400);
        }
        for($i=0;$i<$days;$i++){
            if($i ==0){
                $day = $date_from;
            }else{
                $day = date('Y-m-d',strtotime($date_from.' +'.$i.' day'));
            }
            $data = array(
			'day'=> $day,
			'session'=>$session,
			'node_id'=>$node_id,
            );
            kernel::single('taocrm_service_queue')->addJob('market_backstage_orders@fetchOrders',$data);
        }
    }

    //下载商品
    function downloadGoods($host,$node_id){
        $data = array('node_id'=>$node_id);
        kernel::single('taocrm_service_queue')->addJob('market_backstage_goods@fetch',$data);
    }

    /**
     * 获取中心通知的淘宝session过期的时间
     *
     * @param  void
     * @return void
     * @author
     **/
    public function shop_session()
    {
        $data = $_POST;
        $certi_ac = $data['certi_ac'];
        unset($data['certi_ac']);
        $token = base_certificate::get('token');
        $sign = $this->genSign($data,$token);
        if($certi_ac != $sign){
            echo json_encode(array('res'=>'fail','msg'=>'签名错误'));
            exit;
        }
        $filter = array('node_id'=>$data['to_node']);
        $session_expire_time = $data['session_expire_time'];
        $shopMdl  = app::get('ecorder')->model('shop');
        $shopinfo = $shopMdl->getList('addon',$filter);

        if(is_array($shopinfo) && count($shopinfo)>0){
            if ($addon = $shopinfo[0]['addon'] ) {
                $newaddon['addon'] = array_merge($addon,$data);
            }
            $shopMdl->update($newaddon,$filter);
            echo json_encode(array('res'=>'succ','msg'=>''));
        }else{
            echo json_encode(array('res'=>'fail','msg'=>'没有找到网店'));
        }
        exit;
    }

    public function genSign($params,$token)
    {
        ksort($params);
        $str = '';
        foreach ($params as $key =>$value) {

            if ($key != 'certi_ac' && $key != 'certificate_id') {
                $str .= $value;
            }
        }
        $signString = md5($str.$token);
        return $signString;
    }

    //注册微信事件回调地址
    function reg_weixin_service($node_id)
    {
        $card_api_url = kernel::openapi_url('openapi.marketcenter.weixin','eventbackend');
        $wx_api_url = kernel::single('market_service_weixin')->get_wx_openapi();
        
        $weixin_event = kernel::single('ecorder_rpc_request_weixin_event');
        $weixin_msg = kernel::single('ecorder_rpc_request_weixin_msg');
        
        //用户订阅,用户取消订阅,二维码扫描,上报地理位置
        $params = array(
            'event'=>'subscribe,unsubscribe,SCAN,LOCATION,CLICK,VIEW', 
            'backend_url'=>$wx_api_url
        );
        $weixin_event->eventbackend_set($params, $node_id);
        
        //关键词响应
        $weixin_msg->msgbackend_set($node_id);

        //领取事件
        $params = array('event'=>'user_get_card', 'backend_url'=>$card_api_url);
        $weixin_event->eventbackend_set($params, $node_id);

        //删除事件
        $params = array('event'=>'user_del_card', 'backend_url'=>$card_api_url);
        $weixin_event->eventbackend_set($params, $node_id);

        //核销事件
        $params = array('event'=>'user_consume_card', 'backend_url'=>$card_api_url);
        $weixin_event->eventbackend_set($params, $node_id);
    }
    
    public function write_log($params)
    {
        unset($params['session']);
        
        $act = ($params['status']=='bind') ? '绑定' : '解绑';
        $log = app::get('ecorder')->model('api_log');
        $logTitle = '店铺'.$act.'接口['. $params['shop_id'] .']';
        $logInfo = '店铺'.$act.'接口：<BR>';
        $logInfo .= '接收参数 $sdf 信息：' . var_export($params, true) . '<BR>';
        $log->write_log(
            $log->gen_id(), 
            $logTitle, 
            __CLASS__,__METHOD__,'','', 
            'response', 
            'success', 
            $logInfo, 
            array('task_id'=>$params['shop_id'])
        );
    }
    
}