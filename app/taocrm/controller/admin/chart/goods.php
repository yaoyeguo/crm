<?php

/**
 * 获取图形报表数据
 */
class taocrm_ctl_admin_chart_goods extends desktop_controller {

    
    public function goods_rank(){
		$shop_id=$_GET['shop_id'];
		$shopobj=app::get('ecorder')->model("shop");
		$shoptype=$shopobj->dump(array('shop_id'=>$shop_id),'node_type');
		$node_type=$shoptype['node_type'];
        $filter = array();
        $order_status = $_GET['order_status']?$_GET['order_status']:'all';
        $filter['order_status'] = $order_status;
        $filter['shop_id'] = $_GET['shop_id'];
        $filter['service'] = $_GET['service'];
        $filter['target'] = intval($_GET['target']);//1:金额,2:单价,3:订单和单价
        $filter['date_from'] = ($_GET['date_from']);
        $filter['date_to'] = ($_GET['date_to']);
                
        $oAnalysisDay = kernel::single('taocrm_analysis_day');
        $analysis_data = $oAnalysisDay->get_goods_rank($filter);
        
		switch($filter['target']) :
            default:
                $rs = $analysis_data['analysis_data'];
                $rs = $this->array_sort($rs,'avg_nums','desc');//数组排序
                if($rs) {
                    $i = 0;
                    foreach($rs as $k=>$v){
                        $i++;
                        $temp_v = 'x:"'.str_replace('"',"",$v['name']).'"';
                        $temp_v .= ',y1:"'.$v['avg_nums'].'"';
                        $temp_v .= ',y2:"'.$v['avg_store'].'"';
                        $temp_v .= ',y3:'.$v['amount'];
                        if($i<=10) $dataset[] = $temp_v;
                    }
                }
            break;
            
            case 2:
                $rs = $analysis_data['analysis_data'];  
                $rs = $this->array_sort($rs,'avg_store','asc');//数组排序
                if($rs) {
                    $i = 0;
                    foreach($rs as $k=>$v){
                        $i++;
                        $temp_v = 'x:"'.str_replace('"',"",$v['name']).'"';
                        $temp_v .= ',y1:"'.$v['avg_nums'].'"';
                        $temp_v .= ',y2:"'.$v['avg_store'].'"';
                        $temp_v .= ',y3:'.$v['amount'];
                        if($i<=10) $dataset[] = $temp_v;
                    }
                }
            break;
        endswitch;
        
        
        foreach($dataset as $k=>$v){ $dataset[$k] = '{'.$v.'}';}
        $chartData = '['.implode(',',$dataset).']';
		if ($node_type!="taobao"){
			$this->pagedata['chartData'] = $chartData;
			$this->display("admin/analysis/chart_type/barClustered2.html");
		}else {
	        $this->pagedata['chartData'] = $chartData;
			$this->display("admin/analysis/chart_type/barClustered.html");
		}
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

