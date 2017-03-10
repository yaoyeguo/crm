<?php
class taocrm_analysis_sales extends taocrm_analysis_abstract implements taocrm_analysis_interface {
    public $logs_options = array(
            '1' => array(
                            'name' => '已支付订单总额',
                            'flag' => array(),
            ),
            '2' => array(
                            'name' => '未支付订单总额',
                            'flag' => array(),
            ),
            '3' => array(
                            'name' => '成功订单总额',
                            'flag' => array(),
            ),
            '4' => array(
                            'name' => '未成功定单总额',
                            'flag' => array(),
            ),
            '5' => array(
                            'name' => '订单总量',
                            'flag' => array(),
            ),
            '6' => array(
                            'name' => '成功定单总量',
                            'flag' => array(),
            ),
            '7' => array(
                            'name' => '未支付定单总量',
                            'flag' => array(),
            ),
            '8' => array(
                            'name' => '取消定单量',
                            'flag' => array(),
            ),
            '9' => array(
                            'name' => '订单总额',
                            'flag' => array(),
            ),

    );

    public function __construct() {
        $relateObj = &app::get('taocrm')->model('analysis_relate');
        $flagList = $relateObj->get_shop();
        foreach($this->logs_options as $key=>$val) {
            $this->logs_options[$key]['flag'][0] = '全部';
            foreach($flagList as $shop) {
                $this->logs_options[$key]['flag'][$shop['relate_id']] = $shop['name'];
            }
        }
    }

    public function get_logs($time) {
    	$date = date('Y-m-d', $time);
        $filter = array(
                'time_from' => $date,
                'time_to' => $date,
        );

        $logObj = &app::get('taocrm')->model('member_analysis');
//		多店铺之和
        $result[] = array('type'=>0, 'target'=>1, 'flag'=>0, 'value'=>$logObj->get_sales_count(array('start_time' => $filter['time_from'],'end_time' => $filter['time_to'],'type' => 1)));
        $result[] = array('type'=>0, 'target'=>2, 'flag'=>0, 'value'=>$logObj->get_sales_count(array('start_time' => $filter['time_from'],'end_time' => $filter['time_to'],'type' => 2)));
        $result[] = array('type'=>0, 'target'=>3, 'flag'=>0, 'value'=>$logObj->get_sales_count(array('start_time' => $filter['time_from'],'end_time' => $filter['time_to'],'type' => 3)));
        $result[] = array('type'=>0, 'target'=>4, 'flag'=>0, 'value'=>$logObj->get_sales_count(array('start_time' => $filter['time_from'],'end_time' => $filter['time_to'],'type' => 4)));
        $result[] = array('type'=>0, 'target'=>5, 'flag'=>0, 'value'=>$logObj->get_sales_count(array('start_time' => $filter['time_from'],'end_time' => $filter['time_to'],'type' => 5)));
        $result[] = array('type'=>0, 'target'=>6, 'flag'=>0, 'value'=>$logObj->get_sales_count(array('start_time' => $filter['time_from'],'end_time' => $filter['time_to'],'type' => 6)));
        $result[] = array('type'=>0, 'target'=>7, 'flag'=>0, 'value'=>$logObj->get_sales_count(array('start_time' => $filter['time_from'],'end_time' => $filter['time_to'],'type' => 7)));
        $result[] = array('type'=>0, 'target'=>8, 'flag'=>0, 'value'=>$logObj->get_sales_count(array('start_time' => $filter['time_from'],'end_time' => $filter['time_to'],'type' => 8)));
        $result[] = array('type'=>0, 'target'=>9, 'flag'=>0, 'value'=>$logObj->get_sales_count(array('start_time' => $filter['time_from'],'end_time' => $filter['time_to'],'type' => 9)));
//		各个分店铺
        $relateObj = &app::get('taocrm')->model('member_analysis_day');
        $flagList = $relateObj->get_shop();
        foreach($flagList as $key=>$val) {
            $result[] = array('type'=>0, 'target'=>1, 'flag'=>$val['relate_id'], 'value'=>$logObj->get_sales_count(array('start_time' => $filter['time_from'],'end_time' => $filter['time_to'],'type' => 1,'shop_id' =>$val['type_id'])));
            $result[] = array('type'=>0, 'target'=>2, 'flag'=>$val['relate_id'], 'value'=>$logObj->get_sales_count(array('start_time' => $filter['time_from'],'end_time' => $filter['time_to'],'type' => 2,'shop_id' =>$val['type_id'])));
            $result[] = array('type'=>0, 'target'=>3, 'flag'=>$val['relate_id'], 'value'=>$logObj->get_sales_count(array('start_time' => $filter['time_from'],'end_time' => $filter['time_to'],'type' => 3,'shop_id' =>$val['type_id'])));
            $result[] = array('type'=>0, 'target'=>4, 'flag'=>$val['relate_id'], 'value'=>$logObj->get_sales_count(array('start_time' => $filter['time_from'],'end_time' => $filter['time_to'],'type' => 4,'shop_id' =>$val['type_id'])));
            $result[] = array('type'=>0, 'target'=>5, 'flag'=>$val['relate_id'], 'value'=>$logObj->get_sales_count(array('start_time' => $filter['time_from'],'end_time' => $filter['time_to'],'type' => 5,'shop_id' =>$val['type_id'])));
            $result[] = array('type'=>0, 'target'=>6, 'flag'=>$val['relate_id'], 'value'=>$logObj->get_sales_count(array('start_time' => $filter['time_from'],'end_time' => $filter['time_to'],'type' => 6,'shop_id' =>$val['type_id'])));
            $result[] = array('type'=>0, 'target'=>7, 'flag'=>$val['relate_id'], 'value'=>$logObj->get_sales_count(array('start_time' => $filter['time_from'],'end_time' => $filter['time_to'],'type' => 7,'shop_id' =>$val['type_id'])));
            $result[] = array('type'=>0, 'target'=>8, 'flag'=>$val['relate_id'], 'value'=>$logObj->get_sales_count(array('start_time' => $filter['time_from'],'end_time' => $filter['time_to'],'type' => 8,'shop_id' =>$val['type_id'])));
            $result[] = array('type'=>0, 'target'=>9, 'flag'=>$val['relate_id'], 'value'=>$logObj->get_sales_count(array('start_time' => $filter['time_from'],'end_time' => $filter['time_to'],'type' => 9,'shop_id' =>$val['type_id'])));
        }

        return $result;
    }

    public function detail() {
    }
}