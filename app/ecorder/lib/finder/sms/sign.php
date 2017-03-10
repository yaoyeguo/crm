<?php
class ecorder_finder_sms_sign
{
    var $addon_cols = "shop_ids,review";
    
    var $column_edit = "操作";
    var $column_edit_width = 80;
    var $column_edit_order = COLUMN_IN_HEAD;
    function column_edit($row)
    {
        $finder_id = $_GET['_finder']['finder_id'];
        $button1 = '<a href="index.php?app=ecorder&ctl=admin_sms_sign&act=edit&sign_id='.$row['sign_id'].'" target="dialog::{width:600,height:300,title:\'修改签名\'}">修改</a>';
        return $button1;
    }
    
    var $column_shop_id = "适用店铺";
    var $column_shop_id_width = 180;
    var $column_shop_id_order = 80;
    function column_shop_id($row)
    {
        $finder_id = $_GET['_finder']['finder_id'];
        $shop_ids = $row[$this->col_prefix.'shop_ids'];
        if($shop_ids){
            $shop_ids = substr($shop_ids,1);
            $shop_ids = explode(',', $shop_ids);
            $rs_shop = app::get('ecorder')->model('shop')->getList('name', array('shop_id'=>$shop_ids));
            $button1 = $rs_shop[0]['name'].'...('.count($rs_shop).'个店铺)';
        }else{
            $button1 = '-';
        }

        return $button1;
    }
    
    var $column_review = "审核状态";
    var $column_review_width = 80;
    var $column_review_order = 90;
    function column_review($row)
    {
        $finder_id = $_GET['_finder']['finder_id'];
        $review = $row[$this->col_prefix.'review'];
        
        if($review){
            $button1 = '<img src="'.kernel::base_url(0).'/app/taocrm/statics/tick_ok.gif" />';
        }else{
            $button1 = '<img src="'.kernel::base_url(0).'/app/taocrm/statics/tick_close.gif" />';   
        }
        return $button1;
    }
}
