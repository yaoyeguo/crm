<?php
class taocrm_analysis_member_buynum extends taocrm_analysis_abstract implements taocrm_analysis_interface{
    public $logs_options = array();

    public function __construct(){
        $relateObj = &app::get('taocrm')->model('analysis_relate');
        $flagList = $relateObj->get_shop();
        foreach($this->logs_options as $key=>$val){
            foreach($flagList as $shop){
                $this->logs_options[$key]['flag'][$shop['relate_id']] = $shop['name'];
            }
        }
    }

    public function get_logs($time){
    	$date = date('Y-m-d', $time);

        $relateObj = &app::get('taocrm')->model('analysis_relate');
        $flagList = $relateObj->get_shop();

        foreach ($flagList as $k=>$v){
            $shop[$v['type_id']] = $v['relate_id'];
        }

        $logObj = &app::get('taocrm')->model('analysis_logs');
        $tmp = $logObj->get_member_by_shop(array(
            'start_time' => $date,
            'end_time' => $date
        )) ;

        foreach($tmp as $k=> $v){
            $result[] = array('type'=>0, 'target'=>1, 'flag'=>$shop[$v['shop_id']], 'value'=>$v['count']);
        }

        return $result;
    }
    
    public function resetLogsOptions($param) {
        $relateObj = &app::get('taocrm')->model('analysis_relate');
        $flagList = $relateObj->get_shop();
        foreach ($flagList as $key => $shop) {
        	if ($param['shopId'] == $shop['type_id']) {
        		$this->logs_options[1]['name'] = $shop['name'];
        		$this->logs_options[1]['flag'][$shop['relate_id']] = $shop['name'];
        	}
        }
    }          
}