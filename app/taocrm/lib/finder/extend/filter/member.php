<?php
class taocrm_finder_extend_filter_member{
    function get_extend_colums(){
        $db['members']=array (
        'columns' => array (
            'ext_order_amount' => array (
                'type' => 'money',
                'label' => '订单额',
                'width' => 75,
                'filtertype' => 'yes',
                'filterdefault' => true,
            ),
            'ext_order_createtime' => array(
                'type' => 'time',
                'label' => '下单时间',
                'width' => 130,
                'filtertype' => 'time',
                'filterdefault' => true,
            ),
        ));
        return $db;
    }
}