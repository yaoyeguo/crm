<?php
//积分日志表
$db['points_log']=array (
    'columns' => 
    array (
        'log_id' =>
        array(
            'type' => 'int unsigned',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
            'editable' => false,
        ),
        'member_id' =>
        array (
            'type' => 'table:members@taocrm',
            'label' => '客户名',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'searchtype' => 'has',
            'filtertype' => 'normal',
            'width' => 100,
            'order' => 10,
        ),
        'points' => 
        array (
            'type' => 'int',
            'default' => 0,
            'required' => false,
            'label' => '积分',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 80,
            'order' => 20,
        ),
        'points_type' => 
        array (
            'type' => array(
                'trade' => '交易积分',
                'wechat' => '微信积分',
                'active' => '活动积分',
                'other' => '其它积分',
            ),
            'default' => 'other',
            'required' => false,
            'label' => '积分类型',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 100,
            'order' => 15,
        ),
        'order_id' =>
        array(
            'type' => 'table:orders@ecorder',
            'required' => false,
            'label' => '订单ID',
            'width' => 150,
            'order' => 30,
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
            'order' => 40,
        ),
        'shop_id' =>
        array (
            'type' => 'table:shop@ecorder',
            'label' => '来源店铺',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 150,
            'order' => 50,
        ),
        'create_time' =>
        array(
            'type' => 'time',
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'label' => '创建时间',
            'width' => 140,
            'order' => 60,
        ),
        'is_active' =>
        array(
            'type' => 'intbool',
            'default' => '1',
            'required' => false,
            'editable' => false,
            'label' => '状态',
        ),
        'remark' =>
        array(
            'type' => 'varchar(200)',
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'searchtype' => 'has',
            'filtertype' => 'normal',
            'label' => '备注',
            'order' => 150,
            'width' => 180,
        ),
        'op_user' =>
        array(
            'type' => 'varchar(32)',
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'label' => '操作人',
            'order' => 180,
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
        'ind_order_id' =>
        array(
            'columns' =>
            array(
                0 => 'order_id',
            ),
        ),
        'ind_order_bn' =>
        array(
            'columns' =>
            array(
                0 => 'order_bn',
            ),
        ),
        'ind_refund_id' =>
        array(
            'columns' =>
            array(
                0 => 'refund_id',
            ),
        ),
        'ind_shop_id' =>
        array(
            'columns' =>
            array(
                0 => 'shop_id',
            ),
        ),
        'ind_member_id' =>
        array(
            'columns' =>
            array(
                0 => 'member_id',
            ),
        ),
    ),
    'comment' => '积分日志表',
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);
