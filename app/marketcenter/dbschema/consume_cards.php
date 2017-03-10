<?php
$db['consume_cards']=array (
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
      'UserCardCode' =>
      array (
          'type' => 'varchar(36)',
          'label' => app::get('marketcenter')->_('code序列号'),
          'width' => 100,
          'order' => 20,
          'searchtype' => 'has',
          'filtertype' => 'yes',
          'editable' => false,
          'in_list' => true,
          'default_in_list' => true,
      ),
    ),
    'engine' => 'innodb',
    'version' => '$Rev: 42376 $',
    'comment' => app::get('marketcenter')->_('卡劵核销'),
);