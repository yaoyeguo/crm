<?php
// 客户积分日志表
$db['member_point_log'] = array(
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
        	'type' =>'int',
            'required' => false,
            'label' => '客户ID',
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
            //'searchtype' => 'has',
            'width' => 110,
            'order' => 12,
        ),
        /*'uname' =>
        array(
            'type' => 'varchar(50)',
            'required' => false,
            'label' => '客户名',
            'editable' => false,
            'searchtype' => 'head',
            'filtertype' => 'normal',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 85,
            'order' => 10,
        ),*/
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
            'order' => 22,
        ),
        'point_type' =>
        array(
            'type' => 'varchar(32)',
            'label' => '积分类型',
            'required' => false,
            'editable' => false,
            'in_list' => false,
        	//'searchtype' => 'has',
            'default_in_list' => false,
            'width' => 120,
            'order' => 22,
        ),
        'point_mode' =>
        array(
            'type' => 'varchar(1)',
            'label' => '积分方式',
            'required' => false,
            'editable' => false,
            'in_list' => false,
        	//'searchtype' => 'has',
            'default_in_list' => false,
            'width' => 120,
            'order' => 22,
        ),
        'op_time' => 
        array (
        'type' => 'time',
        'label' => '操作时间',
        'editable' => false,
        'in_list' => true,
        'default_in_list' =>false,
        'width' => 140,
        'order' => 160,
       ),
       'op_before_point' =>
        array(
            'type' => 'number',
            'default' => 0,
            'editable' => false,
            'label' => '调整前的积分',
            'in_list' => true,
            'default_in_list' => true,
//            'filtertype' => 'normal',
//            'filterdefault' => 'true',
            'width' => 70,
            'order' => 110,
        ),
        'op_after_point' =>
        array(
            'type' => 'number',
            'default' => 0,
            'editable' => false,
            'label' => '调整后的积分',
            'in_list' => true,
            'default_in_list' => true,
//            'filtertype' => 'normal',
//            'filterdefault' => 'true',
            'width' => 70,
            'order' => 110,
        ),
        'point' =>
        array(
            'type' => 'number',
            'default' => 0,
            'editable' => false,
            'label' => '积分',
            'in_list' => true,
            'default_in_list' => true,
//            'filtertype' => 'normal',
//            'filterdefault' => 'true',
            'width' => 70,
            'order' => 110,
        ),
        'freeze_time' => 
        array (
        'default' => 0,
        'type' => 'time',
        'label' => '冻结时间',
        'editable' => false,
        'in_list' => true,
        'default_in_list' =>false,
        'width' => 140,
        'order' => 160,
       ),
        'unfreeze_time' => 
        array (
        'default' => 0,
        'type' => 'time',
        'label' => '解冻时间',
        'editable' => false,
        'in_list' => true,
        'default_in_list' =>false,
        'width' => 140,
        'order' => 160,
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
        'in_list' => true,
        'default_in_list' =>false,
        'width' => 140,
        'order' => 160,
       ),
        'point_desc' => 
        array (
        'type' => 'text',
        'label' => '积分说明',
        'editable' => false,
        'in_list' => false,
        'default_in_list' =>false,
        'width' => 140,
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

