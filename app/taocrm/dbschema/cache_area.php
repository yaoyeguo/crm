<?php

$db['cache_area']=array (
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
        'area_id' =>
        array (
            'type' => 'int unsigned',
            'label' => '区域ID',
            'required' => false,
            'editable' => false,
        ),
        'area_name' =>
        array (
            'type' => 'char(32)',
            'label' => '区域名称',
            'required' => false,
            'editable' => false,
        ),
        'total_orders' => 
        array (
            'type' => 'int unsigned',
            'default' => 0,
            'label' => '总订单数',
            'required' => false,
            'editable' => false,
        ),
        'total_amount' => 
        array (
            'type' => 'money',
            'default' => 0,
            'label' => '订单总金额',
            'required' => false,
            'editable' => false,
        ),
        'total_per_amount' => 
        array (
            'type' => 'money',
            'default' => 0,
            'label' => '平均单价',
            'required' => false,
            'editable' => false,
        ),
        'total_members' =>
        array(
            'type' => 'int unsigned',
            'default' => 0,
            'label' => '客户总数',
            'required' => false,
            'editable' => false,
        ),
        'paid_orders' =>
        array(
            'type' => 'int unsigned',
            'default' => 0,
            'label' => '付款订单数',
            'required' => false,
            'editable' => false,
        ),
        'paid_amount' =>
        array (
            'type' => 'money',
            'default' => 0,
            'label' => '付款金额',
            'required' => false,
            'editable' => false,
        ),
        'paid_per_amount' =>
        array (
            'type' => 'money',
            'default' => 0,
            'label' => '平均付款单价',
            'required' => false,
            'editable' => false,
        ),
        'paid_members' =>
        array(
            'type' => 'int unsigned',
            'default' => 0,
            'label' => '付款客户数',
            'required' => false,
            'editable' => false,
        ),
        'finish_members' =>
        array(
            'type' => 'int unsigned',
            'default' => 0,
            'label' => '完成客户数',
            'required' => false,
            'editable' => false,
        ),
        'finish_orders' =>
        array(
            'type' => 'int unsigned',
            'default' => 0,
            'label' => '完成订单数',
            'required' => false,
            'editable' => false,
        ),
        'finish_amount' =>
        array(
            'type' => 'money',
            'default' => 0,
            'label' => '完成金额',
            'required' => false,
            'editable' => false,
        ),
        'finish_per_amount' =>
        array(
            'type' => 'money',
            'default' => 0,
            'label' => '平均完成单价',
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
        'ind_area_id' =>
        array(
            'columns' =>
            array(
                0 => 'area_id',
            ),
        ),
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
    'comment' => '报表缓存(地域分析)',
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);


