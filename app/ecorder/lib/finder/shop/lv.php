<?php
class ecorder_finder_shop_lv{

    var $addon_cols = 'lv_id,is_default,amount_symbol,min_amount,max_amount,buy_times_symbol,min_buy_times,max_buy_times';
    var $sign_arr = array(
                        'unlimited' => '无限制',
                        'gthan' => '大于',
                        'sthan' => '小于',
                        'equal' => '等于',
                        'gethan' => '大于等于',
                        'sethan' => '小于等于',
                        'between' => '介于'
                    );
    
    var $column_edit = "操作";
    var $column_edit_width = 80;
    var $column_edit_order = COLUMN_IN_HEAD;
    function column_edit($row){
        $finder_id = $_GET['_finder']['finder_id'];
        $lv_id = $row[$this->col_prefix.'lv_id'];

        $button1 = '<a href="index.php?app=ecorder&ctl=admin_shop_lv&act=addnew&p[0]='.$lv_id.'&finder_id='.$finder_id.'" target="dialog::{width:680,height:270,title:\'修改客户等级\'}">编辑</a>';

        return $button1;
    }
    
    var $column_default = "默认等级";
    var $column_default_width = 80;
    var $column_default_order = 20;
    function column_default($row){
        $is_default = $row[$this->col_prefix.'is_default'];

        if($is_default == '1') {
            $button1 = '<font color="red">是</font>';
        }
        return $button1;
    }
    
    var $column_amount = "消费金额";
    var $column_amount_width = 150;
    var $column_amount_order = 30;
    function column_amount($row){
        $is_default = $row[$this->col_prefix.'is_default'];
        if($is_default == '1') {return false;}
    
        $amount_symbol = $row[$this->col_prefix.'amount_symbol'];
        $min_amount = $row[$this->col_prefix.'min_amount'];
        $max_amount = $row[$this->col_prefix.'max_amount'];

        $button1 = $this->sign_arr[$amount_symbol];
        if($amount_symbol == 'between') {
            $button1 .= ': '.$min_amount.' ~ '.$max_amount.' 元';
        }elseif($amount_symbol != 'unlimited') {
            $button1 .= ': '.$min_amount.' 元';
        }
        return $button1;
    }  

    var $column_buytimes = "消费次数";
    var $column_buytimes_width = 150;
    var $column_buytimes_order = 35;
    function column_buytimes($row){
        $is_default = $row[$this->col_prefix.'is_default'];
        if($is_default == '1') {return false;}
    
        $buy_times_symbol = $row[$this->col_prefix.'buy_times_symbol'];
        $min_buy_times = $row[$this->col_prefix.'min_buy_times'];
        $max_buy_times = $row[$this->col_prefix.'max_buy_times'];

        $button1 = $this->sign_arr[$buy_times_symbol];
        if($buy_times_symbol == 'between') {
            $button1 .= ': '.$min_buy_times.' ~ '.$max_buy_times.' 次';
        }elseif($buy_times_symbol != 'unlimited') {
            $button1 .= ': '.$min_buy_times.' 次';
        }
        return $button1;
    } 
    
}


