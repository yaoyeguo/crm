<?php
 
$db['member_import_batch']=array (
    'columns' => 
    array (
    'batch_id' =>
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
        'required' => true,
        'editable' => false,
    ),
    'send_nums' => 
	     array (
		      'type' => 'int',
		      'default' => '0',
		      'label' => '发送次数',
		      'width' => 75,
		      'editable' => true,
		      'in_list' => true,
    ),
     'last_send_status' =>
    array (
      'type' =>   array (
	        'succ' => '发送成功',
	        'fail' => '发送失败',
	        'unsend' => '未发送',
    		'sending' => '发送中',
	      ),
      'default' => 'unsend',
      'required' => FALSE,
      'label' => '发送状态',
      'comment' => '短信是否发送',
      'width' => 75,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
  ),
    'total_nums' => 
        array (
            'type' => 'int',
            'default' => 0,
            'required' => false,
            'label' => '批次总人数',
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
            'width' => 80,
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
     
    ),
  'comment' => '导入客户分组批次表',
  'engine' => 'innodb',
  'version' => '$Rev:  $',
);
