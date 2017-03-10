<?php

/**
 * 获取图形报表数据
 */
class taocrm_ctl_admin_chart_member extends desktop_controller 
{

    public function buy_times_bar()
    {
        base_kvstore::instance('analysis')->fetch('member_buy_times',$all_sales_data);
        $rs = $all_sales_data['analysis_data'];
        if($rs) {
            foreach($rs as $v){
                $k = $v['key_name'];                
                $v['avg_order_amount'] = round($v['total_amount']/$v['total_orders'],2);
                $data[$k] = $v;
            }
        }
        
        foreach($data as $k=>$v){
            $temp_v = 'x:"购买'.$k.'次"';
            $temp_v .= ',y:'.floatval($v['avg_order_amount']).'';
            $dataset[] = $temp_v;
        }
        $chartTitle = '平均订单价';

        foreach($dataset as $k=>$v){
            $dataset[$k] = '{'.$v.'}';
        }
        $chartData = '['.implode(',',$dataset).']';
        
        //echo('<pre>');var_dump($chartData);

        $this->pagedata['chartLabel'] = $chartLabel;
        $this->pagedata['chartData'] = $chartData;
        $this->pagedata['chartTitle'] = $chartTitle;
        $this->display("admin/analysis/chart_type/MSBar3D1.html");
    }
  
    //近12个月客户成功购买频次占比
    public function buy_times_new()
    {
        $data = $this->getBuyTimeNewData($_GET);
        $dataset = array();
        
        switch (intval($_GET['target'])) {
            case 2:
                $data = $this->buytTimeDataSort($data);
                foreach ($data as $k => $v) {
                    $temp = 'x:"' . $k . '次"';
                    $temp .= ',y:' . intval($v['MemberCount']) . '';
                    $dataset[] = $temp;
                }
                $chartTitle = '客户数';
                break;
        }
        foreach($dataset as $k => $v){
            $dataset[$k] = '{'.$v.'}';
        }
        $chartData = '['.implode(',',$dataset).']';
        if(!$chartData || $chartData == '[]') {
            echo('暂无数据');
            die();
        }
        $this->pagedata['chartData'] = $chartData;
        $this->pagedata['chartTitle'] = $chartTitle;
        if ($_GET['target'] == 2) {
            $this->display("admin/analysis/chart_type/pieSimple.html");
        }
    }
    
    protected function buytTimeDataSort($data)
    {
    	
        $sub = array();
        $i = 0;
        foreach ($data as $k => $v) {
            if (!is_numeric($k)) {
                $sub[$i]['k'] = $k;
                $sub[$i]['v'] = $v;
                unset($data[$k]);
                $i++;
            }
        }
        $subTmp = array();
        foreach ($sub as $v) {
//            $num = substr($v['k'], 1);
            switch (substr($v['k'], 0, 1)) {
                case '>':
                    $subTmp['多于' . (count($data))] = $v['v'];
                    break;
            }
        }
        ksort($data);
        foreach ($subTmp as $k => $v) {
            $data[$k] = $v;
        }
        
        return $data;
    }
    
    public function getBuyTimeNewData($data)
    {
    	
        $connect = kernel::single('taocrm_middleware_connect');
        $params = array();
        $params['shopId'] = $data['shop_id'];
        $beginTime = strtotime($data['date_from']);
        $params['beginTime'] = $beginTime;
        $tmpEndTime = strlen($data['date_to']) > 10 ?  $data['date_to'] :  $data['date_to'] . ' ' . date('H:i:s');
        $params['endTime'] = strtotime($tmpEndTime);
        $params['ctl'] = $_GET['ctl'];
        
        $func = 'BuyFreqByTime';
        
    	if ($func != '') {
            $data = $connect->$func($params);
            $keys = array_keys($data);
            foreach($keys as $v){
            	if($v < 6){
            		$result[$v]['MemberCount'] = $data[$v];
            	}else{
            		$nums += $data[$v];
            	}
            }
            $result['>5']['MemberCount'] = $nums;
            return $result;
        }
       
    }
    
