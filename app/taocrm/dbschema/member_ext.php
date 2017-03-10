<?php

//会员扩展属性
$db['member_ext']=array (
    'columns' => 
    array (
        'ext_id' => array(
            'type' => 'int unsigned',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
            'editable' => false,
        ),
        'member_id' => array(
            'type' => 'int unsigned',
            'label' => '会员ID',
            'required' => true,
        ),
        'b_year' => array (
            'type' => 'smallint unsigned',
            'default' => 0,
            'label' => '生日(年)',
            'editable' => false,
            'in_list'=>false,
            'width' => 40,
        ),
        'b_month' => array (
            'type' => 'tinyint unsigned',
            'default' => 0,
            'label' => '生日(月)',
            'editable' => false,
            'hidden' => true,
            'in_list' => false,
            'width' => 40,
        ),
        'b_day' => array (
            'type' => 'tinyint unsigned',
            'default' => 0,
            'label' => '生日(天)',
            'editable' => false,
            'hidden' => true,
            'in_list' => false,
            'width' => 40,
        ),
        'create_time' => array (
            'type' => 'time',
            'label' => '创建时间',
            'in_list' => false,
        ),
        'update_time' => array (
            'type' => 'time',
            'label' => '修改时间',
            'in_list' => false,
        ),
    ),
    'index' => 
    array(
        'ind_member_id' =>
        array(
            'columns' =>
            array(
                0 => 'member_id',
            ),
        ),
        'ind_b_year' =>
        array(
            'columns' =>
            array(
                0 => 'b_year',
            ),
        ),
        'ind_b_month' =>
        array(
            'columns' =>
            array(
                0 => 'b_month',
            ),
        ),
        'ind_b_day' =>
        array(
            'columns' =>
            array(
                0 => 'b_day',
            ),
        ),
    ),
    'comment' => '会员扩展属性',
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);
