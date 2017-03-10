<?php 

$db['member_caselog_category'] = array(
	'columns' => array(
		'category_id' => array(
			'type' => 'int(11)',
			'required' => true,
			'pkey' => true,
			'extra' => 'auto_increment'
        ),
	   'category_name' =>
        array(
            'type' => 'varchar(50)',
            'label' => '名称',
            'width' => 120,
            'editable' => false,
            'searchtype' => 'has',
            'in_list' => true,
            'default_in_list' => true,
            'order' => 20,
        ),
        'desc' =>
        array(
            'type' => 'varchar(100)',
            'label' => '描述',
            'width' => 300,
            'editable' => false,
            'searchtype' => 'has',
            'in_list' => true,
            'default_in_list' => true,
            'order' => 30,
        ),
        'type' =>
        array(
            'type' => array(
                1=>'媒体',
                2=>'类型',
                3=>'来源',
                4=>'状态',
                5=>'其它',
            ),
            'label' => '分类',
            'width' => 100,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'order' => 10, 
        ),
        'create_time' => 
        array(
            'type' => 'time',
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'label' => '创建时间',
            'width' => 150,
            'order' => 50,
        ),
        'status' =>
        array(
            'type' => 'tinyint(2)',
            'label' => '状态',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => false,
            'width' => 150,
            'order' => 120,
        ),
    ),
    'index' =>
    array(
        'ind_category_name' =>
        array(
            'columns' =>
            array(
                0 => 'category_name',
            ),
        ),
    ),
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);