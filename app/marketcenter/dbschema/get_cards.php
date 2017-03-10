<?php
$db['get_cards']=array (
    'columns' =>
    array (
      'id' =>
      array (
        'type' => 'int(8)',
        'required' => true,
        'pkey' => true,
        'label' => app::get('marketcenter')->_('序号'),
        'editable' => false,
        'extra' => 'auto_increment',
        'in_list' => false,
      ),
      'CardId' =>
      array (
          'type' => 'varchar(50)',
          'label' => app::get('marketcenter')->_('卡劵id'),
          'width' => 200,
          'order' => 10,
          'searchtype' => 'has',
          'filtertype' => 'yes',
          'editable' => false,
          'in_list' => true,
          'default_in_list' => true,
      ),
      'ToUserName' =>
      array (
          'type' => 'varchar(36)',
          'label' => app::get('marketcenter')->_('开发者微信号'),
          'width' => 80,
          'order' => 20,
          'searchtype' => 'has',
          'filtertype' => 'yes',
          'editable' => false,
          'in_list' => true,
          'default_in_list' => true,
      ),
      'FromUserName' =>
      array (
          'type' => 'varchar(36)',
          'label' => app::get('marketcenter')->_('领券方帐号'),
          'width' => 80,
          'order' => 30,
          'searchtype' => 'has',
          'filtertype' => 'yes',
          'editable' => false,
          'in_list' => true,
          'default_in_list' => true,
      ),
      'FriendUserName' =>
      array (
          'type' => 'varchar(36)',
          'label' => app::get('marketcenter')->_('赠送方账号'),
          'width' => 80,
          'order' => 40,
          'searchtype' => 'has',
          'filtertype' => 'yes',
          'editable' => false,
          'in_list' => true,
          'default_in_list' => true,
      ),
      'CreateTime' =>
      array (
          'type' => 'time',
          'label' => app::get('marketcenter')->_('消息创建时间 '),
          'width' => 80,
          'order' => 50,
          'searchtype' => 'has',
          'filtertype' => 'yes',
          'editable' => false,
          'in_list' => true,
          'default_in_list' => true,
      ),
      'IsGiveByFriend' =>
      array (
          'type' => 'bool',
          'default' => 'false',
          'label' => app::get('marketcenter')->_('是否为转赠'),
          'width' => 80,
          'order' => 60,
          'searchtype' => 'has',
          'filtertype' => 'yes',
          'editable' => false,
          'in_list' => true,
          'default_in_list' => true,
      ),
      'UserCardCode' =>
      array (
          'type' => 'varchar(36)',
          'label' => app::get('marketcenter')->_('code序列号'),
          'width' => 80,
          'order' => 80,
          'searchtype' => 'has',
          'filtertype' => 'yes',
          'editable' => false,
          'in_list' => true,
          'default_in_list' => true,
      ),
      'OldUserCardCode' =>
      array (
          'type' => 'varchar(36)',
          'label' => app::get('marketcenter')->_('转赠前的code序列号'),
          'width' => 80,
          'order' => 90,
          'searchtype' => 'has',
          'filtertype' => 'yes',
          'editable' => false,
          'in_list' => true,
          'default_in_list' => true,
      ),
      'OuterId' =>
      array (
          'type' => 'varchar(36)',
          'label' => app::get('marketcenter')->_('领取场景值'),
          'width' => 80,
          'order' => 100,
          'searchtype' => 'has',
          'filtertype' => 'yes',
          'editable' => false,
          'in_list' => true,
          'default_in_list' => true,
      ),
    ),
    'engine' => 'innodb',
    'version' => '$Rev: 42376 $',
    'comment' => app::get('marketcenter')->_('卡劵领取'),
);