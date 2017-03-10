<?php
class market_mdl_active extends dbeav_model {

    public function modifier_start_time($row){
        $date = date("Y-m-d",$row);
        return $date ;
    }

    public function modifier_end_time($row){
        $date = date("Y-m-d",$row);
        return $date ;
    }

    //根据活动ID查询客户总数
    public function get_member_count($active_id)
    {
        $count = 0;
        $rs = $this->dump($active_id);
        if($rs['member_list']){
            $member_list = unserialize($rs['member_list']);
            if(is_numeric($member_list[0])){
                $count = sizeof($member_list);
            }else{
                //获取自定义分组客户数
                $group_id = intval(str_replace('group_id:','',$member_list[0]));
                $filter_mem = app::get('taocrm')->model('member_group')->getMemberList($group_id,'filter');
                $count = app::get('taocrm')->model('member_analysis')->count($filter_mem);
                //echo('<pre>');print_r($count);
            }

        }elseif($rs['filter_mem']){
            $filter_mem = unserialize($rs['filter_mem']);
            //获取自定义条件客户数
            $oMemberGroup = app::get('taocrm')->model('member_group');
            $oMemberAnalysis = app::get('taocrm')->model('member_analysis');

            //转换过滤条件
            $filter = $oMemberGroup->buildFilter($filter_mem['filter'],$rs['shop_id']);//var_dump($filter);
            $count = $oMemberAnalysis->count($filter);

        }elseif($rs['report_filter']){
            $report_filter = json_decode($rs['report_filter'],1);
            if($report_filter['filter_from'] == 'market'){
                $count = kernel::single('plugins_market')->getMemberCounts($report_filter['market_id'],$report_filter['shop_id']);
            }else{
                switch ($report_filter['filter_type']){

                    case 'rfm':
                        $rs = kernel::single('taocrm_ctl_admin_analysis_rfm')->get_filter_member($report_filter);
                        break;

                    default :
                        $rs = app::get('taocrm')->model('member_analysis')->get_filter_member($report_filter);
                }
                $count = $rs['total'];//var_dump($report_filter);
            }

        }else{
            $count = app::get('taocrm')->model('member_analysis')->count(array('shop_id'=>$rs['shop_id']));
        }
        return $count;
    }

    function get_filter_member(&$filter){

        //echo('<pre>');print_r($filter);

        $db = kernel::database();
        $page = $filter['page'];
        $page_size = $filter['plimit'];
        $active_id = $filter['active_id'];

        $shop_id = $filter['shop_id'];
        $flag = $filter['flag'];
        $status = $filter['is_compare'];

        //获取活动的营销效果
        $oAssess = $this->app->model('active_assess');
        $rs = $oAssess->dump(array('active_id'=>$active_id));
        $active_members_res = unserialize($rs['active_members_res']);//参加活动客户
        $control_members_res = unserialize($rs['control_members_res']);//对照组客户
        $end_time = !empty($rs['end_time']) ? date("Y-m-d H:i:s" ,$rs['end_time']) : '';
        $exec_time = !empty($rs['exec_time']) ? date("Y-m-d H:i:s" ,$rs['exec_time']) : '';
        $exec_time = strtotime($exec_time);
        $end_time = strtotime($end_time);

        $date_from=$exec_time;
        $date_to=$exec_time+1296000;

        if($status == 2) $active_members_res=$control_members_res;

        //echo('<pre>');print_r($active_members_res);

        if($flag == 'all'){
            $res['total'] = $active_members_res['acmembers'];
            $sql = ('select member_id from sdb_market_active_member where
            active_id='.$active_id .' and status='.$status.'  limit '.($page*$page_size).','.$page_size);

        }elseif($flag == 'buy'){
            $res['total'] = $active_members_res['ordernums'];
            $sql = ('select distinct(a.member_id) from sdb_market_active_member as a
            inner join sdb_ecorder_orders as b
            on a.member_id=b.member_id
            where a.active_id='.$active_id .' and b.createtime>'.$exec_time.' and b.createtime <='.$date_to .' and b.shop_id = "'.$shop_id.'" and a.status='.$status.'
            limit '.($page*$page_size).','.$page_size);

        }elseif($flag == 'paid'){
            $res['total'] = $active_members_res['apaynums'];
            $sql = ('select distinct(a.member_id) from sdb_market_active_member as a
            inner join sdb_ecorder_orders as b
            on a.member_id=b.member_id
            where a.active_id='.$active_id .' and b.createtime>'.$exec_time.' and b.createtime <='.$date_to .'  and b.shop_id = "'.$shop_id.'" and a.status='.$status.' and b.pay_status="1"
            limit '.($page*$page_size).','.$page_size);

        }elseif($flag == 'finish'){
            $res['total'] = $active_members_res['afinish_members'];
            $sql = ('select distinct(a.member_id) from sdb_market_active_member as a
            inner join sdb_ecorder_orders as b
            on a.member_id=b.member_id
            where a.active_id='.$active_id .' and b.createtime>'.$exec_time.' and b.createtime <='.$date_to .' and b.shop_id = "'.$shop_id.'" and a.status='.$status.' and b.status="finish"
            limit '.($page*$page_size).','.$page_size);
        }
        //die($sql);
        $rs = $db->select($sql);
        if(!$rs) return false;
        foreach($rs as $v){
            $res['member_id'][] = $v['member_id'];
        }

        //创建营销活动参数
        $res['params'] = array(
            'active_id'=>$active_id,
            'status'=>$status,
            'exec_time'=>$exec_time,
            'end_time'=>$end_time,
            'flag'=>$flag,
        );

        return $res;
    }

    //转换活动类型字段
    function modifier_type($row)
    {
        $arr = array();
        $row = unserialize($row);
        if($row){
            if(in_array('sms', $row)) $arr[] = '短信';
            if(in_array('edm', $row)) $arr[] = '邮件';
            if(in_array('coupon', $row)) $arr[] = '优惠券';
        }
        return implode(',', $arr);
    }

    function getActiveTop($shop_id,$num){

        $sql = 'select active_id,active_name from sdb_market_active where is_active="finish" ';
        if($shop_id){
            $sql .= " and shop_id='$shop_id' ";
        }
        $sql .=  ' limit 0,'.$num;
        $rows = kernel::database()->select($sql);

        return $rows;
    }

    function getActiveByKeyWord($shop_id,$keyword){

        $sql = 'select active_id,active_name from sdb_market_active where is_active="finish" and active_name like "%'.$keyword.'%"';
        if($shop_id){
            $sql .= " and shop_id='$shop_id' ";
        }
        $rows = kernel::database()->select($sql);

        return $rows;
    }
}
