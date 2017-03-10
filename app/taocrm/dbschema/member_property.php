<?php
// 客户统计数据
$db['member_property'] = array(
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
        'shop_id' =>
        array(
            'type' => 'varchar(32)',
            'label' => '来源店铺',
            'required' => true,
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
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
     	'ind_member_property' =>
        array(
            'columns' =>
            array(
                0 => 'shop_id',
                1 => 'uname',
            ),
        ),
    ),
    
    'engine' => 'innodb',
    'version' => '$Rev:  $',
); 

