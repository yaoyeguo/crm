<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

// 赠品发放日志
$db['gift_logs']=array (
    'columns' => 
    array (
        'id' =>
        array(
            'type' => 'int(11)',
            'required' => true,
            'pkey' => true,
            'editable' => false,
            'extra' => 'auto_increment',
        	'order' => 10
        ),
        'order_bn' => 
        array (
        	'type' => 'varchar(50)',
            'required' => false,
            'editable' => false,
            'label' => '订单号',
            'in_list' => true,
            'default_in_list' => true,
            'searchtype' => 'has',
            'filtertype' => 'normal',
            'filterdefault' => true,
            'width' => 150,
        	'order' => 20
        ),
        'buyer_account' =>
        array (
            'type' => 'varchar(100)',
            'required' => false,
            'editable' => false,
        	'in_list' => true,
            'default_in_list' => true,
         	'label' => '客户帐号',
            'searchtype' => 'has',
            'filtertype' => 'normal',
            'filterdefault' => 'true',
        	'width' => 120,
        	'order' => 30
        ),
        'order_source' => 
        array (
            'type' => 'varchar(50)',
            'required' => false,
            'editable' => false,
            'label' => '订单来源',
            'in_list' => false,
            'default_in_list' => false,
            'width' => 150,
        	'order' => 40
        ),
        'shop_id' => 
        array (
            'type' => 'table:shop@ecorder',
            'required' => false,
            'editable' => false,
            'label' => '店铺',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 150,
        	'order' => 200
        ),
        'paid_amount' => 
        array (
            'type' => 'money',
            'default' => 0,
            'required' => false,
            'label' => '付款金额',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 65,
        	'order' => 50
        ),
        'goods_num' => 
        array (
            'type' => 'number',
            'default' => 0,
            'required' => false,
            'editable' => true,
            'label' => '购买商品总数',
        	'in_list' => false,
            'default_in_list' => false,
            'width' => 50,
            'order' => 70,
        ),
        'gift_num' => 
        array (
            'type' => 'number',
            'default' => 0,
            'required' => false,
            'editable' => true,
            'label' => '赠品数量',
        	'in_list' => true,
            'default_in_list' => true,
            'width' => 65,
            'order' => 70,
        ),
        'send_num' => 
        array (
            'type' => 'number',
            'default' => 0,
            'required' => false,
            'editable' => true,
            'label' => '发货数量',
            'in_list' => false,
            'default_in_list' => false,
            'width' => 65,
            'order' => 75,
        ),
        'gift_rule_id' => 
        array (
            'type' => 'table:gift_rule@ecorder',
            'default' => 0,
            'required' => false,
            'editable' => true,
            'label' => '赠品活动名称',
        	'in_list' => true,
            'default_in_list' => true,
            'width' => 150,
            'order' => 80,
        ),
        'gift_bn' => 
        array(
        	'type' => 'varchar(32)',
            'label'=>'赠品编码',
        	'in_list' => true,
            'default_in_list' => true,
            'width' => 100,
            'order' => 100,
        ),
        'gift_name' => 
        array(
        	'type' => 'varchar(100)',
            'label'=>'赠品名称',
        	'in_list' => true,
            'default_in_list' => true,
            'searchtype' => 'has',
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'width' => 170,
            'order' => 110,
        ),
        'create_time' => 
        array(
        	'type' => 'time',
            'label'=>'赠送时间',
        	'in_list' => true,
            'default_in_list' => true,
            'filtertype' => 'normal',
            'filterdefault' => true,
            'order' => 120,
        ),
        'update_time' => 
        array(
        	'type' => 'time',
            'label'=>'发货时间',
            'in_list' => false,
            'default_in_list' => false,
            'order' => 130,
        ),
        'md5_key' => 
        array(
            'type' => 'varchar(32)',
            'label'=>'唯一识别码',
            'comment'=>'唯一识别码，同一秒同一订单号只送出一个货号(order_bn + create_time + gift_bn)',
            'in_list' => false,
            'default_in_list' => false,
            'order' => 130,
        ),
        'status' => 
        array(
        	'type' => array(
                '0'=>'未完成',
                '1'=>'已完成',
            ),
            'default' => '0',
            'label'=>'是否完成',
            'in_list' => false,
            'default_in_list' => false,
            'width' => 60,
            'order' => 150,
        ),
    ), 
    'index' =>
    array (
        'ind_order_bn' =>
        array (
            'columns' =>
            array (
                'order_bn',
            ),
        ),
        'ind_md5_key' =>
        array (
            'columns' =>
            array (
                'md5_key',
            ),
            'prefix' => 'unique',
        ),
    ),
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);