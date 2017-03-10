<?php
// 客户全局属性
$db['member_overall_property'] = array(
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
        'member_id' =>
        array(
            'type' => 'int unsigned',
            'required' => true,
            'editable' => false,
        ),
         'uname' =>
	    array(
	        'type' => 'varchar(50)',
	        'required' => true,
	        'label' => '客户名',
	        'editable' => false,
	        'in_list' => true,
	        'default_in_list' => true,
	    ),
        'property' =>
        array(
            'type' => 'varchar(50)',
	        'required' => true,
	        'label' => '客户属性名',
	        'editable' => false,
	        'in_list' => true,
	        'default_in_list' => true,
        ),
        'value'=>
        array(
        	'type' => 'varchar(50)',
	        'required' => true,
	        'label' => '客户属性值',
	        'editable' => false,
	        'in_list' => true,
	        'default_in_list' => true,
        )
    ),
    
    'index' =>
    array(
     	'ind_mmember_id' =>
        array(
            'columns' =>
            array(
                0 => 'member_id',
            ),
        ),
    ),
    
    'engine' => 'innodb',
    'version' => '$Rev:  $',
); 

