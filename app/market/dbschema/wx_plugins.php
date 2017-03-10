<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

$db['wx_plugins'] = array(
    'columns' =>
    array(
        'id' =>array(
            'type' => 'int',
            'required' => true,
            'label'=> app::get('market')->_('自增id'),
            'pkey' => true,
            'extra' => 'auto_increment',
            'width' => 10,
            'editable' => false,
            'in_list' => true,
        ),
        'plugin_name' =>array(
            'type' => 'varchar(255)',
            'label'=> app::get('market')->_('插件名称'),
            'editable' => false,
            'width' => 150,
            'searchtype' => 'has',
            'filtertype' => 'has',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => true,
            'order' => 1,
        ), 
         'img' =>array(
            'type' => 'varchar(255)',
            'label'=> app::get('market')->_('图标'),
            'editable' => false,
            'width' => 150,
            'in_list' => true,
            'default_in_list' => true,
            'order' => 1,
        ), 
        'desc' =>array(
            'type' => 'text',
            'label'=> app::get('market')->_('插件说明'),
            'width' => 300,
            'in_list' => true,
            'default_in_list' => true,
            'order' => 2,
        ),
          'keyword' =>array (
            'type' => 'varchar(255)',
            'label'=> app::get('market')->_('关键词'),
            'editable' => false,
            'width' => 150,
            'searchtype' => 'has',
            'filtertype' => 'has',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => true,
            'order' => 3,
        ),
        'status' =>
        array(
        'type' => 'tinyint unsigned',
        'default' => 0,
        'required' => false,
        'editable' => false,
        'in_list' => true,
        'default_in_list' => true,
        'label' => '插件状态',
       ),
      
  ),
  'comment' => app::get('market')->_('微信用户互动插件规则表'),
  'engine' => 'innodb',
  'version' => '$Rev$',
);
