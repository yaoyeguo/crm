<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class taocrm_analysis_day {

    function __construct(){
        $this->db = &app::get('taocrm')->model('members')->db;
    }

    private function load_redis($shop_id){
        $total_orders = app::get('ecorder')->model('shop')->get_shop_orders($shop_id);
        $max_orders = 100*1000;//订单数阀值
        if($total_orders >= $max_orders){
            return true;
        }else{
            return false;
        }
    }

    public function loading_tips($msg){
        return kernel::single('taocrm_analysis_cache')->loading_tips($msg);
    }

    // 销售分析
    public function get_all_sales_data($filter)
    {
        if( ! $filter) return false;
        
        $date_from = strtotime($filter['date_from']) - 1;
        $date_to = strtotime($filter['date_to']) - 1;
        if($date_from == $date_to){
            $date_to += 86400;
        }
        
        $filter = array(
            'startDate'=> $date_from,
            'endDate'=> $date_to,
            'countBy'=> $this->get_java_date($filter['count_by']),
            'shopId'=>$filter['shop_id']
        );
        
        $memory_data = kernel::single('taocrm_middleware_connect')->getSaleCount($filter);
        $memory_data_arr = json_decode($memory_data, 1);
        //echo('<pre>');var_dump($memory_data_arr);
        if($memory_data_arr['rsp'] != 'succ') return false;
        foreach($memory_data_arr['info']['data'] as $v){
            if($filter['count_by'] == 'week')
                $v['key'] = $this->_correct_week($v['c_year'],$v['c_week'],$v['c_time']);
            $this->key_maps($v);
            if($filter['count_by']=='date')
                $v['key'] = strtotime($v['key']);
            foreach($v as $kk=>$vv){
                $total_data[$kk] += $vv;
            }
            if(isset($analysis_data[$v['key']])){
                foreach($v as $kk=>$vv){
                    if($kk!='key') $analysis_data[$v['key']][$kk] += $vv;
                }
            }else{
                $analysis_data[$v['key']] = $v;
            }
        }
        $this->array_sort($analysis_data,'key','asc');
        //echo('<pre>');var_dump($filter);

        /*
        $where = " AND (c_time between $date_from and $date_to) ";
        $where .= ' AND shop_id = "'.$filter['shop_id'].'" ';
        
        $count_by = 'c_'.$filter['count_by'];            
        if($filter['count_by'] == 'week'){
            $count_by = "CONCAT(c_year,'.',c_week)";
            $group_by = 'c_year,c_week';
        }else{
            $group_by = $count_by;
        }
        */        

        /*
        $sql = "select $count_by as date,c_year,c_week,c_time,
            sum(total_orders) as total_orders, sum(total_members) as total_members,
            sum(total_amount) as total_amount,
            sum(paid_orders) as paid_orders, sum(paid_members) as paid_members,
            sum(paid_amount) as paid_amount, sum(buy_products) as buy_products,
            sum(finish_orders) as finish_orders, sum(finish_members) as finish_members, sum(finish_total_amount) as finish_amount,
            sum(unpay_orders) as unpay_orders, sum(unpay_amount) as unpay_amount,
            sum(refund_orders) as refund_orders, sum(refund_amount) as refund_amount
        from sdb_taocrm_member_analysis_day 
        where 1=1 $where
        group by $group_by ";//var_dump($sql);
        $rs = $this->db->select($sql);
        if(!$rs) $rs = array();
        foreach($rs as $v){
            if($filter['count_by'] == 'week')
                $v['date'] = $this->_correct_week($v['c_year'],$v['c_week'],$v['c_time']);
            foreach($v as $kk=>$vv){
                $total_data[$kk] += $vv;
            }
            if(isset($analysis_data[$v['date']])){
                foreach($v as $kk=>$vv){
                    if($kk!='date') $analysis_data[$v['date']][$kk] += $vv;
                }
            }else{
                $analysis_data[$v['date']] = $v;
            }
        }

        $this->array_sort($analysis_data,'date','desc');

        //对比数据
        if($filter['c_date_from'] && $filter['c_date_to']) {

            $date_from = strtotime($filter['c_date_from']) - 1;
            $date_to = strtotime($filter['c_date_to']) - 1;
            $where = " AND (c_time between $date_from and $date_to) ";
            $where .= ' AND shop_id = "'.$filter['shop_id'].'" ';
            $count_by = 'c_'.$filter['count_by'];

            $sql = "select $count_by as date,c_year,c_week,c_time,
                sum(total_orders) as total_orders, sum(total_members) as total_members, sum(total_amount) as total_amount,
                sum(buy_products) as buy_products,
                sum(finish_orders) as finish_orders, sum(finish_members) as finish_members, sum(finish_total_amount) as finish_total_amount,
                sum(unpay_orders) as unpay_orders, sum(unpay_amount) as unpay_amount,
                sum(refund_orders) as refund_orders, sum(refund_amount) as refund_amount
            from sdb_taocrm_member_analysis_day 
            where 1=1 $where group by $group_by ";
            $rs = $this->db->select($sql);
            foreach($rs as $v){
                if($filter['count_by'] == 'week')
                    $v['date'] = $this->_correct_week($v['c_year'],$v['c_week'],$v['c_time']);
                if(isset($compare_data[$v['date']])){
                    foreach($v as $kk=>$vv){
                        if($kk!='date') $compare_data[$v['date']][$kk] += $vv;
                    }
                }else{
                    $compare_data[$v['date']] = $v;
                }
            }
            $this->array_sort($compare_data,'date','desc');
        }
        */

        //echo('<pre>');var_dump($sql);
        $data['sales_data'] = $sales_data;
        $data['analysis_data'] = array_values($analysis_data);
        $data['compare_data'] = array_values($compare_data);
        $data['total_data'] = $total_data;
        return $data;
    }

    //订单状态分析
    public function get_order_status($filter)
    {
        if($filter){
            $date_from = strtotime($filter['date_from']) - 1;
            $date_to = strtotime($filter['date_to']) - 1;
            $where .= " AND c_time > $date_from ";
            $where .= " AND c_time < $date_to ";
            $where .= ' AND shop_id = "'.$filter['shop_id'].'" ';
            $count_by = 'c_'.$filter['count_by'];
        }

        $sql = "select $count_by as date,
            sum(total_orders) as total_orders, sum(total_members) as total_members,
            sum(total_amount) as total_amount, sum(buy_products) as buy_products,
            sum(finish_orders) as finish_orders, sum(finish_members) as finish_members,
            sum(finish_total_amount) as finish_total_amount,
            sum(unpay_orders) as unpay_orders, sum(unpay_amount) as unpay_amount,
            sum(refund_orders) as refund_orders, sum(refund_amount) as refund_amount,
            sum(failed_orders) as failed_orders, sum(failed_amount) as failed_amount,
            sum(failed_members) as failed_members
        from sdb_taocrm_member_analysis_day 
        where 1=1 $where group by $count_by ";
        $rs = $this->db->select($sql);
        foreach($rs as $v){
            $analysis_data[] = $v;
        }

        $data['sales_data'] = $sales_data;
        $data['analysis_data'] = $analysis_data;
        return $analysis_data;
    }

    //购买时段分析
    public function get_hours_data($filter)
    {
        $date_from = strtotime($filter['date_from']);
        $date_to = strtotime($filter['date_to']);
        if($date_from == $date_to){
            $filter['date_to'] = date('Y-m-d',$date_to + 86400);
        }
        
        $filter = array(
            'startDate'=> $date_from,
            'endDate'=> $date_to,
            'shopId'=>$filter['shop_id']
        );

        $memory_data = kernel::single('taocrm_middleware_connect')->getHoursCount($filter);
        $memory_data_arr = json_decode($memory_data, 1);
        //echo('<pre>');var_dump($memory_data_arr);
        
        if($memory_data_arr['rsp'] != 'succ') return false;
        foreach($memory_data_arr['info']['data'] as $v){
            $this->key_maps($v);
            foreach($v as $kk=>$vv){
                $total_data[$kk] += $vv;
            }
            if(isset($analysis_data[$v['key']])){
                foreach($v as $kk=>$vv){
                    if($kk!='key') $analysis_data[$v['key']][$kk] += $vv;
                }
            }else{
                $analysis_data[$v['key']] = $v;
            }
        }
        $this->array_sort($analysis_data,'key','asc');
        $data['analysis_data'] = $analysis_data;
        $data['total_data'] = $total_data;
        return $data;
        
        if($this->load_redis($filter['shop_id']) == false){
            $data = kernel::single('taocrm_analysis_cache')->get_hour_cache($filter);
        }else{//进入后台队列运算
            unset($filter['count_by']);
            $func = 'get_hours_data';
            $oCacheReport = kernel::single('taocrm_cache_report');
            $cache_id = $oCacheReport->getCacheId($func,$filter);
            $cache_status = $oCacheReport->get($cache_id);
            if($cache_status['status'] == 'REQ_CACHE'){
                die($this->loading_tips(2));
            }elseif($cache_status['status'] == 'PRE_CACHE'){
                die($this->loading_tips(3));
            }elseif($cache_status['status'] == 'SUCC'){
                $data = $cache_status['data'];//报表数据
            }else{
                $oCacheReport->fetch($func,$filter);
                die($this->loading_tips(1));
            }
        }
        return $data;
    }
    
    //购买频次分析
    public function get_buy_freq($filter)
    {
        $filter = array(
            'shopId'=>$filter['shop_id']
        );

        $memory_data = kernel::single('taocrm_middleware_connect')->getBuyFreqCount($filter);
        $memory_data_arr = json_decode($memory_data, 1);
        //echo('<pre>');var_dump($memory_data_arr);
        
        if($memory_data_arr['rsp'] != 'succ') return false;
        foreach($memory_data_arr['info']['data'] as $v){
            $this->key_maps($v);
            $analysis_data[$v['key']] = $v;
        }
        return $analysis_data;
    }

    function get_area_data($filter)
    {
        @set_time_limit(360);

        $shop_id = $filter['shop_id'];
        $date_from = strtotime($filter['date_from']);
        $date_to = strtotime($filter['date_to']);
        if($date_from == $date_to){
            $filter['date_to'] = date('Y-m-d',$date_to + 86400);
        }
        
        $data = kernel::single('taocrm_analysis_cache')->get_area_cache($filter);

        /*
        if($this->load_redis($filter['shop_id']) == false) {
            $data = kernel::single('taocrm_analysis_cache')->get_area_cache($filter);
        }else{
            //进入后台队列运算
            unset($filter['count_by']);
            $func = 'get_area_data';
            $oCacheReport = kernel::single('taocrm_cache_report');
            $cache_id = $oCacheReport->getCacheId($func,$filter);
            $cache_status = $oCacheReport->get($cache_id);
            //echo('<pre>');var_dump($cache_id);
            if($cache_status['status'] == 'REQ_CACHE'){
                die($this->loading_tips(2));
            }elseif($cache_status['status'] == 'PRE_CACHE'){
                die($this->loading_tips(3));
            }elseif($cache_status['status'] == 'SUCC'){
                $data = $cache_status['data'];//报表数据
            }else{
                $oCacheReport->fetch($func,$filter);
                die($this->loading_tips(1));
            }
        }
        */

        //echo('<pre>');var_dump($data);
        return $data;
    }

    // 获取每个城市的具体数据
    public function get_city_data($filter) {

        $shop_id = $filter['shop_id'];
        $date_from = strtotime($filter['date_from']);
        $date_to = strtotime($filter['date_to']);
        $state = $filter['state'];
        $data = array();

        if($state=='北京'||$state=='上海'||$state=='重庆'||$state=='天津') {
            $state .= '市';
            $filter_id = 'city_id';
            $target_id = 'district_id';
        }else{
            $filter_id = 'state_id';
            $target_id = 'city_id';
        }

        //地区列表
        $rs = &app::get('ectools')->model('regions')->getList('region_id',array('local_name'=>$state));
        if($rs){
            $p_region_id = $rs[0]['region_id'];
        }
        $rs = &app::get('ectools')->model('regions')->getList('region_id,local_name,group_name',array('p_region_id'=>intval($p_region_id)));
        if($rs){
            foreach($rs as $v){
                $regions[$v['region_id']] = trim($v['local_name']);
            }
        }

        $sql = "SELECT  count(*) as total_orders,
            sum(total_amount) as total_amount,
            count(distinct member_id) as total_members,
            $target_id as area_id
            from sdb_ecorder_orders
            where shop_id='$shop_id' and $filter_id=$p_region_id and (createtime between $date_from and $date_to)
            group by $target_id order by sum(total_amount) desc ";
            $rs = &app::get('ectools')->model('regions')->db->select($sql);
            if($rs) {
                foreach($rs as $v){
                    $v['area'] = $regions[$v['area_id']];
                    if(!$v['area']) continue;
                    if($v['total_orders']) $v['per_amount'] = round($v['total_amount']/$v['total_orders'],2);
                    $data[$v['area']] = $v;
                }}unset($rs);

                $sql = "SELECT  count(*) as paid_orders,
            sum(total_amount) as paid_amount,
            count(distinct member_id) as paid_members,
            $target_id as area_id
            from sdb_ecorder_orders
            where pay_status='1'
            and shop_id='$shop_id' and $filter_id=$p_region_id and (createtime between $date_from and $date_to) group by $target_id ";
            $rs = &app::get('ectools')->model('regions')->db->select($sql);
            if($rs) {
                foreach($rs as $v){
                    $v['area'] = $regions[$v['area_id']];
                    if(!$v['area']) continue;
                    if($v['paid_orders']) $v['paid_per_amount'] = round($v['paid_amount']/$v['paid_orders'],2);
                    $v = array_merge($v,$data[$v['area']]);
                    $data[$v['area']] = $v;
                }}unset($rs);

                //计算合计
                foreach($data as $k=>$v) {
                    if(!$k) unset($data[$k]);
                    foreach($v as $kk=>$vv){
                        if($kk!='area') {
                            $total_data[$kk] += $vv;
                        }
                    }
                }

                //计算比例
                foreach($data as $k=>$v) {
                    foreach($v as $kk=>$vv){
                        if($kk!='area') {
                            $data[$k][$kk.'_ratio'] = round($vv*100/$total_data[$kk],2);
                        }
                    }
                }

                $data = array('analysis_data'=>$data,'total_data'=>$total_data);
                return $data;
    }
    
	// 获取每个城市的具体数据(针对分销订单)
    public function get_fx_city_data($filter) {

        $shop_id = $filter['shop_id'];
        $date_from = strtotime($filter['date_from']);
        $date_to = strtotime($filter['date_to']);
        $state = $filter['state'];
        $data = array();

        if($state=='北京'||$state=='上海'||$state=='重庆'||$state=='天津') {
            $state .= '市';
            $filter_id = 'city_id';
            $target_id = 'district_id';
        }else{
            $filter_id = 'state_id';
            $target_id = 'city_id';
        }

        //地区列表
        $rs = &app::get('ectools')->model('regions')->getList('region_id',array('local_name'=>$state));
        if($rs){
            $p_region_id = $rs[0]['region_id'];
        }
        $rs = &app::get('ectools')->model('regions')->getList('region_id,local_name,group_name',array('p_region_id'=>intval($p_region_id)));
        if($rs){
            foreach($rs as $v){
                $regions[$v['region_id']] = trim($v['local_name']);
            }
        }

        $sql = "SELECT  count(*) as total_orders,
            sum(total_amount) as total_amount,
            count(distinct member_id) as total_members,
            $target_id as area_id
            from sdb_ecorder_fx_orders
            where shop_id='$shop_id' and $filter_id=$p_region_id and member_id> 0 and (createtime between $date_from and $date_to)
            group by $target_id order by sum(total_amount) desc ";
            $rs = &app::get('ectools')->model('regions')->db->select($sql);
            if($rs) {
                foreach($rs as $v){
                    $v['area'] = $regions[$v['area_id']];
                    if(!$v['area']) continue;
                    if($v['total_orders']) $v['per_amount'] = round($v['total_amount']/$v['total_orders'],2);
                    $data[$v['area']] = $v;
                }}unset($rs);

                $sql = "SELECT  count(*) as paid_orders,
            sum(total_amount) as paid_amount,
            count(distinct member_id) as paid_members,
            $target_id as area_id
            from sdb_ecorder_fx_orders
            where pay_status='1' and member_id>0 
            and shop_id='$shop_id' and $filter_id=$p_region_id and (createtime between $date_from and $date_to) group by $target_id ";
            $rs = &app::get('ectools')->model('regions')->db->select($sql);
            if($rs) {
                foreach($rs as $v){
                    $v['area'] = $regions[$v['area_id']];
                    if(!$v['area']) continue;
                    if($v['paid_orders']) $v['paid_per_amount'] = round($v['paid_amount']/$v['paid_orders'],2);
                    $v = array_merge($v,$data[$v['area']]);
                    $data[$v['area']] = $v;
                }}unset($rs);

                //计算合计
                foreach($data as $k=>$v) {
                    if(!$k) unset($data[$k]);
                    foreach($v as $kk=>$vv){
                        if($kk!='area') {
                            $total_data[$kk] += $vv;
                        }
                    }
                }

                //计算比例
                foreach($data as $k=>$v) {
                    foreach($v as $kk=>$vv){
                        if($kk!='area') {
                            $data[$k][$kk.'_ratio'] = round($vv*100/$total_data[$kk],2);
                        }
                    }
                }

                $data = array('analysis_data'=>$data,'total_data'=>$total_data);
                return $data;
    }

    public function get_member_freq(&$filter){
        $shop_id = $filter['shop_id'];
        $page = $filter['page'];
        $page_size = $filter['plimit'];

        $db = kernel::database();
        $buy_times = $filter['buy_freq'];
        if($buy_times){
            $sql = "select distinct member_id from sdb_taocrm_member_analysis where
        	buy_freq =".$buy_times." and shop_id='$shop_id'";
        }else{
            $sql ="select distinct member_id from sdb_taocrm_member_analysis where
        	buy_freq>8 and shop_id='$shop_id'";
        }
        $data['filter_sql'] = $sql;
        $sql .= " LIMIT ".($page*$page_size).",$page_size";
        $rs = $db->select($sql);

        if($rs){
            foreach($rs as $v){
                $data['members'][] = $v['member_id'];
            }
        }
         
        if($buy_times){
            $sql = "select count(distinct member_id) as total from sdb_taocrm_member_analysis where
        	buy_freq =".$buy_times." and shop_id='$shop_id'";
        }else{
            $sql ="select count(distinct member_id) as total from sdb_taocrm_member_analysis where
        	buy_freq>8 and shop_id='$shop_id'";
        }
        $rs = $this->db->selectRow($sql);//die($sql);
        $data['total'] = $rs['total'];
        return $data;
    }

    public function get_member_buy_times(&$filter)
    {
        @ini_set('memory_limit','128M');

        $page = $filter['page'];
        $page_size = $filter['plimit'];

        $db = kernel::database();
        $shop_id = $filter['shop_id'];
        $buy_times = intval($filter['buy_times']);
        $date_from = strtotime($filter['date_from']);
        $date_to = strtotime($filter['date_to']);
       	if($date_from == $date_to){
       	    $date_to += 86400;
       	    $filter['date_to'] = date('Y-m-d',$date_to);
       	}
        $where = '';
        if($date_from) $where .= " AND (createtime between $date_from and $date_to) ";
        if($shop_id) $where .= " AND shop_id='$shop_id' ";
        if($buy_times>0){
            if($buy_times > 5){
                $sql = "SELECT member_id FROM sdb_ecorder_orders WHERE 1=1 $where AND pay_status='1'
            GROUP BY member_id HAVING COUNT(order_id)>=$buy_times
            ";//die($sql);
            }else{
                $sql = "SELECT member_id FROM sdb_ecorder_orders WHERE 1=1 $where AND pay_status='1'
            GROUP BY member_id HAVING COUNT(order_id)=$buy_times
            ";
            }
            $data['filter_sql'] = $sql;
            $sql .= "LIMIT ".($page*$page_size).",$page_size";
            $rs = $db->select($sql);
            if(!$rs) $rs=array();
            foreach($rs as $k=>$v) {
                $data['members'][] = $v['member_id'];
                unset($rs[$k]);
            }

            //返回合计
            if($buy_times > 5){
                $sql = "select count(*) as total from (SELECT member_id as total FROM sdb_ecorder_orders
            WHERE 1=1 $where AND pay_status='1'
            GROUP BY member_id HAVING COUNT(order_id)>=$buy_times) as a";
            }else{
                $sql = "select count(*) as total from (SELECT member_id as total FROM sdb_ecorder_orders
            WHERE 1=1 $where AND pay_status='1'
            GROUP BY member_id HAVING COUNT(order_id)=$buy_times) as a";
            }
            $rs = $this->db->selectRow($sql);//die($sql);
            $data['total'] = $rs['total'];

            return $data;
        }
        
        if($this->load_redis($filter['shop_id']) == false){
            $data = kernel::single('taocrm_analysis_cache')->get_buy_times_cache($filter);
            //echo('<pre>');var_dump($data);
            $funnel_data = $data['funnel_data'];
            $data = $data['analysis_data'];
        }else{
            //进入后台队列运算
            unset($filter['count_by']);
            $func = 'get_member_buy_times';
            $oCacheReport = kernel::single('taocrm_cache_report');
            $cache_id = $oCacheReport->getCacheId($func,$filter);
            $cache_status = $oCacheReport->get($cache_id);
            //echo('<font style="font-size:12px">');var_dump($cache_id);
            if($cache_status['status'] == 'REQ_CACHE'){
                die($this->loading_tips(2));
            }elseif($cache_status['status'] == 'PRE_CACHE'){
                die($this->loading_tips(3));
            }elseif($cache_status['status'] == 'SUCC'){//报表数据
                $data = $cache_status['data']['analysis_data'];
                $funnel_data = $cache_status['data']['funnel_data'];
            }else{
                $oCacheReport->fetch($func,$filter);
                die($this->loading_tips(1));
            }
        }

        $this->array_sort($data,'total_orders','asc');
        foreach($data as $k=>$v) {
            $remain_members += $v['total_members'];
            $data[$k]['remain_members'] = $remain_members;

            foreach($v as $kk=>$vv) {
                if(is_numeric($vv)) $total_data[$kk] += $vv;
            }
        }

        $funnel_data = '{"chart": {"showlegend" : "1","showborder": "0","bgcolor": "ffffff","manageresize": "1","decimals": "1","basefontsize": "12","issliced": "1","usesameslantangle": "1","ishollow": "0","labeldistance": "5"},"data": [{"label": "客户数","value": "'.$total_data['total_members'].'"},{"label": "购买一次","value": "'.$data[1]['total_members'].'"},{"label": "购买两次","value": "'.$data[2]['total_members'].'"},{"label": "购买3-5次","value": "'.$funnel_data['3-5'].'"},{"label": "购买多次","value": "'.$funnel_data['>5'].'"}],"styles": {"definition": [{"type": "font","name": "captionFont","size": "15"}],"application": [{"toobject": "CAPTION","styles": "captionFont"}]}}';

        $data = array('analysis_data'=>$data,'total_data'=>$total_data,'members'=>$members,'funnel_data'=>$funnel_data);
        return $data;
    }

    public function get_member_level($filter){

        $db = kernel::database();
        $shop_id = $filter['shop_id'];
        $date_from = strtotime($filter['date_from']);
        $date_to = strtotime($filter['date_to']);

        //店铺的客户等级
        $oShopLv = &app::get('ecorder')->model('shop_lv');
        $rs = $oShopLv->getList('lv_id,name',array('shop_id'=>$shop_id));
        foreach($rs as $v) {
            $shop_lv[$v['lv_id']] = $v['name'];
        }

        $sql = "select lv_id,count(member_id) as total_members,sum(total_amount) as total_amount
        from sdb_taocrm_member_analysis 
        where shop_id='$shop_id' and lv_id>0
        group by lv_id ";
        $rs = $db->select($sql);
        $this->array_sort($rs,'lv_id','asc');//数组排序
        foreach($rs as $k=>$v) {//合计
            $rs[$k]['lv_name'] = $shop_lv[$v['lv_id']];
            foreach($v as $kk=>$vv) {
                $total_data[$kk] += $vv;
            }
        }
        $data = array('analysis_data'=>$rs,'total_data'=>$total_data);
        return $data;
    }

    public function get_member_old_new($filter)
    {
        $db = kernel::database();
        $shop_id = $filter['shop_id'];
        $count_by = $filter['count_by'];
        $member_status = $filter['member_status'];
        $date_from = strtotime($filter['date_from']);
        $date_to = strtotime($filter['date_to']);
        if($date_from == $date_to){
            $date_to += 86400;
            $filter['date_to'] = date('Y-m-d',$date_to);
        }
        $page = $filter['page'];
        $page_size = $filter['plimit'];

        switch($count_by):
            case 'week':
                $count_unit = '%Y.%U';
            break;

            case 'month':
                $count_unit = '%Y-%m';
            break;

            case 'year':
                $count_unit = '%Y';
            break;

            default:
                $count_unit = '%Y-%m-%d';
                break;
        endswitch;

        if($filter['date']){
            $where_new = " AND (FROM_UNIXTIME(a.first_buy_time,'$count_unit')='".$filter['date']."')";
            $where_old = " AND (FROM_UNIXTIME(createtime,'$count_unit')='".$filter['date']."')";
        }

        $where_new .= " AND (a.first_buy_time between $date_from and $date_to)";
        $where_old .= " AND (createtime between $date_from and $date_to)";

        //finder列表
        if($member_status == 'new'){
            $sql = "select a.member_id from sdb_taocrm_member_analysis as a where a.shop_id='$shop_id' $where_new ";
            $data['filter_sql'] = $sql;
            $sql .= "LIMIT ".($page*$page_size).",$page_size ";
            $rs = $db->select($sql);
            foreach($rs as $v){
                $member_ids[] = $v['member_id'];
            }
            //var_dump($member_ids);

            $sql = "select count(a.member_id) as total from sdb_taocrm_member_analysis as a where a.shop_id='$shop_id' $where_new ";
            $rs = $db->selectRow($sql);
            $total = $rs['total'];

            $data['member_id'] = $member_ids;
            $data['total'] = $total;
            return $data;
        }

        //finder列表
        if($member_status == 'old'){
            $sql = "select distinct(member_id) from sdb_ecorder_orders where shop_id='$shop_id' $where_old
            and member_id not in (
                select a.member_id from sdb_taocrm_member_analysis as a
                where a.shop_id='$shop_id' $where_new
            )";
            $data['filter_sql'] = $sql;
            $sql .= "LIMIT ".($page*$page_size).",$page_size ";
            $rs = $db->select($sql);
            //die($sql);
            if($rs) {
                foreach($rs as $v){$member_ids[] = $v['member_id'];}
            }

            $sql = "select count(distinct(member_id)) as total from sdb_ecorder_orders where shop_id='$shop_id' $where_old
            and member_id not in (
                select a.member_id from sdb_taocrm_member_analysis as a
                where a.shop_id='$shop_id' $where_new
            )";
            $rs = $db->selectRow($sql);
            $total = $rs['total'];

            $data['member_id'] = $member_ids;
            $data['total'] = $total;
            return $data;
        }
        
        $filter = array(
            'startDate'=> $date_from,
            'endDate'=> $date_to,
            'countBy'=> $this->get_java_date($filter['count_by']),
            'shopId'=>$filter['shop_id']
        );
        
        $memory_data = kernel::single('taocrm_middleware_connect')->getNewOldMemberCount($filter);
        $memory_data_arr = json_decode($memory_data, 1);
        //echo('<pre>');var_dump($memory_data_arr);
        if($memory_data_arr['rsp'] != 'succ') return false;
        foreach($memory_data_arr['info']['data'] as $v){
            if($filter['count_by'] == 'week')
                $v['key'] = $this->_correct_week($v['c_year'],$v['c_week'],$v['c_time']);
            $this->key_maps($v);
            if($filter['count_by']=='date')
                $v['key'] = strtotime($v['key']);
                
            $v['new_amount'] = floatval($v['new_amount']);
            $v['old_amount'] = floatval($v['old_amount']);
            if($v['new_amount']+$v['old_amount']>0)
                $v['old_amount_ratio'] = round($v['old_amount']*100/($v['new_amount']+$v['old_amount']),2);

            $v['old_member'] = floatval($v['old_member']);
            $v['new_member'] = floatval($v['new_member']);
            if($v['new_member']+$v['old_member']>0)
                $v['old_ratio'] = round($v['old_member']*100/($v['new_member']+$v['old_member']),2);
                
            foreach($v as $kk=>$vv){
                $total_data[$kk] += $vv;
            }
            if(isset($analysis_data[$v['key']])){
                foreach($v as $kk=>$vv){
                    if($kk!='key') $analysis_data[$v['key']][$kk] += $vv;
                }
            }else{
                $analysis_data[$v['key']] = $v;
            }
        }
        $this->array_sort($analysis_data,'key','asc');
        $data = array(
            'analysis_data'=>$analysis_data,
            'total_data'=>$total_data
        );
        return $data;
        
        
        
        
        
        
        
        if($this->load_redis($filter['shop_id']) == false){
            $data = kernel::single('taocrm_analysis_cache')->get_old_new_cache($filter);
        }else{
            //进入后台队列运算
            $func = 'get_member_old_new';
            $oCacheReport = kernel::single('taocrm_cache_report');
            $cache_id = $oCacheReport->getCacheId($func,$filter);
            $cache_status = $oCacheReport->get($cache_id);
            if($cache_status['status'] == 'REQ_CACHE'){
                die($this->loading_tips(2));
            }elseif($cache_status['status'] == 'PRE_CACHE'){
                die($this->loading_tips(3));
            }elseif($cache_status['status'] == 'SUCC'){
                $data = $cache_status['data'];//报表数据
            }else{
                $oCacheReport->fetch($func,$filter);
                die($this->loading_tips(1));
            }
        }
        return ($data);
    }

    public function get_goods_rank($filter){
         

        $db = kernel::database();
        $shop_id = $filter['shop_id'];
        $date_from = strtotime($filter['date_from']);
        $date_to = strtotime($filter['date_to']);

        //计算间隔的天数
        $days = ($date_to - $date_from)/(24*60*60);
        $init_store = 10;//默认库存值

        //商品的当前库存
        $sql = "select goods_id,store from sdb_ecgoods_shop_goods where no_use=0";
        $rs = $db->select($sql);
        foreach($rs as $v) {
            $goods[$v['goods_id']] = $v;
        }
        /*
         $sql = "select item_id,shop_goods_id,goods_id,bn,name,
         sum(amount) as amount,sum(nums) as nums
         from sdb_ecorder_order_items
         where shop_id='$shop_id' and (create_time between $date_from and $date_to) and goods_id>0
         group by goods_id
         order by nums desc";
         */
        //使用已支付的订单来统计销量

        $sql = "select tab1.item_id,tab1.shop_goods_id,tab1.goods_id,tab1.bn,tab1.name,
            sum(tab1.amount) as amount,sum(tab1.nums) as nums
        from sdb_ecorder_order_items as tab1
        left join sdb_ecorder_orders as tab2 on tab1.order_id = tab2.order_id
        left join sdb_ecgoods_shop_goods as c on tab1.goods_id = c.goods_id
        where tab1.shop_id='$shop_id' and 
        (tab1.create_time between $date_from and $date_to) and tab1.goods_id>0 
        and tab2.pay_status='1' and c.no_use=0
        group by tab1.goods_id
        order by nums desc limit 20 ";
        $rs = $db->select($sql);

        foreach($rs as $k=>$v) {
            $rs[$k]['store'] = $goods[$v['goods_id']]['store'];
            if(!$rs[$k]['store']) $rs[$k]['store'] = $init_store;
            $rs[$k]['avg_nums'] = round($v['nums']/$days,2);
            $rs[$k]['avg_store'] = round($rs[$k]['store']/$rs[$k]['avg_nums'],2);
            foreach($v as $kk=>$vv) {//合计
                if ($kk=='amount' || $kk=='nums')
                $total_data[$kk] += $vv;
            }
        }

        $data = array('analysis_data'=>$rs,'total_data'=>$total_data);
        return $data;
    }

    public function get_rfm_data($filter){

        if(1 or $this->load_redis($filter['shop_id']) == false){
            $data = kernel::single('taocrm_analysis_cache')->get_rfm_cache($filter);
        }else{
            //进入后台队列运算
            $func = 'get_rfm_data';
            $oCacheReport = kernel::single('taocrm_cache_report');
            $cache_id = $oCacheReport->getCacheId($func,$filter);
            $cache_status = $oCacheReport->get($cache_id);
            //echo('<pre>');var_dump($cache_status);
            if($cache_status['status'] == 'REQ_CACHE'){
                die($this->loading_tips(2));
            }elseif($cache_status['status'] == 'PRE_CACHE'){
                die($this->loading_tips(3));
            }elseif($cache_status['status'] == 'SUCC'){
                $data = $cache_status['data'];//报表数据
            }else{
                $oCacheReport->fetch($func,$filter);
                die($this->loading_tips(1));
            }
        }
        return $data;
    }

    public function getNewRfmData($filter, $shop_id = '')
    {
        if (isset($filter['shop_id']) && $filter['shop_id']) {
            $shop_id = $filter['shop_id'];
        }
        else {
            $filter['shop_id'] = $shop_id;
        }

        //        if ($this->load_redis($filter['shop_id']) == false) {
        //            $data = kernel::single('taocrm_analysis_cache')->getRewRfmCache($filter);
        //        }
        //        else {
        //            $data = array();
        //        }
        $data = kernel::single('taocrm_analysis_cache')->getRewRfmCache($filter);
        return $data;

    }

    function get_profit_data($filter){

        if($this->load_redis($filter['shop_id']) == true){
            //进入后台队列运算
            $func = 'get_profit_data';
            $oCacheReport = kernel::single('taocrm_cache_report');
            $cache_id = $oCacheReport->getCacheId($func,$filter);
            $cache_status = $oCacheReport->get($cache_id);
            if($cache_status['status'] == 'REQ_CACHE'){
                die($this->loading_tips(2));
            }elseif($cache_status['status'] == 'PRE_CACHE'){
                die($this->loading_tips(3));
            }elseif($cache_status['status'] == 'SUCC'){
                $data = $cache_status['data'];//报表数据
            }else{
                $oCacheReport->fetch($func,$filter);
                die($this->loading_tips(1));
            }
            return $data;
        }

        if(!empty($_GET['shop_id'])&& empty($_GET['nums']) &&empty($_GET['money'])){
            $shop_id=$_GET['shop_id'];
            $totalmembers=$this->get_members($shop_id);
            $on_members=round(($totalmembers/10));
            $data_array=array();
            for ($i=1;$i<=10;$i++){
                $tota_money=$this->get_all_mon($shop_id,$on_members*$i);
                $data_array[$i]['date']=($on_members*$i);
                $data_array[$i]['duration']=ceil($tota_money)?ceil($tota_money):0;//销售总金额
                //$data_array[$i]['distance']=$this->get_para($shop_id, ($on_members*$i));//预测销售额
            }
            $sumxy=0;
            $sumx=0;
            $sumy=0;
            $sum2=0;
            foreach ($data_array as $k=>$v){
                $n=$v['date'];
                $sumxy=$sumxy+($v['date']*$v['duration']);
                $sumx=$sumx+$v['date'];
                $sumy=$sumy+$v['duration'];
                $sum2=$sum2+($v['date']*$v['date']);
            }
            foreach ($data_array as $k=>$v){
                $B=($sumxy-(($sumx*$sumy)/$v['date']))/($sum2-($sum2/$v['date']));
                $A=($sumy-($B*$sumx))/$v['date'];
                $y_money=$A+($B*$v['date']);
                $data_array[$k]['distance']=ceil($y_money)?ceil($y_money):0;
            }
        }elseif(!empty($_GET['shop_id'])&& !empty($_GET['nums']) && empty($_GET['money'])){
            $shop_id=$_GET['shop_id'];
            $mnums=$_GET['nums'];
            $ta_num=$this->get_members($shop_id);
            $on_members=round($mnums/10);
            $data_array=array();
            for ($i=1;$i<=10;$i++){
                if (($on_members*$i)<=$ta_num){
                    $tota_money=$this->get_all_mon($shop_id,$on_members*$i);//总销售额
                }else {
                    $tota_money=$this->get_para($ta_num,$shop_id,($on_members*$i)); //总销售额
                }
                $data_array[$i]['date']=($on_members*$i);
                $data_array[$i]['duration']=ceil($tota_money)?ceil($tota_money):0;//销售总金额
                $data_array[$i]['distance']=$this->get_para($ta_num,$shop_id, ($on_members*$i));//预测销售额
            }
        }elseif(!empty($_GET['shop_id'])&& empty($_GET['nums']) && !empty($_GET['money'])){
            return false;
        }
        return $data_array;
    }

    //获取参数
    private function get_para($total_members,$shop_id,$mnums){
        $shop_id=$shop_id;
        $on_members=round(($total_members/10));
        $data_array=array();
        for ($i=1;$i<=10;$i++){
            $tota_money=$this->get_all_mon($shop_id,$on_members*$i);
            $data_array[$i]['date']=($on_members*$i);
            $data_array[$i]['duration']=ceil($tota_money)?ceil($tota_money):0;//销售总金额
        }
        $sumxy=0;
        $sumx=0;
        $sumy=0;
        $sum2=0;
        foreach ($data_array as $k=>$v){
            $n=$v['date'];
            $sumxy=$sumxy+($v['date']*$v['duration']);
            $sumx=$sumx+$v['date'];
            $sumy=$sumy+$v['duration'];
            $sum2=$sum2+($v['date']*$v['date']);
        }
        $B=($sumxy-(($sumx*$sumy)/$mnums))/($sum2-($sum2/$mnums));
        $A=($sumy-($B*$sumx))/$mnums;
        $y_money=$A+($B*$mnums);
        return ceil($y_money);
    }

    private function get_members($shop_id){
        $sql='select count(member_id) as totalme from sdb_taocrm_member_analysis where shop_id="'.$shop_id.'"';
        $count=$this->db->select($sql);
        return $count[0]['totalme'];
    }

    private function get_all_mon($shop_id,$lim){
        $sql='SELECT sum( `total_amount` ) as tota_money
        FROM (
        SELECT total_amount
        FROM sdb_taocrm_member_analysis
        WHERE `shop_id` = "'.$shop_id.'"
        ORDER BY `id` ASC
        LIMIT 0 , '.$lim.'
        ) AS aa';
        $tatal_money=$this->db->select($sql);
        return $tatal_money[0]['tota_money'];
    }

    public function array_sort(&$arr,$keys,$type='desc')
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
        $arr = $new_array;
    }
    
    //修正12月可能出现周数的错误
    private function _correct_week($year,$week,$time)
    {
        if($week=='01' && date('m',$time) == 12){
            $year++;
        }
        return "{$year}.{$week}";
    }
    
    public function key_maps(&$arr)
    {
        foreach($arr as $k=>$v){
            if($k=='orders') $arr['total_orders']=$v;
            if($k=='amount' or $k=='amountTotals')
                $arr['total_amount']=$v;
            if($k=='members') $arr['total_members']=$v;
            if($k=='pOrders') $arr['paid_orders']=$v;
            if($k=='pAmount') $arr['paid_amount']=$v;
            if($k=='pMembers') $arr['paid_members']=$v;
            if($k=='fOrders') $arr['finish_orders']=$v;
            if($k=='fAmount') $arr['finish_amount']=$v;
            if($k=='fMembers') $arr['finish_members']=$v;
            
            if($k=='newMembers') $arr['new_member']=$v;
            if($k=='newTotalAmount') $arr['new_amount']=$v;
            if($k=='oldMembers') $arr['old_member']=$v;
            if($k=='oldTotalAmount') $arr['old_amount']=$v;
            
            if($k=='key') $arr['date']=$v;
        }
    }
    
    public function get_java_date($countBy)
    {
        switch($countBy){
            case 'month':
                return 'yyyy-M';
            break;
            
            case 'week':
                return 'yyyy.w';
            break;
            
            case 'year':
                return 'yyyy';
            break;
            
            default:
                return 'yyyy-M-d';
        }
    }
}

