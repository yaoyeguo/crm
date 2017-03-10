<?php
class taocrm_finder_member_level{
    var $addon_cols = "rule_type,rule_point_month,rule_point_min,rule_point_max,rule_amount_condition,rule_amount_min,rule_amount_max,rule_count_month,rule_count_condition,rule_count_min,rule_count_max,rule_select";

    var $column_level_rule = "等级规则";
    var $column_level_rule_width = 300;
    var $column_level_rule_order = 15;
    function column_level_rule($row)
    {
        if($row[$this->col_prefix.'rule_type'] == 'point')
        {
                $str1 = '';
                $row[$this->col_prefix.'rule_point_month'] > 0 && $str1 .= $row[$this->col_prefix.'rule_point_month'].'个月内,';
                $str1 .= '积分是'.$row[$this->col_prefix.'rule_point_min'].'到'.$row[$this->col_prefix.'rule_point_max'].'之间';
                $text = $str1;
        }
        else
        {
        if($row[$this->col_prefix.'rule_amount_condition'] != 'nolimit')
        {
            $str1 = '';
            $row[$this->col_prefix.'rule_amount_month'] > 0 && $str1 .= $row[$this->col_prefix.'rule_amount_month'].'个月内,';
            $str1 .= '成功交易金额是'.$row[$this->col_prefix.'rule_amount_min'].'到'.$row[$this->col_prefix.'rule_amount_max'].'元之间';
        }

        if($row[$this->col_prefix.'rule_count_condition'] != 'nolimit')
        {
            $str2 = '';
            $row[$this->col_prefix.'rule_count_month'] > 0 && $str2.=  $row[$this->col_prefix.'rule_count_month'].'个月内';
            $str2.='成功交易次数'.$row[$this->col_prefix.'rule_count_min'].'到'.$row[$this->col_prefix.'rule_count_max'].'次之间';
        }

        if(isset($str1) && isset($str2))
            $text = $row[$this->col_prefix.'rule_select'] == 'or' ? $str1 .'或'. $str2 : $str1 .'且'. $str2;
        elseif(isset($str1))
            $text = $str1;
        elseif(isset($str2))
            $text = $str2;
        else
            $text = '规则有误';
        }
        return $text;
    }

    var $column_edit = "操作";
    var $column_edit_width = 80;
    var $column_edit_order = COLUMN_IN_HEAD;
    function column_edit($row)
    {
        $id = $row['level_id'];

        $log_mod = app::get('taocrm')->model('members_rule_log');
        $sql = "select * from sdb_taocrm_members_rule_log order by create_time desc limit 1;";
        $rule = $log_mod->db->select($sql);
        $rule = current($rule);

        if($rule && $rule['type'] != $row[$this->col_prefix.'rule_type']){
            return  $button1 = '不可用';
        }else{
            return  $button1 = '<a href="index.php?app=taocrm&ctl=admin_member_level&act=level_edit&item_id='.$id.'" target="dialog::{width:650,height:320,title:\'编辑\'}">编辑</a>';
        }
    }

}
