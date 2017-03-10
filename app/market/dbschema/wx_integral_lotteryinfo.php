<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['wx_integral_lotteryinfo']=array (
    'columns' =>
        array (
        //info_id
        'info_id' =>
            array (
              'type' => 'int unsigned',
              'required' => true,
              'pkey' => true,
              'extra' => 'auto_increment',
              'editable' => false,
            ),

        //lottery_id
        'lottery_id' =>
            array (
              'type' => 'int unsigned',
              'required' => true,
            ),

        //awards_name
        'awards_name' =>
            array (
              'type' => 'varchar(100)',
              'label' => '奖项名称',
              'comment' => '奖项名称',
            ),
        //awards_info
        'awards_info' =>
            array (
              'type' => 'varchar(100)',
              'label' => '奖项内容',
              'comment' => '奖项内容',
            ),
        //win_rate
        'win_rate' =>
            array (
              'type' => 'int',
              'label' => '中奖几率',
            ),
        //awards_stock
        'awards_stock' =>
        array (
          'type' => 'int',
          'default' => 0,
          'label' => '库存',
        ),
        'send_num' =>
        array (
          'type' => 'int',
          'default' => 0,
          'label' => '已送出',
        ),
        //create_time
         'create_time' =>
            array (
              'type' => 'time',
              'label' => '创建时间',
              'in_list' => true,
              'default_in_list' => true,
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