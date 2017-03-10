<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['chat_logs']=array (
  'columns' => 
  array (
    'log_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
    ),
    'chat_length' => 
    array (
      'type' => 'number',
      'required' => false,
      'label' => '聊天时长(s)',
    ),
    'start_time' => 
    array (
      'type' => 'time',
      'required' => false,
      'label' => '开始时间',
    ),
    'end_time' => 
    array (
      'type' => 'time',
      'required' => false,
      'label' => '结束时间',
    ),
    'eval_code' => 
    array (
      'type' => 'varchar(32)',
      'required' => false,
      'label' => '评价',
    ),
    'agent_id' => 
    array (
      'type' => 'table:shop_agents@ecorder',
      'required' => false,
      'label' => '客服',
    ),
    'member_id' => 
    array (
      'type' => 'table:members@taocrm',
      'required' => false,
      'label' => '客户编号',
    ),
    'buyer_nick' => 
    array (
      'type' => 'varchar(32)',
      'required' => false,
      'label' => '买家',
    ),
    'chat_content' => 
    array (
      'type' => 'text',
      'required' => false,
      'label' => '聊天内容',
    ),
    'chat_date' => 
    array (
      'type' => 'time',
      'required' => false,
      'label' => '聊天日期',
    ),
    'create_time' => 
    array (
      'type' => 'time',
      'required' => false,
      'label' => '创建时间',
    ),
  ),
  'index' => 
      array (
        'ind_log_id' => 
        array (
          'columns' => 
          array (
            0 => 'log_id',
          ),
        ),
    ),
  'ignore_cache' => true,
  'engine' => 'innodb',
);

