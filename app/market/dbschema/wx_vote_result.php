
<?php
 
$db['wx_vote_result']=array (
    'columns' => 
    array (
    'result_id' =>
    array(
        'type' => 'int unsigned',
        'required' => true,
        'pkey' => true,
        'extra' => 'auto_increment',
        'editable' => false,
    ),
    'vote_id' =>
    array(
        'type' => 'table:wx_vote@market',
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
    'result_no' =>
    array(
        'type' => 'varchar(32)',
        'required' => false,
        'label' => '答案编号',
        'editable' => false,
        'searchtype' => 'head',
        'filtertype' => 'normal',
        'in_list' => true,
        'default_in_list' => true,
        'width' => 80,
        'order' => 30,
    ),
    'result' =>
    array(
        'type' => 'varchar(500)',
        'required' => false,
        'label' => '回答内容',
        'editable' => false,
        'searchtype' => 'head',
        'filtertype' => 'normal',
        'in_list' => true,
        'default_in_list' => true,
        'width' => 200,
        'order' => 40,
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
    
  'comment' => '微信投票结果',
  'engine' => 'innodb',
  'version' => '$Rev:  $',
);
