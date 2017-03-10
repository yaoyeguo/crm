<?php

$db['wx_point_log']=array (
    'columns' => 
    array (
        'log_id' =>
        array(
            'type' => 'int unsigned',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
            'editable' => false,
        ),
        'wx_member_id' =>
        array (
            'type' => 'int unsigned',
            'label' => '微信客户ID',
        ),
        'ToUserName' =>
        array(
            'type' => 'varchar(100)',
            'required' => false,
            'label' => '微信公众号',
            'editable' => false,
            'searchtype' => 'head',
            'filtertype' => 'normal',
            //'in_list' => true,
            //'default_in_list' => true,
            'width' => 100,
            'order' => 10,
        ),
        'FromUserName' =>
        array(
            'type' => 'varchar(100)',
            'required' => false,
            'label' => '客户微信号',
            'editable' => false,
            'searchtype' => 'head',
            'filtertype' => 'normal',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 120,
            'order' => 10,
        ),
        'points' => 
        array (
            'type' => 'int',
            'default' => 0,
            'required' => false,
            'label' => '积分',
            'editable' => false,
            'width' => 80,
            'order' => 20,
        ),
        'point_mode' =>
        array(
            'type' => 'varchar(1)',
            'label' => '积分方式',
            'required' => false,
            'editable' => false,
            'width' => 120,
            'order' => 22,
        ),
       'op_before_point' =>
        array(
            'type' => 'int',
            'default' => 0,
            'editable' => false,
            'label' => '调整前',
            'in_list' => true,
            'default_in_list' => true,
            //'filtertype' => 'normal',
            //'filterdefault' => 'true',
            'width' => 100,
            'order' => 110,
        ),
        'op_after_point' =>
        array(
            'type' => 'int',
            'default' => 0,
            'editable' => false,
            'label' => '调整后',
            'in_list' => true,
            'default_in_list' => true,
            //'filtertype' => 'normal',
            //'filterdefault' => 'true',
            'width' => 100,
            'order' => 120,
        ),
        'create_time' =>
        array(
            'type' => 'time',
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'label' => '创建时间',
            'width' => 140,
            'order' => 140,
        ),
       'point_desc' => 
        array (
            'type' => 'varchar(100)',
            'label' => '积分备注',
            'editable' => false,
            'in_list' => true,
            'default_in_list' =>true,
            'width' => 140,
            'order' => 160,
        ),
        'op_user' => 
        array (
            'type' => 'varchar(50)',
            'label' => '操作人',
            'editable' => false,
            'in_list' => true,
            'default_in_list' =>true,
            'width' => 90,
            'order' => 180,
        ),
    ),
    'index' =>
    array(
        'ind_wx_member_id' =>
        array(
            'columns' =>
            array(
                0 => 'wx_member_id',
            ),
        ),
      
    ),
    'comment' => '积分日志表',
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);
