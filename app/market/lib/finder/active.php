<?php 

class market_finder_active {

    var $addon_cols = 'is_active,type,control_group,template_id,template_id_b,pay_type,is_timing,plan_send_time';
    
    var $column_invalid='操作';
    var $column_invalid_width = 80;
    var $column_invalid_order = 'COLUMN_IN_HEAD';
    function column_invalid($row)
    {
        $addon_cols = 'sale_money,sale_money';
        $row['is_active'] = $row[$this->col_prefix.'is_active'];
        $outer_id = $row[$this->col_prefix.'sale_money'];
        $payType = trim($row[$this->col_prefix.'pay_type']);
        $is_timing = $row[$this->col_prefix.'is_timing'];
        $plan_send_time = $row[$this->col_prefix.'plan_send_time'];
        $disp = '';
        
        if(strstr($row[$this->col_prefix.'type'], 'edm')) {
            $act_type = 'edm';
        }else{
            $act_type = 'sms';
        }
        $url = "index.php?app=market&ctl=admin_active_{$act_type}";
        
        if ($row['is_active']=='dead'){
            $disp =  '已作废';
        }else {
        	if($row['is_active']=='finish'){
                $is_active = 'wait_exec';
                if ($payType == 'market') {
                    $disp =  '已完成';
                }else{
                    $disp = '<a href="'.$url.'&act=repeat_active&p[0]='.$row['active_id'].'&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '" target="dialog::{title:\''.app::get('taocrm')->_('重复营销').'\', width:700, height:355}">重复营销</a>';
                }
        	}elseif($row['is_active'] == 'execute' && ($is_timing==0 or $plan_send_time<(time()+30*60)) ){
        	    $disp =  '活动执行中';
        	}else{
                $disp =  '<a href="'.$url.'&act=invalid_active&p[0]='.$row['active_id'].'&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '" target="dialog::{title:\''.app::get('taocrm')->_('是否删除该活动？').'\', width:400, height:150}" style="color:#F30;">删除</a>';
        	}
        }
        return $disp;
    }
	
    var $column_edit = '活动步骤';
    var $column_edit_width = 100;
    var $column_edit_order = 5;
    function column_edit($row)
    {    
        $row['is_active'] = $row[$this->col_prefix.'is_active'];
        $is_timing = $row[$this->col_prefix.'is_timing'];
        $plan_send_time = $row[$this->col_prefix.'plan_send_time'];
        if(strstr($row[$this->col_prefix.'type'], 'sms')) $act_type = 'sms';
        if(strstr($row[$this->col_prefix.'type'], 'edm')) $act_type = 'edm';
        $url = "index.php?app=market&ctl=admin_active_{$act_type}&act=editer_data";
        $pic_res_url = app::get('market')->res_url.'/img/';
        $edit_link = '<a href="'.$url.'&p[0]='.$row['active_id'].'&p[1]='.$row['is_active'].'&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '" target="dialog::{title:\'创建营销活动\', width:700, height:355}">';
        
        switch($row['is_active']){
            case 'sel_member':
                $btn = '<a href="'.$url.'&p[0]='.$row['active_id'].'&p[1]='.$row['is_active'].'&p[selectmember]=selecemember'.'&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '" target="dialog::{title:\'创建营销活动\', width:700, height:355}"><img border=0 src="'.$pic_res_url.'2.gif" /> 选择客户</a>';
            break;
            
            case 'sel_template':
                $btn = $edit_link.'<img border=0 src="'.$pic_res_url.'3.gif" /> 选择模板</a>';
            break;
            
            case 'wait_exec':
                $btn = $edit_link.'<img border=0 src="'.$pic_res_url.'4.gif" /> 等待执行</a>';
            break;
            
            case 'finish':
                $btn = '<span style="color:#AAA;"><img border=0 src="'.$pic_res_url.'7.gif" /> 已经完成</span>';
            break;
            
            case 'dead':
                $btn = '<span style="color:#A8A8A8;">已经作废</span>';
            break;
            
            case 'execute':
                if($is_timing==1 && $plan_send_time>(time()+30*60)){
                    $btn = $edit_link.'<span style="color:#5A8D18;"><img border=0 src="'.$pic_res_url.'5.gif" /> 定时任务</span></a>';
                }else{
                    $btn = '<span style="color:#97621D;"><img border=0 src="'.$pic_res_url.'6.gif" /> 正在执行</span>';
                }
            break;
            
        }        
        return $btn;
    }    
    
    public $column_isActiveGroup = '是否对照组';
    public $column_isActiveGroup_width  = 80;
    public function column_isActiveGroup($row)
    {
        $controlGroup = $row[$this->col_prefix.'control_group'];
        $templateId = $row[$this->col_prefix.'template_id'];
        $templateIdB = $row[$this->col_prefix.'template_id_b'];
        if(strtolower($controlGroup) == 'yes' || ($templateId != 0 && $templateIdB != 0 )){
            return '<span style="color:#0000FF;">是</span>';
        }else{
            return '否';
        }
    }
    
    var $detail_active_info = '活动快照';
    public function detail_active_info($id)
    {
        $app = app::get('market');
        $render = $app->render();
        $active = $app->model('active')->dump($id);
        
        if($active){
            $active['start_time'] = date('Y-m-d', $active['start_time']);
            $active['end_time'] = date('Y-m-d', $active['end_time']);
            $active['plan_send_time'] = date('Y-m-d H:i:s', $active['plan_send_time']);
            
            if($active['exec_time'] == 0){
                $active['exec_time'] = '<font color=red>未执行</font>';
            }else{
                $active['exec_time'] = date('Y-m-d H:i:s', $active['exec_time']);
            }
            
            $active['exclude_filter'] = '无';
            if($active['filter_mem']){
                $filter_mem = unserialize($active['filter_mem']);
                //echo('<pre>');var_dump($filter_mem);
                $active['include_filter'] = $this->get_include_filter($filter_mem);
                $active['exclude_filter'] = $this->get_exclude_filter($filter_mem);
            }elseif($active['filter_sql']){
                $active['include_filter'] = '快捷营销';
            }elseif($active['report_filter']){
                $active['include_filter'] = '报表营销';
            }elseif($active['member_list']){
                $member_list = unserialize($active['member_list']);
                if(stristr($member_list[0],'group_id:')){
                    $group = app::get('taocrm')->model('member_group')->dump(str_replace('group_id:','',$member_list[0]));
                    $active['include_filter'] = '会员分组：'.$group['group_name'];
                }else{
                    $active['include_filter'] = '直选会员';
                }
            }
        }
        
        $render->pagedata['active'] = $active;
        return $render->fetch('admin/active/monitor/snap_shoot.html');
    }
    
    function get_include_filter($filter)
    {
        $filter_arr = array();
        $keys = array(
            'last_buy_time' => '最后下单时间',
            'finish_orders' => '成功的订单数',
            'total_amount' => '订单总金额',
            'buy_freq' => '购买频次',
            'buy_products' => '购买商品总数',
            'create_time' => '下单时间',
            'points' => '客户积分',
            'goods_id' => '购买过的商品',
            'regions_id' => '收货区域',
            'lv_id' => '客户等级',
        );
        $signs = array(
            'nequal' => '等于',
            'than' => '大于',
            'bthan' => '大于等于',
            'sthan' => '小于等于',
            'lthan' => '小于',
            'between' => '介于',
        );
        foreach($filter['filter'] as $k=>$v){
            switch($k){
                case 'goods_id':
                    $filter_arr[] = '购买过指定商品';
                break;
                
                case 'lv_id':
                    if($v) $filter_arr[] = '指定客户等级:'.$v;
                break;
                
                case 'regions_id':
                    $filter_arr[] = '指定收货区域';
                break;
                
                default:
                    if($v['sign']=='between'){
                        $filter_arr[] = $keys[$k].' '.$signs[$v['sign']].' '.$v['min_val'].'~'.$v['max_val'];
                    }elseif(is_array($v) && $v['sign']){
                        $filter_arr[] = $keys[$k].' '.$signs[$v['sign']].' '.$v['min_val'];
                    }
                break;
            }
        }
        $filter_str = implode('<br/>', $filter_arr);
        if(!$filter_str) $filter_str = '无';
        return $filter_str;
    }
    
    function get_exclude_filter($filter)
    {
        $filter_arr = array();
        if(in_array('2', $filter['exclude_filter'])){
            if($filter['exclude_hours']){
                $filter_arr[] = $filter['exclude_hours'].'小时内营销过的客户'; 
            }
        }
        
        if(in_array('3', $filter['exclude_filter'])){
            if($filter['exclude_tag_id']){
                $filter_arr[] = '标签组 '.$filter['exclude_tag'].'的客户'; 
            }
        }
        
        if(in_array('4', $filter['exclude_filter'])){
            if($filter['exclude_active_id']){
                $filter_arr[] = '参加过 '.$filter['exclude_active'].' 营销活动的客户'; 
            }
        }
        
        if(!$filter_arr) $filter_arr[] = '无';
        return implode('<br/>', $filter_arr);
    }
}
