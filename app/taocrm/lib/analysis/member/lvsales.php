<?php
class taocrm_analysis_member_lvsales extends taocrm_analysis_abstract implements taocrm_analysis_interface{
    public $logs_options = array();

    public function __construct(){
        $lvObj = &app::get('taocrm')->model('shop_lv');
        $lvList = $lvObj->getList();

        $relateObj = &app::get('taocrm')->model('analysis_relate');
        $flagList = $relateObj->get_shop();
        
        foreach($lvList as $lv){
            $this->logs_options[$lv['shop_lv_id']]['name'] = $lv['name'];
            foreach($flagList as $shop){
            	if ($shop['type_id'] == $lv['shop_id']) {
            		$this->logs_options[$lv['shop_lv_id']]['flag'][$shop['relate_id']] = $shop['name'];	
            	}
            }
        }
    }

    public function get_logs($time){
    	$date = date('Y-m-d', $time);
        $filter = array(
                'time_from' => $date,
                'time_to' => $date,
        );      

        $relateObj = &app::get('taocrm')->model('analysis_relate');
        $flagList = $relateObj->get_shop();
        $logObj = &app::get('taocrm')->model('analysis_logs');

        foreach($flagList as $shop) {
            $data = $logObj->get_sales_data(array(
                'start_time' => $filter['time_from'],
                'end_time' => $filter['time_to'],
                'shop_member_buy' => true,
                'shop_id' => $shop['type_id'])
            );
            
            if ($data[0]['shop_id']) {
            	foreach ($data as $v) {
            		$result[] = array('type'=>0, 'target'=>$v['shop_lv_id'], 'flag'=>$shop['relate_id'], 'value'=>$v['total']);
            	}
            }
            else {
            	$result[] = array('type'=>0, 'target'=>$data['shop_lv_id'], 'flag'=>$shop['relate_id'], 'value'=>$data['total']);
            }
        }

        return $result;
    }
    
    public function resetLogsOptions($param) {
    	$this->logs_options = array();
        $lvObj = &app::get('taocrm')->model('shop_lv');
        $lvList = $lvObj->getList('*', array('shop_id' => $param['shopId']));	
        $relateObj = &app::get('taocrm')->model('analysis_relate');
        $flagList = $relateObj->get_shop();
        
        if ($lvList) {
	        foreach($lvList as $lv){
	            $this->logs_options[$lv['shop_lv_id']]['name'] = $lv['name'];
	            foreach($flagList as $shop){
	            	if ($shop['type_id'] == $lv['shop_id']) {
	            		$this->logs_options[$lv['shop_lv_id']]['flag'][$shop['relate_id']] = $shop['name'];	
	            	}
	            }
	        }
        }
        else {
        	$this->logs_options[0] = array();
        	foreach ($flagList as $shop) {
        		if ($shop['type_id'] == $param['shopId']) {
        			$this->logs_options[0]['flag'][$shop['relate_id']] = $shop['name'];	
        		}
        	}
        }      
    }
}