<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

$db['wx_keywords_autoreply'] = array(
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
        'rulename' =>array(
            'type' => 'varchar(255)',
            'label'=> app::get('market')->_('规则名'),
            'editable' => false,
            'width' => 150,
            'searchtype' => 'has',
            'filtertype' => 'has',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => true,
            'order' => 1,
        ), 
        'replycontent' =>array(
            'type' => 'text',
            'label'=> app::get('market')->_('微信回复内容'),
            'width' => 300,
            'order' => 2,
        ),
          'keyword' =>array (
            'type' => 'text',
            'label'=> app::get('market')->_('回复关键词'),
            'editable' => false,
            'width' => 150,
            'searchtype' => 'has',
            'filtertype' => 'has',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => true,
            'order' => 3,
        ),
          'reply_type' => array(
			'type' => array('msg' => '一般消息', 'news' => '图文素材'),
			'required' => false,
			'label' => '回复方式',
            'in_list' => true,
            'default_in_list' => true,
            'default' => 'msg',
		),
		 'wx_news_id' =>
        array(
            'type' => 'int unsigned',
            'editable' => false,
        ),
      
  ),
  'comment' => app::get('market')->_('微信用户关键字回复规则表'),
  'engine' => 'innodb',
  'version' => '$Rev$',
);
