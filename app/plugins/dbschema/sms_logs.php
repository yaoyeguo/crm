<?php

//插件短信发送日志 
$db['sms_logs'] = array(
    'columns' =>
    array(
        'id' =>
        array(
            'type' => 'bigint',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
            'editable' => false,
        ),
        'tid' => 
        array(
        	'type' =>'bigint',
            'required' => false,
            'label' => '订单号',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 150,
            'order' => 10,
        ),
        'shop_id' => 
        array(
        	'type' =>'varchar(50)',
            'required' => false,
            'label' => '店铺ID',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 150,
            'order' => 10,
        ),
        'plugin_name' => 
        array(
        	'type' =>'varchar(50)',
            'required' => false,
            'label' => '插件名称',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 150,
            'order' => 10,
        ),
        'shop_name' => 
        array(
        	'type' =>'varchar(50)',
            'required' => false,
            'label' => '店铺名称',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 150,
            'order' => 10,
        ),
        'worker' => 
        array(
        	'type' =>'varchar(100)',
            'required' => false,
            'label' => '执行方法',
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
            'width' => 110,
            'order' => 20,
        ),
        'create_time' =>
        array(
            'type' => 'time',
            'default' => 0,
            'label' => '创建时间',
            'editable' => false,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 160,
            'order' => 80,
        ),
        'create_date' =>
        array(
            'type' => 'datetime',
            'default' => 0,
            'label' => '创建时间',
            'editable' => false,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'in_list' => false,
            'default_in_list' => false,
            'width' => 160,
            'order' => 80,
        ),
        'sms_content' =>
        array(
            'type' => 'varchar(500)',
            'default' => 0,
            'label' => '短信内容',
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
        ),
        'mobile' =>
        array(
            'type' => 'varchar(11)',
            'default' => 0,
            'label' => '手机号码',
            'editable' => false,
            'in_list' => false,
            'in_list' => true,
            'default_in_list' => false,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'width' => 100,
            'order' => 100,
        ),
        'status' =>
        array(
            'type' => 'varchar(32)',
            'default' => '未知',
            'label' => '运行结果',
            'editable' => false,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 100,
            'order' => 110,
        )
    ),
    'index' =>
    array(
        'ind_create_time' =>
        array(
            'columns' =>
            array(
                0 => 'create_time',
            ),
        ),
        'ind_worker' =>
        array(
            'columns' =>
            array(
                0 => 'worker',
            ),
        ),
        'ind_tid' =>
        array(
            'columns' =>
            array(
                0 => 'tid',
            ),
        ),
        'ind_mobile' =>
        array(
            'columns' =>
            array(
                0 => 'mobile',
            ),
        ),
    ),
    'engine' => 'innodb',
    'version' => '$Rev:  $',
); 
