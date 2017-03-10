<?php
class market_backstage_report{

    var $unuse_words = array('补运费','补差价','不拍不送','邮费专拍','拍下无效');

    // 'cache:report:aa:3324'
    function fetch($data){

        $result = array('status'=>'runing');
        kernel::single('taocrm_service_redis')->redis->SET($data['cacheId'],json_encode($result));
        $db = kernel::database();
        $cacheData = $this->{$data['func']}($data['filter']);
        $db->dbclose();
        $result = array('status'=>'finish','data'=>$cacheData,'expired'=>strtotime(date('Y-m-d 23:59:59')));
        kernel::single('taocrm_service_redis')->redis->SET($data['cacheId'],json_encode($result));
         
        return array('status'=>'succ');
    }
     

    //新老客户
    public function get_member_old_new($filter){
        $db = kernel::database();
        $shop_id = $filter['shop_id'];
        $count_by = $filter['count_by'];
        //$member_status = $filter['member_status'];
        $date_from = strtotime($filter['date_from']);
        $date_to = strtotime($filter['date_to']);

        switch($count_by):
        case 'week':
            $count_unit = '%U';
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

            //新客户数量
            $new_member = array();
            $old_member = array();
            $sql = "select
        FROM_UNIXTIME(a.first_buy_time,'$count_unit') as first_buy_time,
        count(distinct(a.member_id)) as members,
        sum(b.payed) as cost_items
        from sdb_taocrm_member_analysis as a
        left join sdb_ecorder_orders as b on a.member_id=b.member_id
        where 
        a.shop_id='$shop_id' $where_new
        and FROM_UNIXTIME(b.createtime,'$count_unit')=FROM_UNIXTIME(a.first_buy_time,'$count_unit')
        group by FROM_UNIXTIME(a.first_buy_time,'$count_unit') ";
            $rs = $db->select($sql);
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
        sum(payed) as cost_items
        FROM sdb_ecorder_orders WHERE 
        shop_id='$shop_id' $where_old
        GROUP BY FROM_UNIXTIME(createtime,'$count_unit')";
            $rs = $db->select($sql);
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

    //客户下单次数
    public function get_member_buy_times($filter){
        $db = kernel::database();
        $shop_id = $filter['shop_id'];
        //$buy_times = intval($filter['buy_times']);
        $date_from = strtotime($filter['date_from']);
        $date_to = strtotime($filter['date_to']);

        $where = '';
        if($shop_id) $where .= " AND shop_id='$shop_id' ";
        if($date_from) $where .= " AND (createtime between $date_from and $date_to) ";

        //只统计已付款订单
        $sql = "select total_orders,
        count(distinct member_id) as total_members,
        sum(total_amount) as total_amount,
        sum(total_num) as total_num from 
            (select count(order_id) as total_orders,
             sum(payed) as total_amount,
             sum(item_num) as total_num,
             member_id
            from sdb_ecorder_orders 
            where 1=1 $where AND pay_status='1'
            group by member_id) as a 
        group by total_orders";
        //die($sql);
        $rs = $db->select($sql);
        if(!$rs) $rs=array();
        foreach($rs as $v) {

            //if($buy_times == $v['total_orders']) $members[] = $v['member_id'];

            if($v['total_orders']>6) {
                $v['total_orders']=7;
                $data[$v['total_orders']]['key_name'] = '多于6';
            }else{
                $data[$v['total_orders']]['key_name'] = $v['total_orders'];
            }

            if($v['total_orders']>5) $funnel_data['>5'] += $v['total_members'];
            if($v['total_orders']>=3 && $v['total_orders']<=5) $funnel_data['3-5'] += $v['total_members'];

            $data[$v['total_orders']]['total_num'] += $v['total_num'];
            $data[$v['total_orders']]['total_orders'] = $v['total_orders'];
            $data[$v['total_orders']]['total_amount'] += round($v['total_amount']);
            $data[$v['total_orders']]['total_members'] += 1;

            $data[$v['total_orders']]['total_num'] = $v['total_num'];
            $data[$v['total_orders']]['total_orders'] = $v['total_orders'];
            $data[$v['total_orders']]['total_amount'] = round($v['total_amount']);
            $data[$v['total_orders']]['total_members'] = $v['total_members'];
        }unset($rs);

        $data = array('analysis_data'=>$data,'funnel_data'=>$funnel_data);
        return $data;
    }

    //rfm缓存数据
    function get_rfm_data($filter){
        $db = kernel::database();
        //rfm统计区域
        //$r = array(array(0,30),array(31,90),array(91,0));
        //$f = array(array(0,3),array(4,7),array(8,0));

        $r = $filter['r'];
        $f = $filter['f'];
        $shop_id = $filter['shop_id'];

        $page_size = 10000;//防止内存溢出
        for($page=0;$page<=100;$page++){//最多支持单店铺100w客户
            $sql = "SELECT member_id,last_buy_time,total_orders,
                    total_amount,unpay_amount
                FROM sdb_taocrm_member_analysis
                WHERE shop_id='".$shop_id."'
                LIMIT ".$page*$page_size.",$page_size ";
            $rs = $db->select($sql);
            if($rs){
                foreach($rs as $v){
                    $orders = $v['total_orders'];
                    $date_diff = (strtotime(date('Y-m-d')) - strtotime(date('Y-m-d',$v['last_buy_time'])))/(24*60*60);
                    $pay_amount = $v['total_amount'] - $v['unpay_amount'];
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


    //购买时段分析
    public function get_hours_data($filter){
        $db = kernel::database();
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
                sum(cost_item) as total_amount
            from sdb_ecorder_orders 
            where 1=1 $where group by FROM_UNIXTIME(createtime,'%H') ";
        $rs = $db->select($sql);
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
        $rs = $db->select($sql);
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
        $rs = $db->select($sql);
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

    function get_area_data($filter) {
        $db = kernel::database();
        //@set_time_limit(60);

        $shop_id = $filter['shop_id'];
        $date_from = strtotime($filter['date_from']);
        $date_to = strtotime($filter['date_to']);
        $data = array();

        //地区列表
        $sql = "SELECT region_id,local_name,group_name FROM sdb_ectools_regions
                WHERE region_grade=1 ";
        $rs = $db->select($sql);
        if($rs){
            foreach($rs as $v){
                $regions[$v['region_id']] = trim($v['local_name']);
            }
        }

        $sql = "SELECT
            count(*) as total_orders,
            sum(total_amount) as total_amount,
            count(distinct member_id) as total_members,
            state_id
            from sdb_ecorder_orders
            where shop_id='$shop_id' and (createtime between $date_from and $date_to) group by state_id
            order by sum(total_amount) desc ";//die($sql);
        $rs = $db->select($sql);
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
            }
        }

        //完成订单
        $sql = "SELECT
        count(*) as finish_orders,
        sum(total_amount) as finish_amount,
        count(distinct member_id) as finish_members,
        state_id from sdb_ecorder_orders
        where status='finish'
        and shop_id='$shop_id' and (createtime between $date_from and $date_to)
        group by state_id ";
        $rs = $db->select($sql);
        if($rs) {
            foreach($rs as $v){
                $v['area'] = $regions[$v['state_id']];
                if(!$v['area']) continue;
                if($v['finish_orders']) $v['finish_per_amount'] = round($v['finish_amount']/$v['finish_orders'],2);
                if($data[$v['area']]) $v = array_merge($v,$data[$v['area']]);
                $data[$v['area']] = $v;
            }
        }

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
        $rs = $db->select($sql);
        if($rs) {
            foreach($rs as $v){
                $v['area'] = $regions[$v['state_id']];
                if(!$v['area']) continue;
                if($v['paid_orders']) $v['paid_per_amount'] = round($v['paid_amount']/$v['paid_orders'],2);
                if($data[$v['area']]) $v = array_merge($v,$data[$v['area']]);
                $data[$v['area']] = $v;
            }
        }

        //计算合计
        foreach($data as $k=>$v) {
            if(!$k) unset($data[$k]);
            if(!is_array($v)) continue;
            foreach($v as $kk=>$vv){
                if(is_numeric($vv)) {
                    $total_data[$kk] += $vv;
                }
            }
        }

        //计算比例
        foreach($data as $k=>$v) {
            if(!is_array($v)) continue;
            foreach($v as $kk=>$vv){
                if(is_numeric($vv)) {
                    $data[$k][$kk.'_ratio'] = round($vv*100/$total_data[$kk],2);
                }
            }
        }

        $data = array('analysis_data'=>$data,'total_data'=>$total_data);
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

    function get_profit_data($filter){
        $_GET = $filter;
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
        $db = kernel::database();
        $sql='select count(member_id) as totalme from sdb_taocrm_member_analysis where shop_id="'.$shop_id.'"';
        $count=$db->select($sql);
        return $count[0]['totalme'];
    }

    private function get_all_mon($shop_id,$lim){
        $db = kernel::database();
        $sql='SELECT sum( `total_amount` ) as tota_money
			FROM (
				SELECT total_amount
				FROM sdb_taocrm_member_analysis
				WHERE `shop_id` = "'.$shop_id.'"
				ORDER BY `id` ASC
				LIMIT 0 , '.$lim.'
			) AS aa';
        $tatal_money=$db->select($sql);
        return $tatal_money[0]['tota_money'];
    }
    
    //关联商品分析
    public function get_goods_relation($filter){
    
        $max_num = 15;//最多返回前15个分析结果
        $db = kernel::database();
        $goods_id = $filter['goods_id'];
  
        $goods_a_arr = $this->get_goods_relate($goods_id,$filter);
        $order_a = $goods_a_arr['orders'];//购买A商品的订单编号
        $member_a = $goods_a_arr['members'];//购买A商品的客户

        $sql = "select goods_id,name,order_id from sdb_ecorder_order_items 
            where order_id in (".implode(',',$order_a).")
            order by order_id";
        $rs = $db->select($sql);
        foreach($rs as $v){
        
            $unuse = 0;
            foreach($this->unuse_words as $vv){
                if(strstr($v['name'],$vv)){$unuse = 1;break;}
            }
            if($unuse == 1) continue;
        
            if($goods_id != $v['goods_id']) {
                $analysis_data[$v['goods_id']]['times'] += 1;
                $analysis_data[$v['goods_id']]['name'] = $v['name'];
            }
        }
        
        //按购买次数排序
        kernel::single('taocrm_analysis_day')->array_sort($analysis_data,'times','desc');
        
        $i = 0;
        foreach($analysis_data as $k=>$v){
            if($i >= $max_num) {
                unset($analysis_data[$k]);
                continue;
            }
            
            $goods_b = $k;
            $goods_b_arr = $this->get_goods_relate($goods_b,$filter);
            $order_b = $goods_b_arr['orders'];//购买B商品的订单编号
            $member_b = $goods_b_arr['members'];//购买B商品的客户
            
            //购买过A或B的订单总数
            $order_ab = array_unique(array_merge($order_a,$order_b));
            foreach($order_a as $vv){
                if(in_array($vv,$order_b)) {
                    $b_in_a += 1;
                }
            }
            foreach($order_b as $vv){
                if(in_array($vv,$order_a)) {
                    $a_in_b += 1;
                }
            }
            
            foreach($member_a as $vv){
                if(in_array($vv,$member_b)) {
                    $member_ab[] = $vv;
                }else{
                    $a_members[] = $vv;
                }
            }
            
            $analysis_data[$k]['ab_ratio'] = $v['times']/count($order_ab);
            $analysis_data[$k]['b_ratio'] = $b_in_a/count($order_a);
            $analysis_data[$k]['a_ratio'] = $a_in_b/count($order_b);
            $analysis_data[$k]['ab_members'] = count(array_unique($member_ab));
            $analysis_data[$k]['a_members'] = count(array_unique($a_members));
            $analysis_data[$k]['order_a'] = count($order_a);
            $analysis_data[$k]['order_b'] = count($order_b);
            
            unset($b_in_a,$a_in_b,$member_ab,$a_members);
            
            $i++;
        }
        
        
        return $analysis_data;
    }
    
    //商品购物篮分析
    public function get_basket($filter){
    
        $db = kernel::database();
        $shop_id = $filter['shop_id'];
    
        //符合条件的order_id
        $where = '';
        if ($filter['shop_id']) $where .= " and shop_id='$shop_id' ";
        $where .= " and (create_time between ".$filter['date_from']." and ".$filter['date_to'].") ";
        $sql = "
            SELECT order_id FROM `sdb_ecorder_order_items`
            where goods_id>0 $where
            group by order_id HAVING count(goods_id)>1
        ";
        $rs = $db->select($sql);
        foreach($rs as $v){
            $order_id[] = $v['order_id']; 
        }
        
        if($order_id) {
            $sql = "
                select a.goods_id,a.shop_goods_id,a.name,a.order_id,b.pic_url,c.member_id from sdb_ecorder_order_items as a
                left join sdb_ecgoods_shop_goods as b on a.shop_goods_id=b.outer_id
                left join sdb_ecorder_orders as c on a.order_id=c.order_id
                where a.goods_id>0 and a.order_id in (".implode(',',$order_id).")
            ";
            $rs = $db->select($sql);
            foreach($rs as $v){
            
                $unuse = 0;
                foreach($this->unuse_words as $vv){
                    if(strstr($v['name'],$vv)){
                        $unuse = 1;
                        break;
                    }
                }
                if($unuse == 1) continue;
            
                $orders[$v['order_id']]['member_id'] = $v['member_id'];
                if(!$orders[$v['order_id']]['goods_id'][$v['goods_id']]):
                    $orders[$v['order_id']]['goods_id'][$v['goods_id']] = 
                        $v['goods_id'];
                    $orders[$v['order_id']]['goods'][] = 
                        array($v['goods_id'],$v['shop_goods_id'],mb_substr($v['name'],0,25,'utf-8'),'pic_url'=>$v['pic_url']);
                endif;
            }
            
            foreach($orders as $v) {
                for($i=0;$i<count($v['goods'])-1;$i++){
                    for($j=$i+1;$j<count($v['goods']);$j++){
                        $good_x = $v['goods'][$i][0];
                        $good_y = $v['goods'][$j][0];
                        if($analysis_data[$good_x.'_'.$good_y]):
                            $analysis_data[$good_x.'_'.$good_y]['count']++;
                            $analysis_data[$good_x.'_'.$good_y]['member_id'][$v['member_id']] = $v['member_id'];
                        elseif($analysis_data[$good_y.'_'.$good_x]):
                            $analysis_data[$good_y.'_'.$good_x]['count']++;
                            $analysis_data[$good_y.'_'.$good_x]['member_id'][$v['member_id']] = $v['member_id'];
                        else:
                            $analysis_data[$good_x.'_'.$good_y] = array(
                                'good_x'=>$v['goods'][$i],
                                'good_y'=>$v['goods'][$j],
                                'member_id'=>array($v['member_id']=>$v['member_id']),
                                'count'=>1
                            );
                        endif;
                    }
                }
            }
            
            foreach($analysis_data as $k=>$v){
                $analysis_data[$k]['members'] = count($v['member_id']);
                unset($analysis_data[$k]['member_id']);
            }
            
            //数组排序
            kernel::single('taocrm_analysis_day')->array_sort($analysis_data,'count','desc');
            //只显示Top100
            $analysis_data = array_slice($analysis_data,0,100);
           
        }
        
        return $analysis_data;
    }

    //获取相关商品的订单数和用户数
    public function get_goods_relate($goods_id,$filter){
    
        $db = kernel::database();
        $where = '';
        if ($filter['shop_id']) $where .= " and shop_id='".$filter['shop_id']."' ";
        $where .= " and (create_time between ".$filter['date_from']." and ".$filter['date_to'].") ";
        
        //购买B商品的订单编号
        $sql = "select order_id from sdb_ecorder_order_items where goods_id=$goods_id $where ";
        $rs = $db->select($sql);
        foreach($rs as $v){
           $orders[] = $v['order_id']; 
        }
        
        //购买B商品的用户
        $sql = "select member_id from sdb_ecorder_orders where order_id in (".implode(',',$orders).") ";
        $rs = $db->select($sql);
        foreach($rs as $v){
            $members[$v['member_id']] = $v['member_id']; 
        }
        
        $res = array('orders'=>$orders,'members'=>$members);
        return $res;
    }

}
