<?php

/**
 * 获取图形报表数据
 */
class taocrm_ctl_admin_fx_chart extends desktop_controller {
    
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
        $salesController = app::get("taocrm")->controller('admin_fx_analysis');
        $filter = $this->getSalesAreaParams($_GET);
        $areaInfo = $salesController->getAreaData($filter);
        

        $target_arr = array('','total_amount','total_orders','total_members','paid_amount','paid_orders','paid_members','paid_per_amount');
        $target = $target_arr[$data_type];
        $area_data = $this->array_sort($areaInfo['analysis_data'], $target,$type='desc');
        if($top5 == 1) {
            if($data_type % 5 ==1) $label = '订单金额';
            if($data_type % 5 ==2) $label = '订单数';
            if($data_type % 5 ==3) $label = '下单客户数';
            if($data_type % 5 ==4) $label = '付款订单金额';
            if($data_type % 5 ==0) $label = '付款订单数';
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
            
            if($data_type % 5 ==1) $desc = '订单金额：'.number_format($data[$target], 2, ".", '').'<br/>占比(全国)：'.number_format($data[$target.'_ratio'], 2, ".", '').'%';
            if($data_type % 5 ==2) $desc = '订单数：'.number_format($data[$target], 2, ".", '').'<br/>占比(全国)：'.number_format($data[$target.'_ratio'], 2, ".", '').'%';
            if($data_type % 5 ==3) $desc = '下单客户数：'.number_format($data[$target], 2, ".", '').'<br/>占比(全国)：'.number_format($data[$target.'_ratio'], 2, ".",'').'%';
            if($data_type % 5 ==4) $desc = '付款订单金额：'.number_format($data[$target], 2, ".", '').'<br/>占比(全国)：'.number_format($data[$target.'_ratio'], 2, ".",'').'%';
            if($data_type % 5 ==0) $desc = '付款订单数：'.number_format($data[$target], 2, ".", '').'<br/>占比(全国)：'.number_format($data[$target.'_ratio'], 2, ".",'').'%';
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
        
        $salesController = app::get("taocrm")->controller('admin_fx_analysis');
        $filterEr = $this->getSalesAreaParams($_GET);
        $analysis_data = $salesController->getAreaData($filterEr);
//        echo "<pre>";
//        print_r($analysis_data['analysis_data']);
//        exit;
                
//        $oAnalysisDay = kernel::single('taocrm_analysis_day');
//        $analysis_data = $oAnalysisDay->get_area_data($filter);
        
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
        
        base_kvstore::instance('analysis')->fetch('fx_buy_freq',$result);
       
    	foreach($result as $k=>$v){
            if($k > 6){
            	$v['key'] = '>';
            }else{
            	$v['key'] = $k;
            }
            $arr[$k] = $v;
        }
        //var_dump($result);
        $data = array();

        foreach($arr as $k=>$v){
        	if($v['key'] == '>'){
	        	$temp_orders += $v['TotalOrders'];
            	$temp_members += $v['TotalMembers'];
        	}else{
        		$data[$v['key']] = $v;
        	}
            
        	$total_orders += $v['TotalOrders'];
        	$total_members += $v['TotalMembers'];
        	
        }
        
        if($temp_members || $temp_orders){
        	$data['6次以上'] = array(
        					'TotalOrders'=>$temp_orders,
        					'TotalMembers'=>$temp_members,
        				  );
        }
		
		foreach($data as $k=>$v){
			$temp_v = 'y1:"'.$v['TotalOrders'].'"';
			$temp_v .= ',"y2":"'.round(($v['TotalOrders'] * 100 / $total_orders),2).'"';
			$temp_v .= ',"y3":"'.$v['TotalMembers'].'"';
			$temp_v .= ',"y4":"'.round(($v['TotalMembers'] * 100 /$total_members),2).'"';
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
		$chartLabel = array('y1'=>'订单数','y2'=>'订单数占比','y3'=>'下单客户数','y4'=>'下单客户数占比');
        $chartData = '['.implode(',',$dataset).']';
    	if($chartData == '[]') {echo('暂无数据');die();}
		$this->pagedata['chartData'] = $chartData;
		$this->pagedata['chartLabel'] = $chartLabel;
		$this->display("admin/fx/analysis/freq.html");
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

