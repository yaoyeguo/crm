<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['wx_integral_lotterylog']=array (
    'columns' =>
        array (
        'log_id' =>
            array (
              'type' => 'int unsigned',
              'required' => true,
              'pkey' => true,
              'extra' => 'auto_increment',
              'editable' => false,
            ),
        'lottery_id' =>
            array (
              'type' => 'int unsigned',
              'required' => true,
            ),
        'people_name' =>
            array (
              'type' => 'varchar(100)',
              'label' => '参与人',
              'comment' => '参与人',
            ),
        'people_adr' =>
            array (
              'type' => 'varchar(255)',
              'label' => '参与人地址',
              'comment' => '参与人地址',
            ),
        'lottery_name' =>
            array (
              'type' => 'varchar(100)',
              'label' => '活动名称',
              'comment' => '活动名称',
            ),
        'FromUserName' =>
            array(
                'type' => 'varchar(100)',
                'required' => false,
                'label' => '发送方账号',
            ),
        'phone' =>
            array (
              'type' => 'char(11)',
              'label' => '手机号',
            ),
        'minus_score' =>
            array (
              'type' => 'int',
              'label' => '扣减积分',
            ),
        'lottery_info_id' =>
            array (
              'type' => 'int',
              'label' => '中奖编号',//默认0，未中奖
              'default' => 0
            ),
        'lottery_info_name' =>
            array (
              'type' => 'varchar(255)',
              'label' => '中奖结果',
            ),
         'create_time' =>
            array (
              'type' => 'time',
              'label' => '创建时间',
              'in_list' => true,
              'default_in_list' => true,
            ),
         'update_time' =>
            array (
              'type' => 'time',
              'label' => '修改时间',
            ),
    ),
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);