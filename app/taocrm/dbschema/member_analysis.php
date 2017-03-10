<?php
// 客户统计数据
$db['member_analysis'] = array(
    'columns' =>
    array(
        'id' =>
        array(
            'type' => 'int unsigned',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
            'editable' => false,
            'orderby' => false
        ),
        'member_id' => 
        array(
            'type' =>'table:members@taocrm',
            'required' => false,
            'label' => '客户名',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 110,
            'order' => 10,
            'orderby' => false
        ),
        'shop_id' =>
        array(
            'type' => 'table:shop@ecorder',
            'label' => '来源店铺',
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => false,
            'width' => 120,
            'order' => 20,
            'orderby' => false
        ),
        'district' =>
	    array(
	        'label' => '地区',
	        'type' => 'varchar(32)',
	        'sdfpath' => 'contact/district',
	        'width' => 110,
	    ),
        'channel_id' =>
        array(
            'type' => 'int',
            'label' => '来源渠道',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => false,
            'orderby' => false
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
            'order' => 70
        ),
        'total_amount' =>
        array(
            'type' => 'money',
            'default' => 0,
            'label' => '订单总金额',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'width' => 100,
            'order' => 80,
            'orderby' => true
        ),
        'total_per_amount' =>
        array(
            'type' => 'money',
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
            'orderby' => true
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
            'orderby' => true
        ),
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
            'orderby' => true
        ),
        'buy_month' =>
        array(
            'type' => 'number',
            'default' => 0,
            'editable' => false,
            'label' => '购买月数',
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'orderby' => true
        ),
        'buy_skus' =>
        array(
            'type' => 'number',
            'default' => 0,
            'editable' => false,
            'label' => '下单商品种数',
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'orderby' => true
        ),
        'buy_products' =>
        array(
            'type' => 'number',
            'default' => 0,
            'label' => '下单商品总数',
            'editable' => false,
            'in_list' => true,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
        ),
        'avg_buy_skus' =>
        array(
            'type' => 'avg',
            'default' => 0,
            'label' => '平均下单商品种数',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => false,
        ),
        'avg_buy_products' =>
        array(
            'type' => 'avg',
            'default' => 0,
            'label' => '平均下单商品件数',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => false,
        ),
        'finish_orders' =>
        array(
            'type' => 'number',
            'default' => 0,
            'width' => 80,
            'label' => '成功订单数',
            'editable' => false,
            'filtertype' => 'normal',
            'in_list' => true,
            'default_in_list' => false,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'order' => 70,
            'orderby' => true
        ),
        'finish_total_amount' =>
        array(
            'type' => 'money',
            'default' => 0,
            'width' => 90,
            'label' => '成功订单金额',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => false,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'order' => 75,
            'orderby' => true
        ),
        'finish_per_amount' =>
        array(
            'type' => 'money',
            'default' => 0,
            'label' => '成功的平均订单价',
            'editable' => false,
            'in_list' => false,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'orderby' => true
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
            'orderby' => true
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
            'orderby' => true
        ),
        'unpay_per_amount' =>
        array(
            'type' => 'money',
            'default' => 0,
            'label' => '未支付平均订单价',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => false,
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
            'orderby' => true
        ),
        'refund_amount' =>
        array(
            'type' => 'money',
            'default' => 0,
            'editable' => false,
            'label' => '退款总金额',
            'filtertype' => 'normal',
            'filterdefault' => 'false',
            'orderby' => true
        ),
        'points' =>
        array(
            'type' => 'bigint',
            'default' => 0,
            'editable' => false,
            'label' => '客户积分',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 70,
            'order' => 110,
        ),
        'lv_id' =>
        array(
            'type' => 'table:shop_lv@ecorder',
            'editable' => false,
            'label' => '客户等级',
            'in_list' => true,
            'default_in_list' => true,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
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
            'orderby' => false
        ),
        'shop_grade' => 
        array(
            'type' => array(
                '0'=>'店铺客户',
                '1'=>'普通客户',
                '2'=>'高级客户',
                '3'=>'VIP客户',
                '4'=>'至尊VIP客户',
                '-1'=>'非客户'
            ),
            'required' => false,
            'editable' => false,
        	'default' => '-1',
            'label' => '店铺客户等级',
            'in_list' => true,
            'default_in_list' => false,
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
            'in_list' => true,
            'default_in_list' => true,
            'width' => 150,
            'order' => 130,
            'orderby' => false
        ),
        'f_vip_info' =>
        array(
            'type' => array('c'=>'普通客户','asso_vip'=>'荣誉客户','vip1'=>'vip1','vip2'=>'vip2','vip3'=>'vip3','vip4'=>'vip4','vip5'=>'vip5','vip6'=>'vip6'),
            'editable' => false,
            'label' => '淘宝客户等级',
            'in_list' => false,
            'default_in_list' => false,
            'width' => 80,
            'order' => 120,
        ),
       'f_level' =>
        array(
            'type' => 'number',
        	'default' => '0',
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'label' => '信用等级',
            'orderby' => true
        ),
       'f_score' =>
        array(
            'type' => 'bigint',
        	'default' => '0',
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'label' => '信用总分',
            'orderby' => true
        ),
        'f_last_visit' =>
        array(
            'type' => 'time',
            'label' => '店铺最后访问时间',
            'editable' => false,
            'in_list' => false,
            'width' => 140,
            'order' => 125,
        ),
        'f_created' =>
        array(
            'type' => 'time',
            'label' => '店铺客户注册时间',
            'editable' => false,
            'in_list' => false,
            'width' => 140,
            'order' => 125,
        ),
        'f_status' => 
        array(
            'type' => array('unkown'=>'-','normal'=>'正常','inactive'=>'未激活','delete'=>'删除','reeze'=>'冻结','supervise'=>'监管'),
            'required' => false,
            'editable' => false,
        	'default' => 'normal',
            'label' => '店铺客户状态',
            'in_list' => false,
            'orderby' => false
        ),
        'property' => 
	    array (
	        'type' => 'text',
	        'label' => '客户属性',
	        'width' => 75,
	        'in_list' => false,
            'orderby' => false
	    ),
        'month3_finish_amount' =>
        array(
            'type' => 'money',
            'default' => 0,
            'label' => '近3个月成功金额',
            'editable' => false,
            'in_list' => true,
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
            'in_list' => true,
            'default_in_list' => false,
            'width' => 100,
            'order' => 100,
        ),
        'active_times' =>
        array(
            'type' => 'number',
            'default' => 0,
            'label' => '参加营销次数',
            'orderby' => true
        ),
        'active_buy_times' =>
        array(
            'type' => 'number',
            'default' => 0,
            'label' => '营销成功购买次数',
            'orderby' => true
        ),
    ),
    'index' =>
    array(
     	'ind_member_shop' =>
        array(
            'columns' =>
            array(
                0 => 'member_id',
                1 => 'shop_id',
            ),
            'prefix' => 'UNIQUE',
        ),
        'ind_first_buy_time' =>
        array(
            'columns' =>
            array(
                0 => 'first_buy_time',
            ),
        ),
        'ind_last_buy_time' =>
        array(
            'columns' =>
            array(
                0 => 'last_buy_time',
            ),
        ),
        'ind_channel_id' =>
        array(
            'columns' =>
            array(
                0 => 'channel_id',
            ),
        ),
        'ind_lv_id' =>
        array(
            'columns' =>
            array(
                0 => 'lv_id',
            ),
        ),
        'is_vip' =>
        array(
            'columns' =>
            array(
                0 => 'is_vip',
            ),
        ),
        'ind_month3_finish_amount' =>
        array(
            'columns' =>
            array(
                0 => 'month3_finish_amount',
            ),
        ),
    ),
    'engine' => 'innodb',
    'version' => '$Rev:  $',
); 

