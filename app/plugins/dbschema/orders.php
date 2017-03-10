<?php
// 客户统计数据
$db['orders'] = array(
    'columns' =>
    array(
        'order_id' =>
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
        'amount' =>
	    array(
	        'label' => '付款金额',
	        'type' => 'int unsigned',
	        'editable' => false,
	        'filtertype' => 'yes',
	        'filterdefault' => 'true',
	        'in_list' => true,
	        'default_in_list' => true,
	        'width' => 110,
	        'order' => 40,
	    ),
        'month' =>
        array(
            'type' => 'int',
            'label' => '服务时限',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 110,
	        'order' => 50,
        ), 
        'buy_time' =>
        array(
            'type' => 'time',
            'default' => 0,
            'label' => '购买时间',
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
            'label' => '截止时间',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'width' => 160,
            'order' => 90,
        ),
        'desc' =>
        array(
            'type' => 'tinytext',
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
        ),
        'op_user' => 
        array(
        	'type' =>'varchar(32)',
            'required' => false,
            'label' => '操作人',
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
            //'searchtype' => 'has',
            'width' => 80,
            'order' => 120,
        ),
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
