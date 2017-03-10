<?php
$db['wx_join_log']=array (
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
    'wx_member_id' =>
    array(
        'type' => 'int unsigned',
        'required' => true,
        'editable' => false,
    ),
    'FromUserName' =>
    array(
        'type' => 'varchar(100)',
        'required' => false,
        'label' => '开发者账号',
        'editable' => false,
        'searchtype' => 'head',
        'filtertype' => 'normal',
        //'in_list' => true,
        //'default_in_list' => true,
        'width' => 85,
        'order' => 10,
    ),
      'ToUserName' =>
    array(
        'type' => 'varchar(100)',
        'required' => false,
        'label' => '发送者账号',
        'editable' => false,
        'searchtype' => 'head',
        'filtertype' => 'normal',
        'in_list' => true,
        'default_in_list' => true,
        'width' => 85,
        'order' => 10,
    ),
     'is_regist' => 
        array (
          'type' => 'tinyint unsigned',
          'label' => '是否签到',
          'width' => 130,
         'default' => 0,
        ),
        'is_chat' => 
        array (
          'type' => 'tinyint unsigned',
          'label' => '是否聊天',
          'width' => 130,
        'default' => 0,
        ),
        'is_keyword' => 
        array (
          'type' => 'tinyint unsigned',
          'label' => '是否关键字',
          'width' => 130,
        'default' => 0,
        ),
        'is_survey' => 
        array (
          'type' => 'tinyint unsigned',
          'label' => '是否问卷',
          'width' => 130,
        'default' => 0,
        ),
         'is_vote' => 
        array (
          'type' => 'tinyint unsigned',
          'label' => '是否投票',
          'width' => 130,
        'default' => 0,
        ),
         'is_due' => 
        array (
          'type' => 'tinyint unsigned',
          'label' => '是否预约',
          'width' => 130,
        'default' => 0,
        ),
        'created' => 
        array (
          'type' => 'time',
          'label' => '创建时间',
          'width' => 130,
          'editable' => false,
          'in_list' => true,
          'default_in_list' => false,
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
         'ind_created' =>
        array(
            'columns' =>
            array(
                0 => 'created',
            ),
        ),
    ),
  'comment' => '微信用户参与日志表',
  'engine' => 'innodb',
  'version' => '$Rev:  $',
);
