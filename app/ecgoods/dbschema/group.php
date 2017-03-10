<?php
$db['group']=array (
  'columns' => 
  array (
    'group_id' => 
    array (
      'type' => 'int unsigned',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => 'ID',
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => false,
    ),
    'group_name' => 
    array (
      'type' => 'varchar(100)',
      'required' => true,
      'is_title' => true,
      'default' => '',
      'label' => '分组名称',
      'width' => 180,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => false,
      'order'=>20
    ),
    'parent_id' => 
    array (
      'type' => 'int unsigned',
      'label' => '父分组ID',
      'width' => 110,
      'editable' => false,
      'in_list' => false,
      'parent_id'=>true,
    ),
    'cat_path' => 
    array (
      'type' => 'varchar(100)',
      'default' => ',',
      'label' => '分类路径(从根至本结点的ID路径,逗号分隔,首部有逗号)',
      'width' => 110,
      'editable' => false,
      'in_list' => false,
    ),
    'is_leaf' => 
    array (
      'type' => 'bool',
      'required' => false,
      'default' => 'false',
      'label' => '是否叶子结点（true：是；false：否）',
      'width' => 110,
      'editable' => false,
      'in_list' => false,
    ),
    'disabled' => 
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => false,
      'label' => '是否屏蔽（true：是；false：否）',
      'width' => 110,
      'editable' => false,
      'in_list' => false,
    ),
    'p_order' => 
    array (
      'type' => 'number',
      'label' => '排序',
      'width' => 110,
      'editable' => false,
      'in_list' => false,
    ),
    'goods_count' => 
    array (
      'type' => 'number',
      'label' => '包含商品数',
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'orderby' => false,
      'order'=>60
    ),
    'goods_id' => 
    array (
      'type' => 'varchar(5000)',
      'label' => '商品ID',
      'width' => 110,
      'editable' => false,
      'in_list' => false,
      'default_in_list' => false,
      'order'=>60
    ),
    'create_time' => 
    array (
        'type' => 'datetime',
        'label' => '创建时间',
        'editable' => false,
        'in_list' => true,
        'default_in_list' => false,
        'width' => 140,
        'order' => 180,
    ),
    'child_count' => 
    array (
      'type' => 'number',
      'default' => 0,
      'required' => false,
      'editable' => false,
    ),
    'avg_price' => 
    array (
      'type' => 'money',
      'label' => '平均价格',
      'default' => 0,
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'orderby' => false,
      'order'=>70
    ),
    'total_num' => 
    array (
      'type' => 'number',
      'label' => '销售数量',
      'default' => 0,
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'orderby' => false,
      'order'=>80
    ),
    'total_amount' => 
    array (
      'type' => 'money',
      'label' => '销售金额',
      'default' => 0,
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'orderby' => false,
      'order'=>90
    ),
    'buy_person' => 
    array (
      'type' => 'number',
      'label' => '购买客户数',
      'default' => 0,
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'orderby' => false,
      'order'=>100
    ),
    'update_time' => 
    array (
        'type' => 'time',
        'label' => '数据更新时间',
        'editable' => false,
        'in_list' => true,
        'default_in_list' => false,
        'width' => 140,
        'order' => 150,
    ),
  ),
  'comment' => '商品分组',
  'index' => 
  array (
    'ind_group_name' => 
    array (
      'columns' => 
      array (
        0 => 'group_name',
      ),
    ), 
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 1 $',
);