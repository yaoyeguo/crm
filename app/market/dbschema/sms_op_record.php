<?php 
$db['sms_op_record'] = array(
	'columns' => array(
	  'id' =>  array (
			'type' => 'number',
            'required' => true,
            'extra' => 'auto_increment',
            'pkey' => true
        ),
       'action' => 
	     array (
		      'type' => 
		      array (
		        'freeze' => '冻结',
		        'unfreeze' =>'解冻',
		      	'deduct'=>'扣除',
		      ),
		      'label' => '操作类型',
		      'width' => 75,
		      'editable' => true,
		      'default_in_list' => true,
		      'in_list' => true,
             // 'searchtype' =>'head',
              'order' => 10,
	    ),
	'msgid' => 
        array(
        	'type' =>'varchar(100)',
            'required' => false,
            'label' => '客户ID',
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
            'width' => 20,
        	'order' =>2,
	    ),
    'nums' => 
	    array (
	      'type' => 'number',
	      'label' => '短信条数',
	      'width' =>60,
	      'editable' => false,
	      'in_list' => true,
	      'default_in_list' => true,
	       'filterdefault' => true,
          'order' => 30,
	    ),
	 'create_time' => array(
			'type' => 'time',
			'required' => false,
            'label' => '操作时间',
	    	'in_list' => true,
	        'default_in_list' => true,
            'order' => 40,
		),
	 'status' =>
        array(
            'type' =>
            array(
                'deductsucc' => '扣除成功',
                'deductfail' => '扣除失败',
                'freezesucc' => '冻结成功',
                'freezefail' => '冻结失败',
            ),
            'required' => false,
            'label' => '操作状态',
            'width' => 70,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => false,
        ),
     'remark' =>
	    array (
	        'type' =>'varchar(50)',
	        'editable' => false,
	    	'in_list' => true,
	      	'default_in_list' => true,
            'filterdefault' => true,
	    	'filtertype' => 'normal',
	    	'searchtype' => 'head',
	        'label' => '备注信息',
            'order' =>50,
	    ),
	    'reason' => array(
			'type' => 'text',
			'label' => '失败原因'
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
        
        'ind_remark' =>
        array (
            'columns' =>
            array (
              0 => 'remark',
            ),
        ),
        
        'ind_action' =>
        array (
            'columns' =>
            array (
              0 => 'action',
            ),
        ),
    ),
    'engine' => 'innodb',
);

