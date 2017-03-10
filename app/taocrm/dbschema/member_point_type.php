<?php
// 客户积分类型表
$db['member_point_type'] = array(
    'columns' =>
    array(
        'id' =>
        array(
            'type' => 'int unsigned',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
            'editable' => false,
        ),
        'name' =>
        array(
            'type' => 'varchar(100)',
            'required' => false,
            'label' => '类型名称',
            'editable' => false,
            'searchtype' => 'head',
            'filtertype' => 'normal',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 85,
            'order' => 10,
        ),
        'code' =>
        array(
            'type' => 'varchar(50)',
            'label' => '类型代码',
            'required' => false,
            'editable' => false,
            'in_list' => false,
        	//'searchtype' => 'has',
            'default_in_list' => false,
            'width' => 120,
            'order' => 22,
        ),
        'create_time' => 
        array (
        'type' => 'time',
        'label' => '创建时间',
        'editable' => false,
        'in_list' => true,
        'default_in_list' =>false,
        'width' => 140,
        'order' => 160,
       ),
    ),
    'index' =>
    array(
     	  
    ),
    'engine' => 'innodb',
    'version' => '$Rev:  $',
); 

