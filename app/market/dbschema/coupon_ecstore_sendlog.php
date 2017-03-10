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
$db['coupon_ecstore_sendlog'] = array(
	'columns' => array(
		'log_id' => array(
			'type' => 'int(11)',
			'required' => true,
			'pkey' => true,
			'extra' => 'auto_increment'
		),
		 'coupon_id' => 
        array (
          'type' => 'int unsigned',
          'required' => true,
          'editable' => false,
        ),
		'shop_id' => array(
			'type' => 'varchar(32)',
			'required' => false,
		),
		'is_send' =>
    array (
      'type' =>   array (
	        'succ' => '发送成功',
	        'fail' => '发送失败',
	        'unsend' => '未发送',
    		'sending' => '发送中',
	      ),
      'default' => 'unsend',
      'required' => FALSE,
      'label' => '发送状态',
      'comment' => '优惠劵是否发送',
      'width' => 75,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
	'coupon_total_num' => 
    array (
      'type' => 'number',
      'label' => '优惠劵发送总数',
      'width' => 130,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'coupon_valid_num' => 
    array (
      'type' => 'number',
      'label' => '优惠劵发送有效数',
      'width' => 130,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'conpon_success_num' => 
    array (
      'type' => 'number',
      'label' => '优惠劵发送成功数',
      'width' => 130,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
        'created' => array(
			'type' => 'time',
			'required' => false,
            'label' => '创建时间',
            'in_list' => true,
            'default_in_list' => true,
            'order'=>50,
		),
		'finish_time' => array(
			'type' => 'time',
			'required' => false,
            'label' => '完成时间',
            'in_list' => true,
            'default_in_list' => true,
            'order'=>50,
		),
	),
	'index' =>
    array(
       
    ),
	'engine' => 'innodb',
);
