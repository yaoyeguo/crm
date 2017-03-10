<?php
// 客户统计数据
$db['middleware_member_analysis'] = array(
    'columns' =>
    array(
        'id' =>
        array(
            'type' => 'int unsigned',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
            'editable' => false,
        ),
        'member_id' => 
        array(
        	'type' =>'int',
            'required' => false,
            'label' => '客户ID',
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
            //'searchtype' => 'has',
            'width' => 110,
            'order' => 12,
        ),
        'shop_id' =>
        array(
            'type' => 'varchar(32)',
            'label' => '来源店铺',
            'required' => false,
            'editable' => false,
            'in_list' => false,
        	//'searchtype' => 'has',
            'default_in_list' => false,
            'width' => 120,
            'order' => 22,
        ),
        'district' =>
	    array(
	        'label' => '地区',
	        'type' => 'varchar(32)',
	        'sdfpath' => 'contact/district',
//	        'editable' => false,
//	        'filtertype' => 'yes',
//	        'filterdefault' => 'true',
//	        'in_list' => true,
//	        'default_in_list' => false,
	        'width' => 110,
	        'order' => 70,
	    ),
        'channel_id' =>
        array(
            'type' => 'int',
            'label' => '来源渠道',
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
        ),
        
        'total_orders' =>
        array(
            'type' => 'number',
            'default' => 0,
            'label' => '订单总数',
            'editable' => false,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 70,
            'order' => 70,
        ),
        'total_amount' =>
        array(
            'type' => 'float',
            'default' => 0,
            'label' => '订单总金额',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'width' => 100,
            'order' => 80,
        ),
        'total_per_amount' =>
        array(
            'type' => 'float',
            'default' => 0,
            'label' => '平均订单价',
            'editable' => false,
            'in_list' => false,
            'in_list' => true,
            'default_in_list' => false,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'width' => 100,
            'order' => 90,
        ),
        'buy_freq' =>
        array(
            'type' => 'number',
            'default' => 0,
            'label' => '购买频次(天)',
            'editable' => false,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'in_list' => true,
            'default_in_list' => false,
            'width' => 100,
            'order' => 30,
        ),
        /*
        'avg_buy_interval' =>
        array(
            'type' => 'avg',
            'default' => 0,
            'label' => '平均购买间隔(天)',
            'editable' => false,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'in_list' => true,
            'default_in_list' => false,
            'width' => 100,
            'order' => 40,
        ),
        */
        'buy_month' =>
        array(
            'type' => 'number',
            'default' => 0,
            'editable' => false,
            'label' => '购买月数',
            'filtertype' => 'normal',
            'filterdefault' => 'true',
        ),
        'buy_skus' =>
        array(
            'type' => 'number',
            'default' => 0,
            'editable' => false,
            'label' => '下单商品种数',
            'filtertype' => 'normal',
            'filterdefault' => 'true',
        ),
        'buy_products' =>
        array(
            'type' => 'number',
            'default' => 0,
            'label' => '下单商品总数',
            'editable' => false,
            'in_list' => false,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
        ),
        'avg_buy_skus' =>
        array(
            'type' => 'avg',
            'default' => 0,
            'label' => '平均下单商品种数',
            'editable' => false,
//            'filtertype' => 'normal',
//            'filterdefault' => 'true',
            'in_list' => false,
            'default_in_list' => false,
        ),
        'avg_buy_products' =>
        array(
            'type' => 'avg',
            'default' => 0,
            'label' => '平均下单商品件数',
            'editable' => false,
//            'filtertype' => 'normal',
//            'filterdefault' => 'true',
            'in_list' => false,
            'default_in_list' => false,
        ),
        'finish_orders' =>
        array(
            'type' => 'number',
            'default' => 0,
            'label' => '成功的订单数',
            'editable' => false,
            'filtertype' => 'normal',
            'in_list' => true,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
        	'orderby' => false
        ),
        'finish_total_amount' =>
        array(
            'type' => 'float',
            'default' => 0,
            'label' => '成功的订单金额',
            'editable' => false,
            'hidden' => true,
            'in_list' => true,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
        	'orderby' => false
        ),
        'finish_per_amount' =>
        array(
            'type' => 'float',
            'default' => 0,
            'label' => '成功的平均订单价',
            'editable' => false,
            'in_list' => false,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
        ),
        'unpay_orders' =>
        array(
            'type' => 'number',
            'default' => 0,
            'label' => '未付款订单数',
            'width' => 80,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => false,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'width' => 100,
            'order' => 100,
        	'orderby' => false
        ),
        'unpay_amount' =>
        array(
            'label' => '未付款订单金额',
            'type' => 'money',
            'default' => 0,
            'editable' => false,
            'hidden' => true,
            'in_list' => false,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
        	'orderby' => false
        ),
        'unpay_per_amount' =>
        array(
            'type' => 'float',
            'default' => 0,
            'label' => '未支付平均订单价',
            'editable' => false,
            'in_list' => true,
        	'orderby' => false
//            'filtertype' => 'normal',
//            'filterdefault' => 'false',
        ),
        'refund_orders' =>
        array(
            'type' => 'number',
            'default' => 0,
            'required' => false,
            'label' => '退款订单数',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => false,
            'filtertype' => 'normal',
            'filterdefault' => 'false',
        	'orderby' => false
        ),
        'refund_amount' =>
        array(
            'type' => 'float',
            'default' => 0,
            'editable' => false,
            'label' => '退款总金额',
            'filtertype' => 'normal',
            'filterdefault' => 'false',
        	'orderby' => false
        ),
        'points' =>
        array(
            'type' => 'number',
            'default' => 0,
            'editable' => false,
            'label' => '客户积分',
            'in_list' => false,
            'default_in_list' => false,
//            'filtertype' => 'normal',
//            'filterdefault' => 'true',
            'width' => 70,
            'order' => 110,
        ),
        'lv_id' =>
        array(
            'type' => 'int(11)',
            'editable' => false,
            'label' => '客户等级',
            'in_list' => false,
            'default_in_list' => false,
//            'filtertype' => 'normal',
//            'filterdefault' => 'true',
            'width' => 80,
            'order' => 120,
        ),
         'is_vip' =>
		    array(
		        'type' => 'bool',
		        'required' => true,
		    	'default' => 'false',
		        'editable' => false,
		        'in_list' => true,
		        'label' => '贵宾客户',
		    	'orderby' => false
		    ),
        'shop_evaluation' => 
        array(
            'type' => array('good'=>'好评','bad'=>'差评','neutral'=>'中评','unkown'=>'-'),
            'required' => false,
            'editable' => false,
        	'default' => 'unkown',
            'label' => '店铺评价',
            'in_list' => false,
        ),
        'first_buy_time' =>
        array(
            'type' => 'time',
            'label' => '第一次下单时间',
            'editable' => false,
            'filtertype' => 'yes',
            'filterdefault' => 'false',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 140,
            'order' => 124,
        ),
        'last_buy_time' =>
        array(
            'type' => 'time',
            'label' => '最后下单时间',
            'editable' => false,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 140,
            'order' => 125,
        ),
        'update_time' =>
        array(
            'type' => 'time',
            'required' => false,
            'editable' => false,
            'label' => '更新时间',
            'in_list' => false,
            'default_in_list' => false,
            'width' => 150,
            'order' => 130,
        ),
        'f_vip_info' =>
        array(
            'type' => array('c'=>'普通客户','asso_vip'=>'荣誉客户','vip1'=>'vip1','vip2'=>'vip2','vip3'=>'vip3','vip4'=>'vip4','vip5'=>'vip5','vip6'=>'vip6'),
            'editable' => false,
            'label' => '淘宝客户等级',
            'in_list' => false,
            'default_in_list' => false,
//            'filtertype' => 'normal',
//            'filterdefault' => 'true',
            'width' => 80,
            'order' => 120,
        ),
       'f_level' =>
        array(
            'type' => 'number',
        	'default' => '0',
            'required' => false,
            'editable' => false,
            'in_list' => false,
            'label' => '信用等级',
        ),
      'f_score' =>
        array(
            'type' => 'number',
        	'default' => '0',
            'required' => false,
            'editable' => false,
            'in_list' => false,
            'label' => '信用总分',
        ),
        'f_last_visit' =>
        array(
            'type' => 'time',
            'label' => '店铺最后访问时间',
            'editable' => false,
//            'filtertype' => 'normal',
//            'filterdefault' => 'true',
            'in_list' => true,
            'width' => 140,
            'order' => 125,
        	'default' => 0,
        	'orderby' => false
        ),
        'f_created' =>
        array(
            'type' => 'time',
            'label' => '店铺客户注册时间',
            'editable' => false,
//            'filtertype' => 'normal',
//            'filterdefault' => 'true',
            'in_list' => true,
            'width' => 140,
            'order' => 125,
        	'default' => 0,
        	'orderby' => false
        ),
        'f_status' => 
        array(
            'type' => array('unkown'=>'-','normal'=>'正常','inactive'=>'未激活','delete'=>'删除','reeze'=>'冻结','supervise'=>'监管'),
            'required' => false,
            'editable' => false,
        	'default' => 'unkown',
            'label' => '店铺客户状态',
            'in_list' => false,
        ),
        'property' => 
	    array (
	        'type' => 'text',
	        'label' => '客户属性',
	        'width' => 75,
	        'in_list' => false,
	    ),
        'month3_finish_amount' =>
        array(
            'type' => 'float',
            'default' => 0,
            'label' => '近3个月成功金额',
            'editable' => false,
            'in_list' => false,
            'in_list' => false,
            'default_in_list' => false,
            'width' => 100,
            'order' => 90,
        ),
        'month3_finish_orders' =>
        array(
            'type' => 'number',
            'default' => 0,
            'label' => '近3个月成功订单数',
            'editable' => false,
            'in_list' => false,
            'in_list' => false,
            'default_in_list' => false,
            'width' => 100,
            'order' => 90,
        ),
    ),
); 

