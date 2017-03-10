
<?php
 
$db['wx_due_orders']=array (
    'columns' => 
    array (
    'order_id' =>
    array(
        'type' => 'int unsigned',
        'required' => true,
        'pkey' => true,
        'extra' => 'auto_increment',
        'editable' => false,
    ),
    'due_id' =>
    array(
        'type' => 'table:wx_due@market',
        'required' => false,
        'label' => '活动编号',
        'editable' => false,
        'searchtype' => 'head',
        'filtertype' => 'normal',
        'in_list' => true,
        'default_in_list' => true,
        'width' => 100,
        'order' => 10,
    ),
    'order_content' =>
    array(
        'type' => 'varchar(500)',
        'required' => false,
        'label' => '预约内容',
        'editable' => false,
        'searchtype' => 'head',
        'filtertype' => 'normal',
        'in_list' => true,
        'default_in_list' => true,
        'width' => 80,
        'order' => 30,
    ),
    'wx_id' =>
    array(
        'type' => 'varchar(100)',
        'required' => false,
        'label' => '微信用户识别码',
        'editable' => false,
        'searchtype' => 'head',
        'filtertype' => 'normal',
        'in_list' => true,
        'default_in_list' => true,
        'width' => 120,
        'order' => 60,
    ),
    'created' => 
    array (
      'type' => 'datetime',
      'label' => '创建时间',
      'width' => 130,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'filtertype' => 'normal',
      'filterdefault' =>true,
      'order' => 100,
    ),
    'truename' =>
    array(
        'type' => 'varchar(50)',
        'required' => false,
        'label' => '姓名',
        'editable' => false,
        'searchtype' => 'head',
        'filtertype' => 'normal',
        'in_list' => true,
        'default_in_list' => true,
        'width' => 120,
        'order' => 60,
    ),
    'size' =>
    array(
        'type' => 'varchar(50)',
        'required' => false,
        'label' => '颜色',
        'editable' => false,
        'searchtype' => 'head',
        'filtertype' => 'normal',
        'in_list' => false,
        'default_in_list' => false,
        'width' => 120,
        'order' => 60,
    ),
    'color' =>
    array(
        'type' => 'varchar(50)',
        'required' => false,
        'label' => '尺码',
        'editable' => false,
        'searchtype' => 'head',
        'filtertype' => 'normal',
        'in_list' => false,
        'default_in_list' => false,
        'width' => 120,
        'order' => 60,
    ),
    'mobile' =>
    array(
        'type' => 'varchar(50)',
        'required' => false,
        'label' => '手机号码',
        'editable' => false,
        'searchtype' => 'head',
        'filtertype' => 'normal',
        'in_list' => true,
        'default_in_list' => true,
        'width' => 120,
        'order' => 60,
    ),
    'addr' =>
    array(
        'type' => 'varchar(100)',
        'required' => false,
        'label' => '详细地址',
        'editable' => false,
        'searchtype' => 'head',
        'filtertype' => 'normal',
        'in_list' => true,
        'default_in_list' => true,
        'width' => 120,
        'order' => 60,
    ),
    'num' =>
    array(
        'type' => 'int(11)',
        'required' => false,
        'label' => '数量',
        'editable' => false,
        'searchtype' => 'head',
        'filtertype' => 'normal',
        'in_list' => true,
        'default_in_list' => true,
        'width' => 120,
        'order' => 60,
    ),
    'log' =>
    array(
        'type' => 'text',
        'required' => false,
        'label' => '请求字符串',
        'editable' => false,
        'searchtype' => 'head',
        'filtertype' => 'normal',
        'in_list' => true,
        'default_in_list' => true,
        'width' => 120,
        'order' => 60,
    ),
  ),
  'index' =>
    array(
    	 'ind_wx_id' =>
        array(
            'columns' =>
            array(
                0 => 'wx_id',
            ),
        ),
    ),
    
  'comment' => '微信预约订单',
  'engine' => 'innodb',
  'version' => '$Rev:  $',
);
