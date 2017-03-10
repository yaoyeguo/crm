<?php

/**
 * 获取图形报表数据
 */
class taocrm_ctl_admin_analysis_chart extends desktop_controller {
    
    var $am_map_setting = array(
        '安徽' => 'CN_34','浙江' => 'CN_33','江西' => 'CN_36','江苏' => 'CN_32',
        '吉林' => 'CN_22','青海' => 'CN_63','福建' => 'CN_35','黑龙江' => 'CN_23',
        '河南' => 'CN_41','河北' => 'CN_13','湖南' => 'CN_43','湖北' => 'CN_42',
        '新疆' => 'CN_65','西藏' => 'CN_54','甘肃' => 'CN_62','广西' => 'CN_45',
        '贵州' => 'CN_52','辽宁' => 'CN_21','内蒙古' => 'CN_15','宁夏' => 'CN_64',
        '北京' => 'CN_11','上海' => 'CN_31','陕西' => 'CN_61','山东' => 'CN_37',
        '山西' => 'CN_14','天津' => 'CN_12','云南' => 'CN_53','广东' => 'CN_44',
        '海南' => 'CN_46','四川' => 'CN_51','重庆' => 'CN_50','香港' => 'CN_91',
        '澳门' => 'CN_92','台湾' => 'TW'
    );
 
    
    //近12个月客户走势（NEW）
    public function chart_line_new()
    {
        $data = $this->getChartLineData($_GET);
        if ($_GET['target'] == 1) {
            $dataSet = array();
            foreach ($data as $k => $v) {
                if ($_GET['service'] == 'widgets') {
                    $temp = 'x:"' . substr($k, 2, 5) . '"';
                }
                else {
                    $temp = 'x:"' . substr($k, 5, 5) . '"';
                }
                $temp .= ',"y1": "'.$v['finish_orders'].'"';
                $temp .= ',"y3": "'.number_format($v['finish_amount'], 2, ".", '').'"';
                $temp .= ',"y2": "'.$v['finish_members'].'"';
                $dataSet[] = $temp;
            }
            foreach($dataSet as $k=>$v) {
                $dataSet[$k] = '{'.$v.'}';
            }
            $chartData = '['.implode(',',$dataSet).']';
            $chartLabel = array('y1' => '订单数', 'y2' => '客户数', 'y3' => '订单金额');
        }
        if(!$chartData || $chartData == '[]') {
            echo('暂无数据');
            die();
        }
        $this->pagedata['chartData'] = $chartData;
        $this->pagedata['chartLabel'] = $chartLabel;
        if('widgets' == $_GET['service']){
            $this->display("admin/analysis/chart_type/widgets_line2.html");
        }else {
            $this->display("admin/analysis/chart_type/MSCol3D2.html");
        }
    }
    
    protected function getChartLineData($data)
    {
    	
        $connect = kernel::single('taocrm_middleware_connect');
        $params = array();
        $params['shopId'] = $data['shop_id'];
        $func = '';
      	
        if(!$params['shopId']){
            unset($params['shopId']);
        }
        
        switch ($data['service']) {
            case 'widgets':
                $params['monthCount'] = isset($data['month_count']) ? $data['month_count'] : 12;
               	//按月统计
                $params['beginTime'] = strtotime($data['date_from']);
                $params['endTime'] = strtotime($data['date_to']);
                $func = 'OrderMemberCountByMonth';
                break;
            case 'taocrm_analysis_sales':
                $params['beginTime'] = strtotime($data['date_from']);
                $tmpEndTime = strlen($data['date_to']) > 10 ?  $data['date_to'] :  $data['date_to'] . ' ' . date('H:i:s');
                $params['endTime'] = strtotime($tmpEndTime);
                $func = 'OrderMemberAmountCountByDay';
                break;
        }
        if ($func != '') {
        	//$data = json_decode($connect->$func($params), true);
        	return $connect->$func($params);
            //return json_decode($connect->$func($params), true);
        }
        return '';
    }
    
