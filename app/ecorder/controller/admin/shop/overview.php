<?php
class ecorder_ctl_admin_shop_overview extends desktop_controller{

    var $name = "店铺概览";
    var $workground = "setting_tools";

    function index(){
        $this->page("admin/shop/overview.html");
    }

    function chart()
    {
        //获取店铺数据
        $shop_members = $this->get_shop_members();
        $shop_members = $this->array_sort($shop_members,'members','desc');
        foreach($shop_members as $v){
            $chart_member[] = '{x:"'.$v['name'].'",y:'.floatval($v['members']).'}';
            if(sizeof($chart_member)>=5) break;
        }


        $shop_members = $this->array_sort($shop_members,'amount','desc');
        foreach($shop_members as $v){
            $chart_amount[] = '{x:"'.$v['name'].'",y:'.floatval($v['amount']).'}';
            if(sizeof($chart_amount)>=5) break;
        }

        $today = strtotime(date('Y-m-d 00:00:00'));
        $start_time = strtotime('-7 days',$today);
        $end_time = $today;
        $shop_data = $this->get_shop_chart($start_time, $end_time);
        foreach($shop_data as $v){
            $chart_shop[] = '{x:"'.$v['c_date'].'",y1:'.floatval($v['orders']).',y2:'.floatval($v['amount']).',y3:'.floatval($v['members']).'}';
        }

        if($chart_shop)
        $this->pagedata['chart_shop'] = implode(',',$chart_shop);
        if($chart_member)
        $this->pagedata['chart_member'] = implode(',',$chart_member);
        if($chart_amount)
        $this->pagedata['chart_amount'] = implode(',',$chart_amount);
        $this->display("admin/shop/overview_chart.html");
    }

    function get_shop_chart($start_time, $end_time)
    {
        $db = kernel::database();
        $sql = "select FROM_UNIXTIME(createtime,'%Y-%m-%d') as c_date,count(distinct(member_id)) as members,count(order_id) as orders,sum(payed) as amount from sdb_ecorder_orders where createtime between {$start_time} and {$end_time} and pay_status='1' group by FROM_UNIXTIME(createtime,'%Y-%m-%d') ";
        $rs = $db->select($sql);
        return $rs;
    }

    function get_shop_members()
    {
        $db = kernel::database();
        $sql = "select a.*,b.name from sdb_ecorder_shop_analysis as a inner join sdb_ecorder_shop as b on a.shop_id=b.shop_id";
        
        $sql = "select shop_id,name from sdb_ecorder_shop";
        $rs = $db->select($sql);
        foreach($rs as $v){
            $shops[$v['shop_id']] = $v['name'];
        }
        
        $sql = "select sum(payed) as amount,shop_id,count(distinct member_id) as members from sdb_ecorder_orders where pay_status='1' group by shop_id ";
        $rs = $db->select($sql);
        foreach($rs as $k=>$v){
            $rs[$k]['amount'] = intval($rs[$k]['amount']);
            $rs[$k]['name'] = $shops[$v['shop_id']];
        }
        return $rs;
    }

    function array_sort($arr,$keys,$type='desc')
    {
        $keysvalue = $new_array = array();
        foreach ($arr as $k=>$v){
            $keysvalue[$k] = $v[$keys];
        }
        if($type == 'asc'){
            asort($keysvalue);
        }else{
            arsort($keysvalue);
        }
        reset($keysvalue);
        $i = 1;
        foreach ($keysvalue as $k=>$v){
            $new_array[$k] = $arr[$k];
            $new_array[$k]['order'] = $i++;
        }
        return $new_array;
    }
}
