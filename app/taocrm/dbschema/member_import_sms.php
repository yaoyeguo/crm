<?php 

$db['member_import_sms'] = array(
	'columns' => array(
		'sms_id' => array(
			'type' => 'int(11)',
			'required' => true,
			'pkey' => true,
			'extra' => 'auto_increment'
			),
       'group_id' => array(
        'type' => 'int unsigned',
		'label' => '分组名称',
        'required' => true,
        'editable' => false,
			),
     'batch_id' =>
			array(
        'type' => 'int unsigned',
        'required' => true,
        'editable' => false,
			),
		 'total_num' => 
			array (
      'type' => 'int',
      'default' => 0,
      'label' => '发送总数',
      'width' => 130,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
			),
	 'succ_num' => 
			array (
      'type' => 'int',
      'default' => 0,
      'label' => '成功总数',
      'width' => 130,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
			),
     'fail_num' => 
			array (
      'type' => 'int',
      'default' => 0,
      'label' => '失败总数',
      'width' => 130,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => false,
			),
        'last_send_time' => 
        array(
			'type' => 'time',
			'default' => 0,
			'required' => false,
            'label' => '最后发送时间',
			'width' => 130,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
			),
		'template' =>
        array(
          'type' => 'text',
          'label' => '模板内容',
          'editable' => false,
        ),
        'send_status' =>
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
    ),
	'index' =>
	array(
	),
    'comment' => '外部客户短信发送记录',
    'engine' => 'innodb',
);