    public function chart_line()
    {
        $compare_data = false;
        $filter = $_GET;
        $order_status = $_GET['order_status'];
        $filter['target'] = intval($_GET['target']);//1:金额,2:单价,3:订单和单价
        
        //对比数据
        if($_GET['c_date_from'] && $_GET['c_date_to']) {
            $this->show_compare_chart($filter);
        }
        //$data = kernel::single('taocrm_analysis_day')->get_all_sales_data($_GET);
        
        base_kvstore::instance('analysis')->fetch('sales_index',$all_sales_data);        
        $data = $all_sales_data['analysis_data'];
        //var_dump($data);
        
        switch($filter['target']) :
            default:
                if($data) {
                    //ksort($data);
                    foreach($data as $v){
                        $v['date'] = substr($v['date'],2);
                        $temp_v = 'x:"'.$v['date'].'"';
                        if($order_status=='finish'):
                            $temp_v .= ',y1: "'.$v['finish_orders'].'"';
                            $temp_v .= ',y3: "'.number_format($v['finish_amount'], 2, ".", '').'"';
                            $temp_v .= ',"y2": "'.$v['finish_members'].'"';
                        elseif($order_status=='paid'):
                            $temp_v .= ',"y1": "'.$v['paid_orders'].'"';
                            $temp_v .= ',"y3": "'.number_format($v['paid_amount'], 2, ".", '').'"';
                            $temp_v .= ',"y2": "'.$v['paid_members'].'"';
                        else:
                            $temp_v .= ',"y1": "'.$v['total_orders'].'"';
                            $temp_v .= ',"y3": "'.number_format($v['total_amount'], 2, ".", '').'"';
                            $temp_v .= ',"y2": "'.$v['total_members'].'"';
                        endif; 
                        $dataset[] = $temp_v;
                    }
                }
                
                foreach($dataset as $k=>$v){$dataset[$k] = '{'.$v.'}';}
                $chartData = '['.implode(',',$dataset).']';
                $chartLabel = array('y1'=>'订单数','y2'=>'客户数','y3'=>'订单金额');
            break;
            
            case 2:
                if($data) {
                    //ksort($data);
                    foreach($data as $k=>$v){
                        $v['date'] = substr($v['date'],2); 
                        $temp_v = 'x:"'.$v['date'].'"';
                        if($order_status=='finish'):
                            $temp_v .= ',"y1": "'.$v['finish_orders'].'"';
                            $temp_v .= ',"y2": "'.$v['finish_members'].'"';
                            $temp_v .= ',"y3": "'.round($v['finish_amount']/$v['finish_orders'],2).'"';
                            $temp_v .= ',"y4": "'.round($v['finish_amount']/$v['finish_members'],2).'"';
                        elseif($order_status=='paid'):
                            $temp_v .= ',"y1": "'.$v['paid_orders'].'"';
                            $temp_v .= ',"y2": "'.$v['paid_members'].'"';
                            $temp_v .= ',"y3": "'.round($v['paid_amount']/$v['paid_orders'],2).'"';
                            $temp_v .= ',"y4": "'.round($v['paid_amount']/$v['paid_members'],2).'"';
                        else:
                            $temp_v .= ',"y1": "'.$v['total_orders'].'"';
                            $temp_v .= ',"y2": "'.$v['total_members'].'"';
                            $temp_v .= ',"y3": "'.round($v['total_amount']/$v['total_orders'],2).'"';
                            $temp_v .= ',"y4": "'.round($v['total_amount']/$v['total_members'],2).'"';
                        endif;
                        $dataset[] = $temp_v;
                    }
                }
                
                foreach($dataset as $k=>$v){$dataset[$k] = '{'.$v.'}';}
                $chartData = '['.implode(',',$dataset).']';
                $chartLabel = array('y1'=>'订单数','y2'=>'客户数','y3'=>'平均订单单价','y4'=>'平均客单价');
            break;
        endswitch;

        if(!$chartData or $chartData == '[]') {echo('暂无数据');die();}
        $this->pagedata['chartData'] = $chartData;
        $this->pagedata['chartLabel'] = $chartLabel;
        if('widgets' == $_GET['service']){
            $this->display("admin/analysis/chart_type/widgets_line2.html");
        }elseif(count($chartLabel)==4){
            $this->display("admin/analysis/chart_type/MSCol3D3.html");
        }else{
            $this->display("admin/analysis/chart_type/MSCol3D2.html");
        }
    }
    
    
    //订单状态
    public function order_status()
    { 
        $filter = $_GET;
        $filter['shop_id'] = $_GET['shop_id'];
        $filter['service'] = $_GET['service'];
        $filter['target'] = intval($_GET['target']);//1:金额,2:单价,3:订单和单价
        $filter['date_from'] = strtotime($_GET['date_from']) - 1;
        $filter['date_to'] = strtotime($_GET['date_to']) - 1;
        if($filter['date_from'] == $filter['date_to']){
        	$filter['date_to'] += 86400;
        }
        
        base_kvstore::instance('analysis')->fetch('sales_ostatus',$data);        
        $rs = $data['analysis_data'];
        
        //var_dump($rs);
        
        /*
		$count_unit = 'c_'.$_GET['count_by'];//c_date,c_month,c_week,c_year
        $count_unit_name = '';
        if($count_unit=='c_week') {
            $count_unit_name = '周';
            $group_by = 'c_year,c_week';
            $count_unit = "CONCAT(c_year,'.',c_week)";
        }
        $where = " shop_id = '".$filter['shop_id']."' 
                AND (c_time between ".$filter['date_from']." and ".$filter['date_to'].")
                ";
                
        $sql = "select 
                    $count_unit as date,
                    sum(total_orders) as total_orders,
                    sum(total_members) as total_members,
                    sum(total_amount) as total_amount,

                    sum(finish_orders) as finish_orders,
                    sum(finish_members) as finish_members,
                    sum(finish_total_amount) as finish_amount,
                    
                    sum(paid_orders) as paid_orders,
                    sum(paid_amount) as paid_amount,
                    sum(paid_members) as paid_members
                from sdb_taocrm_member_analysis_day 
                where 1=1 and $where
                group by $count_unit
        ";
        $rs = $this->app->model('member_analysis_day')->db->select($sql);
        */
        
        
        //$data = kernel::single('taocrm_analysis_day')->get_all_sales_data($_GET);
        //$rs = $data['analysis_data'];

        if($rs){
            foreach($rs as $k=>$v){
                /*
                foreach($v as $kk=>$vv) {
                    $data[$kk][] = '{"value": "'.$vv.'"}';                    
                }*/
                
                $v['total_per_amount'] = round($v['total_amount']/($v['total_orders']),2);
                $v['paid_per_amount'] = round($v['paid_amount']/($v['total_orders']),2);
                $v['finish_per_amount'] = round($v['finish_amount']/($v['total_orders']),2);
                
                $v['order_ratio1'] = round($v['paid_orders']*100/($v['total_orders']),2);
                $v['order_ratio2'] = round($v['finish_orders']*100/($v['total_orders']),2);
                
                $v['amount_ratio1'] = round($v['paid_amount']*100/($v['total_amount']),2);
                $v['amount_ratio2'] = round($v['finish_amount']*100/($v['total_amount']),2);
                
                $v['member_ratio1'] = round($v['paid_members']*100/($v['total_members']),2);
                $v['member_ratio2'] = round($v['finish_members']*100/($v['total_members']),2);
                $categorys[] = '{"label": "'.substr($v['date'],2).'"}'; 
                
                $rs[$k] = $v;
            }
        }
        
		switch($filter['target']) :
            default:
                $chartLabel = array('y1'=>'订单数','y2'=>'付款订单数','y3'=>'完成订单数','y4'=>'付款率','y5'=>'成交率');
                foreach($rs as $k=>$v){
                    $temp_v = 'x:"'.substr($v['date'],2).'"';
                    $temp_v .= ',y1:'.$v['total_orders'];
                    $temp_v .= ',y2:'.$v['paid_orders'];
                    $temp_v .= ',y3:'.$v['finish_orders'];
                    $temp_v .= ',y4:'.$v['order_ratio1'];
                    $temp_v .= ',y5:'.$v['order_ratio2'];
                    $dataset[] = $temp_v;
                }
                
            break;
            
            case 2:
                $chartLabel = array('y1'=>'订单金额','y2'=>'付款金额','y3'=>'完成金额','y4'=>'付款率','y5'=>'成交率');
                foreach($rs as $k=>$v){
                    $temp_v = 'x:"'.substr($v['date'],2).'"';
                    $temp_v .= ',y1:"'.$v['total_amount'].'"';
                    $temp_v .= ',y2:"'.$v['paid_amount'].'"';
                    $temp_v .= ',y3:"'.$v['finish_amount'].'"';
                    $temp_v .= ',y4:'.$v['amount_ratio1'];
                    $temp_v .= ',y5:'.$v['amount_ratio2'];
                    $dataset[] = $temp_v;
                }
                
            break;
            
            case 3:
                $chartLabel = array('y1'=>'客户数','y2'=>'付款客户数','y3'=>'完成客户数','y4'=>'付款率','y5'=>'成交率');
                foreach($rs as $k=>$v){
                    $temp_v = 'x:"'.substr($v['date'],2).'"';
                    $temp_v .= ',y1:'.$v['total_members'];
                    $temp_v .= ',y2:'.$v['paid_members'];
                    $temp_v .= ',y3:'.$v['finish_members'];
                    $temp_v .= ',y4:'.$v['member_ratio1'];
                    $temp_v .= ',y5:'.$v['member_ratio2'];
                    $dataset[] = $temp_v;
                }
            break;
            
            case 4:
                $chartLabel = array('y1'=>'平均单价','y2'=>'平均付款单价','y3'=>'平均完成单价');
                foreach($rs as $k=>$v){
                    $temp_v = 'x:"'.substr($v['date'],2).'"';
                    $temp_v .= ',y1:'.$v['total_per_amount'];
                    $temp_v .= ',y2:'.$v['paid_per_amount'];
                    $temp_v .= ',y3:'.$v['finish_per_amount'];
                    $dataset[] = $temp_v;
                }
            break;                   
        endswitch;
        
        foreach($dataset as $k=>$v){ $dataset[$k] = '{'.$v.'}';}
        $chartData = '['.implode(',',$dataset).']';
        
        //var_dump($chartData);

        $this->pagedata['chartLabel'] = $chartLabel;
        $this->pagedata['chartData'] = $chartData;
        $this->display("admin/analysis/chart_type/Col3Line2.html");
        
		if(count($chartLabel)==5) {
            $this->display("admin/analysis/chart_type/Col3Line2.html");
        }else{
            $this->display("admin/analysis/chart_type/Col3.html");
        }
    }
    
