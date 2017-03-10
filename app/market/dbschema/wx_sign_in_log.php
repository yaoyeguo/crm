<?php 
$db['wx_sign_in_log']=array (
    'columns' => 
    array (
    'id' =>
    array(
        'type' => 'int unsigned',
        'required' => true,
        'pkey' => true,
        'extra' => 'auto_increment',
        'editable' => false,
    ),
     'FromUserName' =>
    array(
        'type' => 'varchar(100)',
        'required' => false,
        'label' => '客户微信账号',
        'editable' => false,
        'searchtype' => 'head',
        'filtertype' => 'normal',
        'in_list' => true,
        'default_in_list' => true,
        'width' => 120,
        'order' => 10,
    ),
    'member_id' =>
        array(
            'type' => 'table:members@taocrm',
            'required' => false,
            'label' => '关联帐号',
            'editable' => false,
            'comment' => '',
        ),
    'sign_in_times' =>
    array (
        'type' => 'int(10)',
        'default' => 0,
        'required' => false,
        'label' => '连续签到次数',
        'editable' => false,
        'width' => 80,
        'order' => 20,
        'in_list' => true,
        'default_in_list' => true,
    ),
      'create_time' => 
        array (
          'type' => 'time',
          'label' => '创建时间',
          'width' => 130,
          'editable' => false,
          'in_list' => true,
          'default_in_list' => true,
          'filtertype' => 'normal',
          'filterdefault' =>true,
          'order' => 30,
        ),
  ),
  'index' =>
    array(
        'ind_wx_id' =>
        array(
            'columns' =>
            array(
                0 => 'id',
            ),
        ),
    ),
  'comment' => '微信客户签到日志表',
  'engine' => 'innodb',
  'version' => '$Rev:  $',
);
