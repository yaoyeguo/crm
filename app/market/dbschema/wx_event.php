<?php
 
$db['wx_event']=array (
    'columns' => 
    array (
    'wx_event_id' =>
    array(
        'type' => 'int unsigned',
        'required' => true,
        'pkey' => true,
        'extra' => 'auto_increment',
        'editable' => false,
    ),
    'ToUserName' =>
    array(
        'type' => 'varchar(100)',
        'required' => false,
        'label' => '开发者微信号',
        'editable' => false,
        'searchtype' => 'head',
        'filtertype' => 'normal',
        //'in_list' => true,
        //'default_in_list' => true,
        'width' => 85,
        'order' => 10,
    ),
    'FromUserName' =>
    array(
        'type' => 'varchar(100)',
        'required' => false,
        'label' => '发送方账号',
        'editable' => false,
        'searchtype' => 'head',
        'filtertype' => 'normal',
        'in_list' => true,
        'default_in_list' => true,
        'width' => 120,
        'order' => 10,
    ),
    'event_type' =>
    array(
        'type' => 'varchar(50)',
        'required' => false,
        'label' => '事件类型',
        'editable' => false,
        'searchtype' => 'head',
        'filtertype' => 'normal',
        'in_list' => true,
        'default_in_list' => true,
        'width' => 85,
        'order' => 10,
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
        ),
  ),
  'index' =>
    array(
    	 'ind_ToUserName' =>
        array(
            'columns' =>
            array(
                0 => 'ToUserName',
            ),
        ),
    ),
  'comment' => '微信客户事件表',
  'engine' => 'innodb',
  'version' => '$Rev:  $',
);
