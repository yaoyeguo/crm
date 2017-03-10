<?php
$db['wx_chat']=array (
    'columns' =>
    array (
    'chat_id' =>
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
        'label' => '发送方账号',
        'editable' => false,
        'searchtype' => 'head',
        'filtertype' => 'normal',
        'in_list' => true,
        'default_in_list' => true,
        'width' => 85,
        'order' => 10,
    ),
      'ToUserName' =>
    array(
        'type' => 'varchar(100)',
        'required' => false,
        'label' => '开发者账号',
        'editable' => false,
        'searchtype' => 'head',
        'filtertype' => 'normal',
        'in_list' => true,
        //'default_in_list' => true,
        'width' => 85,
        'order' => 10,
    ),
     'chat_content' =>
        array (
          'type' => 'text',
          'label' => '聊天内容',
          'width' => 130,
         'in_list' => true,
        'default_in_list' => true,
        ),
     'chat_type' =>
        array (
          'type' => array(
                    'receive' => '接收的',
                    'send' => '发送的',
                ),
          'label' => '回复内容',
          'default' => 'receive',
        ),
      'chat_pid' =>
        array (
          'type' => 'int unsigned',
          'label' => '首短信id',
          'default' => 0,
        ),
       'response_type' =>
        array (
          'type' => array(
                    0 => '未回复',
                    1 => '已回复',
                ),
          'label' => '是否回复',
          'width' => 130,
          'default' => '0',
          'in_list' => true,
          'default_in_list' => true,
        ),
     'is_response' =>
        array (
          'type' => 'tinyint unsigned',
          'label' => '是否响应',
          'width' => 130,
          'default' => 1,
        ),
        'created' =>
        array (
          'type' => 'datetime',
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
    	 'ToUserName' =>
        array(
            'columns' =>
            array(
                0 => 'ToUserName',
            ),
        ),
    ),
  'comment' => '微信聊天记录表',
  'engine' => 'innodb',
  'version' => '$Rev:  $',
);