    protected function getSalesAreaParams($params)
    {
        $filter = array();
        $filter['shopId'] = $params['shop_id'];
        $filter['beginTime'] = strtotime(date("Y-m-d 00:00:00", strtotime($params['date_from'])));
        $filter['endTime'] = strtotime(date("Y-m-d 23:59:59", strtotime($params['date_to'])));
        $filter['data_type'] = isset($params['data_type']) ? max(intval($params['data_type']), 1) : 1;
        $filter['top5'] = isset($params['top5']) ? max(intval($params['top5']), 1) : 1;
        return $filter;
    }
    
    /**
     * 订单地域分析
     */
    public function sales_area(){
    	
        $data_type = intval($_GET['data_type']);
        $top5 = intval($_GET['top5']);
        $salesController = app::get("taocrm")->controller('admin_analysis_sales');
        $filter = $this->getSalesAreaParams($_GET);
        $areaInfo = $salesController->getAreaData($filter);
//        print_r($areaInfo);
//        exit;
        //$analysis_data = kernel::single("taocrm_analysis_day")->get_area_data($_GET);

        //$area_data = $analysis_data['analysis_data'];

        $target_arr = array('','total_amount','total_orders','total_members','per_amount','paid_amount','paid_orders','paid_members','paid_per_amount','finish_amount','finish_orders','finish_members','finish_per_amount');
        $target = $target_arr[$data_type];
        $area_data = $this->array_sort($areaInfo['analysis_data'], $target,$type='desc');
        if($top5 == 1) {
            if($data_type % 4 ==1) $label = '订单金额';
            if($data_type % 4 ==2) $label = '订单数';
            if($data_type % 4 ==3) $label = '客户数';
            if($data_type % 4 ==0) $label = '平均单价';
            foreach($area_data as $v) {
                $json_data[] = array('h3'=>$v['area'],'label'=>$label,'b'=>$v[$target]);
            }
            echo(json_encode($json_data));
            die();
        }
        
        foreach($this->am_map_setting as $key=>$value) {
            $args['type'] = $filter['data_type'];
            $args['ship_area'] = $key;
            $data = $area_data[$key];
            $data_value = $data[$target];
            
            if($data_type % 4 ==1) $desc = '订单金额：'.number_format($data[$target], 2, ".", '').'<br/>占比(全国)：'.number_format($data[$target.'_ratio'], 2, ".", '').'%';
            if($data_type % 4 ==2) $desc = '订单数：'.number_format($data[$target], 2, ".", '').'<br/>占比(全国)：'.number_format($data[$target.'_ratio'], 2, ".", '').'%';
            if($data_type % 4 ==3) $desc = '客户数：'.number_format($data[$target], 2, ".", '').'<br/>占比(全国)：'.number_format($data[$target.'_ratio'], 2, ".",'').'%';
            if($data_type % 4 ==0) $desc = '平均单价：'.number_format($data[$target], 2, ".", '').' ';
            $desc .= '<br/>排名(全国)：'.$data['order'];

            $map_data[] = array(
                'order'=> $data['order'],
                'title'=> $key,
                'code' => $value,
                'value' => $data_value,
                'description' => '<br/><br/>'.$desc.''
            );
        }
        $str = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $str .= '<map map_file="maps/china.swf" tl_long="73.620045" tl_lat="53.553745" br_long="134.768463" br_lat="18.168882" zoom="95%" zoom_x="3.63%" zoom_y="2.68%">'."\n";
        $str .= "<areas>\n";
        $movie_str = '';
        foreach($map_data as $k=>$v){
            //$v['order'] = sizeof($map_data) - $v['order'];
            $str .= '<area mc_name="'.$v['code'].'" title="'.$v['title'].'" value="'.$v['value'].'">
                         <description><![CDATA['.$v['description'].']]></description></area>'."\n";
        }
        $str .= '<area mc_name="borders" title="borders" color="#FFFFFF" balloon="false"></area>'."\n";
        $str .= '</areas>'."\n";
        $str .= '<movies>'."\n";
        $str .= $movie_str;
        $str .= '</movies>'."\n";
        $str .= '
         <labels>
    <label long="84.947427" lat="42.4255522182285" text_size="12" color="#ADADAD" remain="true" >
      <text>新疆</text>
    </label>
    <label long="93.664221" lat="35.9844221852234" text_size="12" color="#ADADAD" remain="true" >
      <text>青海</text>
    </label>
    <label long="87.351449" lat="32.0448599653983" text_size="12" color="#ADADAD" remain="true" >
      <text>西藏</text>
    </label>
    <label long="94.451443" lat="40.2213340878427" text_size="12" color="#ADADAD" remain="true" >
      <text>甘肃</text>
    </label>
    <label long="102.503601" lat="40.7053671176742" text_size="12" color="#ADADAD" remain="true" >
      <text>内蒙古</text>
    </label>
    <label long="105.42382" lat="37.3192801788836" text_size="12" color="#ADADAD" remain="true" >
      <text>宁夏</text>
    </label>
    <label long="107.159457" lat="34.4963888002001" text_size="12" color="#ADADAD" remain="true" >
      <text>陕西</text>
    </label>
    <label long="111.342981" lat="38.0728866718267" text_size="12" color="#ADADAD" remain="true" >
      <text>山西</text>
    </label>
    <label long="114.2632" lat="38.6925749771918" text_size="12" color="#ADADAD" remain="true" >
      <text>河北</text>
    </label>
    <label long="116.313726" lat="40.1379247682859" text_size="12" color="#ee0000" remain="true" >
      <text>北京</text>
    </label>
    <label long="119.863724" lat="41.6601350846444" text_size="12" color="#ADADAD" remain="true" >
      <text>辽宁</text>
    </label>
    <label long="123.81108" lat="44.3814182161181" text_size="12" color="#ADADAD" remain="true" >
      <text>吉林</text>
    </label>
    <label long="124.838218" lat="47.4675971256227" text_size="12" color="#ADADAD" remain="true" >
      <text>黑龙江</text>
    </label>
    <label long="116.235004" lat="36.684938676849" text_size="12" color="#ADADAD" remain="true" >
      <text>山东</text>
    </label>
    <label long="112.287647" lat="34.1014332163018" text_size="12" color="#ADADAD" remain="true" >
      <text>河南</text>
    </label>
    <label long="110.394566" lat="31.6387174148497" text_size="12" color="#ADADAD" remain="true" >
      <text>湖北</text>
    </label>
    <label long="106.289764" lat="30.0746786984768" text_size="12" color="#ADADAD" remain="true"  >
      <text>重庆</text>
    </label>
    <label long="100.580529" lat="30.5783306607435" text_size="12" color="#ADADAD" remain="true" >
      <text>四川</text>
    </label>
    <label long="99.111048" lat="24.8874581099438" text_size="12" color="#ADADAD" remain="true" >
      <text>云南</text>
    </label>
    <label long="105.502542" lat="27.1538015125393" text_size="12" color="#ADADAD" remain="true" >
      <text>贵州</text>
    </label>
    <label long="110.158399" lat="27.9248636108764" text_size="12" color="#ADADAD" remain="true" >
      <text>湖南</text>
    </label>
    <label long="114.499367" lat="28.9016002459833" text_size="12" color="#ADADAD" remain="true" >
      <text>江西</text>
    </label>
    <label long="116.156282" lat="31.6387174148497" text_size="12" color="#ADADAD" remain="true" >
      <text>安徽</text>
    </label>
    <label long="118.049364" lat="33.9704099613927" text_size="12" color="#ADADAD" remain="true" >
      <text>江苏</text>
    </label>
    <label long="121.524387" lat="31.1663917093303" text_size="12" color="#ADADAD" remain="true" >
      <text>上海</text>
    </label> 
    <label long="118.68289" lat="29.3160185360148" text_size="12" color="#ADADAD" remain="true" >
      <text>浙江</text>
    </label>
    <label long="117.104697" lat="26.4448121027352" text_size="12" color="#ADADAD" remain="true" >
      <text>福建</text>
    </label>
    <label long="106.844569" lat="24.2987022240325" text_size="12" color="#ADADAD" remain="true" >
      <text>广西</text>
    </label>
    <label long="112.36637" lat="23.7175422550551" text_size="12" color="#ADADAD" remain="true" >
      <text>广东</text>
    </label>
    <label long="109.052539" lat="19.2954337299303" text_size="12" color="#ADADAD" remain="true" >
      <text>海南</text>
    </label>
    <label long="120.418528" lat="23.8622173402318" text_size="12" color="#ADADAD" remain="true" >
      <text>台湾</text>
    </label>
    <label long="117.18342" lat="39.0004249712802" text_size="12" color="#ADADAD" remain="true" >
      <text>天津</text>
    </label>
  </labels>
        </map>';
        empty($str)?' ':$str;
        echo($str);
    }
    
