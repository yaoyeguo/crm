<?php 
$db['fx_sms_log'] = array(
	'columns' => array(
		'sms_log_id' => array(
			'type' => 'int(11)',
			'required' => true,
			'pkey' => true,
			'extra' => 'auto_increment'
			),
		'sms_id' => array(
			'type' => 'int(11)',
			'required' => true,
			),	
		'page' => array(
			'type' => 'int(11)',
			'required' => true,
			),	
	    'is_send' => 
	     array (
		      'type' => 'tinyint unsigned',
		      'default' => '0',
		      'label' => '是否发送(0:未发送 1:发送成功 2:发送失败)',
		      'width' => 75,
		      'editable' => true,
		      'in_list' => true,
         ),
         'reason' => array(
			'type' => 'text',
			'label' => '失败原因'
		),
		 'send_time' =>
        array(
            'type' => 'time',
            'default' => 0,
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'label' => '发送时间',
            'width' => 140,
            'order' => 60,
        ),
			),
	'index' =>
	  array(
         	   'ind_sms_id' =>
                array(
                    'columns' =>
                    array(
                        0 => 'sms_id',
                    ),
                ),
                
         	   'ind_is_send' =>
                array(
                    'columns' =>
                    array(
                        0 => 'is_send',
                    ),
			),
		'ind_sms_page' =>
            array(
            'columns' =>
            array(
                0 => 'sms_id',
                1 => 'page',
            ),
            'prefix' => 'unique',
        ),
	),
    'engine' => 'innodb',
);
