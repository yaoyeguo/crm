<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
$db['coupons']=array (
  'columns' => 
  array (
    'coupon_id' => 
    array (
      'type' => 'int unsigned',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
    ),
    'coupon_name' =>
    array (
      'type' => 'varchar(255)',
      'label' => '优惠券名称',
      'comment' => '优惠券名称',
      'is_title' => true,
      'width' => 260,
      'editable' => false,
      'searchtype' => 'has',
      'filtertype' => 'normal',
      'filterdefault' => 'true',
      'in_list' => true,
      'default_in_list' => true,
      'order' => 30,
    ),
    'active_id' =>
    array (
      'type' => 'int unsigned',
      'required' => false,
      'editable' => false,
      'label' => '活动ID',
    ),
    'outer_activity_id' => 
    array (
      'type' => 'varchar(32)',
      'required' => false,
      'label' => '外部活动ID',
      'editable' => false,
      'default' => 0,
      'order' => 10,
    ),
    'outer_coupon_id' => 
    array (
      'type' => 'varchar(32)',
      'required' => false,
      'label' => '优惠券编号',
      //'hidden' => true,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => false,
      'default' => 0,
      'order' => 20,
    ),
    'outer_activity_url' => 
    array (
      'type' => 'text',
      'required' => false,
      'label' => '外部活动url',
      'hidden' => true,
      'editable' => false,
      'default' => 0,
    ),
    'shop_id' =>
    array (
      'type' => 'table:shop@ecorder',
      'required' => true,
      'label' => '所属店铺',
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'order' => 40,
    ),
    'status' =>
    array (
      'type' => 'intbool',
      'required' => true,
      'default' => '1',
      'label' => '是否启用',
      'comment' => '优惠券方案状态',
      'width' => 75,
      'editable' => false,
      'in_list' => false,
      'default_in_list' => false,
      'filterdefault'=>false,
      'order'=>110,
    ),
    'created' => 
    array (
      'type' => 'time',
      'label' => '创建时间',
      'width' => 130,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'order'=>90,
    ),
    'updated' => 
    array (
      'type' => 'time',
      'label' => '更新时间',
      'width' => 130,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => false,
    ),
    'start_time' => 
    array (
      'type' => 'time',
      'label' => '开始时间',
      'width' => 130,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'order'=>5,
    ),
    'end_time' => 
    array (
      'type' => 'time',
      'label' => '结束时间',
      'width' => 130,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'order' => 100,
    ),
    /*'config' =>
    array (
      'type' => 'serialize',
      'editable' => false,
    ),*/
    'denominations' =>
    array (
      'type' => 'number',
      'editable' => false,
      'label' => '面额',
      'in_list' => true,
      'default_in_list' => false,
      'width'=> 50,
      'order' => 50,
    ),
    'conditions' =>
    array (
      'type' => 'number',
      'editable' => false,
      'label' => '订单满x元',
    ),
    'coupon_count' =>
    array (
      'type' => 'number',
      'editable' => false,
      'label' => '总数',
      'in_list' => true,
      'default_in_list' => false,
      'width'=> 50,
      'order' => 60,
    ),
    'person_limit_count' =>
    array (
      'type' => 'number',
      'editable' => false,
      'label' => '每人限领',
    ),
    'applied_count' =>
    array (
      'type' => 'number',
      'editable' => false,
      'label' => '已领取数',
      'default' => '0',
      'in_list' => true,
      'default_in_list' => false,
      'width'=>80,
      'order' => 70,
    ),
    'used_num' =>
    array (
      'type' => 'number',
      'editable' => false,
      'label' => '已使用数',
      'default' => '0',
      'in_list' => true,
      'default_in_list' => false,
      'width'=>80,
      'order' => 80,
    ),
    'f_sync_coupon_msg' =>
    array (
      'type' => 'text',
      'editable' => false,
      'label' => '前台优惠券同步信息',
      'in_list' => true,
      'default_in_list' => false,
    ),
    'f_sync_activity_msg' =>
    array (
      'type' => 'text',
      'editable' => false,
      'label' => '前台活动同步信息',
      'in_list' => true,
      'default_in_list' => false,
    ),
    'f_sync_coupon' =>
    array (
       'type' =>
            array(
                'y' => '是',
                'n' => '否',
         ),
      'editable' => false,
      'label' => '前台优惠券是否同步',
      'default' => 'n',
    ),
   'f_sync_activity' =>
    array (
       'type' =>
            array(
                'y' => '是',
                'n' => '否',
         ),
      'editable' => false,
      'label' => '前台活动是否同步',
      'default' => 'n',
    ),
    'remark' =>
    array (
      'type' => 'text',
      'editable' => false,
      'label' => '备注',
    ),
    'source' =>
    array (
      'type' =>
            array(
                'local' => '本地',
                'taobao' => '淘宝',
            ),
      'label' => '来源',
      'width' => 125,
      'editable' => false,
      'in_list' => true,
      'comment' => '1:本地 2:淘宝',
    ),
    'is_exchange' =>
    array (
      'type' => 'tinyint',
      'editable' => false,
      'label' => '可否兑换',
      'default' => 0,
    ),
  ), 
    'index' =>
    array(
        'ind_end_time' =>
        array(
            'columns' =>
            array(
                0 => 'end_time',
            ),
        ),
    ),
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);