    //时间段对比分析
    private function show_compare_chart($filter){

        /*
        //原始数据
        $sql = "SELECT $count_unit,
            sum(total_orders) as total_orders,
            sum(total_amount) as total_amount,
            sum(total_members) as total_members 
            FROM sdb_taocrm_member_analysis_day 
            WHERE $where
            Group BY $count_unit
        ";
        $rs = $this->app->model('member_analysis_day')->db->select($sql);
        if($rs) {
            foreach($rs as $v){
                $data[] = $v;
            }
        }
        
        //对比数据
        $where = " shop_id = '".$_GET['shop_id']."' 
                AND (c_time between ".(strtotime($_GET['c_date_from']) - 1)." 
				and ".(strtotime($_GET['c_date_to']) - 1).")
                ";
        $sql = "SELECT $count_unit,
            sum(total_orders) as total_orders,
            sum(total_amount) as total_amount,
            sum(total_members) as total_members 
            FROM sdb_taocrm_member_analysis_day 
            WHERE $where
            Group BY $count_unit
        ";
        $rs = $this->app->model('member_analysis_day')->db->select($sql);
        if($rs) {
            foreach($rs as $v){
                $compare_data[] = $v;
            }
        }
        */
        
        base_kvstore::instance('analysis')->fetch('sales_index',$all_sales_data);
        
        //$memory_data = kernel::single('taocrm_analysis_day')->get_all_sales_data($filter);
        $data = $all_sales_data['analysis_data'];
        
        $filter['date_from'] = $filter['c_date_from'];
        $filter['date_to'] = $filter['c_date_to'];
        //$memory_data = kernel::single('taocrm_analysis_day')->get_all_sales_data($filter);
        $compare_data = $all_sales_data['compare_data'];
    
        if($_GET['target']==1):
            foreach($data as $k=>$v){
                $vv = $compare_data[$k];
                $temp_v = 'x:"'.substr($v['date'],2).'"';
                $temp_v .= ',y1:"'.$v['total_amount'].'"';
                $temp_v .= ',y2:"'.intval($vv['total_amount']).'"';
                $temp_v .= ',label1:"'.$v['date'].'销售金额"';
                $temp_v .= ',label2:"'.$vv['date'].'销售金额"';
                $dataset[] = $temp_v;
            }
            
            foreach($dataset as $k=>$v){
                $dataset[$k] = '{'.$v.'}';
            }
            $chartData = '['.implode(',',$dataset).']';
        else:
            //平均订单单价
            foreach($data as $k=>$v){
                $vv = $compare_data[$k];
                $temp_v = 'x:"'.substr($v['date'],2).'"';
                $temp_v .= ',y1:"'.round($v['total_amount']/$v['total_orders'],2).'"';
                $temp_v .= ',y2:"'.round($vv['total_amount']/$vv['total_orders'],2).'"';
                $temp_v .= ',label1:"'.$v['date'].'平均单价"';
                $temp_v .= ',label2:"'.$vv['date'].'平均单价"';
                $dataset[] = $temp_v;
            }
            
            foreach($dataset as $k=>$v){
                $dataset[$k] = '{'.$v.'}';
            }
            $chartData = '['.implode(',',$dataset).']';
        endif;
        
        //var_dump($filter);
        $this->pagedata['chartLabel'] = $chartLabel;
        $this->pagedata['chartData'] = $chartData;
        $this->display("admin/analysis/chart_type/Zoomline1.html");
        die();
    }
    
