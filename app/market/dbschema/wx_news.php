<?php

$db['wx_news']=array (
    'columns' => 
    array (
        'wx_news_id' =>
        array(
            'type' => 'int unsigned',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
            'editable' => false,
        ),
         'news_info' =>
        array(
            'type' => 'longtext',
            'label' => '图文信息',
            'order' => 10,
        ),
        'title' =>
        array(
            'type' => 'varchar(100)',
            'required' => false,
            'label' => '标题',
            'editable' => false,
            'searchtype' => 'head',
            'filtertype' => 'normal',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 240,
            'order' => 10,
        ),
        'picurl' =>
        array(
            'type' => 'varchar(100)',
            'required' => false,
            'label' => '封面',
            'editable' => false,
            'searchtype' => 'head',
            'filtertype' => 'normal',
            'in_list' => false,
            'default_in_list' => false,
            'width' => 185,
            'order' => 20,
        ),
        'digest' =>
        array(
            'type' => 'varchar(500)',
            'required' => false,
            'label' => '摘要',
            'editable' => false,
            'searchtype' => 'head',
            'filtertype' => 'normal',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 185,
            'order' => 20,
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
        'modified' => 
        array (
          'type' => 'datetime',
          'label' => '更新时间',
          'width' => 130,
          'editable' => false,
          'in_list' => true,
          'default_in_list' => true,
          'filtertype' => 'normal',
          'filterdefault' =>true,
          'order' => 110,
        ),
        'type' => array(
			'type' => array('1' => '单图文', '2' => '多图文'),
			'required' => false,
			'label' => '图文类型',
            'in_list' => true,
            'default_in_list' => true,
		),
    ),
    'index' =>
    array(
      
    ),
    'comment' => '图文素材',
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);
