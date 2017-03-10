<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['wx_integral_lottery']=array (
    'columns' =>
        array (
        'lottery_id' =>
            array (
              'type' => 'int unsigned',
              'required' => true,
              'pkey' => true,
              'extra' => 'auto_increment',
              'editable' => false,
            ),
        'ToUserName' =>
            array(
                'type' => 'varchar(100)',
                'required' => false,
                'label' => '开发者微信号',
                'editable' => false,
            ),
        'lottery_name' =>
            array (
              'type' => 'varchar(100)',
              'label' => '活动名称',
              'comment' => '活动名称',
              'required' => true,
              'is_title' => true,
              'searchtype' => 'has',
              'filtertype' => 'normal',
              'filterdefault' => 'true',
              'in_list' => true,
              'default_in_list' => true,
              'order'=>20,
              'width'=>150,
            ),
        'lottery_status' =>
            array (
               'type' => array (
                    'create' => '未开始',
                    'start' => '进行中',
                    'end' => '已结束',
                    'close' => '已关闭',
                ),
              'required' => true,
              'default' => 'create',
              'label' => '活动状态',
              'searchtype' => 'has',
              'filtertype' => 'normal',
              'filterdefault' => 'true',
              'in_list' => true,
              'default_in_list' => true,
              'order'=>10,
              'width'=>70,
            ),
         'start_time' =>
            array (
              'type' => 'time',
              'label' => '开始时间',
              'in_list' => true,
              'default_in_list' => true,
              'required' => true,
              'order'=>30,
              'width'=>120,
            ),
         'end_time' =>
            array (
              'type' => 'time',
              'label' => '结束时间',
              'in_list' => true,
              'default_in_list' => true,
              'required' => true,
              'order'=>40,
              'width'=>120,
            ),
         'create_time' =>
            array (
              'type' => 'time',
              'label' => '创建时间',
              'in_list' => true,
              'default_in_list' => true,
              'required' => true,
              'order'=>50,
              'width'=>150,
            ),
         'close_time' =>
            array (
              'type' => 'time',
              'label' => '关闭时间',
              'in_list' => true,
              'default_in_list' => true,
              'order'=>60,
              'width'=>150,
            ),
         'participants' =>
            array (
              'type' => 'int',
              'default' => 0,
              'label' => '参加人数',
              'in_list' => true,
              'default_in_list' => true,
              'order'=>70,
              'width'=>80,
            ),
         'win_msg' =>
            array (
              'type' => 'varchar(255)',
              'label' => '中将提示语',
            ),
        'lose_msg' =>
            array (
              'type' => 'varchar(255)',
              'label' => '未中奖提示语',
            ),
        'start_msg' =>
            array (
              'type' => 'varchar(255)',
              'label' => '活动未开始提示语',
            ),
        'end_msg' =>
            array (
              'type' => 'varchar(255)',
              'label' => '活动结束提示语',
            ),
        'minus_score' =>
            array (
              'type' => 'int',
              'default' => 0,
              'label' => '每次扣减积分',
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