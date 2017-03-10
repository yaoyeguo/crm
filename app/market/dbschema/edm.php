<?php
/**
 * ShopEx
 *
 * @author Tian Xingang
 * @email ttian20@gmail.com
 * @copyright 2003-2011 Shanghai ShopEx Network Tech. Co., Ltd.
 * @website http://www.shopex.cn/
 *
 */
$db['edm'] = array(
	'columns' => array(
		'id' => array(
			'type' => 'int(11)',
			'required' => true,
			'pkey' => true,
			'extra' => 'auto_increment'
		),
		'type' => array(
			'type' => array('single' => 'single', 'batch' => 'batch'),
			'required' => true,
			'is_title'=>true,
			'in_list' => true,
            'default_in_list'=>true,
			'label' => '发送方式'
		),
        'theme_id' =>array(
          'type'     => 'table:edm_templates@market',
          'label'    => '邮件模板',
          'width'    => 110,
          'editable' => false,
           'in_list' => true,
          'default_in_list' => true,
        ),
		'shop_id' => array(
			'type' => 'table:shop@ecorder',
            'label' => '店铺ID',
			'is_title'=>true,
			'in_list' => true,
            'default_in_list'=>true,
			'required' => true,
		),
        'active_id' => array(
			'type' => 'table:active@market',
            'label' => '活动ID',
			'is_title'=>true,
			'in_list' => true,
            'default_in_list'=>true,
			'required' => true,
		),
        'active_name' =>array (
          'type' => 'varchar(255)',
          'label' => '活动名称',
          'comment' => '活动名称',
          'is_title' => true,
          'width' => 180,
          'editable' => false,
          'searchtype' => 'has',
          'filtertype' => 'normal',
          'filterdefault' => 'true',
          'in_list' => true,
          'default_in_list' => true,
          'order'=>1,
        ),
        'title' => array(
			'type' => 'varchar(100)',	
            'label' => '邮件主题',
			'is_title'=>true,
            'searchtype' => 'has',
			'in_list' => true,
            'default_in_list'=>true,        
		),
		'content' => array(
			'type' => 'text',
			'is_title'=>true,
            'default_in_list'=>true,	
            'label' => '邮件内容',        
		),
        /*'send_num' => array(
			'type' => 'number',
			'is_title'=>true,
            'default_in_list'=>true,
			'in_list' => true,	
            'label' => '发送人数',        
		),*/
        'end_time' => array (
          'type' => 'time',
          'label' => '结束时间',
          'width' => 130,
          'editable' => false,
          'in_list' => true,
          'default_in_list' => true,
        ),
		'plan_send_time' => array(
			'type' => 'time',
			'is_title'=>true,
            'default_in_list'=>true,
			'in_list' => true,
			'required' => true,
            'label' => '计划发送时间',
		),
        'total_num' => array (
          'type' => 'number',
          'label' => '总数',
          'width' => 130,
          'editable' => false,
          'in_list' => true,
          'default_in_list' => true,
        ),
        'success_num' => array (
          'type' => 'number',
          'label' => '成功数',
          'width' => 130,
          'editable' => false,
          'in_list' => true,
          'default_in_list' => true,
        ),
        'is_send' =>array (
          'type' =>   array (
                'succ' => '发送成功',
                'fail' => '发送失败',
                'unsend' => '未发送',
                'sending' => '发送中',
              ),
          'default' => 'unsend',
          'required' => FALSE,
          'label' => '发送状态',
          'comment' => '邮件是否发送',
          'width' => 75,
          'editable' => false,
          'in_list' => true,
          'default_in_list' => true,
        ),
        'create_time' => array(
			'type' => 'time',
			'is_title'=>true,
            'default_in_list'=>true,
			'in_list' => true,
			'required' => true,
            'label' => '创建时间',
		),
	),
    'engine' => 'innodb',
);
