<?php
/*
sdb_taocrm_member_orders_day	CREATE TABLE `sdb_taocrm_member_orders_day` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `member_id` int(10) unsigned NOT NULL,
  `pay_amount` decimal(20,2) DEFAULT NULL,
  `finish_amount` decimal(20,2) DEFAULT NULL,
  `day` date DEFAULT NULL,
  `update_time` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_day_memberid` (`day`,`member_id`)
) ENGINE=InnoDB AUTO_INCREMENT=216914 DEFAULT CHARSET=utf8
 */
$db['member_orders_day']=array (
    'columns' =>
    array (
        'id' =>
        array(
            'type' => 'bigint unsigned',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
            'editable' => false,
        ),
        'member_id' =>
        array(
            'type' => 'int(10) unsigned',
        ),
        'pay_amount' =>
        array (
            'type' => 'decimal(20,2)',
        ),
        'finish_amount' =>
        array (
            'type' => 'decimal(20,2)',
        ),
        'day' =>
        array (
            'type' => 'date',
        ),
        'update_time' =>
        array (
            'type' => 'date',
        ),
    ),
    'index' =>
    array(
        'uk_day_memberid' =>
        array(
            'columns' =>
            array(
                'day','member_id'
            ),
            'prefix' => 'unique'
        ),
    ),
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);
