<?php
$db['wangwang_shop']=array (
  'columns' => 
  array (
    'id' => array(
       'type' => 'int unsigned',
       'required' => true,
       'pkey' => true,
       'extra' => 'auto_increment',
       'editable' => false,
       'label' => '序号',
    ),
    'shop_id' => 
    array (
      'type' => 'varchar(32)',
      'required' => true,
      'editable' => false,
      'label' => '店铺ID',
    ),
    'seller_nick' =>
    array (
      'type' => 'varchar(65)',
      'label' => '主账号昵称',
      'in_list' => true,
      'default_in_list' => true,
    ),
    'nick' => array(
      'type' => 'varchar(65)',
      'label' => '子帐户用户名',
      'in_list' => true,
      'default_in_list' => true,
    ),
    'seller_id' => array(
      'type' => 'int',
      'label' => '子账号所属的主账号的唯一标识',
    ),
    'status' => array(
      'type' => array('1' => '正常', '-1' => '删除', '2' => '冻结'),
      'label' => '子旺旺状态',
    ),
    'sub_id' => array(
        'type' => 'int',
        'label' => '子帐号ID',
    ),
    'is_online' => array(
        'type' => array('1' =>  '不参与 ', '2' => '参与'),
        'label' => '是否参与分流',
    ),
  ),
  'index' =>
  array (
    'ind_shop_id' =>
    array (
        'columns' =>
        array (
          0 => 'shop_id',
        ),
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev:  $',
);