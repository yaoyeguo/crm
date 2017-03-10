<?php
//短信签名
$db['sms_sign']=array (
  'columns' => 
  array (
    'sign_id' =>
    array (
        'type' => 'int unsigned',
        'required' => true,
        'pkey' => true,
        'editable' => false,
        'extra' => 'auto_increment',
    ),
    'sms_sign' => 
    array (
        'type' => 'varchar(30)',
        'required' => false,
        'label' => '短信签名',
        'in_list' => true,
        'default_in_list' => true,
        'width' => 120,
        'order' => 10,
    ),
    'extend_no' =>
    array (
        'type' => 'varchar(32)',
        'required' => false,
        'label' => '签名编号',
        'in_list' => true,
        'default_in_list' => true,
        'width' => 120,
        'order' => 20,
    ),
    'review' =>
    array (
        'type' => 'varchar(32)',
        'required' => false,
        'label' => '是否审核',
        'in_list' => false,
        'default_in_list' => false,
        'width' => 80,
        'order' => 30,
    ),
      'is_code_sign' =>
      array (
          'type' => 'bool',
          'required' => false,
          'label' => '是否验证码签名',
          'default'=>'false',
          'in_list' => true,
          'default_in_list' => true,
          'width' => 100,
          'order' => 40,
      ),
    'shop_ids' =>
    array (
        'type' => 'varchar(1000)',
        'required' => false,
        'label' => '店铺ID',
        'in_list' => false,
        'default_in_list' => false,
        'width' => 80,
        'order' => 50,
    ),
    'create_time' =>
    array (
        'type' => 'time',
        'editable' => false,
        'label' => '创建时间',
        'in_list' => false,
        'default_in_list' => false,
        'width' => 130,
        'order' => 60,
    ),
    'modified_time' =>
    array (
        'type' => 'time',
        'editable' => false,
        'label' => '更新时间',
        'in_list' => true,
        'default_in_list' => true,
        'width' => 130,
        'order' => 100,
    )
  ),
  'index' =>
  array (
    'ind_sms_sign' =>
    array (
        'columns' =>
        array (
            0 => 'sms_sign',
        ),
    ),
    'ind_extend_no' =>
    array (
        'columns' =>
        array (
            0 => 'extend_no',
        ),
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev:  $',
);