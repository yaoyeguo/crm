<?php
/**
 * ShopEx
 *
 * @author lb
 * @email ttian20@gmail.com
 * @copyright 2003-2011 Shanghai ShopEx Network Tech. Co., Ltd.
 * @website http://www.shopex.cn/
 *
 */
$db['stored_value_log'] = array(
    'columns' => array(
        'log_id' => array(
            'type' => 'bigint unsigned',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
        ),
        'member_id' => array(
            'type' => 'int',
            'label' => '客户ID',
            'default' => 0,
            'required' => false,
            'editable' => false,
        ),
        'shop_id' =>
        array (
            'type' => 'varchar(32)',
            'label' => '店铺ID',
            'required' => false,
            'in_list' => false,
        ),
        'uname' => array(
            'type' => 'varchar(32)',
            'default' => '',
            'required' => false,
            'editable' => false,
            'searchtype' => 'has',
            'filtertype' => 'normal',
            'label' => '客户名称',
            'in_list' => true,
            'default_in_list' => true,
            'width'=>100,
            'order'=>1,
        ),
        'mobile' => array(
            'type' => 'varchar(20)',
            'required' => false,
            'searchtype' => 'has',
            'filtertype' => 'normal',
            'label' => '手机号码',
            'in_list' => true,
            'default_in_list' => true,
            'width'=>100,
            'order'=>5,
        ),
        'value_time' => array(
            'type' => 'time',
            'default' => 0,
            'required' => false,
            'label' => '储值更改时间',
            'in_list' => true,
            'default_in_list' => true,
            'searchtype' => 'has',
            'filtertype' => 'normal',
            'width' => 150,
            'order'=>40,
        ),
        'change_amount' => array(
            'type' => 'money',
            'required' => false,
            'label' => '变动金额',
            'in_list' => true,
            'default_in_list' => true,
            'width'=>100,
            'order'=>20,
        ),
        'before_change_amount' => array(
            'type' => 'money',
            'required' => false,
            'label' => '期初值(变动前金额)',
            'in_list' => true,
            'default_in_list' => true,
            'width'=>150,
            'order'=>30,
        ),
        'after_change_amount' => array(
            'type' => 'money',
            'required' => false,
            'label' => '期末值(变动后金额)',
            'in_list' => true,
            'default_in_list' => true,
            'width'=>150,
            'order'=>40,
        ),
        'trade_no' => array(
            'type' => 'varchar(50)',
            'required' => false,
            'label' => '订单号',
            'in_list' => true,
            'default_in_list' => true,
            'width'=>150,
            'order'=>50,
        ),
        'payment_no' => array(
            'type' => 'varchar(50)',
            'required' => false,
            'label' => '支付单号',
            'in_list' => false,
            'default_in_list' => false,
        ),
        'sn' => array(
            'type' => 'varchar(50)',
            'required' => false,
            'label' => '序列号',
            'in_list' => false,
            'default_in_list' => false,
        ),
        'op_user' => array(
            'type' => 'varchar(50)',
            'required' => false,
            'label' => '操作人',
            'searchtype' => 'has',
            'filtertype' => 'normal',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 100,
            'order'=>150,
        ),
        'remark' =>
        array(
            'type' => 'varchar(200)',
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'label' => '备注',
            'order' => 200,
            'width' => 180,
        ),
    ),
    'index' =>
    array(
        'ind_member' =>
        array(
            'columns' =>
            array(
                0 => 'member_id',
            ),
        ),
        'ind_mobile' =>
        array(
            'columns' =>
            array(
                0 => 'mobile',
            ),
        ),
        'ind_op_user' =>
        array(
            'columns' =>
            array(
                0 => 'op_user',
            ),
        ),
        'ind_sn' =>
        array(
            'columns' =>
            array(
                0 => 'sn',
            ),
        ),
        'ind_value_time' =>
        array(
            'columns' =>
            array(
                0 => 'value_time',
            ),
        ),
    ),
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);