    //购买时段分析
    public function chart_hours()
    {
        $compare_data = false;
        $filter = array();
        $order_status = $_GET['order_status']?$_GET['order_status']:'total';
        $filter['order_status'] = $order_status;
        $filter['shop_id'] = $_GET['shop_id'];
        $filter['service'] = $_GET['service'];
        $filter['target'] = intval($_GET['target']);//1:金额,2:单价,3:订单和单价
        $filter['date_from'] = ($_GET['date_from']);
        $filter['date_to'] = ($_GET['date_to']);
        if($_GET['c_date_from'] && $_GET['c_date_to']) {
            $compare_data = true;
            $filter['c_date_from'] = ($_GET['c_date_from']);
            $filter['c_date_to'] = ($_GET['c_date_to']);
        }
		$count_unit = 'c_'.$_GET['count_by'];//c_date,c_month,c_week,c_year
        $where = " shop_id = '".$filter['shop_id']."' 
                AND c_time > ".$filter['date_from']."
                AND c_time < ".$filter['date_to']." ";
                
        /*
        $oAnalysisDay = kernel::single('taocrm_analysis_day');
        $all_sales_data = $oAnalysisDay->get_hours_data($filter);
        $rs = $all_sales_data['analysis_data'];
        */
        
        base_kvstore::instance('analysis')->fetch('sales_hours',$all_sales_data);        
        $rs = $all_sales_data['analysis_data'];
		switch($filter['target']) :
            default:
                if($rs) {
                    foreach($rs as $k=>$v){
                        $temp_v = 'x:"'.$k.'"';
                        $temp_v .= ',y1:"'.$v[$order_status.'_orders'].'"';
                        $temp_v .= ',y2:"'.$v[$order_status.'_members'].'"';
                        $temp_v .= ',y3:"'.number_format($v[$order_status.'_amount'], 2, ".", '').'"';
                        $dataset[] = $temp_v;
                    }
                }
                
                $chartLabel = array('y1'=>'订单数','y2'=>'客户数','y3'=>'订单金额');
                
            break;
            
            case 2:
                if($rs) {
                    foreach($rs as $k=>$v){
                        $temp_v = 'x:"'.$k.'"';
                        $temp_v .= ',y1:"'.$v[$order_status.'_orders'].'"';
                        $temp_v .= ',y2:"'.$v[$order_status.'_members'].'"';
                        $temp_v .= ',y3:"'.number_format(round($v[$order_status.'_amount']/$v[$order_status.'_orders'],2), 2, ".", '').'"';
                        $temp_v .= ',y4:"'.number_format(round($v[$order_status.'_amount']/$v[$order_status.'_members'],2), 2, ".", '').'"';
                        $dataset[] = $temp_v;
                    }
                }
                
                $chartLabel = array('y1'=>'订单数','y2'=>'客户数','y3'=>'平均订单单价','y4'=>'平均客单价');

            break;
        
        endswitch;
        
        foreach($dataset as $k=>$v){ $dataset[$k] = '{'.$v.'}';}
        $chartData = '['.implode(',',$dataset).']';
        $this->pagedata['chartLabel'] = $chartLabel;
        $this->pagedata['chartData'] = $chartData;
        if(count($chartLabel)==4):
            $this->display("admin/analysis/chart_type/MSCol3D3.html");
        else:
            $this->display("admin/analysis/chart_type/MSCol3D2.html");
        endif;
    }
    