    // 购买次数
    public function buy_times()
    {
        $filter = array();
        $order_status = $_GET['order_status'];
        $filter['shop_id'] = $_GET['shop_id'];
        $filter['service'] = $_GET['service'];
        $filter['target'] = intval($_GET['target']);//1:金额,2:单价,3:订单和单价
        $filter['date_from'] = ($_GET['date_from']);
        $filter['date_to'] = ($_GET['date_to']);
		$count_unit = 'c_'.$_GET['count_by'];//c_date,c_month,c_week,c_year
        $where = " shop_id = '".$filter['shop_id']."' 
                AND (c_time between ".$filter['date_from']." and ".$filter['date_to'].")
                ";
        
        /*
        $analysis_data = kernel::single('taocrm_analysis_day')->get_member_buy_times($filter);
        $rs = $analysis_data['analysis_data'];
        */
        
        base_kvstore::instance('analysis')->fetch('member_buy_times',$all_sales_data);
        $rs = $all_sales_data['analysis_data'];
        
        if($rs) {
            foreach($rs as $v){
                $k = $v['key_name'];
                /*
                if($v['total_orders']>=10) 
                    $k = '大于等于10';
                if($v['total_orders']>10) {
                    $v['total_members'] += $data[$k]['total_members'];
                    $v['total_amount'] += $data[$k]['total_amount'];
                }*/
                
                $v['total_members_ratio'] = round($v['total_members']*100/$all_sales_data['total_data']['total_members'],2);
                $v['total_amount_ratio'] = round($v['total_amount']*100/$all_sales_data['total_data']['total_amount'],2);
                
                $data[$k] = $v;
            }
        }
        
        switch($filter['target']) :
            default:
                foreach($data as $k=>$v){
                    $temp_v = 'x:"'.$k.'次"';
                    $temp_v .= ',y1:"'.$v['total_members_ratio'].'"';
                    $temp_v .= ',y2:"'.$v['total_amount_ratio'].'"';
                    $dataset[] = $temp_v;
                }
                $chartLabel = array('y1'=>'人数占比','y2'=>'销售额占比');
            break;
            
            case 2:
                foreach($data as $k=>$v){
                    $temp_v = 'x:"'.$k.'次"';
                    $temp_v .= ',y:'.number_format(intval($v['total_members']), 2, ".", '').'';
                    $dataset[] = $temp_v;
                }
                $chartTitle = '客户数';
            break;
            
            case 3:
                foreach($data as $k=>$v){
                    $temp_v = 'x:"'.$k.'次"';
                    $temp_v .= ',y:'.number_format(floatval($v['total_amount']), 2, ".", '').'';
                    $dataset[] = $temp_v;
                }
                $chartTitle = '订单金额';
            break;
        
        endswitch;

        foreach($dataset as $k=>$v){
            $dataset[$k] = '{'.$v.'}';
        }
        $chartData = '['.implode(',',$dataset).']';

        $this->pagedata['chartLabel'] = $chartLabel;
        $this->pagedata['chartData'] = $chartData;
        $this->pagedata['chartTitle'] = $chartTitle;
        if($filter['target'] == 1)
            $this->display("admin/analysis/chart_type/MSBar3D.html");
        if($filter['target'] == 2)
            $this->display("admin/analysis/chart_type/pieSimple.html");
        if($filter['target'] == 3)
            $this->display("admin/analysis/chart_type/pieSimple.html");
    }
    
