<?php
 
$db['wx_news_img']=array (
    'columns' => 
    array (
        'wx_news_img_id' =>
        array(
            'type' => 'int unsigned',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
            'editable' => false,
        ),
        'picurl' =>
        array(
            'type' => 'varchar(100)',
            'required' => false,
            'label' => '封面',
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
    ),
    'index' =>
    array(
      
    ),
    'comment' => '图文图片',
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);
