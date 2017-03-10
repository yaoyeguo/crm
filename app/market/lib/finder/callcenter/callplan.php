<?php

class market_finder_callcenter_callplan {

    var $addon_cols = "status,err_msg,assign_users";

    var $column_edit = '操作';
    var $column_edit_width = 60;
    var $column_edit_order = COLUMN_IN_HEAD;
    function column_edit($row) {
        $finder_id = $_GET['_finder']['finder_id'];
        $result = '';
        $callplan_id = $row['callplan_id'];
        $status = $row[$this->col_prefix.'status'];
        $err_msg = $row[$this->col_prefix.'err_msg'];
        $assign_users = ';'.$row[$this->col_prefix.'assign_users'].';';
        $user_name = ';'.kernel::single('desktop_user')->get_name().';';
        
        //工作台显示拨打
        if($_GET['ctl']=='admin_callcenter_workspace'){
            if($status==1 && $err_msg==''){
                //判断是否分配给当前用户
                if(stristr($assign_users,$user_name)){
                    $result .= '<a href="index.php?app=market&ctl=admin_callcenter_callin&act=callplan&callplan_id='.$callplan_id.'&finder_id='.$finder_id.'" target="dialog::{width:1000,height:450,title:\'拨打\'}">拨打</a>';
                }else{
                    $result .= '-';
                }
            }
        //管理员显示管理
        }else{
            $result = '<a href="index.php?app=market&ctl=admin_callcenter_callplan&act=edit&p[0]='.$callplan_id.'&finder_id='.$finder_id.'" target="dialog::{width:1000,height:450,title:\'修改\'}">修改</a>';
        }
        return $result;
    }
    
    var $column_status = '状态';
    var $column_status_order = 5;
    var $column_status_width = 80;
    function column_status($row) {
        $status = $row[$this->col_prefix.'status'];
        $err_msg = $row[$this->col_prefix.'err_msg'];
        
        if($err_msg && $err_msg!=''){
            $status = $err_msg;
        }else{
            $status = ($status==1) ? '开启':'关闭';
        }
        return $status;
    }
    
}
