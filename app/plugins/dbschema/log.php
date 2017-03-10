<?php
// 客户统计数据
$db['log'] = array(
    'columns' =>
    array(
        'log_id' =>
        array(
            'type' => 'int',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
            'editable' => false,
        ),
        'plugin_id' =>
        array(
            'type' => 'int',
            'required' => true,
            'editable' => false,
            'label' => '插件编号',            
        ),
        'plugin_name' => 
        array(
        	'type' =>'varchar(100)',
            'required' => false,
            'label' => '插件名称',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            //'searchtype' => 'has',
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
            //'searchtype' => 'has',
            'width' => 110,
            'order' => 20,
        ),
        'run_key' => 
        array(
        	'type' =>'varchar(32)',
            'required' => false,
            'label' => '防止重复运行',
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
            //'searchtype' => 'has',
            'width' => 110,
            'order' => 20,
        ),
        'start_time' =>
        array(
            'type' => 'time',
            'default' => 0,
            'label' => '开始时间',
            'editable' => false,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 160,
            'order' => 80,
        ),
        'end_time' =>
        array(
            'type' => 'time',
            'default' => 0,
            'label' => '结束时间',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'width' => 160,
            'order' => 90,
        ),
        'sms_count' =>
        array(
            'type' => 'int',
            'default' => 0,
            'label' => '发送短信数',
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
        ),
        'desc' =>
        array(
            'type' => 'text',
            'default' => 0,
            'label' => '备注',
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
        'ind_start_time' =>
        array(
            'columns' =>
            array(
                0 => 'start_time',
            ),
        ),
        'ind_end_time' =>
        array(
            'columns' =>
            array(
                0 => 'end_time',
            ),
        ),
        'ind_run_key' =>
        array(
            'columns' =>
            array(
                0 => 'run_key',
            ),
        ),
    ),
    'engine' => 'innodb',
    'version' => '$Rev:  $',
); 
