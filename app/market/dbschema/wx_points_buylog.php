<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['wx_points_buylog']=array (
    'columns' =>
        array(
        'log_id' =>
            array (
              'type' => 'int unsigned',
              'required' => true,
              'pkey' => true,
              'extra' => 'auto_increment',
              'editable' => false,
            ),
        'buy_id' =>
            array (
              'type' => 'int unsigned',
              'required' => true,
            ),
        'FromUserName' =>
            array(
                'type' => 'varchar(100)',
                'required' => false,
                'label' => '发送方账号',
            ),
        //参与时间
        'create_time' =>
            array (
              'type' => 'time',
              'label' => '创建时间',
              'in_list' => true,
              'default_in_list' => true,
            ),
        //参与人
        'receiver' =>
            array (
              'type' => 'varchar(100)',
              'label' => '参与人',
              'comment' => '参与人',
            ),
        //手机号
        'mobile' =>
            array (
              'type' => 'char(11)',
              'label' => '手机号',
            ),
        //收货地址
         'addr' =>
            array (
              'type' => 'varchar(255)',
              'label' => '收货地址',
              'comment' => '参与人地址',
            ),
        //扣除积分
        'minus_score' =>
            array (
              'type' => 'int unsigned',
              'label' => '扣减积分',
            ),
        //商品编码
        'goods_code' =>
            array (
              'type' => 'varchar(50)',
              'label' => '商品编码',
            ),
        //商品名称
        'goods_name' =>
            array (
              'type' => 'varchar(255)',
              'label' => '商品名称',
            ),
        //兑换数量
        'buy_num' =>
            array (
              'type' => 'int unsigned',
              'required' => true,
            ),

         //update_time
         'update_time' =>
            array (
              'type' => 'time',
              'label' => '修改时间',
            ),
    ),
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);