    public function level(){
        
        $compare_data = false;
        $filter = array();
        $order_status = $_GET['order_status'];
        $filter['shop_id'] = $_GET['shop_id'];
        $filter['service'] = $_GET['service'];
        $filter['target'] = intval($_GET['target']);//1:金额,2:单价,3:订单和单价
        $filter['date_from'] = strtotime($_GET['date_from']);
        $filter['date_to'] = strtotime($_GET['date_to']);
        if($_GET['c_date_from'] && $_GET['c_date_to']) {
            $compare_data = true;
            $filter['c_date_from'] = strtotime($_GET['c_date_from']);
            $filter['c_date_to'] = strtotime($_GET['c_date_to']);
            
        }
		
        $count_unit = 'c_'.$_GET['count_by'];//c_date,c_month,c_week,c_year
        $where = " shop_id = '".$filter['shop_id']."' 
                AND (c_time between ".$filter['date_from']." and ".$filter['date_to'].")
                 ";
        if($compare_data == true) {
            $this->show_compare_chart($count_unit,$where,$filter['target']);
        }
        
//        $analysis_data = kernel::single('taocrm_analysis_day')->get_member_level($filter);
        $analysis_data = app::get("taocrm")->controller('admin_analysis_member')->getMemberCountByShopId($filter);
        $rs = $analysis_data['analysis_data'];
        if($rs) {
            foreach($rs as $v){
                $data[] = $v;
            }
        }
        
        switch($filter['target']) :
            default:
                foreach($data as $k=>$v){
                    $temp_v = 'x:"'.$v['lv_name'].'"';
                    $temp_v .= ',y1:"'.$v['total_members'].'"';
                    $temp_v .= ',y2:"'.number_format($v['total_amount'], 2, ".", '').'"';
                    $dataset[] = $temp_v;
                }
                $chartLabel = array('y1'=>'客户数','y2'=>'订单金额');
            break;    
                
            case 2:
                foreach($data as $k=>$v){
                    $temp_v = 'x:"'.$v['lv_name'].'"';
                    $temp_v .= ',y:"'.number_format($v['total_members'], 2, ".", '').'"';
                    $dataset[] = $temp_v;
                }
                $chartTitle = '客户数';
            break;
            
            case 3:
                foreach($data as $k=>$v){
                    $temp_v = 'x:"'.$v['lv_name'].'"';
                    $temp_v .= ',y:"'.number_format($v['total_amount'], 2, ".", '').'"';
                    $dataset[] = $temp_v;
                }
                $chartTitle = '订单金额';
            break;
        
        endswitch;

        foreach($dataset as $k=>$v){ $dataset[$k] = '{'.$v.'}';}
        $chartData = '['.implode(',',$dataset).']';

        $this->pagedata['chartLabel'] = $chartLabel;
        $this->pagedata['chartData'] = $chartData;
        $this->pagedata['chartTitle'] = $chartTitle;
        if($filter['target'] == 1)
            $this->display("admin/analysis/chart_type/ColLine.html");
        if($filter['target'] == 2)
            $this->display("admin/analysis/chart_type/pieSimple.html");
        if($filter['target'] == 3)
            $this->display("admin/analysis/chart_type/pieSimple.html");
    }
    
    public function old_new(){
        $compare_data = false;
        $filter = array();
        $order_status = $_GET['order_status'];
        $filter['count_by'] = $_GET['count_by'];
        $filter['shop_id'] = $_GET['shop_id'];
        $filter['service'] = $_GET['service'];
        $filter['target'] = intval($_GET['target']);//1:金额,2:单价,3:订单和单价
        $filter['date_from'] = ($_GET['date_from']);
        $filter['date_to'] = ($_GET['date_to']);

		$count_unit = 'c_'.$_GET['count_by'];//c_date,c_month,c_week,c_year
        if($compare_data == true) {
            $this->show_compare_chart($count_unit,$where,$filter['target']);
        }
        
        switch($filter['target']) :
            default:
            
                //$analysis_data = kernel::single('taocrm_analysis_day')->get_member_old_new($filter);
                $oldNewController = app::get("taocrm")->controller('admin_analysis_member');
                $date = $oldNewController->format_date($filter);
                $filter['date_from'] = $date['date_from'];
                $filter['date_to'] = $date['date_to'];
                $analysis_data = $oldNewController->getOldNewData($filter);
                $rs = $analysis_data['analysis_data'];
                if($rs) {
                    foreach($rs as $k=>$v){
                        if(strlen($k)>5) $k = substr($k,2);
                        $temp_v = 'x:"'.$k.'"';
                        $temp_v .= ',y1:'.$v['old_member'];
                        $temp_v .= ',y2:'.$v['new_member'];
                        $temp_v .= ',y3:"'.number_format($v['old_ratio'], 2, ".", '').'"';
                        $temp_v .= ',y4:"'.number_format($v['old_amount'], 2, ".", '').'"';
                        $temp_v .= ',y5:"'.number_format($v['new_amount'], 2, ".", '').'"';
                        $temp_v .= ',y6:"'.number_format($v['old_amount_ratio'], 2, ".", '').'"';
                        $dataset[] = $temp_v;
                    }
                }
                
                $chartLabel = array('y1'=>'老客户','y2'=>'新客户','y3'=>'老客户比例','y4'=>'老客户下单金额','y5'=>'新客户下单金额','y6'=>'老客户金额比例');
            break;
        
        endswitch;

        foreach($dataset as $k=>$v){ $dataset[$k] = '{'.$v.'}';}
        $chartData = '['.implode(',',$dataset).']';

        $this->pagedata['chartLabel'] = $chartLabel;
        $this->pagedata['chartData'] = $chartData;
		$this->display("admin/analysis/chart_type/columnStackedDY.html");
    }
    
