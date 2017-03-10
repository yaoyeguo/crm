<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


$db['wx_msg_send_all_log'] = array(
    'columns' => array(
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
        //回执部分
        
        'send_openid' => array(
            'type' => 'varchar(50)', 
            'required' => false,
            'label' => '发送所用公共账号',
            'default' => '',
        ),
        'responses_user_name' => array(
            'type' => 'varchar(50)', 
            'required' => false,
            'label' => '公众号群发助手的微信号公众号群发助手的微信号',
            'default' => '',
        ),
        'create_time_for_wx' => array(
            'type' => 'time', 
            'required' => true,
            'label' => '微信创建时间',
            'default' => '0',
        ),
        'msg_type' => array(
            'type' => 'varchar(20)', 
            'required' => true,
            'label' => '消息类型',
            'default' => '',
        ),
        'event' => array(
            'type' => 'varchar(25)', 
            'required' => true,
            'label' => '事件信息',
            'default' => '',
        ),
        'send_status' =>array (
            'type' => 'varchar(20)',
            'label'=> '发送状态',
            'default' => '',
        ),
        'total_count' => array(
            'type' => 'int',
            'label' => '发送粉丝数',
            'default' => '0',
        ),
        'filter_count' => array(
            'type' => 'int',
            'label' => '过滤后人数',
            'default' => '0',
        ),
        'send_count' => array(
            'type' => 'int',
            'label' => '发送成功人数',
            'default' => '0',
        ),
        'error_count' => array(
            'type' => 'int',
            'label' => '发送失败粉丝数',
            'default' => '0',
        ),
        //回执部分结束
        'error_code' => array(
            'type' => 'int',
            'label' => '错误码',
            'default' => '0',
        ),
        'error_msg' => array(
            'type' => 'varchar(100)',
            'label' => '错误信息',
            'default' => '',
        ),
        'msg_id' => array(
            'type' => 'int',
            'label' => '微信返回消息ID',
            'default' => '0',
        ),
        'create_time' => array(
            'type' => 'time', 
            'required' => true,
            'label' => '创建时间',
            'default' => '0',
        ),
        'send_msg_id' => array(
            'type' => 'int unsigned',
            'label'=> '群发消息id',
            'editable' => false,
            'default' => '0',
        ),
        'update_time' => array(
            'type' => 'time', 
            'required' => false,
            'label' => '修改时间',
            'default' => '0',
        ),
        'send_list' => array(
            'type' => 'mediumtext', 
            'required' => false,
            'label' => '接受者id列表',
            'default' => '',
        ),
        'send_time' => array(
            'type' => 'time', 
            'required' => true,
            'label' => '发送时间',
            'in_list' => true,
            'default_in_list' => true,
            'default' => '0',
        ),
        'send_man' => array(
            'type' => 'varchar(25)', 
            'required' => true,
            'label' => '发送人',
            'in_list' => true,
            'default_in_list' => true,
            'default' => '',
        ),
    ),
    'index' =>
    array(
        'ind_send_msg_id' =>
        array(
            'columns' =>
            array(
                0 => 'send_msg_id',
            ),
        ),
    ),
    'comment' => app::get('market')->_('微信群发素材发送log'),
    'engine' => 'innodb',
    'version' => '$Rev$',
);
