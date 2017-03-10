<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['wx_points_buy']=array (
    'columns' =>
        array (
        'buy_id' =>
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
        'buy_name' =>
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
        'buy_status' =>
            array (
               'type' => array (
                    'create' => '未开始',
                    'start' => '进行中',
                    'end' => '已结束',
                    'close' => '已关闭',
                ),
              'required' => true,
              'label' => '活动状态',
              'comment' => '活动状态',
              'searchtype' => 'has',
              'filtertype' => 'normal',
              'filterdefault' => 'true',
              'in_list' => true,
              'default_in_list' => true,
              'order'=>10,
              'width'=>70,
            ),
        'minus_score' =>
            array (
              'type' => 'bigint',
              'label' => '每次扣减积分',
            ),
        'msg' =>
            array (
              'type' => 'varchar(255)',
              'label' => '活动说明',
            ),
        'goods_name' =>
            array (
              'type' => 'varchar(255)',
              'label' => '商品名称',
              'in_list' => true,
              'default_in_list' => true,
              'required' => true,
              'order'=>40,
              'width'=>150,
            ),
            'goods_code' =>
            array (
              'type' => 'varchar(50)',
              'label' => '商品编码',
              'comment' => '商品编码',
              'in_list' => true,
              'default_in_list' => true,
              'required' => true,
              'order'=>50,
              'width'=>100,
            ),
        'goods_img' =>
            array (
              'type' => 'varchar(255)',
              'label' => '商品图片',
            ),
        'goods_msg' =>
            array (
              'type' => 'varchar(300)',
              'label' => '商品描述',
            ),
        'goods_all_stock' =>
            array (
              'type' => 'int',
              'label' => '可兑换总量',
              'default' => 0,
              'in_list' => true,
              'default_in_list' => true,
              'required' => true,
              'order'=>150,
              'width'=>80,
            ),
        'goods_stock' =>
            array (
              'type' => 'int',
              'label' => '剩余总量',
              'default' => 0,
            ),
        'join_num' =>
            array (
              'type' => 'int',
              'default' => 0,
              'label' => '兑换数量',
              'in_list' => false,
              'default_in_list' => false,
              'order'=>60,
              'width'=>80,
        ),
        'limit_times' =>
            array (
                'type' => array (
                    'Unlimited' => '不限',
                    1 => '1次',
                    2 => '2次',
                    3 => '3次',
                    4 => '4次',
                    5 => '5次'
                ),
                'default' => 'Unlimited',
                'label' => '限制次数',
                'in_list' => false,
                'default_in_list' => false,
                'order'=>70,
                'width'=>80,
            ),
        'start_time' =>
            array (
              'type' => 'time',
              'label' => '开始时间',
              'in_list' => true,
              'default_in_list' => true,
              'required' => true,
              'order'=>80,
              'width'=>120,
            ),
        'end_time' =>
            array (
              'type' => 'time',
              'label' => '结束时间',
              'in_list' => true,
              'default_in_list' => true,
              'required' => true,
              'order'=>100,
              'width'=>120,
            ),
         'create_time' =>
            array (
              'type' => 'time',
              'label' => '创建时间',
            ),
        'update_time' =>
        array (
          'type' => 'time',
          'label' => '修改时间',
        ),
        'shop_gift_id' =>
        array (
            'type' => 'int unsigned',
            'label' => 'erp赠品id',
        ),
    ),
    'index' =>
    array(
    	 'ind_code' =>
        array(
            'columns' =>
            array(
                0 => 'goods_code',
            ),
        ),
    ),
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);
