<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class taocrm_analysis_cache {

    public function __construct(){
        $this->db = kernel::database();
    }
    
    function loading_tips($msg){
        if($msg == 1) $msg = '提交云计算中，请稍等。。。';
        if($msg == 2) $msg = '正在发起请求，请稍等。。。';
        if($msg == 3) $msg = '正在加载数据，请稍等。。。';
        return '<div id="loading_div" style="width:100%;height:800px; background:#FFF;bold;position:absolute;left:0;top:0;text-align:center"><br/><br/>'.$msg.'<br/>
        <img src="'.kernel::base_url(0).'/app/market/statics/loading42.gif" }>
        </div>
        <script>setTimeout("window.location.reload();",30000)</script>';
    }
    
    public function get_hour_cache($filter){
    
        //初始化时段数组
        for($i=0;$i<24;$i++) {
            ($i<10)?$analysis_data['0'.$i] = '0':$analysis_data["$i"] = '0';
        }
        
        if($filter){
            $date_from = strtotime($filter['date_from']);
            $date_to = strtotime($filter['date_to']);
            $where = " AND (createtime between $date_from and $date_to) ";
            $where .= ' AND shop_id = "'.$filter['shop_id'].'" ';
        }
        
        $sql = "select
                FROM_UNIXTIME(createtime,'%H') as hours,count(*) as total_orders,
                count(distinct member_id) as total_members,
                sum(payed) as total_amount
            from sdb_ecorder_orders 
            where 1=1 $where group by FROM_UNIXTIME(createtime,'%H') ";
        $rs = $this->db->select($sql);
        if($rs) {
            foreach($rs as $v){
                $analysis_data[$v['hours']] = $v;
                foreach($v as $kk=>$vv){
                    if(is_numeric($vv)) $total_data[$kk] += $vv;
                }
            }
        }

        $sql = "select
                FROM_UNIXTIME(createtime,'%H') as hours,count(*) as paid_orders,
                count(distinct member_id) as paid_members,
                sum(payed) as paid_amount
            from sdb_ecorder_orders 
            where 1=1 $where AND pay_status='1' group by FROM_UNIXTIME(createtime,'%H')";
        $rs = $this->db->select($sql);
        if($rs){
            foreach($rs as $v){
                $analysis_data[$v['hours']] = array_merge($analysis_data[$v['hours']],$v);
                foreach($v as $kk=>$vv){
                    if(is_numeric($vv)) $total_data[$kk] += $vv;
                }
            }
        }

        $sql = "select FROM_UNIXTIME(createtime,'%H') as hours,
                count(*) as finish_orders,
                count(distinct member_id) as finish_members,
                sum(payed) as finish_amount
            from sdb_ecorder_orders 
            where 1=1 $where AND status='finish'
            group by FROM_UNIXTIME(createtime,'%H')";
        $rs = $this->db->select($sql);
        if($rs) {
            foreach($rs as $v){
                $analysis_data[$v['hours']] = array_merge($analysis_data[$v['hours']],$v);
                foreach($v as $kk=>$vv){
                    if(is_numeric($vv)) $total_data[$kk] += $vv;
                }
            }
        }
        
        $data['analysis_data'] = $analysis_data;
        $data['total_data'] = $total_data;
        return $data;
    }

    /**
     * @desc 创建地域分析的缓存
     */
    public function get_area_cache($filter){
    
        $shop_id = $filter['shop_id'];
        $date_from = strtotime($filter['date_from']);
        $date_to = strtotime($filter['date_to']);

        //地区列表
        $sql = "SELECT region_id,local_name,group_name FROM sdb_ectools_regions 
                WHERE region_grade=1 ";
        $rs = $this->db->select($sql);
        if($rs){
        	foreach($rs as $v){
        		$regions[$v['region_id']] = trim($v['local_name']);
        	}
        }
        
        $filter = array(
            'startDate'=> $date_from,
            'endDate'=> $date_to,
            'shopId'=>$filter['shop_id']
        );
        
        $memory_data = kernel::single('taocrm_middleware_connect')->getAreaCount($filter);
        $memory_data_arr = json_decode($memory_data, 1);
        //echo('<pre>');var_dump($memory_data_arr);
        
        if($memory_data_arr['rsp'] != 'succ') return false;
        foreach($memory_data_arr['info']['data'] as $v){
        
            $v['area'] = $regions[$v['key']];
            $v['area'] = str_replace(
                array('省','市','壮族自治区','维吾尔自治区','回族自治区','自治区','特别行政区'),'',$v['area']);        
            kernel::single('taocrm_analysis_day')->key_maps($v);
            foreach($v as $kk=>$vv){
                $total_data[$kk] += $vv;
            }
            if(isset($analysis_data[$v['area']])){
                foreach($v as $kk=>$vv){
                    if($kk!='area') $analysis_data[$v['area']][$kk] += $vv;
                }
            }else{
                $analysis_data[$v['area']] = $v;
            }
        }
        kernel::single('taocrm_analysis_day')->array_sort($analysis_data,'total_amount','desc');
        $data = array('analysis_data'=>$analysis_data,'total_data'=>$total_data);
        //echo('<pre>');var_dump($data);
        return $data; 

        $sql = "SELECT 
            count(*) as total_orders,
            sum(total_amount) as total_amount,
            count(distinct member_id) as total_members,
            state_id
            from sdb_ecorder_orders
            where shop_id='$shop_id' and (createtime between $date_from and $date_to) group by state_id
            order by sum(total_amount) desc ";//die($sql);
        $rs = $this->db->select($sql);
        if($rs) {
        foreach($rs as $v){
            $v['area'] = $regions[$v['state_id']];
            $v['area'] = str_replace('省','',$v['area']);
            $v['area'] = str_replace('市','',$v['area']);
            $v['area'] = str_replace('壮族自治区','',$v['area']);
            $v['area'] = str_replace('维吾尔自治区','',$v['area']);
            $v['area'] = str_replace('回族自治区','',$v['area']);
            $v['area'] = str_replace('自治区','',$v['area']);
            $v['area'] = str_replace('特别行政区','',$v['area']);
            if(!$v['area']) continue;
            if($v['total_orders']) $v['per_amount'] = round($v['total_amount']/$v['total_orders'],2);
            $data[$v['area']] = $v;
        }}
        
        //完成订单
        $sql = "SELECT 
            count(*) as finish_orders,
            sum(total_amount) as finish_amount,
            count(distinct member_id) as finish_members,
            state_id from sdb_ecorder_orders
            where status='finish'
            and shop_id='$shop_id' and (createtime between $date_from and $date_to)
            group by state_id ";
        $rs = $this->db->select($sql);
        if($rs) {
        foreach($rs as $v){
            $v['area'] = $regions[$v['state_id']];
            if(!$v['area']) continue;
            if($v['finish_orders']) $v['finish_per_amount'] = round($v['finish_amount']/$v['finish_orders'],2);
            $v = array_merge($v,$data[$v['area']]);
            $data[$v['area']] = $v;
        }}
        
        //付款订单
        $sql = "SELECT 
            count(*) as paid_orders,
            sum(total_amount) as paid_amount,
            count(distinct member_id) as paid_members,
            state_id
            from sdb_ecorder_orders
            where pay_status='1'
            and shop_id='$shop_id' and (createtime between $date_from and $date_to)
            group by state_id";
        $rs = $this->db->select($sql);
        if($rs) {
            foreach($rs as $v){
                $v['area'] = $regions[$v['state_id']];
                if(!$v['area']) continue;
                if($v['paid_orders']) $v['paid_per_amount'] = round($v['paid_amount']/$v['paid_orders'],2);
                $v = array_merge($v,$data[$v['area']]);
                $data[$v['area']] = $v;
            }
        }
        
        //计算合计
        foreach($data as $k=>$v) {
            if(!$k) unset($data[$k]);
            foreach($v as $kk=>$vv){
                if(is_numeric($vv)) {
                    $total_data[$kk] += $vv;
                }
            }
        }
        
        //计算比例
        foreach($data as $k=>$v) {
            foreach($v as $kk=>$vv){
                if(is_numeric($vv)) {
                    $data[$k][$kk.'_ratio'] = round($vv*100/$total_data[$kk],2);
                }
            }
        }
        
        $data = array('analysis_data'=>$data,'total_data'=>$total_data);
        return $data;        
    }
    
    function create_tree_task(){
        
        $rs = &app::get('ecorder')->model('shop')->getList('*');
        foreach($rs as $v){
            $shop_id = $v['shop_id'];
            $this->create_tree(7,$shop_id);
            $this->create_tree(30,$shop_id);
            $this->create_tree(90,$shop_id);
            $this->create_tree(180,$shop_id);
        }
    }
    
    //决策树缓存
    function create_tree($c_unit,$shop_id){
    
        $oCacheDB = &app::get('taocrm')->model('cache_tree');
        $curr_date = strtotime(date('Y-m-d'));
        $c_unit = intval($c_unit);
        
        //判断缓存是否存在
        $rs = $oCacheDB->count(array('date_from'=>$date_from,'date_to'=>$date_to,'shop_id'=>$shop_id));
        if($rs>0) return true;
        
        //最近周期数据
        $date_to = $curr_date;
        $date_from = strtotime('-'.$c_unit.' days',$curr_date);
        $rs = $this->get_tree_data($date_from,$date_to,$shop_id);
        //var_dump($rs);
        
        //写入缓存表
        if($rs) {
           $rs['date_from'] = $date_from; 
           $rs['date_to'] = $date_to; 
           $rs['cdate'] = $curr_date; 
           $rs['shop_id'] = $shop_id; 
           $rs['create_date'] = date('Y-m-d H:i:s');
           $oCacheDB->insert($rs);
        }

        //上一个周期数据
        $date_to = strtotime('-'.($c_unit).' days',$curr_date);
        $date_from = strtotime('-'.($c_unit*2).' days',$curr_date);
        $rs = $this->get_tree_data($date_from,$date_to,$shop_id);
        
        //写入缓存表
        if($rs) {
           $rs['date_from'] = $date_from; 
           $rs['date_to'] = $date_to; 
           $rs['cdate'] = $curr_date; 
           $rs['shop_id'] = $shop_id; 
           $rs['create_date'] = date('Y-m-d H:i:s');
           $oCacheDB->insert($rs);
        }
    }
    
    function get_tree_data($date_from,$date_to,$shop_id){
    
        //全部数据
        $sql = "select 
        sum(item_num) as items,count(order_id) as orders,sum(total_amount) as amount,count(distinct member_id) as members 
        from sdb_ecorder_orders 
        where (createtime between $date_from and $date_to) AND shop_id='$shop_id' ";
		//var_dump($sql);
        $rs = $this->db->selectRow($sql);
        if($rs) {
            $summary['total_orders'] = $rs['orders'];
            $summary['total_amount'] = $rs['amount'];
            $summary['total_members'] = $rs['members'];
            $summary['total_items'] = $rs['items'];
        }
        
        //付款数据
        $sql = "select 
        count(order_id) as orders,sum(total_amount) as amount,
        count(distinct member_id) as members from sdb_ecorder_orders 
        where (createtime between $date_from and $date_to) AND pay_status='1' AND shop_id='$shop_id'";
        $rs = $this->db->selectRow($sql);
        if($rs) {
            $summary['paid_orders'] = $rs['orders'];
            $summary['paid_amount'] = $rs['amount'];
            if($rs['orders']) $summary['paid_per_amount'] = $summary['paid_amount']/$rs['orders'];
            if($rs['members']) $summary['paid_per_user_amount'] = $summary['paid_amount']/$rs['members'];
            $summary['unpaid_amount'] = $summary['total_amount'] - $rs['amount'];
        }
        
        /*
        $sql = "select count(member_id) as new_members from sdb_taocrm_member_analysis 
        where shop_id='$shop_id' and (first_buy_time between $date_from and $date_to) ";
        $rs = $this->db->selectRow($sql);
        if($rs) {
            $new_members = $rs['new_members'];
            $summary['new_members'] = $new_members;
        }*/
        
        //新客户数据
        $sql = "select 
        sum(a.total_amount) as amount,sum(a.item_num) as items,
        count(distinct a.member_id) as new_members,
        count(a.order_id) as orders from sdb_ecorder_orders as a
        inner join (
            select distinct member_id from sdb_taocrm_member_analysis
            where first_buy_time between $date_from and $date_to
        ) as b on a.member_id=b.member_id
        where (a.createtime between $date_from and $date_to) AND a.shop_id='$shop_id'
        ";//die($sql);
        $rs = $this->db->selectRow($sql);
        if($rs) {
            $summary['new_members'] = $rs['new_members'];
            $summary['new_items'] = $rs['items'];
            $summary['new_orders'] = $rs['orders'];
            $summary['new_amount'] = $rs['amount'];
        }
        
        if($summary['new_members']){
            $summary['new_per_amount'] = $summary['new_amount']/$summary['new_members'];
            $summary['new_per_items'] = $summary['new_items']/$summary['new_members'];
        }
        if($summary['new_items'])
            $summary['new_per_price'] = $summary['new_amount']/$summary['new_items'];
            
        //根据新客户数据计算老客户数据
        $summary['old_members'] = $summary['total_members'] - $summary['new_members'];
        $summary['old_amount'] = $summary['total_amount'] - $summary['new_amount'];
        $summary['old_orders'] = $summary['total_orders'] - $summary['new_orders'];
        $summary['old_items'] = $summary['total_items'] - $summary['new_items'];
        if($summary['old_members']){
            $summary['old_per_amount'] = $summary['old_amount']/$summary['old_members'];
            $summary['old_per_items'] = $summary['old_items']/$summary['old_members'];
        }
        if($summary['old_items'])
            $summary['old_per_price'] = $summary['old_amount']/$summary['old_items'];
        return $summary;
    }
    
    public function get_rfm_cache($filter)
    {
        $R = $filter['r'];
        $F = $filter['f'];
        $shop_id = $filter['shop_id'];
        $rules = array(
           0 => array(
               array(0, 2),
               array(1, 2),
               array(2, 2)
           ),
           1 => array(
               array(0, 1),
               array(1, 1),
               array(2, 1),
           ),
           2 => array(
               array(0, 0),
               array(1, 0),
               array(2, 0)
           )
        );
        $dayTime = 86400;
        $time = strtotime(date("Y-m-d 00:00:00"));
        $formatR = array(0 => $R[0][1], 1 => $R[1], 2 => $R[2][0]);
        $formatF = array(0 => $F[0][1], 1 => $F[1], 2 => $F[2][0]);
        //设置UNIX时间戳
        foreach ($formatR as &$v) {
            if (is_array($v)) {
                foreach ($v as &$v1) {
                     $v1 = $time - $v1 * $dayTime;
                }
            }
            else {
                $v = $time - $v * $dayTime;
            }
        }
        $params = array('R' => $formatR, 'F' => $formatF, 'shop_id' => $shop_id);
        $data = $this->getRfmCacheData($rules, $params);
        $returnData = array(
           'analysis_data' => array(),
           'total_r_data' => array(),
           'total_f_data' => array(),
           'total_data' => array()
        );
        if (!empty($data)) {
            for ($i = 0 ; $i < count($data) ; $i++ ) {
                $returnData['total_data']['members'] += $data[$i]['members'];
                $returnData['total_data']['amount'] += $data[$i]['amount'];
                
                $ri = $i % 3;
                $returnData['total_r_data'][$ri]['members'] += $data[$i]['members'];
                $returnData['total_r_data'][$ri]['amount'] += $data[$i]['amount'];
                
                $fi = (int)($i / 3);
                $returnData['total_f_data'][$fi]['members'] += $data[$i]['members'];
                $returnData['total_f_data'][$fi]['amount'] += $data[$i]['amount'];
                
                $returnData['analysis_data'][$fi][$ri]['members'] = $data[$i]['members'];
                $returnData['analysis_data'][$fi][$ri]['amount'] = $data[$i]['amount'];
                
                $returnData['analysis_data'][$fi][$ri]['R'] = $data[$i]['R'];
                $returnData['analysis_data'][$fi][$ri]['F'] = $data[$i]['F'];
                $returnData['analysis_data'][$fi][$ri]['PR'][0] = $data[$i]['PR'][0];
                $returnData['analysis_data'][$fi][$ri]['PR'][1] = $data[$i]['PR'][1];
                $returnData['analysis_data'][$fi][$ri]['PF'][0] = $data[$i]['PF'][0];
                $returnData['analysis_data'][$fi][$ri]['PF'][1] = $data[$i]['PF'][1];
                $returnData['analysis_data'][$fi][$ri]['filter_sql'] = $data[$i]['filter_sql'];
            }
        }
        return $returnData;
    }
    
    public function getRfmCacheData($rules, $params)
    {
        $timeValue = 86400;
        $time = strtotime(date('Y-m-d', time()));
        $desc = array('<=', '-', '>=');
        $data = array();
        $sql = '';
        $i = 0;
        foreach ($rules as $k => $v) {
            foreach ($v as $k1 => $v1) {
                 $sql = 'SELECT COUNT(DISTINCT member_id) AS _count, SUM(finish_total_amount) AS _sum_total_amount FROM `sdb_taocrm_member_analysis` WHERE 1=1';
                 $filterSql = 'SELECT DISTINCT member_id FROM `sdb_taocrm_member_analysis` WHERE 1=1';
                 switch ($v1[0]) {
                     case 0:
                         $sql .= " AND `last_buy_time` >=  {$params['R'][0]} "; 
                         $filterSql .= " AND `last_buy_time` >=  {$params['R'][0]} "; 
                         $data[$i]['R'] = $desc[0] . ($time - $params['R'][0]) / $timeValue; 
                         $data[$i]['PR'][0] = 0;
                         $data[$i]['PR'][1] =($time - $params['R'][0]) / $timeValue;
                         break;
                     case 1:
                         $sql .= " AND `last_buy_time` <= {$params['R'][1][0]} AND `last_buy_time` >= {$params['R'][1][1]} ";
                         $filterSql .= " AND `last_buy_time` <= {$params['R'][1][0]} AND `last_buy_time` >= {$params['R'][1][1]} ";
                         $data[$i]['R'] = ($time - $params['R'][1][0]) / $timeValue .$desc[1]. ($time - $params['R'][1][1]) / $timeValue;
                         $data[$i]['PR'][0] = ($time - $params['R'][1][0]) / $timeValue;
                         $data[$i]['PR'][1] = ($time - $params['R'][1][1]) / $timeValue;
                         break;
                     case 2:
                         $sql .= " AND `last_buy_time` <= {$params['R'][2]} ";
                         $filterSql .= " AND `last_buy_time` <= {$params['R'][2]} ";
                         $data[$i]['R'] = $desc[2] . ($time - $params['R'][2]) / $timeValue;
                         $data[$i]['PR'][0] = ($time - $params['R'][2]) / $timeValue;
                         $data[$i]['PR'][1] = 0;
                         break;
                 }
                 switch ($v1[1]) {
                     case 0:
                         $sql .= " AND `finish_orders` <= {$params['F'][0]} ";
                         $filterSql .= " AND `finish_orders` <= {$params['F'][0]} ";
                         $data[$i]['PF'][0] = 0;
                         $data[$i]['PF'][1] = $params['F'][0];
                         $data[$i]['F'] = $desc[0] . $params['F'][0]; 
                         break;
                     case 1:
                         $sql .= " AND `finish_orders` >= {$params['F'][1][0]} AND  `finish_orders` <= {$params['F'][1][1]}";
                         $filterSql .= " AND `finish_orders` >= {$params['F'][1][0]} AND  `finish_orders` <= {$params['F'][1][1]}";
                         $data[$i]['PF'][0] = $params['F'][1][0];
                         $data[$i]['PF'][1] = $params['F'][1][1];
                         $data[$i]['F'] = $params['F'][1][0] . $desc[1] . $params['F'][1][1];
                         break;
                     case 2:
                         $sql .= " AND `finish_orders`>= {$params['F'][2]}";
                         $filterSql .= " AND `finish_orders`>= {$params['F'][2]}";
                         $data[$i]['PF'][0] = $params['F'][2];
                         $data[$i]['PF'][1] = 0;
                         $data[$i]['F'] = $desc[2] . $params['F'][2];
                         break;
                 }
                 $sql .= " AND shop_id = '{$params['shop_id']}' ";
                 $filterSql .= " AND shop_id = '{$params['shop_id']}' ";
                 $result = $this->db->select($sql);
                 $data[$i]['amount'] = max(0, $result[0]['_sum_total_amount']);
                 $data[$i]['members'] = max(0, $result[0]['_count']);
                 $data[$i]['log_sql'] = $sql;
                 $data[$i]['filter_sql'] = $filterSql;
                 $i++;
            }
        }
        return $data;
    }
    
    //rfm缓存数据
    function get_rfm_cache_back($filter){
        $r = $filter['r'];
        $f = $filter['f'];
        $shop_id = $filter['shop_id'];

        $page_size = 10000;//防止内存溢出
        for($page=0;$page<=100;$page++){//最多支持单店铺100w客户
            $sql = "SELECT member_id,last_buy_time,finish_orders,
                    finish_total_amount,unpay_amount
                FROM sdb_taocrm_member_analysis
                WHERE shop_id='".$shop_id."'
                LIMIT ".$page*$page_size.",$page_size ";
            $rs = $this->db->select($sql);
            if($rs){
                foreach($rs as $v){
                    $orders = $v['finish_orders'];
                    $date_diff = (strtotime(date('Y-m-d')) - strtotime(date('Y-m-d',$v['last_buy_time'])))/(24*60*60);
//                    $pay_amount = $v['total_amount'] - $v['unpay_amount'];
                    $pay_amount = $v['finish_total_amount'];
                    if($pay_amount<0) $pay_amount = 0;

                    $axis = $this->get_rfm_axis($r,$f,$orders,$date_diff);

                    $analysis_data[$axis['f']][$axis['r']]['members'] += 1;
                    $analysis_data[$axis['f']][$axis['r']]['amount'] += $pay_amount;

                    $total_r_data[$axis['r']]['members'] += 1;
                    $total_r_data[$axis['r']]['amount'] += $pay_amount;

                    $total_f_data[$axis['f']]['members'] += 1;
                    $total_f_data[$axis['f']]['amount'] += $pay_amount;

                    $total_data['members'] += 1;
                    $total_data['amount'] += $pay_amount;
                }
                unset($rs);
            }else{
                break;
            }
        }

        $data = array(
            'analysis_data' => $analysis_data,
            'total_r_data' => $total_r_data,
            'total_f_data' => $total_f_data,
            'total_data' => $total_data
        );
        return $data;
    }
    
    public function getRewRfmCache($filter)
    {
        $time = strtotime(date("Y-m-d 00:00:00"));
        $R = $time - $filter['Rmain'] * 86400;
        //规则1： R[0] F[1] M[1]
        $params = array('R' => $R, 'F' => $filter['Fmain'], 'M' => $filter['Mmain'], 'shop_id' => $filter['shop_id']);
        $data = array();
        $rules = array(
            array(1, 1, 1),
            array(1, 0, 0),
            array(1, 0, 1),
            array(0, 1, 1),
            array(1, 1, 0),
            array(0, 0, 1),
            array(0, 1, 0),
            array(0, 0, 0)
        );
        $data = $this->getRewRfmCacheData($rules, $params);
        return $data;
    }
    
    public function getRewRfmCacheData($rules, $params)
    {
        $data = array();
        foreach ($rules as $k => $v) {
            $sql = 'SELECT COUNT(DISTINCT member_id) AS _count, SUM(finish_total_amount) AS _sum_total_amount FROM `sdb_taocrm_member_analysis` WHERE 1=1';
            $filter_sql = 'SELECT DISTINCT member_id FROM `sdb_taocrm_member_analysis` WHERE 1=1';
            if ($v[0] == 0) {
                $sql .= " AND last_buy_time <=  {$params['R']}";
                $filter_sql .= " AND last_buy_time <=  {$params['R']}";
            }
            else {
                $sql .= " AND last_buy_time >  {$params['R']}";
                $filter_sql .= " AND last_buy_time >  {$params['R']}";
            }
            if ($v[1] == 0) {
                $sql .= " AND finish_orders <=  {$params['F']}";
                $filter_sql .= " AND finish_orders <=  {$params['F']}";
            }
            else {
                $sql .= " AND finish_orders >  {$params['F']}";
                $filter_sql .= " AND finish_orders >  {$params['F']}";
            }
            if ($v[2] == 0) {
                $sql .= " AND finish_per_amount <=  {$params['M']}";
                $filter_sql .= " AND finish_per_amount <=  {$params['M']}";
            }
            else {
                $sql .= " AND finish_per_amount >  {$params['M']}";
                $filter_sql .= " AND finish_per_amount >  {$params['M']}";
            }
            $sql .= " AND shop_id = '{$params['shop_id']}'";
            $filter_sql .= " AND shop_id = '{$params['shop_id']}'";
            $result = $this->db->select($sql);
            $data[$k]['count'] = $result[0]['_count'];
            $data[$k]['sum_total_amount'] = max(0, $result[0]['_sum_total_amount']);
            $data[$k]['filter_sql'] = $filter_sql;
        }
        return $data;
    }
    
    private function get_rfm_axis(&$r,&$f,&$orders,&$date_diff){
        $axis = array();
        foreach($r as $k=>$v){
            if($v[1]==0) {
                if($date_diff >= $v[0]) $axis['r'] = $k;
            }else{
                if($date_diff >= $v[0] && $date_diff <= $v[1]) $axis['r'] = $k;
            }
        }
        
        foreach($f as $k=>$v){
            if($v[1]==0) {
                if($orders >= $v[0]) $axis['f'] = $k;
            }else{
                if($orders >= $v[0] && $orders <= $v[1]) $axis['f'] = $k;
            }
        }
        return $axis;
    }
    
    function get_buy_times_cache($filter)
    {
        $shop_id = $filter['shop_id'];
        //$buy_times = intval($filter['buy_times']);
        $date_from = strtotime($filter['date_from']);
        $date_to = strtotime($filter['date_to']);
    
        if($date_from) $where .= " AND (createtime between $date_from and $date_to) ";
        if($shop_id) $where .= " AND shop_id='$shop_id' ";
    
        //只统计已付款订单
        $sql = "select total_orders,sum(total_orders) as sum_orders,count(distinct member_id) as total_members,sum(total_amount) as total_amount,sum(total_num) as total_num from 
        (select
            count(order_id) as total_orders,
            sum(payed) as total_amount,
            sum(item_num) as total_num,
            member_id
        from sdb_ecorder_orders 
        where 1=1 $where AND pay_status='1'
        group by member_id) as a group by total_orders ";//die($sql);
        $rs = $this->db->select($sql);
        if(!$rs) $rs=array();//var_dump($rs);
        foreach($rs as $v) {
        
            //if($buy_times == $v['total_orders']) $members[] = $v['member_id'];
            
            if($v['total_orders']>5) {
                $v['total_orders']=6;
                $data[$v['total_orders']]['key_name'] = '多于5';
            }else{
                $data[$v['total_orders']]['key_name'] = $v['total_orders'];
            }
            
            if($v['total_orders']>5) {
                $funnel_data['>5'] += $v['total_members'];
            }
            
            if($v['total_orders']>=3 && $v['total_orders']<=5) {
                $funnel_data['3-5'] += $v['total_members'];
            }
            
            $data[$v['total_orders']]['total_num'] += $v['total_num'];
            $data[$v['total_orders']]['total_orders'] = $v['total_orders'];
            $data[$v['total_orders']]['sum_orders'] += $v['sum_orders'];
            $data[$v['total_orders']]['total_amount'] += round($v['total_amount']);
            $data[$v['total_orders']]['total_members'] += $v['total_members'];
        }
        
        return array('analysis_data'=>$data,'funnel_data'=>$funnel_data);
    }
    
    public function get_old_new_cache($filter)
    {
        $shop_id = $filter['shop_id'];
        $count_by = $filter['count_by'];
        //$member_status = $filter['member_status'];
        $date_from = strtotime($filter['date_from']);
        $date_to = strtotime($filter['date_to']);

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
            $where_new = " AND (FROM_UNIXTIME(a.first_buy_time,'$count_unit')='".$filter['date']."') AND (FROM_UNIXTIME(b.createtime,'$count_unit')='".$filter['date']."')";
            $where_old = " AND (FROM_UNIXTIME(createtime,'$count_unit')='".$filter['date']."')";
        }

        $where_new .= " AND (a.first_buy_time between $date_from and $date_to) AND (b.createtime between $date_from and $date_to)";
        $where_old .= " AND (createtime between $date_from and $date_to)";

        //新客户数量
        $new_member = array();
        $old_member = array();
        $sql = "select
        FROM_UNIXTIME(b.createtime,'$count_unit') as first_buy_time,
        count(distinct(a.member_id)) as members,
        sum(b.total_amount) as cost_items
        from sdb_ecorder_orders as b
        left join sdb_taocrm_member_analysis as a on a.member_id=b.member_id
        where 1=1 
        $where_new AND a.shop_id='$shop_id' AND b.shop_id='$shop_id'
        AND FROM_UNIXTIME(b.createtime,'$count_unit')=FROM_UNIXTIME(a.first_buy_time,'$count_unit')
        group by FROM_UNIXTIME(b.createtime,'$count_unit') ";
        $rs = $this->db->select($sql);//echo($sql);
        if($rs){
            foreach($rs as $v) {
                $new_member[$v['first_buy_time']] = $v['members'];
                $new_amount[$v['first_buy_time']] = $v['cost_items'];
            }
        }
        unset($rs);
        if($new_member) $data_line = array_keys($new_member);


        //老客户数量
        $sql = "SELECT
        FROM_UNIXTIME(createtime,'$count_unit') as createtime,
        count(distinct(member_id)) as members,
        sum(total_amount) as cost_items
        FROM sdb_ecorder_orders WHERE 
        shop_id='$shop_id' $where_old
        GROUP BY FROM_UNIXTIME(createtime,'$count_unit')";
        $rs = $this->db->select($sql);
        if($rs) {
            foreach($rs as $v) {
                $old_member[$v['createtime']] = $v['members'] - $new_member[$v['createtime']];
                $old_amount[$v['createtime']] = $v['cost_items'] - $new_amount[$v['createtime']];
            }
        }
        unset($rs);

        if($data_line) {
            $data_line = array_unique(array_merge($data_line,array_keys($old_member)));
        }else{
            $data_line = array_unique(array_keys($old_member));
        }

        if($data_line){
            foreach($data_line as $v) {
            
                if(!isset($new_amount[$v])) $new_amount[$v] =0;
                if(!isset($old_amount[$v])) $old_amount[$v] =0;
                if(!isset($old_member[$v])) $old_member[$v] =0;
                if(!isset($new_member[$v])) $new_member[$v] =0;
            
                $data[$v]['new_amount'] = $new_amount[$v];
                $data[$v]['old_amount'] = $old_amount[$v];
                if($data[$v]['new_amount']+$data[$v]['old_amount']>0)
                    $data[$v]['old_amount_ratio'] = round($data[$v]['old_amount']*100/($data[$v]['new_amount']+$data[$v]['old_amount']),2);

                $data[$v]['old_member'] = $old_member[$v];
                $data[$v]['new_member'] = $new_member[$v];
                if($new_member[$v] + $old_member[$v]>0)
                    $data[$v]['old_ratio'] = $old_member[$v] / ($new_member[$v] + $old_member[$v]);
                $data[$v]['old_ratio'] = round($data[$v]['old_ratio']*100,2);

                $total_data['old_member'] += $old_member[$v];
                $total_data['new_member'] += $new_member[$v];
                $total_data['new_amount'] += $new_amount[$v];
                $total_data['old_amount'] += $old_amount[$v];
            }
        }

        if($data) ksort($data);
        $data = array('analysis_data'=>$data,'total_data'=>$total_data);

        return $data;
    }
    
}