<?php
class ecorder_finder_shop_gift{

    var $addon_cols = '';
    
    var $column_edit = "操作";
    var $column_edit_width = 90;
    var $column_edit_order = COLUMN_IN_HEAD;
    function column_edit($row)
    {
        $finder_id = $_GET['_finder']['finder_id'];
        $gift_id = $row[$this->col_prefix.'id'];

        $button1 = '<a href="index.php?app=ecorder&ctl=admin_gift_list&act=edit&p[0]='.$gift_id.'&finder_id='.$finder_id.'" target="dialog::{width:600,height:250,title:\'赠品设置\'}">设置赠品数量</a>';

        return $button1;
    }
  
}
