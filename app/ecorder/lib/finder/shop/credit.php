<?php
class ecorder_finder_shop_credit{

    var $addon_cols = "rule_id,start_time,end_time,amount_symbol,min_amount,max_amount,order_type,time_from,time_to,birthday_type,special_point_rule,activity_point_times,birthday_point_times";
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
        $rule_id = $row[$this->col_prefix.'rule_id'];
        $special_point_rule = $row[$this->col_prefix.'special_point_rule'];
        if($special_point_rule){
            $button1 = '<a href="index.php?app=ecorder&ctl=admin_shop_credit&act=addnew_special&p[0]='.$rule_id.'&finder_id='.$finder_id.'" target="dialog::{width:680,height:270,title:\'修改特殊积分规则\'}">编辑</a>';
        }else{
        $button1 = '<a href="index.php?app=ecorder&ctl=admin_shop_credit&act=addnew&p[0]='.$rule_id.'&finder_id='.$finder_id.'" target="dialog::{width:680,height:270,title:\'修改积分规则\'}">编辑</a>';
        }

        return $button1;
    }
    
    var $column_amount = "条件";
    var $column_amount_width = 200;
    var $column_amount_order = 30;
    function column_amount($row){
        $special_point_rule = $row[$this->col_prefix.'special_point_rule'];
        if($special_point_rule){
            $rule = explode(',',$special_point_rule);
            $time_from = $row[$this->col_prefix.'time_from'];
            $time_to = $row[$this->col_prefix.'time_to'];
            $activity_point_times = $row[$this->col_prefix.'activity_point_times'];
            $birthday_point_times = $row[$this->col_prefix.'birthday_point_times'];
            $birthday_type = $row[$this->col_prefix.'birthday_type'];
            $birthday_type_name = array(1=>'当天',2=>'当月');
            $button1 = '';
            if(in_array('1',$rule)){//活动送积分
                $button1 .= '活动'.$time_from.'至'.$time_to.'送'.$activity_point_times.'倍积分<br>';
            }
            if(in_array('2',$rule)){//生日送积分
                $button1 .= '生日'.$birthday_type_name[$birthday_type].'送'.$birthday_point_times.'倍积分';
            }
        }else{
        $finder_id = $_GET['_finder']['finder_id'];
        $amount_symbol = $row[$this->col_prefix.'amount_symbol'];
        $order_type = $row[$this->col_prefix.'order_type'];
        $min_amount = $row[$this->col_prefix.'min_amount'];
        $max_amount = $row[$this->col_prefix.'max_amount'];

        $button1 = $this->sign_arr[$amount_symbol];
        
        if($order_type == 'all'){
            $button1 = '累计付款'.$button1;
        }else{
            $button1 = '单笔付款'.$button1;
        }
        
        if($amount_symbol == 'between') {
            $button1 .= ': '.$min_amount.' ~ '.$max_amount.' 元';
        }elseif($amount_symbol != 'unlimited') {
            $button1 .= ': '.$min_amount.' 元';
            }
        }
        return $button1;
    }
    
    /*
    var $column_valid = "有效期";
    var $column_valid_width = 160;
    var $column_valid_order = 68;
    function column_valid($row){
        $finder_id = $_GET['_finder']['finder_id'];
        $start_time = $row[$this->col_prefix.'start_time'];
        $end_time = $row[$this->col_prefix.'end_time'];

        if($start_time && $end_time) {
            $button1 = date('Y-m-d',$start_time).' - '.date('Y-m-d',$end_time);
        }else{
            $button1 = '长期有效';
        }
        return $button1;
    }
    */
}

