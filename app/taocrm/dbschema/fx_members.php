<?php
 
$db['fx_members']=array (
    'columns' => 
    array (
    'member_id' =>
    array(
        'type' => 'int unsigned',
        'required' => true,
        'pkey' => true,
        'extra' => 'auto_increment',
        'editable' => false,
    ),
    'mobile' => 
    array (
        'type' => 'varchar(30)',
        'label' => '手机',
        'editable' => false,
        'searchtype' => 'head',
        'filtertype' => 'normal',
        'in_list' => true,
        'default_in_list' => true,
        'width' => 105,
        'order' => 90,
    ),
     'last_send_time' =>
        array(
            'type' => 'time',
            'default' => 0,
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'label' => '最后发送时间',
            'width' => 140,
            'order' => 50,
        ),
         'is_mobile_valid' => 
	     array (
		      'type' => 'tinyint unsigned',
		      'default' => '0',
		      'label' => '手机是否有效',
		      'width' => 75,
		      'editable' => true,
		      'in_list' => true,
         ),
          'update_time' =>
        array(
            'type' => 'time',
            'default' => 0,
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => false,
            'label' => '更新时间',
            'width' => 140,
            'order' => 60,
        ),
         'create_time' =>
        array(
            'type' => 'time',
            'default' => 0,
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'label' => '创建时间',
            'width' => 140,
            'order' => 60,
        ),
  ),
  'index' =>
    array(
     'ind_unique_mobile' =>
        array(
            'columns' =>
            array(
                0 => 'mobile',
            ),
            'prefix' => 'unique',
        ),
       'ind_is_mobile_valid' =>
        array(
            'columns' =>
            array(
                0 => 'is_mobile_valid',
            ),
        ),
    ),
  'comment' => '分销客户表',
  'engine' => 'innodb',
  'version' => '$Rev:  $',
);
