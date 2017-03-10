<?php
$db['tb_refunds']=array (
  'columns' =>
  array (
    'id' =>
    array (
      'type' => 'int unsigned',
      'required' => true,
      'pkey' => true,
      'editable' => false,
      'extra' => 'auto_increment',
    ),
    'tid' =>
    array (
      'type' => 'bigint unsigned',
      'label' => '订单号',
      'filtertype' => 'normal',
      'filterdefault' => true,
      'in_list' => false,
      'default_in_list' => false,
      'editable' => false,
      'searchtype' => 'has',
      'order' => 10,
    ),
    'refund_id' =>
    array (
      'type' => 'bigint unsigned',
      'label' => '退款单号',
      'filtertype' => 'normal',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
      'editable' => false,
      'searchtype' => 'has',
      'order' => 20,
    ), 
    'status' =>
    array (
      'type' => array(
        'WAIT_SELLER_AGREE' => '等待卖家同意',
        'WAIT_BUYER_RETURN_GOODS' => '同意退款，等待买家退货',
        'WAIT_SELLER_CONFIRM_GOODS' => '已退货，等待确认收货',
        'SELLER_REFUSE_BUYER' => '卖家拒绝退款',
        'CLOSED' => '退款关闭',
        'SUCCESS' => '退款成功',
        'UNKOWN' => '未知状态',
      ),
      'default' => 'SUCCESS',
      'label' => '状态',
      'filtertype' => 'normal',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
      'editable' => false,
      'width' => 80,
      'order' => 25,
    ),
    'buyer_nick' =>
    array (
      'type' => 'varchar(50)',
      'label' => '客户帐号',
      'filtertype' => 'normal',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
      'editable' => false,
      'searchtype' => 'has',
      'width' => 100,
      'order' => 30,
    ),
    'mobile' =>
    array (
      'type' => 'varchar(11)',
      'label' => '收货人手机',
      'filtertype' => 'normal',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
      'editable' => false,
      'searchtype' => 'has',
      'width' => 100,
      'order' => 35,
    ),
    'title' =>
    array (
      'type' => 'varchar(100)',
      'label' => '商品',
      'filtertype' => 'normal',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
      'editable' => false,
      'searchtype' => 'has',
      'order' => 40,
    ),
    'num' =>
    array (
      'type' => 'int',
      'label' => '数量',
      'filtertype' => 'normal',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => false,
      'editable' => false,
      'width' => 50,
      'order' => 50,
    ),
    'refund_fee' =>
    array (
      'type' => 'money',
      'label' => '退款金额',
      'filtertype' => 'normal',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
      'editable' => false,
      'width' => 80,
      'order' => 60,
    ),
    'payment' =>
    array (
      'type' => 'money',
      'label' => '付款金额',
      'filtertype' => 'normal',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => false,
      'editable' => false,
      'width' => 80,
      'order' => 70,
    ),
    'reason' =>
    array (
      'type' => 'varchar(50)',
      'label' => '退款原因',
      'filtertype' => 'normal',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => false,
      'editable' => false,
      'order' => 80,
    ),
    'sid' =>
    array (
      'type' => 'varchar(20)',
      'label' => '物流单号',
      'filtertype' => 'normal',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => false,
      'editable' => false,
      'order' => 90,
    ),
    'company_name' =>
    array (
      'type' => 'varchar(50)',
      'label' => '物流公司',
      'filtertype' => 'normal',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => false,
      'editable' => false,
      'order' => 100,
    ),
    'shop_id' =>
    array (
      'type' => 'table:shop@ecorder',
      'label' => '来源店铺',
      'width' => 75,
      'editable' => false,
      'in_list' => true,
      'filtertype' => 'normal',
      'filterdefault' => true,
      'order' => 110,
    ),
    'oid' =>
    array (
      'type' => 'bigint unsigned',
      'label' => '子订单号',
      'filtertype' => 'normal',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => false,
      'editable' => false,
    ),
    'total_fee' =>
    array (
      'type' => 'money',
      'label' => '订单金额',
      'filtertype' => 'normal',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => false,
      'editable' => false,
      'width' => 80
    ),
    'seller_nick' =>
    array (
      'type' => 'varchar(50)',
      'label' => '卖家帐号',
      'filtertype' => 'normal',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => false,
      'editable' => false,
    ),
    'created' =>
    array (
      'type' => 'time',
      'label' => '创建时间',
      'filtertype' => 'normal',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
      'editable' => false,
    ),
    'modified' =>
    array (
      'type' => 'time',
      'label' => '更新时间',
      'filtertype' => 'normal',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
      'editable' => false,
    ),
    'down_time' =>
    array (
      'type' => 'datetime',
      'label' => '下载时间',
      'filtertype' => 'normal',
      'filterdefault' => false,
      'in_list' => true,
      'default_in_list' => false,
      'editable' => false,
    ),
    'order_status' =>
    array (
      'type' => array(
            'TRADE_NO_CREATE_PAY' => '非支付宝交易',
            'WAIT_BUYER_PAY' => '等待付款',
            'WAIT_SELLER_SEND_GOODS' => '等待卖家发货',
            'WAIT_BUYER_CONFIRM_GOODS' => '等待确认收货',
            'TRADE_BUYER_SIGNED' => '买家已签收',
            'TRADE_FINISHED' => '交易成功',
            'TRADE_CLOSED' => '交易关闭',
            'TRADE_CLOSED_BY_TAOBAO' => '被淘宝关闭',
        ),
      'default' => 'TRADE_CLOSED',
      'label' => '订单状态',
      'filtertype' => 'normal',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => false,
      'editable' => false,
      'width' => 100,
    ),
    'good_status' =>
    array (
      'type' => array(
            'BUYER_NOT_RECEIVED' => '买家未收到货',
            'BUYER_RECEIVED' => '买家已收到货',
            'BUYER_RETURNED_GOODS' => '买家已退货',
        ),
      'default' => 'BUYER_RECEIVED', 
      'label' => '商品状态',
      'filtertype' => 'normal',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => false,
      'editable' => false,
    ),
    'has_good_return' =>
    array (
      'type' => 'varchar(20)',
      'label' => '是否退货',
      'filtertype' => 'normal',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => false,
      'editable' => false,
    ),
    'desc' =>
    array (
      'type' => 'varchar(500)',
      'label' => '退款说明',
      'filtertype' => 'normal',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
      'editable' => false,
    ),
    'member_id' =>
    array (
      'type' => 'table:members@taocrm',
      'label' => '客户ID',
      'width' => 75,
      'editable' => false,
      'in_list' => false,
      'filtertype' => 'normal',
      'filterdefault' => false,
    ),
  ),
  'comment' => '淘宝退款',
  'index' =>
  array (
    'ind_refund_id' =>
    array (
        'columns' =>
        array (
          0 => 'refund_id',
        ),
        //'prefix' => 'unique',
    ),
    'ind_buyer_nick' =>
    array (
        'columns' =>
        array (
          0 => 'buyer_nick',
        ),
    ),
    'ind_mobile' =>
    array (
        'columns' =>
        array (
          0 => 'mobile',
        ),
    ),
    'ind_created' =>
    array (
        'columns' =>
        array (
          0 => 'created',
        ),
    ),
    'ind_tid' =>
    array (
        'columns' =>
        array (
          0 => 'tid',
        ),
    ),
  ), 
  'engine' => 'innodb',
  'version' => '$Rev: 41103 $',
);

