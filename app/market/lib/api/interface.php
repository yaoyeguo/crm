<?php
/*
 * api 调用接口
 * 
 * @author 
 * @version 0.1
 */

interface market_api_interface {
    
    /**
     * 获取指定时间内所有的记录
     * 
     * @param Integer $startTime 开始时间
     * @param Integer $endTime 结束时间
     * @return Array
     */
    public function & fetch($startTime, $endTime);
    
}