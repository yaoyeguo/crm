<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class ectools_finder_payment_cfgs{

    var $column_control = '配置';
    function column_control($row){
        return '<a target="dialog::{width:0.6,height:0.7}" href="index.php?app=ectools&ctl=payment_cfgs&act=setting&p[0]='.$row['app_class'].'&finder_id='. $_GET['_finder']['finder_id'] . '">'.app::get('ectools')->_('配置').'</a>';
    }

}
