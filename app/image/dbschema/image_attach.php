<?php
/**
 * ShopEx licence
 *
 * @category ecos
 * @package image.dbschema
 * @author shopex ecstore dev dev@shopex.cn
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 * @version 0.1
 */
 
$db['image_attach']=array (
  'columns' => 
  array (
    'attach_id' => array (
      'type' => 'number',
      'required' => true,
      'editable' => false,
      'pkey'=>true,
      'extra' => 'auto_increment',
    ),
    'target_id' => array (
      'type' => 'number',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
    'target_type' => array (
      'type' => 'varchar(20)',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
    'image_id' => array (
      'type' => 'table:image',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
    'last_modified'=>array(
      'label'=>app::get('image')->_('更新时间'),
      'type' => 'last_modify',
      'width'=>180,
      'required' => true,
      'default' => 0,
      'editable' => false,
      'in_list'=>true,
      'default_in_list'=>true,
    ),
  ),
  'index' =>
  array (
    'index_1' =>
    array (
      'columns' =>
      array (
        0 => 'target_id',
        1 => 'target_type',
      ),
    ),
  ),
  'version' => '$Rev$',
);
