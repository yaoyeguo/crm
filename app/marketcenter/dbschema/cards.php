<?php
$db['cards']=array (
    'columns' =>
    array (
      'card_no' =>
      array (
        'type' => 'int(8)',
        'required' => true,
        'pkey' => true,
        'label' => app::get('marketcenter')->_('卡劵编号'),
        'editable' => false,
        'extra' => 'auto_increment',
        'in_list' => false,
      ),
      'card_id' =>
      array (
          'type' => 'varchar(50)',
          'label' => app::get('marketcenter')->_('卡劵id'),
          'width' => 200,
          'order' => 10,
          'searchtype' => 'has',
          'filtertype' => 'yes',
          'editable' => false,
          'in_list' => true,
          'default_in_list' => true,
      ),
      'node_id' =>
      array (
          'type' => 'varchar(50)',
          'label' => app::get('marketcenter')->_('节点id'),
          'required' => true,
      ),
      'card_type' =>
        array (
          'type' =>
          array (
            'DISCOUNT' => app::get('marketcenter')->_('折扣卷'),
            'CASH' => app::get('marketcenter')->_('代金卷'),
            'GIFT' => app::get('marketcenter')->_('礼品卷'),
            'GROUPON' => app::get('marketcenter')->_('团购卷'),
            'GENERAL_COUPON ' => app::get('marketcenter')->_('优惠券'),
          ),
          'required' => true,
          'label' => app::get('marketcenter')->_('卡劵类型'),
      ),
      'logo_url' =>
        array (
          'type' => 'varchar(128)',
          'label' => app::get('marketcenter')->_('商户logo'),
        ),
        'code_type' =>
        array (
          'type' =>
          array (
            'CODE_TYPE_TEXT' => app::get('marketcenter')->_('文本'),
            'CODE_TYPE_BARCODE' => app::get('marketcenter')->_('一维码'),
            'CODE_TYPE_QRCODE' => app::get('marketcenter')->_('二维码'),
            'CODE_TYPE_ONLY_QRCODE' => app::get('marketcenter')->_('二维码无code显示'),
            'CODE_TYPE_ONLY_BARCODE ' => app::get('marketcenter')->_('一维码无code显示'),
          ),
          'required' => true,
          'label' => app::get('marketcenter')->_('Code展示类型'),
        ),
        'brand_name' =>
        array (
          'type' => 'varchar(36)',
          'required' => true,
          'label' => app::get('marketcenter')->_('商户名字'),
        ),
        'title' =>
        array (
          'type' => 'varchar(27)',
          'required' => true,
          'label' => app::get('marketcenter')->_('卡券名'),
          'searchtype' => 'has',
          'filtertype' => 'yes',
          'editable' => false,
          'width' => 100,
          'order' => 20,
          'in_list' => true,
          'default_in_list' => true,
        ),
        'sub_title' =>
        array (
          'type' => 'varchar(54)',
          'label' => app::get('marketcenter')->_('券名'),
          'width' => 100,
          'order' => 30,
          'in_list' => true,
          'default_in_list' => true,
        ),
        'color' =>
        array (
          'type' => 'varchar(50)',
          'required' => true,
          'label' => app::get('marketcenter')->_('券颜色'),
        ),
        'notice' =>
        array (
          'type' => 'varchar(48)',
          'required' => true,
          'label' => app::get('marketcenter')->_('卡券使用提醒'),
        ),
        'description' =>
        array (
          'type' => 'varchar(3072)',
          'required' => true,
          'label' => app::get('marketcenter')->_('卡券使用说明'),
        ),
        'quantity' =>
        array (
          'type' => 'int(10)',
          'required' => true,
          'label' => app::get('marketcenter')->_('卡券库存的数量'),
          'width' => 100,
          'order' => 40,
          'in_list' => true,
          'default_in_list' => true,
        ),
        'type' =>
        array (
          'type' =>
          array (
            'DATE_TYPE_FIX_TIME_RANGE ' => app::get('marketcenter')->_('固定日期区间'),
            'DATE_TYPE_FIX_TERM' => app::get('marketcenter')->_('固定时长'),
          ),
          'required' => true,
          'label' => app::get('marketcenter')->_('使用时间的类型'),
        ),
        'begin_timestamp' =>
        array (
          'type' => 'time',
          'label' => app::get('marketcenter')->_('起用时间'),
        ),
        'end_timestamp' =>
        array (
          'type' => 'time',
          'label' => app::get('marketcenter')->_('结束时间'),
        ),
        'fixed_term' =>
        array (
          'type' => 'int(8)',
          'label' => app::get('marketcenter')->_('领取后多少天有效'),
        ),
        'fixed_begin_term' =>
        array (
          'type' => 'int(8)',
          'label' => app::get('marketcenter')->_('领取后多少天生效'),
        ),
        'use_custom_code' =>
        array (
          'type' => 'bool',
          'default' => 'false',
          'label' => app::get('marketcenter')->_('是否自定义Code码'),
        ),
        'bind_openid' =>
        array (
          'type' => 'bool',
          'default' => 'false',
          'label' => app::get('marketcenter')->_('是否指定用户领取'),
        ),
        'service_phone' =>
        array (
          'type' => 'varchar(24)',
          'label' => app::get('marketcenter')->_('客服电话'),
        ),
        'location_id_list' =>
        array (
          'type' => 'longtext',
          'label' => app::get('marketcenter')->_('门店位置ID'),
        ),
        'source' =>
        array (
          'type' => 'varchar(36)',
          'label' => app::get('marketcenter')->_('第三方来源名'),
        ),
        'custom_url_name' =>
        array (
          'type' => 'varchar(15)',
          'label' => app::get('marketcenter')->_('自定义跳转外链的入口名字'),
        ),
        'custom_url' =>
        array (
          'type' => 'varchar(128)',
          'label' => app::get('marketcenter')->_('自定义跳转的URL'),
        ),
        'custom_url_sub_title' =>
        array (
          'type' => 'varchar(18)',
          'label' => app::get('marketcenter')->_('显示在入口右侧的提示语'),
        ),
        'promotion_url_name' =>
        array (
          'type' => 'varchar(15)',
          'label' => app::get('marketcenter')->_('营销场景的自定义入口名称'),
        ),
        'promotion_url' =>
        array (
          'type' => 'varchar(128)',
          'label' => app::get('marketcenter')->_('入口跳转外链的地址链接'),
        ),
        'promotion_url_sub_title' =>
        array (
          'type' => 'varchar(128)',
          'label' => app::get('marketcenter')->_('显示在营销入口右侧的提示语'),
        ),
        'get_limit' =>
        array (
          'type' => 'int(8)',
          'label' => app::get('marketcenter')->_('每人可领券的数量限制'),
        ),
        'can_share' =>
        array (
          'type' => 'bool',
          'default' => 'false',
          'label' => app::get('marketcenter')->_('分享'),
          'width' => 50,
          'order' => 60,
          'in_list' => true,
          'default_in_list' => true,
        ),
        'can_give_friend' =>
        array (
          'type' => 'bool',
          'default' => 'false',
          'label' => app::get('marketcenter')->_('转赠'),
          'width' => 50,
          'order' => 70,
          'in_list' => true,
          'default_in_list' => true,
        ),
        'default_detail' =>
        array (
          'type' => 'varchar(3072)',
          'label' => app::get('marketcenter')->_('优惠券专用，填写优惠详情'),
        ),
        'gift' =>
        array (
          'type' => 'varchar(3072)',
          'label' => app::get('marketcenter')->_('礼品券专用，填写礼品的名称'),
        ),
        'discount' =>
        array (
          'type' => 'int(8)',
          'label' => app::get('marketcenter')->_('折扣券专用，表示打折额度'),
        ),
        'least_cost' =>
        array (
          'type' => 'int(8)',
          'label' => app::get('marketcenter')->_('代金券专用，表示起用金额'),
        ),
        'reduce_cost' =>
        array (
          'type' => 'int(8)',
          'label' => app::get('marketcenter')->_('代金券专用，表示减免金额'),
        ),
        'deal_detail' =>
        array (
          'type' => 'varchar(24)',
          'label' => app::get('marketcenter')->_('团购券专用，团购详情'),
        ),
        'status' =>
        array (
          'type' =>
          array (
            'update ' => app::get('marketcenter')->_('已同步'),
            'unupdate' => app::get('marketcenter')->_('未同步'),
            'dead' => app::get('marketcenter')->_('未启用'),
          ),
          'required' => true,
          'label' => app::get('marketcenter')->_('卡劵状态'),
          'width' => 80,
          'order' => 50,
          'in_list' => true,
          'default_in_list' => true,
        ),
        'create_time' =>
        array (
          'type' => 'time',
          'label' => app::get('marketcenter')->_('创建时间'),
          'width' => 100,
          'order' => 120,
          'in_list' => true,
          'default_in_list' => true,
        ),
        'update_time' =>
        array (
          'type' => 'time',
          'label' => app::get('marketcenter')->_('更新时间'),
        ),
        'creater' =>
        array (
          'type' => 'varchar(50)',
          'label' => app::get('marketcenter')->_('创建人'),
          'width' => 50,
          'order' => 90,
          'searchtype' => 'has',
          'filtertype' => 'yes',
          'editable' => false,
          'in_list' => true,
          'default_in_list' => true,
        ),
        'card_deliver' =>
        array (
          'type' => 'int(8)',
          'label' => app::get('marketcenter')->_('已发送'),
          'width' => 50,
          'order' => 100,
          'in_list' => true,
          'default_in_list' => true,
        ),
        'card_use' =>
        array (
          'type' => 'int(8)',
          'label' => app::get('marketcenter')->_('已使用'),
          'width' => 50,
          'order' => 110,
          'in_list' => true,
          'default_in_list' => true,
        ),
    ),
    'engine' => 'innodb',
    'version' => '$Rev: 42376 $',
    'comment' => app::get('marketcenter')->_('卡券信息'),
);