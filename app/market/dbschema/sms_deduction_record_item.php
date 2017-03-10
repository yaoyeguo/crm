<?php 
$db['sms_deduction_record_item'] = array(
	'columns' => array(
	  'id' =>  array (
			'type' => 'number',
            'required' => true,
            'extra' => 'auto_increment',
            'pkey' => true
        ),
       'record_id' => 
	     array (
		      'type' => 'number',
		      'label' => '支付记录id',
		      'width' => 75,
		      'editable' => true,
		      'default_in_list' => true,
		      'in_list' => true,
             // 'searchtype' =>'head',
              'order' => 10,
	    ),
	 'pay_nums' => 
        array(
        	'type' => 'money',
            'default' => '0',
            'label' => '支付短信数',
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
            'label' => '操作时间',
	    	'in_list' => true,
	        'default_in_list' => true,
            'order' => 40,
		),
),
	    
    'index' =>
    array (
        
    ),
    'engine' => 'innodb',
);
