<?php
class ecorder_finder_shop_vcard{

    var $addon_cols = "shop_id,shop_type,node_id,name,vcard_id,vcard_url";
    var $column_edit = "操作";
    var $column_edit_width = "100";
    var $column_edit_order = COLUMN_IN_HEAD;
    function column_edit($row){
        $finder_id = $_GET['_finder']['finder_id'];
        $shop_type = $row[$this->col_prefix.'shop_type'];
        $node_id = $row[$this->col_prefix.'node_id'];
        $shop_id = $row[$this->col_prefix.'shop_id'];

        $button1 = '<a href="index.php?app=ecorder&ctl=admin_shop_vcard&act=editterminal&p[0]='.$shop_id.'&finder_id='.$finder_id.'" target="dialog::{width:640,height:320,title:\'设置店铺名片\'}">设置</a>';
        
        return $button1.$button3.$button2;
    }
    
    var $column_url = "名片短地址";
    var $column_url_width = 300;
    var $column_url_order = 100;
    function column_url($row){
        $vcard_id = $row[$this->col_prefix.'vcard_id'];
        if($vcard_id>0){
            $vcard_url = $row[$this->col_prefix.'vcard_url'];
        }else{
            $vcard_url = '未设置';
        }
        return $vcard_url;
    }
}
