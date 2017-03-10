<?php 
//客户服务记录
$db['member_caselog'] = array(
	'columns' => array(
		'id' => array(
			'type' => 'int(11)',
			'required' => true,
			'pkey' => true,
			'extra' => 'auto_increment'
        ),
	   'member_id' =>
        array(
            'type' => 'table:members@taocrm',
            'label' => '客户',
            'width' => 100,
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
            'order' => 20,
        ),
        'shop_id' =>
        array(
            'type' => 'table:shop@ecorder',
            'label' => '店铺',
            'width' => 120,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'order' => 30,
        ),
        'customer' =>
        array(
            'type' => 'varchar(50)',
            'label' => '客户',
            'editable' => false,
            'searchtype' => 'has',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 100,
            'order' => 40,
        ),
        'mobile' =>
        array(
            'type' => 'char(11)',
            'label' => '手机号码',
            'editable' => false,
            'searchtype' => 'head',
            'in_list' => false,
            'default_in_list' => false,
            'width' => 150,
            'order' => 45,
        ),
        'create_time' =>
        array(
            'type' => 'time',
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => false,
            'label' => '创建时间',
            'width' => 150,
            'order' => 50,
        ),
        'modified_time' =>
        array(
            'type' => 'time',
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'label' => '修改时间',
            'width' => 150,
            'order' => 60,
        ),
        'title' =>
        array(
            'type' => 'varchar(100)',
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => false,
            'label' => '标题',
            'width' => 160,
            'order' => 30,
        ),
        'order_bn' =>
        array(
            'type' => 'table:orders@ecorder',
            'label' => '订单号',
            'width' => 180,
            'editable' => false,
            'searchtype' => 'head',
            'in_list' => true,
            'default_in_list' => false,
            'order' => 40,
        ),
        'content' =>
        array(
            'type' => 'text',
            'required' => false,
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
            'label' => '服务内容',
            'order' => 50,
        ),
        'buyer_nick' =>
        array(
            'type' => 'varchar(50)',
            'required' => false,
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
            'label' => '买家',
            'width' => 140,
            'order' => 70,
        ),
        'seller_nick' =>
        array(
            'type' => 'varchar(50)',
            'required' => false,
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
            'label' => '卖家',
            'width' => 140,
            'order' => 80,
        ),
        'service_nick' =>
        array(
            'type' => 'varchar(50)',
            'required' => false,
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
            'label' => '子帐号',
            'width' => 140,
            'order' => 90,
        ),
        'category' =>
        array(
            'type' => 'table:member_caselog_category@taocrm',
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'label' => '分类',
            'width' => 80,
            'order' => 100,
        ),
        'media' =>
        array(
            'type' => 'table:member_caselog_category@taocrm',
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'label' => '媒体',
            'width' => 80,
            'order' => 95,
        ),
        'alarm_time' =>
        array(
            'type' => 'time',
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'label' => '提醒时间',
            'width' => 150,
            'order' => 110,
        ),
        'agent' =>
        array(
            'type' => 'varchar(50)',
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'label' => '客服',
            'width' => 80,
            'order' => 150,
        ),
        'begin_time' =>
        array(
            'type' => 'time',
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => false,
            'label' => '开始时间',
            'width' => 150,
            'order' => 110,
        ),
        'end_time' =>
        array(
            'type' => 'time',
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => false,
            'label' => '结束时间',
            'width' => 150,
            'order' => 110,
        ),
        'alarm_user_id' =>
        array(
            'type' => 'int(10)',
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => false,
            'label' => '提醒对象',
            'width' => 150,
            'order' => 110,
        ),
        'status' =>
        array(
            'type' => 'table:member_caselog_category@taocrm',
            'label' => '状态',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 100,
            'order' => 120,
        ),
        'source' =>
        array(
            'type' => 'table:member_caselog_category@taocrm',
            'label' => '来源',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 100,
            'order' => 120,
        ),
        'is_finish' =>
        array(
            'type' => 'tinyint',
            'label' => '是否结束',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => false,
            'order' => 180,
        ),
    ),
    'index' =>
    array(
        'ind_order_bn' =>
        array(
            'columns' =>
            array(
                0 => 'order_bn',
            ),
        ),
        'ind_mobile' =>
        array(
            'columns' =>
            array(
                0 => 'mobile',
            ),
        ),
        'ind_alarm_time' =>
        array(
            'columns' =>
            array(
                0 => 'alarm_time',
            ),
        ),
    ),
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);
