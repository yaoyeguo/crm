<?php 
$db['sms_deduction_record'] = array(
	'columns' => array(
	  'id' =>  array (
			'type' => 'number',
            'required' => true,
            'extra' => 'auto_increment',
            'pkey' => true
        ),
      'active_id' => 
        array (
         'type' =>'table:active@market',
         'editable' => false,
         'label' => '活动id',
        ),
	  'actual_pay' => 
        array(
        	'type' => 'money',
            'default' => '0',
            'label' => '实际支付',
            'width' => 70,
            'editable' => false,
            'filtertype' => 'number',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => false,
	    ),
      'plan_pay' => 
        array(
        	'type' => 'money',
            'default' => '0',
            'label' => '计划支付',
            'width' => 70,
            'editable' => false,
            'filtertype' => 'number',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => false,
	    ),
	 'create_time' => array(
			'type' => 'time',
			'required' => false,
            'label' => '创建时间',
	    	'in_list' => true,
	        'default_in_list' => true,
            'order' => 40,
		),
	 'update_time' => array(
			'type' => 'time',
			'required' => false,
            'label' => '更新时间',
	    	'in_list' => true,
	        'default_in_list' => true,
            'order' => 40,
		),
	 'status' =>
        array(
            'type' =>
            array(
                'unpay' => '未支付',
                'paysucc' => '支付成功',
                'paypart' => '部分支付',
            ),
            'default' => 'unpay',
            'required' => false,
            'label' => '支付状态',
            'width' => 70,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => false,
        ),
    
),
	    
'index' =>
  array (
    'ind_create_time' =>
    array (
        'columns' =>
        array (
          0 => 'create_time',
        ),
    ),
    'ind_status' =>
    array (
        'columns' =>
        array (
          0 => 'status',
        ),
    ),
    
    
),
    'engine' => 'innodb',
);
