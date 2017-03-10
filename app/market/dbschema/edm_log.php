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
$db['edm_log'] = array(
	'columns' => array(
		'id' => array(
			'type' => 'int(11)',
			'required' => true,
			'pkey' => true,
			'extra' => 'auto_increment'
		),
        'member_id' =>array(
            'type' => 'text',
            'required' => false,
            'editable' => false,
        ),
        'edm_id' => array(
			'type' => 'table:edm@market',
			'required' => false,
		),
        'active_id' => array (
	      'type' =>'table:active@market',
	      'editable' => false,
	      'label' => '活动编号',
	       'in_list' => true,
	      'default_in_list' => true,
	    ),
		'type' => array(
			'type' => array('single' => 'single', 'batch' => 'batch'),
			'required' => true,
			'label' => '发送方式'
		),
		'shop_id' => array(
			'type' => 'table:shop@ecorder',
            'label' => '店铺ID',
			'required' => true,
		),
        'batch_no' => array(
			'type' => 'varchar(150)',
			'required' => false,
            'label' => '批次编号',
		),
        'edm_batch_no' => array(
			'type' => 'varchar(50)',
			'required' => false,
            'label' => '发送批次',
		),
        'email' => array(
			'type' => 'text',
			'required' => false,
            'label' => 'email账号',
		),
        'title' => array(
			'type' => 'varchar(100)',	
            'label' => '邮件主题',        
		),
		'content' => array(
			'type' => 'text',	
            'label' => '邮件内容',        
		),
        'status' => array(
			'default' => 'failed',			
			'type' => array(
				'failed' => '发送失败',
				'success' => '发送成功'
			),
			'required' => false,
		),
        'reason' => array(
			'type' => 'text',
			'label' => '失败原因'
		),
        'send_num' => array(
			'type' => 'number',	
            'label' => '发送人数',        
		),
        'plan_send_time' => array(
			'type' => 'time',
			 'default' => '0',
			'required' => false,
            'label' => '计划发送时间',
		),
		'create_time' => array(
			'type' => 'time',
			'required' => true,
			'label' => '执行时间'
		),
	),
    'engine' => 'innodb',
);