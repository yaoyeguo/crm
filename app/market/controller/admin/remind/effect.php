<?php

class market_ctl_admin_remind_effect extends desktop_controller{

    /**
     * 催付效果评估
     */
    public function index()
    {
        $db = kernel::database();
        $cuifu_open = 'open';
        $shop_id = $_GET['shop_id'];
        
        //检测催付插件是否开启
        $end_time = time();
        $sql = "select * from sdb_plugins_plugins where end_time>$end_time and worker='plugins_service_reminder'";
        $rs = $db->selectrow($sql);
        if(!$rs){
            //$this->redirect('index.php?app=plugins&ctl=admin_buy&act=index');
            $cuifu_open = 'close';
        }
        
        //获取昨天的催付数据
        $end_time = strtotime(date('Y-m-d 00:00:00'));
        $start_time = $end_time - 86400;
        $effect = $this->get_day_data($start_time, $end_time);
        
        $rs = &app::get('ecorder')->model('shop')->getList('*');
        foreach($rs as $v){
            if($v['name']) 
                $shops[$v['shop_id']] = $v['name'];
        }
    
        $this->pagedata['shop_id'] = $shop_id;
        $this->pagedata['shops'] = $shops;
        $this->pagedata['cuifu_open'] = $cuifu_open;
        $this->pagedata['effect'] = $effect;
        $this->page('admin/remind_effect.html');
    }
    
    //图形报表
    public function chart()
    {
        $chartLabel = array('y1' => '成功单数', 'y2' => '成功金额', 'y3' => '付款率');
        for($i=0;$i<7;$i++){
            $end_time = strtotime(date('Y-m-d 00:00:00')) - 86400*$i;
            $start_time = $end_time - 86400;
            $data[date('Y-m-d',$start_time)] = $this->get_day_data($start_time, $end_time);
        }
        ksort($data);

        foreach($data as $k=>$v){
            if(!$v['succ_ratio'])
                $v['succ_ratio'] = 0;
            $chartData[] = "{x:'{$k}',y1:".(int)$v['succ_orders'].",y2: ".(int)$v['succ_payed'].",y3: ".$v['succ_ratio']."}";
        }
        $chartData = '['.implode(',', $chartData).']';
            
        $this->pagedata['chartLabel'] = $chartLabel;
        $this->pagedata['chartData'] = $chartData;
        $this->display('admin/remind_effect_chart.html');
    }
    
    /**
     * 指定日期范围的催付数据
     */
    private function get_day_data($start_time, $end_time)
    {
        $shop_id = $_GET['shop_id'];
        
        $db = kernel::database();
        $tids = array();
        $effect = array(
            'total_orders' => 0,
            'succ_orders' => 0,
            'succ_payed' => 0,
            'succ_ratio' => 0,
            'roi' => 0,
        );
        
        $wherestr = "create_time between {$start_time} and {$end_time} and ";
        $sql = "select tid from sdb_plugins_sms_logs where {$wherestr} plugin_name in ('订单催付','短信催付','新催付(淘宝)') ";
        if($shop_id)
            $sql .= " and shop_id='{$shop_id}' ";
        $rs = $db->select($sql);
        if($rs){
            foreach($rs as $v){
                $tids[$v['tid']] = 1;
            }
        }
        $effect['total_orders'] = count($tids);
        
        if($tids){
            $sql = "select count(*) as succ_orders,sum(payed) as succ_payed from sdb_ecorder_orders where order_bn in ('".implode("','",array_keys($tids))."') and pay_status='1' ";
            if($shop_id)
                $sql .= " and shop_id='{$shop_id}' ";
            $rs = $db->selectrow($sql);
            
            $effect['succ_orders'] = $rs['succ_orders'];
            $effect['succ_payed'] = $rs['succ_payed'];
            $effect['succ_ratio'] = round($rs['succ_orders']*100/$effect['total_orders'],2);
            $effect['roi'] = ceil($rs['succ_payed']/(0.05*$effect['total_orders'])).':1';
        }
        return $effect;
    }
}