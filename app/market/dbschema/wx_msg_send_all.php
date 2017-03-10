<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


$db['wx_msg_send_all'] = array(
    'columns' => array(
        'id' =>array(
            'type' => 'int unsigned',
            'required' => true,
            'label'=> app::get('market')->_('自增id'),
            'pkey' => true,
            'extra' => 'auto_increment',
        ),
        'send_type' => array(
            'type' => array('msg' => '文本', 'news' => '图文'),
            'label' => '类型',
            'in_list' => true,
            'default_in_list' => true,
            'default' => 'msg',
            'width' => 60,
            'order' => 5,
        ),
        'name' =>array(
            'type' => 'varchar(25)',
            'label'=> app::get('market')->_('群发推广名称'),
            'editable' => false,
            'width' => 150,
            'searchtype' => 'has',
            'filtertype' => 'has',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => true,
            'order' => 10,
        ), 
        'create_man' => array(
            'type' => 'varchar(25)', 
            'required' => true,
            'label' => '创建人',
            'in_list' => true,
            'default_in_list' => true,
            'default' => '',
            'order' => 20,
            'width' => 60,
        ),
        'create_time' => array(
            'type' => 'time', 
            'required' => true,
            'label' => '创建时间',
            'in_list' => true,
            'default_in_list' => true,
            'default' => '0',
            'order' => 30,
        ),
        'upload_man' => array(
            'type' => 'varchar(25)', 
            'required' => true,
            'label' => '上传人',
            'in_list' => true,
            'default_in_list' => true,
            'default' => '',
            'width' => 60,
            'order' => 40,
        ),
        'upload_time' => array(
            'type' => 'time', 
            'required' => true,
            'label' => '上传时间',
            'in_list' => true,
            'default_in_list' => true,
            'default' => '0',
            'order' => 50,
        ),
        'msg_content' => array(
            'type' => 'longtext',
            'label' => '图文信息及详情',
        ),
        'news_id' => array(
            'type' => 'int unsigned',
            'label'=> '图文素材id',
            'editable' => false,
            'default' => 0,
        ),
        'media_id' =>array (
            'type' => 'varchar(255)',
            'label'=> '微信素材标识',
            'default' => '',
            'width' => 150,
            'in_list' => true,
            'default_in_list' => false,
            'order' => 60,
        ),
        'created_at_wx' => array(
            'type' => 'time',
            'label'=> '微信保存素材时间',
            'editable' => false,
            'default' => '0',
        ),
        'past_time_at_wx' => array(
            'type' => 'time',
            'label'=> '微信素材过期时间',
            'editable' => false,
            'default' => '0',
            'default_in_list' => true,
            'in_list' => true,
            'order' => 70,
        ),
        'del_flag' => array(
            'type' => array(0 => '正常', 1 => '删除'),
            'label' => '状态',
            'default' => '0',
            'in_list' => true,
            'order' => 80,
        ),
        'update_time' => array(
            'type' => 'time', 
            'required' => true,
            'label' => '修改时间',
            'default' => '0',
        ),
    ),
    'comment' => '微信群发消息',
    'engine' => 'innodb',
    'version' => '$Rev$',
);
