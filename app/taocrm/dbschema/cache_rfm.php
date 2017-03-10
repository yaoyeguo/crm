<?php

$db['cache_rfm']=array (
    'columns' => 
    array (
        'id' =>
        array(
            'type' => 'int unsigned',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
            'editable' => false,
        ),
        'param_r' =>
        array (
            'type' => 'char(32)',
            'label' => '小时',
            'required' => false,
            'editable' => false,
        ),
        'param_f' => 
        array (
            'type' => 'char(32)',
            'default' => 0,
            'label' => '总订单数',
            'required' => false,
            'editable' => false,
        ),
        'date_from' => 
        array (
            'type' => 'int unsigned',
            'default' => 0,
            'label' => '订单总金额',
            'required' => false,
            'editable' => false,
        ),
        'date_to' => 
        array (
            'type' => 'int unsigned',
            'default' => 0,
            'label' => '平均单价',
            'required' => false,
            'editable' => false,
        ),
        'analysis_data' =>
        array(
            'type' => 'text',
            'default' => 0,
            'label' => '客户总数',
            'required' => false,
            'editable' => false,
        ),
        'total_data' =>
        array(
            'type' => 'text',
            'default' => 0,
            'label' => '付款订单数',
            'required' => false,
            'editable' => false,
        ),
        'total_r_data' =>
        array (
            'type' => 'text',
            'default' => 0,
            'label' => '付款金额',
            'required' => false,
            'editable' => false,
        ),
        'total_f_data' =>
        array (
            'type' => 'text',
            'default' => 0,
            'label' => '平均付款单价',
            'required' => false,
            'editable' => false,
        ),
        'cdate' =>
        array(
            'type' => 'int unsigned',
            'default' => 0,
            'label' => '日期',
            'required' => false,
            'editable' => false,
        ),
        'shop_id' =>
        array(
            'type' => 'char(32)',
            'default' => 0,
            'label' => '店铺ID',
            'required' => false,
            'editable' => false,
        ),
        'create_date' =>
        array(
            'type' => 'datetime',
            'default' => 0,
            'label' => '创建时间',
            'required' => false,
            'editable' => false,
        ),
    ),
    'index' =>
    array(
        'ind_cdate' =>
        array(
            'columns' =>
            array(
                0 => 'cdate',
            ),
        ),
        'ind_shop_id' =>
        array(
            'columns' =>
            array(
                0 => 'shop_id',
            ),
        ),
    ),
    'comment' => '报表缓存(购买时段)',
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);