	public function lose(){
        $filter = array();
        $order_status = $_GET['order_status'];
        $filter['count_by'] = $_GET['count_by'];
        $filter['type'] = $_GET['count_by'];
        $filter['shopId'] = $_GET['shop_id'];
        /*
        $filter['service'] = $_GET['service'];
        $filter['target'] = intval($_GET['target']);//1:金额,2:单价,3:订单和单价
        */
        $filter['date_from'] = $_GET['date_from'];
        $filter['date_to'] = $_GET['date_to'];
        $connect = kernel::single('taocrm_middleware_connect');   
       //$analysis_data = kernel::single('taocrm_analysis_day')->get_member_old_new($filter);
        $loseController = app::get("taocrm")->controller('admin_analysis_member');
        $date = $loseController->format_date($filter);
        $filter['beginTime'] = $date['date_from'];
        $filter['endTime'] = $date['date_to'];
        $rs = $connect->LoseMemberAnalysisByTimeType($filter);
        if($rs) {
        	foreach($rs as $k=>$v){
            	if(strlen($k)>5) $k = substr($k,2);
                $temp_v = 'x:"'.$k.'"';
                $temp_v .= ',y1:'.$v['total_member'];
                $temp_v .= ',y2:'.$v['order_member'];
                $temp_v .= ',y3:'.$v['contact_member'];
                $temp_v .= ',y4:'.$v['uncontact_member'];
                $temp_v .= ',y5:"'.number_format($v['order_member']*100/$v['total_member'], 2, ".", '').'"';
                
                $dataset[] = $temp_v;
            }
        }
                
        $chartLabel = array('y1'=>'接待客户数','y2'=>'下单人数','y3'=>'流失人数(有联系方式)','y4'=>'流失人数(无联系方式)','y5'=>'下单人数占比');
    

        foreach($dataset as $k=>$v){ $dataset[$k] = '{'.$v.'}';}
        $chartData = '['.implode(',',$dataset).']';
        $this->pagedata['chartLabel'] = $chartLabel;
        $this->pagedata['chartData'] = $chartData;
		$this->display("admin/analysis/chart_type/columnStackedLose.html");
    }
    
    function array_sort($arr,$keys,$type='desc'){ 
        $keysvalue = $new_array = array();
        foreach ($arr as $k=>$v){
            $keysvalue[$k] = $v[$keys];
        }
        if($type == 'asc'){
            asort($keysvalue);
        }else{
            arsort($keysvalue);
        }
        reset($keysvalue);
        $i = 1;
        foreach ($keysvalue as $k=>$v){
            $new_array[$k] = $arr[$k];
            $new_array[$k]['order'] = $i++;
        }
        return $new_array; 
    } 

}
