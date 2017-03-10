<?php
$db['buy_times']=array (
  'columns' => 
  array (
    'id' => 
    array (
      'type' => 'int unsigned',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => 'ID',
      'width' => 110,
      'editable' => false,
      'in_list' => false,
      'default_in_list' => false,
    ),
    'bn' => 
    array (
      'type' => 'varchar(50)',
      'label' => '商家编码',
      'default' => 0,
      'width' => 80,
      'editable' => false,
      'orderby' => false,
      'in_list' => true,
      'default_in_list' => true,
      'order' => 20,
    ),
    'name' => 
    array (
      'type' => 'varchar(100)',
      'label' => '商品名称',
      'default' => 0,
      'width' => 200,
      'editable' => false,
      'orderby' => false,
      'in_list' => true,
      'default_in_list' => true,
      'order' => 30,
    ),
    'one_times_person' => 
    array (
      'type' => 'number',
      'label' => '购买单次客户',
      'default' => 0,
      'width' => 110,
      'editable' => false,
      'orderby' => false,
      'in_list' => true,
      'default_in_list' => true,
      'order' => 40,
    ),
    'two_times_person' => 
    array (
      'type' => 'number',
      'label' => '购买2次客户',
      'default' => 0,
      'width' => 110,
      'editable' => false,
      'orderby' => false,
      'in_list' => true,
      'default_in_list' => true,
      'order' => 50,
    ),
    'two_times_days' => 
    array (
      'type' => 'number',
      'label' => '购买2次周期(天)',
      'default' => 0,
      'width' => 110,
      'editable' => false,
      'orderby' => false,
      'in_list' => true,
      'default_in_list' => true,
      'order' => 60,
    ),
    'thr_times_person' => 
    array (
      'type' => 'number',
      'label' => '购买3次客户',
      'default' => 0,
      'width' => 110,
      'editable' => false,
      'orderby' => false,
      'in_list' => true,
      'default_in_list' => true,
      'order' => 70,
    ),
    'thr_times_days' => 
    array (
      'type' => 'number',
      'label' => '购买3次周期(天)',
      'default' => 0,
      'width' => 110,
      'editable' => false,
      'orderby' => false,
      'in_list' => true,
      'default_in_list' => true,
      'order' => 80,
    ),
    'for_times_person' => 
    array (
      'type' => 'number',
      'label' => '购买大于4次客户',
      'default' => 0,
      'width' => 110,
      'editable' => false,
      'orderby' => false,
      'in_list' => true,
      'default_in_list' => true,
      'order' => 90,
    ),
    'for_times_days' => 
    array (
      'type' => 'number',
      'label' => '购买大于4次周期(天)',
      'default' => 0,
      'width' => 130,
      'editable' => false,
      'orderby' => false,
      'in_list' => true,
      'default_in_list' => true,
      'order' => 100,
    ),
    'update_time' => 
    array (
      'type' => 'time',
      'label' => '数据更新时间',
      'default' => 0,
      'width' => 110,
      'editable' => false,
      'in_list' => false,
      'default_in_list' => false,
    ),
  ),
  'comment' => '商品重复购买率',
  'index' => 
  array (
    'ind_update_time' => 
    array (
      'columns' => 
      array (
        0 => 'update_time',
      ),
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 1 $',
);