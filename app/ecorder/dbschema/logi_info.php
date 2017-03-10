<?php
// 订单物流数据
$db['logi_info'] = array(
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
        'rebate_cycle_id' => array(
            'type' => 'int',
            'label' => '返利周期ID',
            'default' => 0,
            'required' => false,
            'editable' => false,
        ),
        'order_id' =>
        array(
            'type' => 'table:orders@ecorder',
            'required' => false,
            'editable' => false,
            'label' => '订单ID',         
        ),
        'order_bn' =>
        array(
            'type' => 'varchar(50)',
            'required' => false,
            'editable' => false,
            'label' => '订单编号',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 150,
            'order' => 10,
        ),
        'ship_name' => 
        array(
            'type' =>'varchar(30)',
            'required' => false,
            'label' => '收货人姓名',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 80,
            'order' => 20,
        ),
        'ship_mobile' => 
        array(
            'type' =>'varchar(11)',
            'required' => false,
            'label' => '手机号码',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 100,
            'order' => 30,
        ),
        'logi_company' => 
        array(
            'type' =>'varchar(30)',
            'required' => false,
            'label' => '物流公司',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 80,
            'order' => 40,
        ),
        'logi_no' => 
        array(
            'type' =>'varchar(50)',
            'required' => false,
            'label' => '物流单号',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 80,
            'order' => 50,
        ),
        'member_id' => 
        array(
            'type' =>'table:members@taocrm',
            'required' => false,
            'label' => '客户ID',
            'editable' => false,
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
            'order' => 90,
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
            'order' => 100,
        ),
        'transit_step_info'=>
        array(
            'type' => 'text',
            'default' => '',
            'label' => '流转信息列表',
            'editable' => false,
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
        'ind_ship_mobile' =>
        array(
            'columns' =>
            array(
                0 => 'ship_mobile',
            ),
        ),
    ),
    'comment' => '订单物流数据',
    'engine' => 'innodb',
    'version' => '$Rev:  $',
); 