    //按星期统计
    public function chart_week()
    {
        $compare_data = false;
        $filter = array();
        $order_status = $_GET['order_status']?$_GET['order_status']:'total';
        $filter['order_status'] = $order_status;
        $filter['shop_id'] = $_GET['shop_id'];
        $filter['service'] = $_GET['service'];
        $filter['target'] = intval($_GET['target']);//1:金额,2:单价,3:订单和单价
        $filter['date_from'] = ($_GET['date_from']);
        $filter['date_to'] = ($_GET['date_to']);
		$count_unit = 'c_'.$_GET['count_by'];//c_date,c_month,c_week,c_year
        
        base_kvstore::instance('analysis')->fetch('sales_week',$all_sales_data);
        //echo('<pre>');var_dump($all_sales_data);        
        $rs = $all_sales_data['analysis_data'];
		switch($filter['target']) :
            default:
                if($rs) {
                    foreach($rs as $k=>$v){
                        $temp_v = 'x:"'.$k.'"';
                        $temp_v .= ',y1:"'.$v[$order_status.'_orders'].'"';
                        $temp_v .= ',y2:"'.$v[$order_status.'_members'].'"';
                        $temp_v .= ',y3:"'.number_format($v[$order_status.'_amount'], 2, ".", '').'"';
                        $dataset[] = $temp_v;
                    }
                }
                
                $chartLabel = array('y1'=>'平均订单数','y2'=>'平均客户数','y3'=>'平均订单金额');
                
            break;
        
        endswitch;
        
        foreach($dataset as $k=>$v){ $dataset[$k] = '{'.$v.'}';}
        $chartData = '['.implode(',',$dataset).']';
        $this->pagedata['chartLabel'] = $chartLabel;
        $this->pagedata['chartData'] = $chartData;
        $this->display("admin/analysis/chart_type/MSCol3D2.html");
    }
    
