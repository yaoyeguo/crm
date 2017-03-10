<?php
class market_coupon_taobao_type{

    /**
     * 节点类型
     * @access public
     * @return Array
     */
    static function get_coupon_type(){
        $coupon_type = array (
            '3' => '3元',
            '5' => '5元',
            '10' => '10元',
            '20' => '20元',
            '50' => '50元',
            '100' => '100元',
        );
        return $coupon_type;
    }

    /**
     * 节点类型
     * @access public
     * @return Array
     */
    static function get_limit_count(){
        $limit_count = array (
            '1' => '1',
            '2' => '2',
            '3' => '3',
            '4' => '4',
            '5' => '5',
        );
        return $limit_count;
    }
}