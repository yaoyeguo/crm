<?php

// 客户权益流水表
$db['member_benefits_log'] = array(
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
        'member_benefits_id' => array (
            'type' => 'number',
            'required' => true,
        ),
        'member_id' => 
        array(
        	'type' =>'int',
            'required' => false,
            'label' => '客户ID',
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
            //'searchtype' => 'has',
            'width' => 110,
        ),
        'benefits_type' =>
        array(
           'type' =>   array (
	        0 => '金额',
	        1 => '计次',
	        2 => '折扣',
	        3 => '其它'
	         ),
            'label' => '权益类型',
            'required' => false,
            'editable' => false,
            'in_list' => false,
        	//'searchtype' => 'has',
            'default_in_list' => false,
            'width' => 120,
            'order' => 10,
        ),
         'get_benefits_mode' =>
        array(
           'type' =>   array (
	        0 => '收费',
	        1 => '赠送',
	        2 => '其它'
	         ),
            'label' => '获取权益方式',
            'required' => false,
            'editable' => false,
            'in_list' => false,
        	//'searchtype' => 'has',
            'default_in_list' => false,
            'width' => 120,
            'order' => 20,
        ),
         'op_mode' =>
        array(
           'type' =>   array (
	        0 => '新增',
	        1 => '扣减(使用)',
	         ),
            'label' => '操作方式',
            'required' => false,
            'editable' => false,
            'in_list' => false,
        	//'searchtype' => 'has',
            'default_in_list' => false,
            'width' => 120,
            'order' => 30,
        ),
        'get_benefits_desc' =>
        array(
            'type' => 'varchar(255)',
            'label' => '获取权益说明',
            'required' => false,
            'editable' => false,
            'width' => 120,
            'order' => 40,
        ),
       'benefits_code' => array (
            'type' => 'varchar(100)',
            'in_list'=>true,
            'is_title'=>true,
            'default_in_list'=>true,
            'label'=> '权益项代码',
            'required' => false,
            'order'=> 50,
        ),
       'benefits_name' => array (
            'type' => 'varchar(100)',
            'in_list'=>true,
            'is_title'=>true,
            'default_in_list'=>true,
            'label'=> '权益项名称',
            'required' => false,
            'order'=> 60,
        ),
         'nums' =>
        array(
            'type' => 'varchar(100)',
            'required' => true,
            'editable' => false,
            'in_list' => true,
            'default_in_list'=>true,
            'label' => '值',
            'width' => 70,
        ),
       'op_before_nums' =>
        array(
            'type' => 'varchar(100)',
            'editable' => false,
            'label' => '调整前的值',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 70,
            'order' => 80,
        ),
        'op_after_nums' =>
        array(
            'type' => 'varchar(100)',
            'editable' => false,
            'label' => '调整后的值',
            'in_list' => true,
            'default_in_list' => true,
//            'filtertype' => 'normal',
//            'filterdefault' => 'true',
            'width' => 70,
            'order' => 90,
        ),
         'effectie_time' => 
        array (
        'type' => 'time',
        'label' => '生效时间',
        'editable' => false,
        'in_list' => true,
        'width' => 140,
        'order' => 150,
       ),
        'failure_time' => 
        array (
        'type' => 'time',
        'label' => '失效时间',
        'editable' => false,
        'in_list' => true,
        'width' => 140,
        'order' => 150,
       ),
          'is_enable' =>array (
      		'type' =>   array (
	        0 => '不可用',
	        1 => '可用',
	         ),
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'label' => '状态',
            'width' => 140,
            'order' => 100,
      ),
        'source_order_bn' =>
        array(
            'type' => 'varchar(100)',
            'editable' => false,
            'label' => '来源关联单号',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 70,
            'order' => 110,
        ),
         'source_business_code' =>
        array(
            'type' => 'varchar(100)',
            'editable' => false,
            'label' => '来源业务Code',
            'in_list' => true,
            'width' => 70,
            'order' => 120,
        ),
        'source_business_name' =>
        array(
            'type' => 'varchar(100)',
            'editable' => false,
            'label' => '来源业务名称',
            'in_list' => true,
            'width' => 70,
            'order' => 130,
        ),
         'source_store_name' =>
        array(
            'type' => 'varchar(100)',
            'editable' => false,
            'label' => '来源门店代码',
            'in_list' => true,
            'width' => 70,
            'order' => 130,
        ),
         'source_terminal_code' =>
        array(
            'type' => 'varchar(100)',
            'editable' => false,
            'label' => '来源终端代码',
            'in_list' => true,
            'width' => 70,
            'order' => 130,
        ),
        'memo' => 
        array (
        'type' => 'text',
        'label' => '说明备注',
        'editable' => false,
        'in_list' => false,
        'width' => 140,
        'order' => 140,
       ),
        'create_time' => 
        array (
        'type' => 'time',
        'label' => '系统创建时间',
        'editable' => false,
        'in_list' => true,
        'width' => 140,
        'order' => 150,
       ),
       'create_op_time' => 
        array (
        'type' => 'time',
        'label' => '创建人时间',
        'editable' => false,
        'in_list' => true,
        'width' => 140,
        'order' => 150,
       ),
        'create_op_name' =>
        array(
            'type' => 'varchar(100)',
            'editable' => false,
            'label' => '创建人',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 70,
            'order' => 160,
        ),
    ),
    'index' =>
    array(
     	  'ind_member_id' =>
        array(
            'columns' =>
            array(
                0 => 'member_id',
            ),
        ),
    ),
    'engine' => 'innodb',
    'version' => '$Rev:  $',
); 

