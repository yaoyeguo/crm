<?php 

class market_finder_fx_activity{

    var $addon_cols = 'is_active,type,control_group,template_id,template_id_b,pay_type';
	
    var $column_edit = '活动步骤';
    var $column_edit_width = 120;
    var $column_edit_order_field = "is_active";
    var $column_edit_order = 'COLUMN_IN_HEAD';
    function column_edit($row)
    {    
        if(strstr($row[$this->col_prefix.'type'], 'sms')) $act_type = 'sms';
        if(strstr($row[$this->col_prefix.'type'], 'edm')) $act_type = 'edm';
        $url = "index.php?app=market&ctl=admin_fx_activity&act=editer_data";
        //echo($url);die();
        
        if ($row['is_active']=='sel_member') {
            return '<a href="'.$url.'&p[0]='.$row['activity_id'].'&p[1]='.$row['is_active'].'&p[selectmember]=selecemember'.'&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '" target="dialog::{title:\'创建营销活动\', width:700, height:355}"><img border=0 src="'.app::get('market')->res_url.'/img/2.gif" /> 等待选择客户</a>';
        }elseif ($row['is_active']=='sel_template') {
            return '<a href="'.$url.'&p[0]='.$row['activity_id'].'&p[1]='.$row['is_active'].'&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '" target="dialog::{title:\'创建营销活动\', width:700, height:355}"><img border=0 src="'.app::get('market')->res_url.'/img/3.gif" /> 等待选择模板</a>';
        }elseif($row['is_active']=='wait_exec') {
            return '<a href="'.$url.'&p[0]='.$row['activity_id'].'&p[1]='.$row['is_active'].'&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '" target="dialog::{title:\'创建营销活动\', width:700, height:355}"><img border=0 src="'.app::get('market')->res_url.'/img/4.gif" /> 等待执行</a>';
        }elseif ($row['is_active']=='finish'){
            return '<span style="color:#0000FF;">已经完成</span>';
        }elseif ($row['is_active']=='dead') {
            return '<span style="color:#A8A8A8;">已经作废</span>';
        }elseif ($row['is_active']=='execute') {
            return '<span style="color:#CC00FF;">正在执行</span>';
        }
    }
    
	
    var $column_invalid='操作';
    function column_invalid($row)
    {
        $addon_cols = 'sale_money,sale_money';
        $outer_id = $row[$this->col_prefix.'sale_money'];
        $payType = trim($row[$this->col_prefix.'pay_type']);
        $disp = '';
        
        if(strstr($row[$this->col_prefix.'type'], 'sms')) $act_type = 'sms';
        if(strstr($row[$this->col_prefix.'type'], 'edm')) $act_type = 'edm';
        $url = "index.php?app=market&ctl=admin_fx_activity";
        
        if ($row['is_active']=='dead'){
            $disp =  '已作废';
        }else {
        	if($row['is_active']=='finish'){
                $is_active = 'wait_exec';
                if ($payType == 'market') {
                    $disp =  '已完成';
                }
                else {
                    //$disp = '<a href="'.$url.'&act=repeat_active&p[0]='.$row['activity_id'].'&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '" target="dialog::{title:\''.app::get('taocrm')->_('重复营销').'\', width:700, height:355}">重复营销</a>';
                }
                
        	}elseif ($row['is_active'] == 'execute'){
        	    $disp =  '活动执行中';
        	}
        	else{
                $disp =  '<a href="'.$url.'&act=invalid_active&p[0]='.$row['activity_id'].'&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '" target="dialog::{title:\''.app::get('taocrm')->_('是否删除该活动？').'\', width:400, height:150}"><img src="/app/plugins/statics/del.gif" alt="作废" /></a>';
        	}
        }
        return $disp;
    }
    
    public $column_isActiveGroup = '是否开启对照组';
    public $column_isActiveGroup_width  = 40;
    public function column_isActiveGroup($row)
    {
        $controlGroup = $row[$this->col_prefix.'control_group'];
        $templateId = $row[$this->col_prefix.'template_id'];
        $templateIdB = $row[$this->col_prefix.'template_id_b'];
        if (strtolower($controlGroup) == 'yes' || ($templateId != 0 && $templateIdB != 0 )) {
            return '<span style="color:#0000FF;">是</span>';
        }
        else {
            return '否';
        }
    }
    
}
