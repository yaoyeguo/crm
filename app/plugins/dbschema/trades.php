<?php
// 插件用订单数据
$db['trades'] = array(
    'columns' =>
    array(
        'id' =>
        array(
            'type' => 'int',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
            'editable' => false, 
        ),
        'tid' =>
        array(
            'type' => 'varchar(50)',
            'required' => false,
            'editable' => false,
            'label' => '订单编号',            
        ),
        'ship_mobile' => 
        array(
        	'type' =>'varchar(50)',
            'required' => false,
            'label' => '手机号码',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 150,
            'order' => 10,
        ),
        'buyer_nick' => 
        array(
        	'type' =>'varchar(50)',
            'required' => false,
            'label' => '买家帐号',
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
            'width' => 80,
            'order' => 120,
        ),
        'ship_name' => 
        array(
        	'type' =>'varchar(50)',
            'required' => false,
            'label' => '收货人姓名',
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
            'width' => 80,
            'order' => 120,
        ),
        'logi_company' => 
        array(
        	'type' =>'varchar(50)',
            'required' => false,
            'label' => '物流公司',
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
            'width' => 80,
            'order' => 120,
        ),
        'logi_no' => 
        array(
        	'type' =>'varchar(50)',
            'required' => false,
            'label' => '物流单号',
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
            'width' => 80,
            'order' => 120,
        ),
        'shop_name' => 
        array(
        	'type' =>'varchar(50)',
            'required' => false,
            'label' => '店铺名称',
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
            'width' => 80,
            'order' => 120,
        ),
        'shop_id' => 
        array(
        	'type' =>'varchar(50)',
            'required' => false,
            'label' => '店铺ID',
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
            'width' => 80,
            'order' => 120,
        ),
        'member_id' => 
        array(
        	'type' =>'int',
            'required' => false,
            'label' => '客户ID',
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
            'width' => 110,
            'order' => 20,
        ),
        'order_status' =>
	    array(
	        'label' => '订单状态',
	        'type' => 'varchar(50)',
	        'editable' => false,
	        'filtertype' => 'yes',
	        'filterdefault' => 'true',
	        'in_list' => false,
	        'default_in_list' => false,
	        'width' => 110,
	        'order' => 40,
	    ),
        'ship_status' =>
	    array(
	        'label' => '物流状态',
	        'type' => 'varchar(50)',
	        'editable' => false,
	        'filtertype' => 'yes',
	        'filterdefault' => 'true',
	        'in_list' => false,
	        'default_in_list' => false,
	        'width' => 110,
	        'order' => 40,
	    ),
        'sms_status' =>
        array(
            'type' => 'int',
            'label' => '短信发送状态(0或1)',
            'default' => 0,
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
            'width' => 110,
	        'order' => 50,
        ), 
        'create_time' =>
        array(
            'type' => 'time',
            'default' => 0,
            'label' => '创建时间',
            'editable' => false,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 160,
            'order' => 80,
        ),
        'update_time' =>
        array(
            'type' => 'time',
            'default' => 0,
            'label' => '更新时间',
            'editable' => false,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 160,
            'order' => 80,
        ),
        'sms_time' =>
        array(
            'type' => 'time',
            'default' => 0,
            'label' => '短信发送时间',
            'editable' => false,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 160,
            'order' => 85,
        ),
        'delivery_time' =>
        array(
            'type' => 'time',
            'default' => 0,
            'label' => '发货时间',
            'editable' => false,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 160,
            'order' => 88,
        ),
        'logi_status' =>
        array(
            'type' => array(
                '0'=>'未送达',
                '1'=>'到达收货城市',
                '2'=>'已签收',
                '9'=>'获取数据失败',
            ),
            'default' => '0',
            'label' => '物流信息是否完整',
            'editable' => false,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 160,
            'order' => 90,
        ),
        'province' =>
        array(
            'type' => 'varchar(50)',
            'default' => 0,
            'label' => '省份',
            'editable' => false,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 160,
            'order' => 90,
        ),
        'city' =>
        array(
            'type' => 'varchar(50)',
            'default' => 0,
            'label' => '城市',
            'editable' => false,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 160,
            'order' => 90,
        ),
        'plugin_id' =>
        array(
            'type' => 'varchar(50)',
            'default' => 0,
            'label' => '插件ID',
            'editable' => false,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 160,
            'order' => 100,
        ),
        'seller_nick' =>
        array(
            'type' => 'varchar(50)',
            'default' => 0,
            'label' => '卖家昵称',
            'editable' => false,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'in_list' => false,
            'default_in_list' => false,
            'width' => 160,
            'order' => 100,
        ),
        'transit_step_info'=>
        array(
            'type' => 'text',
            'default' => '',
            'label' => '流转信息列表',
            'editable' => false,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'in_list' => false,
            'default_in_list' => false,
        ),
        'sign_time' =>
        array(
            'type' => 'time',
            'default' => 0,
            'label' => '签收时间',
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
        ),
    ),
    'index' =>
    array(
        'ind_tid' =>
        array(
            'columns' =>
            array(
                0 => 'tid',
            ),
        ),
        'ind_plugin_id' =>
        array(
            'columns' =>
            array(
                0 => 'plugin_id',
            ),
        ),
        'ind_mobile' =>
        array(
            'columns' =>
            array(
                0 => 'ship_mobile',
            ),
        ),
        'ind_delivery_time' =>
        array(
            'columns' =>
            array(
                0 => 'delivery_time',
            ),
        ),
        'ind_logi_status' =>
        array(
            'columns' =>
            array(
                0 => 'logi_status',
            ),
        ),
    ),
    'engine' => 'innodb',
    'version' => '$Rev:  $',
); 