    // 地区排行
    public function area_rank(){
        $filter = array();
        $order_status = $_GET['order_status']?$_GET['order_status']:'all';//var_dump($_GET);
        $filter['order_status'] = $order_status;
        $filter['shop_id'] = $_GET['shop_id'];
        $filter['service'] = $_GET['service'];
        $filter['target'] = intval($_GET['target']);//1:金额,2:单价,3:订单和单价
        $filter['date_from'] = ($_GET['date_from']);
        $filter['date_to'] = ($_GET['date_to']);
        
        $salesController = app::get("taocrm")->controller('admin_analysis_sales');
        $filterEr = $this->getSalesAreaParams($_GET);
        $analysis_data = $salesController->getAreaData($filterEr);
        
		switch($filter['target']) :
            default:
                $rs = $analysis_data['analysis_data'];
                if($rs) {
                    foreach($rs as $k=>$v){
                        $temp_v = 'x:"'.$v['area'].'"';
                        $temp_v .= ',y2:'.$v['total_orders'];
                        $temp_v .= ',y3:'.$v['total_members'];
                        $temp_v .= ',y1:'.$v['total_amount'];
                        $dataset[] = $temp_v;
                    }
                }
                
                $chartLabel = array('y2'=>'订单数','y3'=>'客户数','y1'=>'订单金额');
                foreach($dataset as $k=>$v){
                    $dataset[$k] = '{'.$v.'}';
                }
                $chartData = '['.implode(',',$dataset).']';
            break;
        
        endswitch;

        $this->pagedata['chartData'] = $chartData;
        $this->pagedata['chartLabel'] = $chartLabel;
		$this->display("admin/analysis/chart_type/CLine2.html");
    }
    
