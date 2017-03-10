<?php
 
$db['member_import_group']=array (
    'columns' => 
    array (
    'group_id' =>
    array(
        'type' => 'int unsigned',
        'required' => true,
        'pkey' => true,
        'extra' => 'auto_increment',
        'editable' => false,
    ),
    'group_name' =>
    array(
        'type' => 'varchar(50)',
        'required' => false,
        'label' => '分组名称',
        'editable' => false,
        'searchtype' => 'head',
        'filtertype' => 'normal',
        'in_list' => true,
        'default_in_list' => true,
        'width' => 150,
        'order' => 10,
    ),
      'total_nums' => 
        array (
            'type' => 'int',
            'default' => 0,
            'required' => false,
            'label' => '分组人数',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 80,
            'order' => 40,
        ),
         'mobile_valid_nums' => 
        array (
            'type' => 'int',
            'default' => 0,
            'required' => false,
            'label' => '手机有效人数',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 100,
            'order' => 40,
        ),
        'email_valid_nums' => 
        array (
            'type' => 'int',
            'default' => 0,
            'required' => false,
            'label' => '邮箱有效人数',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 100,
            'order' => 40,
        ),
         'last_import_time' =>
        array(
            'type' => 'time',
            'default' => 0,
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'label' => '最后导入时间',
            'width' => 140,
            'order' => 50,
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
     
    ),
  'comment' => '导入客户分组表',
  'engine' => 'innodb',
  'version' => '$Rev:  $',
);
