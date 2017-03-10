<?php 

$db['member_export_log'] = array(
	'columns' => array(
		'export_log_id' => array(
			'type' => 'int(11)',
			'required' => true,
			'pkey' => true,
			'extra' => 'auto_increment'
			),
	   'export_id' => array(
			'type' => 'number',
			'required' => true,
			),
       'mobile' =>
        array(
            'type' => 'varchar(32)',
            'label' => '手机',
            'required' => false,
            'editable' => false,
            'in_list' => false,
        	//'searchtype' => 'has',
            'default_in_list' => false,
            'width' => 120,
            'order' => 50,
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
            'order' => 30,
        ),
    ),
    'engine' => 'innodb',
);