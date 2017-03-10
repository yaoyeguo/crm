<?php
// 客户所有积分日志表
$db['all_points_log'] = array(
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
        'member_id' => 
        array(
        	'type' =>'table:members@taocrm',
            'label' => '客户帐号',
            'width' => 100,
            'editable' => false,
            'searchtype' => 'has',
            'in_list' => true,
            'default_in_list' => true,
            'order' => 1,
        ),
        'shop_id' =>
        array(
            'type' => 'varchar(32)',
            'label' => '来源店铺',
            'required' => false,
            'editable' => false,
            'in_list' => false,
        	//'searchtype' => 'has',
            'default_in_list' => false,
            'width' => 120,
            'order' => 2,
        ),
        'op_time' => 
        array (
        'type' => 'time',
        'label' => '操作时间',
        'editable' => false,
        'in_list' => true,
        'default_in_list' =>false,
        'width' => 140,
        'order' => 3,
       ),
       'op_before_point' =>
        array(
            'type' => 'number',
            'default' => 0,
            'editable' => false,
            'label' => '调整前的积分',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 70,
            'order' => 4,
        ),
        'op_after_point' =>
        array(
            'type' => 'number',
            'default' => 0,
            'editable' => false,
            'label' => '调整后的积分',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 70,
            'order' => 5,
        ),
        'points' =>
        array(
            'type' => 'int',
            'default' => 0,
            'editable' => false,
            'label' => '积分',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 70,
            'order' => 6,
        ),
        'freeze_time' => 
        array (
        'default' => 0,
        'type' => 'time',
        'label' => '冻结时间',
        'editable' => false,
        'in_list' => false,
        'default_in_list' =>false,
        'width' => 140,
        'order' => 7,
       ),
        'unfreeze_time' => 
        array (
        'default' => 0,
        'type' => 'time',
        'label' => '解冻时间',
        'editable' => false,
        'in_list' => false,
        'default_in_list' =>false,
        'width' => 140,
        'order' => 8,
       ),
       'is_expired' => 
        array (
            'type' => array (
                '0' => '未过期',
                '1' => '已过期'
            ),
        'default' => '0',
        'required' => false,
        'label' => '是否过期',
        'editable' => true,
        'in_list' => true,
        'width' => 40,
        ),
        'expired_time' => 
        array (
        'default' => 0,
        'type' => 'time',
        'label' => '过期时间',
        'editable' => false,
        'in_list' => false,
        'default_in_list' =>false,
        'width' => 140,
        'order' => 9,
       ),
        'point_desc' => 
        array (
        'type' => 'text',
        'label' => '积分说明',
        'editable' => false,
        'in_list' => false,
        'default_in_list' =>false,
        'width' => 140,
        'order' => 10,
       ),
        'order_id' =>
        array(
            'type' => 'table:orders@ecorder',
            'required' => false,
            'label' => '订单ID',
            'width' => 150,
            'order' => 11,
        ),
        'order_bn' =>
        array(
            'type' => 'varchar(50)',
            'required' => false,
            'editable' => false,
            'in_list' => false,
            'label' => '订单号',
            'width' => 80,
        ),
        'refund_id' =>
        array(
            'type' => 'table:tb_refunds@ecorder',
            'required' => false,
            'label' => '退款单编号',
            'in_list' => false,
            'default_in_list' => false,
            'width' => 150,
            'order' => 12,
        ),
        'is_active' =>
        array(
            'type' => 'intbool',
            'default' => '1',
            'required' => false,
            'editable' => false,
            'label' => '状态',
        ),
        'op_user' =>
        array(
            'type' => 'varchar(32)',
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'label' => '操作人',
            'order' => 13,
            'width' => 80,
        ),
        'pid' =>
        array(
            'type' => 'number',
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => false,
            'label' => '撤销积分日志ID',
        ),
        'user_ip' =>
        array(
            'type' => 'varchar(100)',
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => false,
            'label' => 'IP地址',
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

