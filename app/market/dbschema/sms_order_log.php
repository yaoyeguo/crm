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
$db['sms_order_log'] = array(
	'columns' => array(
        'order_id' => array(
            'type' => 'table:orders@ecorder',
            'label' => '订单号',
        ),
		'sms_type' => array(
			'type' => array('urge'=>'催付','remind'=>'通知'),
			'required' => true,
			'label' => '短信类型'
		),
        'sms_id' => array(
			'type' => "table:sms@market",
			'required' => true,
			'label' => '短信编号'
		),
		'send_time' => array(
			'type' => 'time',
			'label' => '发送时间'
		),
	),
	 'index' =>
    array(
        'ind_sms_id' =>
        array(
            'columns' =>
            array(
                0 => 'sms_id',
            ),
        ),
    ),
    'engine' => 'innodb',
);