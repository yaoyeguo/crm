<?php
class taocrm_finder_member_caselog{   
 
    var $addon_cols = "mobile,member_id";
 
    var $column_edit = '操作';
    var $column_edit_width = 80;
    var $column_edit_order = 'COLUMN_IN_HEAD';
    function column_edit($row)
    {
        $act =  '<a href="index.php?app=taocrm&ctl=admin_member_caselog&act=caselog_edit&id='.$row['id'].'&member_id='.$row['member_id'].'" target="dialog::{title:\''.app::get('taocrm')->_('修改').'\', width:650, height:300}">'.app::get('taocrm')->_('修改').'</a>';
        $mobile = $row[$this->col_prefix.'mobile'];
        $member_id = $row[$this->col_prefix.'member_id'];
        if($mobile){
            $act .= ' | <a href="index.php?app=market&ctl=admin_callcenter_callin&act=send_sms&name='.$row['customer'].'&mobile='.$mobile.'&member_id='.$member_id.'" target="dialog::{title:\''.app::get('taocrm')->_('发送短信').'\', width:650, height:220}">发短信</a>';
        }
        return $act;
    }
    
    var $column_mobile = '手机号码';
    var $column_mobile_width = 150;
    var $column_mobile_order = 45;
    function column_mobile($row)
    {
        $act = '';
        $mobile = $row[$this->col_prefix.'mobile'];
        $member_id = $row[$this->col_prefix.'member_id'];
        if($mobile){
            $act .= $mobile;
        }
        return $act;
    }    

    var $column_uname = '客户帐号';
    var $column_uname_width = 100;
    var $column_uname_order = 45;
    function column_uname($row)
    {
        return $row['uname'];;
    }
    
}
