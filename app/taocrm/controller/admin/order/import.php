<?php
/**
 * 导入ecstore订单
 */
 
class taocrm_ctl_admin_order_import extends desktop_controller{

    private $max_total = 100;

    public function index()
    {
        //kernel::single('ecorder_rpc_request_taobao_orders')->get_unpaid_orders(date('Y-m-d H:i:s', $now_date), date('Y-m-d H:i:s', $end_date), $shops[$v]);
        //kernel::single('taocrm_wangwangjingling_shop')->run();//旺旺会员:nodeid不存在
        //kernel::single('taocrm_wangwangjingling_chat_log')->run();//旺旺咨询会员:验签失败
        //exit;
        $shop_mod = app::get('ecorder')->model('shop');
        $shop_list = $shop_mod->getList();
        foreach($shop_list as $shop)
        {
            if($shop['shop_type'] != 'ecos.b2c')
            {
                continue;
            }
            $shop_arr[] = $shop;
        }
		$this->pagedata['shop_list'] = $shop_arr;
        $this->pagedata['start_date'] = date("Y-m-d",strtotime("-30 day"));
        $this->pagedata['end_date'] = date("Y-m-d");
        $this->page('admin/order/import.html');
    }

    public function import_order()
    {
        $shop = app::get("ecorder")->model("shop");
        $shop_row = $shop->dump(array('shop_id'=>$_POST['shop_id']));

        $this->shop_id = $shop_row['shop_id'];
        $this->node_id = $shop_row['node_id'];
        $this->shop_type = $shop_row['shop_type'];

        $day_num = trim($_GET['day_num']);
        $start_date    = $_POST['start_date'];
        $end_date      = $_POST['end_date'];
        $new_start_date = date("Y-m-d",strtotime("$start_date +".$day_num." day"));
        $add_day = $day_num + 1;
        $new_end_date = date("Y-m-d",strtotime("$start_date +".$add_day." day"));
        $this->page_num = 1;
        $page_size = 100;
        for($i=1;$i<=$this->page_num;$i++)
        {
            $order_ids = $this->get_order_ids($new_start_date,$new_end_date,$page_size,$i);
            
            if(intval($order_ids['total_results']) == 0) break;
            
            $this->page_num = ceil(intval($order_ids['total_results'])/$page_size);
            $order_ids = $order_ids['trades'];

            $response = kernel::single('base_rpc_service');
            $orderObj = new ecorder_rpc_response_order_add();
            base_rpc_service::$node_id = $this->node_id;
            foreach($order_ids as $order_id)
            {
                $order_info = $this->get_order($order_id);
                $sdf = $this->get_sdf($order_info['trade']);
                
                //$rs = $orderObj->add($sdf, $response);
                //走订单队列导入，防止并发错误
                kernel::single('taocrm_service_redis')->redis->RPUSH('tgcrm:SYS_ORDER_QUEUE',serialize(array('order' => $sdf, 'nodeId' => base_rpc_service::$node_id, 'host'=>$_SERVER['SERVER_NAME'])));
            }
        }
        if(strtotime($new_end_date) > strtotime($end_date)){
            echo 'ok';
        }else{
            echo $new_start_date.'(订单数：'.count($order_ids).')'.'导入结束......';
        }
        exit;
    }

    private function get_sdf($order)
    {
        $this->api_obj = $this->api_obj ? $this->api_obj : new ectools_api_prism_syncorder();
        $sdf = $this->api_obj->info2sdf($order,$this->shop_id);
        return $sdf;
    }

    private function get_appid()
    {
        $app_exclusion = app::get('base')->getConf('system.main_app');
        return $app_exclusion['app_id'];
    }

    private function get_order_ids($s_date,$e_date,$page_size=50,$page_num=1)
    {
        $app_id = $this->get_appid();
        $param = array(
            'method'        => 'store.trades.sold.get',
            'from_node_id'  => base_shopnode::node_id($app_id),
            'to_node_id'    => $this->node_id,
            'format'        => 'json',
            'start_time'    => $s_date,
            'end_time'      => $e_date,
            'page_size'     => $page_size,
            'page_no'      => $page_num,
        );

        $resp = $this->get_api($param,$this->shop_id);
        $result = json_decode($resp, true);

        if($result['rsp'] == 'fail'){
            echo('发生错误：'.$resp);
            exit;
        }else{
        $data = json_decode($result['data'],true);
        return $data;
        }
    }

    private function get_order($order)
    {
        $app_id = $this->get_appid();
        $param = array(
            'method'        =>'store.trade.fullinfo.get',
            'from_node_id'  =>base_shopnode::node_id($app_id),
            'to_node_id'    => $this->node_id,
            'format'        =>'json',
            'tid'           =>$order['tid'],
        );
        $result = $this->get_api($param,$this->shop_id);
        $result = json_decode($result,true);
        return json_decode($result['data'],true);
    }

    private function get_api($param,$shop_id)
    {
        $api_obj = new ectools_api_prism_request();
        $result = $api_obj->get_api($param,$shop_id);
        return $result;
    }
}
