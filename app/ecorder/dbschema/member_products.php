<?php
$db['member_products']=array (  
  'columns' => 
  array (
    'mp_id' =>
        array(
            'type' => 'int(11)',
            'required' => true,
            'pkey' => true,
            'editable' => false,
            'label' => 'ID',
            'extra' => 'auto_increment',
    ),
   	'member_id' =>
        array(
            'type' => 'int unsigned',
            'label' => '客户用户名',
            'width' => 75,
            'editable' => false,
            'filtertype' => 'normal',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => false,
    ),
   	'product_id' =>
        array(
            'type' => 'number',
            'required' => false,
            'default' => 0,
            'editable' => false,
    ),
    'goods_id' =>
        array(
           'type' => 'number',
            'required' => false,
            'default' => 0,
            'editable' => false,
    ),
   'name' =>
        array(
            'type' => 'varchar(64)',
            'label' => '商品名称',
            'width' => 75,
            'editable' => false,
            'filtertype' => 'normal',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => true,
            'searchtype' => 'has',
        ),
    'last_time' =>
	    array (
	      'type' => 'time',
	      'label' => '最后购买时间',
	      'width' => 130,
	      'editable' => false,
	      //'filtertype' => 'time',
	      //'filterdefault' => true,
	    ),
	'buy_times' =>
	    array (
	      'type' => 'number',
	      'required' => false,
	      'default' => 0,
	      'width' => 60,
	      'edtiable' => false,
	      'in_list' => true,
	      'label' => '购买次数',
	      'default_in_list' => true,
	    ),
    'buy_num' =>
	    array (
	      'type' => 'number',
	      'required' => false,
	      'default' => 0,
	      'width' => 60,
	      'edtiable' => false,
	      'in_list' => true,
	      'label' => '购买数量',
	      'default_in_list' => true,
	    ),
  ), 
  'index' =>
    array (
        'ind_member_goods' =>
        array (
            'columns' =>
            array (
             0 => 'member_id',
             1 => 'goods_id',
            ),
        ),
    ),
  'engine' => 'innodb',
  'version' => '$Rev:  $',
);