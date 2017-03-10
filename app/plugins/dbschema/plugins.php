<?php
// 客户统计数据
$db['plugins'] = array(
    'columns' =>
    array(
        'plugin_id' =>
        array(
            'type' => 'int',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
            'editable' => false,
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
        'price' =>
        array(
            'type' => 'int unsigned',
            'label' => '单价',
            'required' => false,
            'editable' => false,
            'in_list' => false,
        	//'searchtype' => 'has',
            'default_in_list' => false,
            'width' => 120,
            'order' => 30,
        ),
        'amount' =>
	    array(
	        'label' => '付款金额',
	        'type' => 'int unsigned',
	        'editable' => false,
	        'filtertype' => 'yes',
	        'filterdefault' => 'true',
	        'in_list' => false,
	        'default_in_list' => false,
	        'width' => 110,
	        'order' => 40,
	    ),
        'month' =>
        array(
            'type' => 'int',
            'label' => '服务时限',
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
            'width' => 110,
	        'order' => 50,
        ),
        'last_run_time' =>
        array(
            'type' => 'time',
            'default' => 0,
            'label' => '最后运行时间',
            'editable' => false,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 160,
            'order' => 70,
        ),
        'buy_time' =>
        array(
            'type' => 'time',
            'default' => 0,
            'label' => '启用时间',
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
            'label' => '过期时间',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'width' => 160,
            'order' => 90,
        ),
        'params' =>
        array(
            'type' => 'text',
            'default' => 0,
            'label' => '配置参数',
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'width' => 100,
            'order' => 100,
        ),
        'status' =>
        array(
            'type' => array(
                'wait'=>'等待',
                'running'=>'运行中',
                'success'=>'成功',
                'failed'=>'失败'
            ),
            'default' => 'wait',
            'label' => '插件状态',
            'editable' => false,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'in_list' => true,
            'default_in_list' => false,
            'width' => 100,
            'order' => 110,
        )
    ),
    'index' =>
    array(
        'ind_buy_time' =>
        array(
            'columns' =>
            array(
                0 => 'buy_time',
            ),
        ),
        'ind_end_time' =>
        array(
            'columns' =>
            array(
                0 => 'end_time',
            ),
        ),
    ),
    'engine' => 'innodb',
    'version' => '$Rev:  $',
); 
