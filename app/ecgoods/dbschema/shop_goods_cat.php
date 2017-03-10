<?php
$db['shop_goods_cat']=array (
  'columns' => 
  array (
    'cat_id' => 
    array (
      'type' => 'int unsigned',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => '分类ID',
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'outer_id' => 
    array (
      'type' => 'varchar(32)',
      'required' => false,
      'label' => '外部ID',
      'hidden' => true,
      'editable' => false,
      'default' => 0,
    ),
    'outer_parent_id' => 
    array (
      'type' => 'varchar(32)',
      'required' => false,
      'label' => '外部父ID',
      'hidden' => true,
      'editable' => false,
      'default' => 0,
    ),
    'parent_id' => 
    array (
      'type' => 'int unsigned',
      'label' => '父分类ID',
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'parent_id'=>true,
    ),
    'cat_path' => 
    array (
      'type' => 'varchar(100)',
      'default' => ',',
      'label' => '分类路径(从根至本结点的路径,逗号分隔,首部有逗号)',
      'width' => 110,
      'editable' => false,
      'in_list' => true,
    ),
    'is_leaf' => 
    array (
      'type' => 'bool',
      'required' => true,
      'default' => 'false',
      'label' => '是否叶子结点（true：是；false：否）',
      'width' => 110,
      'editable' => false,
      'in_list' => true,
    ),
    'type_id' => 
    array (
      'type' => 'mediumint',
      'label' => '类型序号',
      'width' => 110,
      'editable' => false,
      'in_list' => true,
    ),
    'cat_name' => 
    array (
      'type' => 'varchar(100)',
      'required' => true,
      'is_title' => true,
      'default' => '',
      'label' => '分类名称',
      'width' => 110,
      'editable' => false,
      'in_list' => true,
    ),
    'disabled' => 
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'label' => '是否屏蔽（true：是；false：否）',
      'width' => 110,
      'editable' => false,
      'in_list' => true,
    ),
    'p_order' => 
    array (
      'type' => 'number',
      'label' => '排序',
      'width' => 110,
      'editable' => false,
      'in_list' => true,
    ),
    'goods_count' => 
    array (
      'type' => 'number',
      'label' => '商品数',
      'width' => 110,
      'editable' => false,
      'in_list' => true,
    ),
    'create_time' => 
    array (
        'type' => 'time',
        'label' => '创建时间',
        'editable' => false,
        'in_list' => true,
        'default_in_list' => false,
        'width' => 140,
        'order' => 180,
    ),
    /*'tabs' => 
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'finder' => 
    array (
      'type' => 'longtext',
      'label' => '渐进式筛选容器',
      'width' => 110,
      'editable' => false,
      'in_list' => true,
    ),
    'addon' => 
    array (
      'type' => 'longtext',
      'editable' => false,
    ),*/
    'child_count' => 
    array (
      'type' => 'number',
      'default' => 0,
      'required' => true,
      'editable' => false,
      'label' => '子分类数',
    ),
  ),
  'comment' => '类别属性值有限表',
  'index' => 
  array (
    'ind_cat_path' => 
    array (
      'columns' => 
      array (
        0 => 'cat_path',
      ),
    ),
    'ind_disabled' => 
    array (
      'columns' => 
      array (
        0 => 'disabled',
      ),
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 41329 $',
);