<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

$db['wx_keywords'] = array(
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
          'keyword' =>array (
            'type' => 'varchar(255)',
            'label'=> app::get('market')->_('关键字'),
            'editable' => false,
            'width' => 150,
            'searchtype' => 'has',
            'filtertype' => 'has',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => true,
            'order' => 3,
        ),
         'source' =>
        array(
         'type' => array(
                'products'=>'商品推荐',
                'reply'=>'关键词回复',
        		'system'=>'系统',
                'plugin'=>'插件',
         		'survey'=>'问答活动',
                'regist'=>'签到',
         		'vote'=>'投票',
         		'due'=>'预约',
                'unknow'=>'未知',
          ),
        'default' => 'unknow',
        'required' => false,
        'editable' => false,
        'in_list' => true,
        'default_in_list' => true,
        'label' => '关键字来源',
       ),
  ),
    'index' =>
    array(
     	'ind_keyword' =>
        array(
            'columns' =>
            array(
                0 => 'keyword',
            ),
            'prefix' => 'UNIQUE',
        ),
     ),
  'comment' => app::get('market')->_('微信用户关键字总表'),
  'engine' => 'innodb',
  'version' => '$Rev$',
);