    public function chart_freq()
    {
    	
        $shop_id = $_GET['shop_id'];
        
        /*
        if(OPEN_MEMO_DATA == true){
            $result = kernel::single('taocrm_analysis_day')->get_buy_freq($_GET);
        }else{
            $db = kernel::database();
            $sql = "SELECT buy_freq, count( distinct member_id ) total_members, sum( total_amount ) total_amount, sum( total_orders ) total_orders
			FROM sdb_taocrm_member_analysis WHERE buy_freq >0 and shop_id='$shop_id'
			GROUP BY buy_freq";
            $result = $db->select($sql);
        }
        */
        base_kvstore::instance('analysis')->fetch('buy_freq',$all_sales_data);
        $result = $all_sales_data['analysis_data'];
    	foreach($result as $v){
            	if($v['buy_freq'] > 6){
            		$v['key'] = '>';
            	}
            	$arr[$v['buy_freq']] = $v;
        }
        //var_dump($result);
        $data = array();
        foreach($arr as $k=>$v){
        	if($v['key'] == '>'){
	        	$total_amount += $v['total_amount'];
	        	$mem_num += $v['total_members'];
	        	$order_num += $v['total_orders'];
        	}else{
        		$data[$v['key']] = $v;
        	}
            
        	$total_memory += $v['total_amount'];
        	$total_mem += $v['total_members'];
        }
        
        if($total_amount >0 && $mem_num >0 && $order_num >0){
            $data['6次以上'] = array('total_amount'=>$total_amount,'total_members'=>$mem_num,'total_orders'=>$order_num);
        }
		
		foreach($data as $k=>$v){
			$temp_v = 'y1:"'.round(($v['total_amount'] / $v['total_members']),2).'"';
			$temp_v .= ',"y2":"'.round(($v['total_amount'] / $v['total_orders']),2).'"';
			$temp_v .= ',"y3":"'.$v['total_members'].'"';
			$temp_v .= ',"y5":"'.round(($v['total_amount'] * 100) /$total_memory,2).'"';
			$temp_v .= ',"y4":"'.round(($v['total_members'] * 100) /$total_mem,2).'"';
			if(is_numeric($k)){
				$temp_v .= ',"x": "'.$k.'次"';
			}else{
				$temp_v .= ',"x": "'.$k.'"';
			}
			$dataset[] = $temp_v;
		}

		foreach($dataset as $k=>$v){
			$dataset[$k] = '{'.$v.'}';
		}
		$chartLabel = array('y1'=>'平均客单价','y2'=>'平均订单价','y3'=>'客户数','y4'=>'人数占比','y5'=>'销售额占比');
        $chartData = '['.implode(',',$dataset).']';
    	if($chartData == '[]') {echo('暂无数据');die();}
		$this->pagedata['chartData'] = $chartData;
		$this->pagedata['chartLabel'] = $chartLabel;
		$this->display("admin/analysis/chart_type/freq.html");
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
    
    //修正12月可能出现周数的错误
    private function _correct_week($year,$week,$time)
    {
        if($week=='01' && date('m',$time) == 12){
            $year++;
        }
        return "{$year}.{$week}";
    }

}

