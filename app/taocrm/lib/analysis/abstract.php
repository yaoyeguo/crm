<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

abstract class taocrm_analysis_abstract 
{
    protected $_serivce = null;
    protected $_render = null;
    
    public $logs_options = array();

    function __construct(&$app){
        $this->app = $app;
        $this->_render = kernel::single('desktop_controller');
        $this->_service = get_class($this);
    }

    public function get_logs($time){
        //todo:各自实现
    }

    public function detail(){
        $detail = array();
        $analysis_id = app::get('taocrm')->model('analysis')->select()->columns('id')->where('service = ?', $this->_service)->instance()->fetch_one();
        $obj = app::get('taocrm')->model('analysis_logs')->select()->columns('target, sum(value) AS value')->where('analysis_id = ?', $analysis_id);
        if(isset($this->_params['type']))   $obj->where('type = ?', $this->_params['type']);
        if(isset($this->_params['target']))   $obj->where('target = ?', $this->_params['target']);
        if(isset($this->_params['time_from']))   $obj->where('time >= ?', strtotime(sprintf('%s 00:00:00', $this->_params['time_from'])));
        if(isset($this->_params['time_to']))   $obj->where('time <= ?', strtotime(sprintf('%s 23:59:59', $this->_params['time_to'])));
        $rows = $obj->where('flag = ?', 0)->group(array('target'))->instance()->fetch_all();
        foreach($rows AS $row){
            $tmp[$row['target']] = $row['value'];
        }

        foreach($this->logs_options AS $target=>$option){
            $detail[$option['name']]['value'] = ($tmp[$target]) ? $tmp[$target] : 0;
            $detail[$option['name']]['memo'] = $this->logs_options[$target]['memo'];
            $detail[$option['name']]['icon'] = $this->logs_options[$target]['icon'];
        }

        foreach($detail AS $key=>$val){
            $name = $this->app->_($key);
            $data[$name]['value'] = $val['value'];
            $data[$name]['memo'] = $this->app->_($val['memo']);
            $data[$name]['icon'] = $val['icon'];
        }

        return $data;
    }

    public function graph_data($params){
        $analysisObj = app::get('taocrm')->model('analysis');
        $analysisInfo = $analysisObj->dump(array('service'=>$params['service']),'*');
        if(empty($analysisInfo))   return array('categories'=>array(), 'data'=>array());

        $logFilter = array();
        $logFilter['analysis_id'] = $analysisInfo['id'] ? $analysisInfo['id'] : '';
        $logFilter['target'] = $params['target'] ? $params['target'] : '';
        $logFilter['type'] = $params['type'] ? $params['type'] : '';
        $logFilter['time|between'][] = $params['time_from'] ? strtotime($params['time_from']) : '';
        if($logFilter['time|between'][0] && $logFilter['time|between'][0] != ''){
            $logFilter['time|between'][] = $params['time_to'] ? (strtotime($params['time_to'])+86400) : time();
        }

        $logObj = app::get('taocrm')->model('analysis_logs');
        $rows = $logObj->getList('target,flag,value,time',$logFilter);
        
        for($i=strtotime($params['time_from']); $i<=strtotime($params['time_to']); $i+=($analysisInfo['interval'] == 'day')?86400:3600){
            $time_range[] = ($analysisInfo['interval'] == 'day') ? date("Y-m-d", $i) : date("Y-m-d H", $i);
        }
        
        $logs_options = kernel::single($params['service'])->logs_options;
        $target = $logs_options[$params['target']];
        if(is_array($target['flag']) && count($target['flag'])){
            foreach($target['flag'] AS $k=>$v){
                foreach($time_range AS $date){
                    $data[$v][$date] = 0;
                }
            }
        }else{
            foreach($time_range AS $date){
                $data['全部'][$date] = 0;
            }
        }

        foreach($rows AS $row){
            $date = ($analysisInfo['interval'] == 'day') ? date("Y-m-d", $row['time']) : date("Y-m-d H", $row['time']);
            $flag_name = $target['flag'][$row['flag']];
            if($flag_name){
                $data[$flag_name][$date] = $row['value'];
            }else{
//                $data['全部'][$date] = $row['value'];
            }
        }

        return array('categories'=>$time_range, 'data'=>$data);
    }
}
