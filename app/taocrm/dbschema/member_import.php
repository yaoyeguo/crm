<?php
 
$db['member_import']=array (
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
     'group_id' =>
    array(
        'type' => 'int unsigned',
        'default' => '0',
        'required' => true,
        'editable' => false,
    ),
     'batch_id' =>
    array(
        'type' => 'int unsigned',
        'default' => '0',
        'required' => true,
        'editable' => false,
    ),
    'succ_member_id' =>
    array(
        'type' => 'int unsigned',
        'default' => '0',
        'required' => true,
        'editable' => false,
    ),
    'uname' =>
    array(
        'type' => 'varchar(50)',
        'required' => false,
        'label' => '客户名',
        'editable' => false,
        'searchtype' => 'head',
        'filtertype' => 'normal',
        'in_list' => true,
        'default_in_list' => true,
        'width' => 85,
        'order' => 10,
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
        'order' => 20,
    ),
    'email' => 
    array (
        'type' => 'varchar(200)',
        'label' => '电子邮件',
        'sdfpath' => 'contact/email',
        'editable' => false,
        'searchtype' => 'has',
        'filtertype' => 'normal',
        'in_list' => true,
        'default_in_list' => true,
        'width' => 160,
        'order' => 30,
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
    'is_email_valid' => 
	     array (
		      'type' => 'tinyint unsigned',
		      'default' => '0',
		      'label' => '邮箱是否有效',
		      'width' => 75,
		      'editable' => true,
		      'in_list' => true,
    ),
      'send_count' => 
        array (
            'type' => 'int',
            'default' => 0,
            'required' => false,
            'label' => '发送次数',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 80,
            'order' => 40,
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
         'unique_md5' => 
    array (
        'type' => 'varchar(32)',
        'label' => '唯一串',
        'editable' => false,
        'width' => 105,
        'order' => 20,
    ),
  
  ),
  'index' =>
    array(
        'ind_group_id' =>
        array(
            'columns' =>
            array(
                0 => 'group_id',
            ),
        ),
         'ind_batch_id' =>
        array(
            'columns' =>
            array(
                0 => 'batch_id',
            ),
        ),
         'ind_mobile' =>
        array(
            'columns' =>
            array(
                0 => 'mobile',
            ),
        ),
         'ind_is_mobile_valid' =>
        array(
            'columns' =>
            array(
                0 => 'is_mobile_valid',
            ),
        ),
         'ind_is_email_valid' =>
        array(
            'columns' =>
            array(
                0 => 'is_email_valid',
            ),
        ),
        'ind_unique_md5' =>
        array(
            'columns' =>
            array(
                0 => 'unique_md5',
            ),
            'prefix' => 'unique',
        ),
    ),
  'comment' => '导入客户表',
  'engine' => 'innodb',
  'version' => '$Rev:  $',
);
