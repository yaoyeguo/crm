<?php
class plugins_market{

    var $plugins =  array();//全部插件

    function __construct(){
//        $new_customer_sale = array(
//        array('tab'=>true,'title'=>'新客户营销','list'=>array(
//        array('id'=>1,'title'=>'最近15天新客户','desc'=>'趁着新客户对店铺还保有较深印象的时候发起短信关怀或回购营销，进一步拉近客户与店铺的距离，促使更多二次购买','recommend'=>5),
//        array('id'=>2,'title'=>'最近30天新客户','desc'=>'趁着新客户对店铺还保有较深印象的时候发起短信关怀或回购营销，进一步拉近客户与店铺的距离，促使更多二次购买','recommend'=>5,'func'=>''),
//        /*array('id'=>3,'title'=>'新客户购买后第5天','desc'=>'新客户购买后的第5天，你可以对他们进行适当的关怀或者进一步的商品推荐，以加深他们对您店铺的印象','recommend'=>4,'func'=>''),*/
//        )));
//        $sleep_customer_sale = array(
//        array('tab'=>true,'title'=>'活跃度较低的休眠客户','list'=>
//        array(
//        array('id'=>4,'title'=>'活跃度较低购物能力较弱的休眠客户','desc'=>'此类客户需要高强度的促销以再次唤醒他们对店铺的记忆.请直接暴力促销吧，但要有转化率不理想的心理准备','recommend'=>3,'func'=>''),
//        array('id'=>5,'title'=>'活跃度较低购物能力一般的休眠客户','desc'=>'此类客户需要高强度的促销以再次唤醒他们对店铺的记忆.请直接暴力促销吧，价格请参考1年前的平均客单价','recommend'=>3,'func'=>''),
//        array('id'=>6,'title'=>'活跃度较低但购物能力较强的休眠客户','desc'=>'此类客户需要高强度的促销以再次唤醒他们对店铺的记忆.曾经消费能力较高,请考虑促销力度、商品选择和组合推荐相结合','recommend'=>4,'func'=>''),
//        ),
//        ),
//        array('tab'=>true,'title'=>'活跃度一般的休眠客户','list'=>
//        array(
//        array('id'=>7,'title'=>'活跃度一般购物能力较弱的休眠客户','desc'=>'此类客户需要高强度的促销以再次唤醒他们对店铺的记忆.建议直接用强力的促销手段来唤醒他们','recommend'=>3,'func'=>''),
//        array('id'=>8,'title'=>'活跃度一般购物能力也一般的休眠客户','desc'=>'此类客户需要高强度的促销以再次唤醒他们对店铺的记忆.建议直接用强力的促销手段并结合适当的商品来唤醒他们','recommend'=>3,'func'=>''),
//        array('id'=>9,'title'=>'活跃度一般但购物能力较强的休眠客户','desc'=>'此类客户需要高强度的促销以再次唤醒他们对店铺的记忆.建议综合考虑强力的促销手段及适当的高价值商品组合来唤醒他们','recommend'=>4,'func'=>''),
//        ),
//        ),
//        array('tab'=>true,'title'=>'活跃度较高的休眠客户','list'=>
//        array(
//        array('id'=>10,'title'=>'活跃度较高购物能力较弱的休眠客户','desc'=>'曾经多次消费,唤醒此类客户不仅需要强力的促销,更需要深入了解当时的购买特性，建议推荐合适的商品并注意折扣力度','recommend'=>4,'func'=>''),
//        array('id'=>11,'title'=>'活跃度较高购物能力一般的休眠客户','desc'=>'曾经多次消费,唤醒此类客户不仅需要强力的促销,更需要深入了解当时的购买特性，建议推荐合适的商品并注意提供有竞争力的价格','recommend'=>4,'func'=>''),
//        array('id'=>12,'title'=>'活跃度较高且购物能力较强的休眠客户','desc'=>'曾经多次消费,唤醒此类客户不仅需要强力的促销刺激，更需要细致的了解当时的购买特性，建议推荐适当的高价值商品组合','recommend'=>5,'func'=>''),
//        ),
//        ),
//        );
//
//        $hid_high_custom_sale = array(
//        array('tab'=>true,'title'=>'近期消费突增客户','list'=>
//        array(
//        array('id'=>13,'title'=>'近3个月消费能力突增客户','desc'=>'此类客户的特征是在最近3个月内其购买力有显著的增长，建议您对这类客户展开针对性的营销活动，将其发展成为高价值客户','recommend'=>4,'func'=>''),
//        array('id'=>14,'title'=>'近6个月消费能力突增客户','desc'=>'此类客户的特征是在最近6个月内其购买力有显著的增长，建议您对这类客户展开针对性的营销活动，将其发展成为高价值客户','recommend'=>4,'func'=>''),
//        ),
//        ),
//        );


//        $this->plugins = array(
//            'new_customer_sale'=>array('title'=>'新客户营销','list'=>$new_customer_sale),
//            'sleep_customer_sale'=>array('title'=>'休眠客户营销','list'=>$sleep_customer_sale),
//            'hid_high_custom_sale'=>array('title'=>'潜在高质客户','list'=>$hid_high_custom_sale),
//        );

        $this->db = kernel::database();
        $this->plugins = $this->getCategaryRoleFormat();
        
        foreach($this->plugins as $type=>$rows){
            $this->cateToPlugins[$type] = array();
            foreach($rows['list'] as $rows2){
                if($rows2['tab']){
                    foreach($rows2['list'] as $row3){
                        $this->cateToPlugins[$type][] = $row3['id'];
                    }
                }else{
                    $this->cateToPlugins[$type][] = $rows2['id'];
                }
            }
        }
    }
    
    protected function getCategaryRoleFormat() {
//        $categoryFirstSql = 'select * from sdb_plugins_market_category where status = 1 and parent_id = 0';
//        $categoryfirstdata = $this->db->select($categoryFirstSql);
//        
//        $data = array();
//        foreach ($categoryfirstdata as $category) {
//            $data[$category['category_varname']] = array('title' => $category['category_name'], 'list' => array(), 'category_id' => $category['category_id']);
//        }
//        
//        foreach ($data as &$category) {
//            $parent_id = $category['category_id'];
//            $categorySecondSql = 'select * from sdb_plugins_market_category where status = 1 and parent_id = ' . $parent_id;
//            $categorySecondData = $this->db->select($categorySecondSql);
//            foreach ($categorySecondData as $SecondData) {
//                $tab = true;
//                $category['list'][] = array(
//                    'tab' => $tab, 
//                    'title' => $SecondData['category_name'], 
//                    'list' => array(), 
//                    'category_id' => $SecondData['category_id'], 
//                    'sms_body' => $SecondData['sms_body']
//                );
//            }
//        }
//        
//        foreach ($data as &$category) {
//            foreach ($category['list'] as &$SecondData) {
//                $roleSql = 'select * from sdb_plugins_market_roles where status = 1 and category_id = '. $SecondData['category_id'];
//                $roles = $this->db->select($roleSql);
//                foreach ($roles as  $roes) {
//                    $SecondData['list'][] = array('id' => $roes['role_id'], 'title' => $roes['role_name'], 'desc' => $roes['role_desc'], 'recommend' => $roes['recommend'], 'func' => $roes['func']);
//                }
//            }
//        }
//        
//        echo json_encode($data);
//        exit;
        include 'categoryjson.php';
        $data = json_decode($categoryJsonData, true);
        return $data;
    } 

    function getMemberCounts($market_id,$shop_id){
        $arr = $this->callFunc($market_id,$shop_id);
        if(is_numeric($arr['count'])){
            return $arr['count'];
        }else{
            $row = $this->db->selectrow($arr['count']);
            return intval($row['total']);
        }
        
        //$user_id = kernel::single('desktop_user')->get_id();
        //base_kvstore::instance('analysis')->fetch('filter_sql_market_'.$user_id,$arr['sql']);        
    }

    function getMarketSql($market_id,$shop_id){
        $arr = $this->callFunc($market_id,$shop_id);
        return $arr['sql'];
    }

    function getMarketSmsBody($market_id){
        $market_type = '';
        foreach($this->cateToPlugins as $type=>$ids){
            if(in_array($market_id,$ids)){
                $market_type = $type;
                break;
            }
        }
        //var_dump($this->plugins[$market_type]);
        return $this->plugins[$market_type]['list'][0]['sms_body'];
    }

    function getRule($market_id){
        $market_type = '';
        foreach($this->cateToPlugins as $type=>$ids){
            if(in_array($market_id,$ids)){
                $market_type = $type;
                break;
            }
        }
        foreach($this->plugins[$market_type]['list'] as $rows){
            if($rows['tab']){
                foreach($rows['list'] as $row){
                    if($row['id'] == $market_id){
                        return $row;
                    }
                }
            }else{
                if($rows['id'] == $market_id){
                    return $rows;
                }
            }
        }

        return array();
    }

    function callFunc($market_id,$shop_id){
        $market_type = '';
        foreach($this->cateToPlugins as $type=>$ids){
            if(in_array($market_id,$ids)){
                $market_type = $type;
                break;
            }
        }
        $func = $market_type.'_'.$market_id;
        if(!method_exists($this, $func)){
            return array('sql'=>'','count'=>'');
        }else{
            return $this->$func($shop_id);
        }
    }


    //最近15天新客户---新客户营销
    function new_customer_sale_1($shop_id){
        $time = strtotime(date("Y-m-d"));
        $dayBefore15 = $time - 15 * 86400;
        
        $sql = "SELECT
                  member_id 
                FROM 
                  `sdb_taocrm_member_analysis`
                WHERE 
                  first_buy_time >= ". $dayBefore15 ."
                AND shop_id = '$shop_id'";
        return $this->callSqlData($sql);
    }

    //最近30天新客户---新客户营销
    function new_customer_sale_2($shop_id){
        $time = strtotime(date("Y-m-d"));
        $dayBefore30 = $time - 30 * 86400;
        $sql = "SELECT
                  member_id
                FROM
                 `sdb_taocrm_member_analysis`
                WHERE
                  first_buy_time >= ". $dayBefore30 ."
                AND 
                  shop_id = '$shop_id'";
        return $this->callSqlData($sql);
    }

    //活跃度较低的休眠客户--活跃度较低的休眠客户--休眠客户营销
    function sleep_customer_sale_4($shop_id){
        //所有状态的订单，最后一次订单创建时间在1年以前,并且总下单次数=1
        //活跃度低消费能力弱=这群人中，订单价排名50%-100%
        $time = strtotime(date("Y-m-d"));
        $dayBefore360 = $time - 360 * 86400;
        $sql = "SELECT
                  member_id
                FROM
                  `sdb_taocrm_member_analysis`
                WHERE
                  last_buy_time <= ". $dayBefore360 ."
                AND
                  total_orders = 1
                AND
                  shop_id = '$shop_id'
                ORDER BY finish_total_amount DESC";
        return $this->callPercentSqlData($sql, 0.5, 1);
//        $sql = "SELECT count(member_id) as total FROM `sdb_taocrm_member_analysis`
//            where DATEDIFF(now(),FROM_UNIXTIME(last_buy_time,'%Y-%m-%d'))<=360 and finish_orders=1 and shop_id='$shop_id' ";
//        $rs = $this->db->selectRow($sql);
//        $total = $rs['total'];
//        $limit = intval($total*0.5);
//        $total = intval($total*0.5);
//
//        $sql = "SELECT member_id FROM `sdb_taocrm_member_analysis`
//                where DATEDIFF(now(),FROM_UNIXTIME(last_buy_time,'%Y-%m-%d'))<=360 and finish_orders=1 and shop_id='$shop_id' order by finish_total_amount desc limit $limit,$total ";
//        $res = array('sql'=>$sql,'count'=>$total);
//        return $res;
    }

    //活跃度较低购物能力一般的休眠客户--活跃度较低的休眠客户--休眠客户营销
    function sleep_customer_sale_5($shop_id){
        //活跃度低消费能力一般=这群人中，订单价排名20%-50%，
        $time = strtotime(date("Y-m-d"));
        $dayBefore360 = $time - 360 * 86400;
        $sql = "SELECT
                  member_id
                FROM
                  `sdb_taocrm_member_analysis`
                WHERE
                  last_buy_time <= ". $dayBefore360 ."
                AND
                  total_orders = 1
                AND
                  shop_id = '$shop_id'
                ORDER BY finish_total_amount DESC";
        return $this->callPercentSqlData($sql, 0.2, 0.5);
//        $sql = "SELECT count(member_id) as total FROM `sdb_taocrm_member_analysis`
//            where DATEDIFF(now(),FROM_UNIXTIME(last_buy_time,'%Y-%m-%d'))<=360 and finish_orders=1 and shop_id='$shop_id' ";
//        $rs = $this->db->selectRow($sql);
//        $total = $rs['total'];
//        $limit = intval($total*0.2);
//        $total = intval($total*0.3);
//
//        $sql = "SELECT member_id FROM `sdb_taocrm_member_analysis`
//                where DATEDIFF(now(),FROM_UNIXTIME(last_buy_time,'%Y-%m-%d'))<=360 and finish_orders=1 and shop_id='$shop_id' order by finish_total_amount desc limit $limit,$total ";
//        $res = array('sql'=>$sql,'count'=>$total);
//        return $res;
    }

    //活跃度较低但购物能力较强的休眠客户--活跃度较低的休眠客户--休眠客户营销
    function sleep_customer_sale_6($shop_id){
        //活跃度低消费能力强=这群人中，订单价排名0%-20%
        $time = strtotime(date("Y-m-d"));
        $dayBefore360 = $time - 360 * 86400;
        $sql = "SELECT
                  member_id
                FROM
                  `sdb_taocrm_member_analysis`
                WHERE
                  last_buy_time <= ". $dayBefore360 ."
                AND
                  total_orders = 1
                AND
                  shop_id = '$shop_id'
                ORDER BY finish_total_amount DESC";
        return $this->callPercentSqlData($sql, 0, 0.2);
//        $sql = "SELECT count(member_id) as total FROM `sdb_taocrm_member_analysis`
//            where DATEDIFF(now(),FROM_UNIXTIME(last_buy_time,'%Y-%m-%d'))<=360 and finish_orders=1 and shop_id='$shop_id' ";
//        $rs = $this->db->selectRow($sql);
//        $total = $rs['total'];
//        $limit = 0;
//        $total = intval($total*0.2);
//
//        $sql = "SELECT member_id FROM `sdb_taocrm_member_analysis`
//                where DATEDIFF(now(),FROM_UNIXTIME(last_buy_time,'%Y-%m-%d'))<=360 and finish_orders=1 and shop_id='$shop_id' order by finish_total_amount desc limit $limit,$total ";
//        $res = array('sql'=>$sql,'count'=>$total);
//        return $res;
    }

    //活跃度一般购物能力较弱的休眠客户--活跃度较低的休眠客户--休眠客户营销
    function sleep_customer_sale_7($shop_id){
        //最后一次订单创建时间在1年以前，并且总下单次数=2或3，根据订单价从高到低排名
        //活跃度低消费能力弱=这群人中，订单价排名50%-100%
        $time = strtotime(date("Y-m-d"));
        $dayBefore360 = $time - 360 * 86400;
        $sql = "SELECT
                  member_id
                FROM
                  `sdb_taocrm_member_analysis`
                WHERE
                  last_buy_time <= ". $dayBefore360 ."
                AND
                  total_orders IN (2, 3)
                AND
                  shop_id = '$shop_id'
                ORDER BY finish_total_amount DESC";
        return $this->callPercentSqlData($sql, 0.5, 1);
//        $sql = "SELECT count(member_id) as total FROM `sdb_taocrm_member_analysis`
//            where DATEDIFF(now(),FROM_UNIXTIME(last_buy_time,'%Y-%m-%d'))<=360 and finish_orders in (2,3) and shop_id='$shop_id' ";
//        $rs = $this->db->selectRow($sql);
//        $total = $rs['total'];
//        $limit = intval($total*0.5);
//        $total = intval($total*0.5);
//
//        $sql = "SELECT member_id FROM `sdb_taocrm_member_analysis`
//                where DATEDIFF(now(),FROM_UNIXTIME(last_buy_time,'%Y-%m-%d'))<=360 and finish_orders in (2,3) and shop_id='$shop_id' order by finish_total_amount desc limit $limit,$total ";
//        $res = array('sql'=>$sql,'count'=>$total);
//        return $res;
    }

    //活跃度一般购物能力也一般的休眠客户--活跃度较低的休眠客户--休眠客户营销
    function sleep_customer_sale_8($shop_id){
        //活跃度低消费能力一般=这群人中，订单价排名20%-50%
        $time = strtotime(date("Y-m-d"));
        $dayBefore360 = $time - 360 * 86400;
        $sql = "SELECT
                  member_id
                FROM
                  `sdb_taocrm_member_analysis`
                WHERE
                  last_buy_time <= ". $dayBefore360 ."
                AND
                  total_orders IN (2, 3)
                AND
                  shop_id = '$shop_id'
                ORDER BY finish_total_amount DESC";
        return $this->callPercentSqlData($sql, 0.2, 0.5);
//        $sql = "SELECT count(member_id) as total FROM `sdb_taocrm_member_analysis`
//            where DATEDIFF(now(),FROM_UNIXTIME(last_buy_time,'%Y-%m-%d'))<=360 and finish_orders in (2,3) and shop_id='$shop_id' ";
//        $rs = $this->db->selectRow($sql);
//        $total = $rs['total'];
//        $limit = intval($total*0.2);
//        $total = intval($total*0.3);
//
//        $sql = "SELECT member_id FROM `sdb_taocrm_member_analysis`
//                where DATEDIFF(now(),FROM_UNIXTIME(last_buy_time,'%Y-%m-%d'))<=360 and finish_orders in (2,3) and shop_id='$shop_id' order by finish_total_amount desc limit $limit,$total ";
//        $res = array('sql'=>$sql,'count'=>$total);
//        return $res;
    }

    //活跃度一般但购物能力较强的休眠客户--活跃度较低的休眠客户--休眠客户营销
    function sleep_customer_sale_9($shop_id){
        //活跃度低消费能力强=这群人中，订单价排名0%-20%
        $time = strtotime(date("Y-m-d"));
        $dayBefore360 = $time - 360 * 86400;
        $sql = "SELECT
                  member_id
                FROM
                  `sdb_taocrm_member_analysis`
                WHERE
                  last_buy_time <= ". $dayBefore360 ."
                AND
                  total_orders IN (2, 3)
                AND
                  shop_id = '$shop_id'
                ORDER BY finish_total_amount DESC";
        return $this->callPercentSqlData($sql, 0, 0.2);
//        $sql = "SELECT count(member_id) as total FROM `sdb_taocrm_member_analysis`
//            where DATEDIFF(now(),FROM_UNIXTIME(last_buy_time,'%Y-%m-%d'))<=360 and finish_orders in (2,3) and shop_id='$shop_id' ";
//        $rs = $this->db->selectRow($sql);
//        $total = $rs['total'];
//        $limit = 0;
//        $total = intval($total*0.2);
//
//        $sql = "SELECT member_id FROM `sdb_taocrm_member_analysis`
//                where DATEDIFF(now(),FROM_UNIXTIME(last_buy_time,'%Y-%m-%d'))<=360 and finish_orders in (2,3) and shop_id='$shop_id' order by finish_total_amount desc limit $limit,$total ";
//        $res = array('sql'=>$sql,'count'=>$total);
//        return $res;
    }

    //活跃度较高购物能力较弱的休眠客户
    function sleep_customer_sale_10($shop_id){
        //所有状态的订单，最后一次订单创建时间在1年以前，并且总下单次数>3，找到这群人，根据订单价从高到低排名
        //活跃度低消费能力弱=这群人中，订单价排名50%-100%
        $time = strtotime(date("Y-m-d"));
        $dayBefore360 = $time - 360 * 86400;
        $sql = "SELECT
                  member_id
                FROM
                  `sdb_taocrm_member_analysis`
                WHERE
                  last_buy_time <= ". $dayBefore360 ."
                AND
                  total_orders >= 3
                AND
                  shop_id = '$shop_id'
                ORDER BY finish_total_amount DESC";
        return $this->callPercentSqlData($sql, 0.5, 1);
//        $sql = "SELECT count(member_id) as total FROM `sdb_taocrm_member_analysis`
//            where DATEDIFF(now(),FROM_UNIXTIME(last_buy_time,'%Y-%m-%d'))<=360 and finish_orders>=4 and shop_id='$shop_id' ";
//        $rs = $this->db->selectRow($sql);
//        $total = $rs['total'];
//        $limit = intval($total*0.5);
//        $total = intval($total*0.5);
//
//        $sql = "SELECT member_id FROM `sdb_taocrm_member_analysis`
//                where DATEDIFF(now(),FROM_UNIXTIME(last_buy_time,'%Y-%m-%d'))<=360 and finish_orders>=4 and shop_id='$shop_id' order by finish_total_amount desc limit $limit,$total ";
//        $res = array('sql'=>$sql,'count'=>$total);
//        return $res;
    }

    //活跃度较高购物能力一般的休眠客户
    function sleep_customer_sale_11($shop_id){
        //活跃度低消费能力一般=这群人中，订单价排名20%-50%
        $time = strtotime(date("Y-m-d"));
        $dayBefore360 = $time - 360 * 86400;
        $sql = "SELECT
                  member_id
                FROM
                  `sdb_taocrm_member_analysis`
                WHERE
                  last_buy_time <= ". $dayBefore360 ."
                AND
                  total_orders >= 3
                AND
                  shop_id = '$shop_id'
                ORDER BY finish_total_amount DESC";
        return $this->callPercentSqlData($sql, 0.2, 0.5);
//        $sql = "SELECT count(member_id) as total FROM `sdb_taocrm_member_analysis`
//            where DATEDIFF(now(),FROM_UNIXTIME(last_buy_time,'%Y-%m-%d'))<=360 and finish_orders>=4 and shop_id='$shop_id' ";
//        $rs = $this->db->selectRow($sql);
//        $total = $rs['total'];
//        $limit = intval($total*0.2);
//        $total = intval($total*0.3);
//
//        $sql = "SELECT member_id FROM `sdb_taocrm_member_analysis`
//                where DATEDIFF(now(),FROM_UNIXTIME(last_buy_time,'%Y-%m-%d'))<=360 and finish_orders>=4 and shop_id='$shop_id' order by finish_total_amount desc limit $limit,$total ";
//        $res = array('sql'=>$sql,'count'=>$total);
//        return $res;
    }

    //活跃度较高且购物能力较强的休眠客户
    function sleep_customer_sale_12($shop_id){
        //活跃度低消费能力强=这群人中，订单价排名0%-20%
        $time = strtotime(date("Y-m-d"));
        $dayBefore360 = $time - 360 * 86400;
        $sql = "SELECT
                  member_id
                FROM
                  `sdb_taocrm_member_analysis`
                WHERE
                  last_buy_time <= ". $dayBefore360 ."
                AND
                  total_orders >= 3
                AND
                  shop_id = '$shop_id'
                ORDER BY finish_total_amount DESC";
        return $this->callPercentSqlData($sql, 0, 0.2);
//        $sql = "SELECT count(member_id) as total FROM `sdb_taocrm_member_analysis`
//            where DATEDIFF(now(),FROM_UNIXTIME(last_buy_time,'%Y-%m-%d'))<=360 and finish_orders>=4 and shop_id='$shop_id' ";
//        $rs = $this->db->selectRow($sql);
//        $total = $rs['total'];
//        $limit = 0;
//        $total = intval($total*0.2);
//
//        $sql = "SELECT member_id FROM `sdb_taocrm_member_analysis`
//                where DATEDIFF(now(),FROM_UNIXTIME(last_buy_time,'%Y-%m-%d'))<=360 and finish_orders>=4 and shop_id='$shop_id' order by finish_total_amount desc limit $limit,$total ";
//        $res = array('sql'=>$sql,'count'=>$total);
//        return $res;
    }
 
    //近3个月消费能力突增客户
    function hid_high_custom_sale_13($shop_id){
        //取出近3个月和3个月之前都有下单记录的人群，然后比较每人近3个月的订单价和3个月之前的订单价，取出近3个月>=3个月之前*1.5倍，>=1.5
        $time = strtotime(date("Y-m-d"));
        $dayBefore90 = $time - 90 * 86400;
//        $lastYearTime = $time - 365 * 86400;
        $ratio = 1.5;
        $sql = "SELECT
                  C.member_id
                FROM(
                    SELECT 
                      B.member_id
                    FROM
                       (SELECT
                          member_id,
                          ( case when (createtime <  {$dayBefore90} ) then  1 else  -1  END  ) as ISBefore,
                          ( case when (createtime >=  {$dayBefore90}) then  avg(payed)  else   avg(payed) * -1.5 END ) as PAY
                        FROM
                          `sdb_ecorder_orders`
                        WHERE
                          payed > 0
                        AND
                          shop_id = '{$shop_id}'
                        GROUP BY ( case when (createtime >=  {$dayBefore90} ) then  1 else  -1  END  ) , member_id
                       ) B
                      GROUP BY B.member_id HAVING (sum( ISBefore ) = 0  and sum(PAY) > 0 )
                 ) C";
//        $sql = "SELECT
//                  A.member_id
//                FROM
//                   (
//                      (SELECT 
//                         member_id AS member_id, avg(payed) as payed_avg_1
//                       FROM
//                         `sdb_ecorder_orders`
//                       WHERE
//                         createtime >= {$dayBefore90}
//                       AND
//                         payed > 0
//                       AND
//                         shop_id = '{$shop_id}'
//                       GROUP BY member_id
//                      ) A
//                      INNER JOIN
//                      (SELECT 
//                         member_id, avg(payed) * {$ratio} as payed_avg_2
//                       FROM
//                         `sdb_ecorder_orders`
//                       WHERE
//                         createtime < {$dayBefore90}
//                       AND
//                         createtime >= {$lastYearTime}
//                       AND
//                         payed > 0
//                      GROUP BY member_id
//                      ) B
//                      ON A.member_id = B.member_id
//                    )
//               WHERE
//                 A.payed_avg_1 > B.payed_avg_2";
        return $this->callSqlData($sql);
    }

    //近6个月消费能力突增客户
    function hid_high_custom_sale_14($shop_id){
        //6个月：计算开店所有状态的订单，取出近6个月和6个月之前都有下单记录的人群，然后比较每人近6
       //个月的订单价和6个月之前的订单价，取出近6个月>=6个月之前*1.5倍，>=1.5
        $time = strtotime(date("Y-m-d"));
        $dayBefore360 = $time - 360 * 86400;
        $ratio = 1.5;
        $sql = "SELECT
                  C.member_id
                FROM(
                    SELECT 
                      B.member_id
                    FROM
                       (SELECT
                          member_id,
                          ( case when (createtime <  {$dayBefore360} ) then  1 else  -1  END  ) as ISBefore,
                          ( case when (createtime >=  {$dayBefore360}) then  avg(payed)  else   avg(payed) * -1.5 END ) as PAY
                        FROM
                          `sdb_ecorder_orders`
                        WHERE
                          payed > 0
                        AND
                          shop_id = '{$shop_id}'
                        GROUP BY ( case when (createtime >=  {$dayBefore360} ) then  1 else  -1  END  ) , member_id
                       ) B
                      GROUP BY B.member_id HAVING (sum( ISBefore ) = 0  and sum(PAY) > 0 )
                 ) C";
        return $this->callSqlData($sql);
    }
    
    //普通客户====客户粘合度较低
    function member_grade_sale_15($shop_id) {
        //近6个月的新客户，且只有一次购买,且订单价在店铺订单价0.8倍以上,>=0.8
        $time = strtotime(date("Y-m-d"));
        $dayBefore180 = $time - 180 * 86400;
        $finishOrders = 2;
        $finishOrdersSecond = 1;
        $ratio = 0.8;
        $sql = "SELECT
                  Z.member_id
                FROM
                (
                  SELECT
                    member_id
                  FROM
                    `sdb_taocrm_member_analysis` E LEFT JOIN `sdb_ecorder_shop_analysis` F ON E.shop_id = F.shop_id
                  WHERE
                    E.last_buy_time >= {$dayBefore180}
                  AND
                    E.finish_orders = {$finishOrders}
                  AND
                    E.total_per_amount < {$ratio} * F.finish_per_amount
                  AND
                    E.shop_id = '{$shop_id}'
                  UNION 
                  SELECT
                    A.member_id
                  FROM
                    `sdb_taocrm_member_analysis` A LEFT JOIN `sdb_ecorder_shop_analysis` B ON A.shop_id = B.shop_id
                  WHERE
                    A.finish_orders = {$finishOrdersSecond}
                  AND
                    A.first_buy_time > {$dayBefore180}
                  AND
                    A.shop_id = '{$shop_id}'
                  AND
                    A.total_per_amount < {$ratio} * B.finish_per_amount
                 ) AS Z";
        return $this->callSqlData($sql);
    }
    
    //高级客户====客户粘合度一般
    function member_grade_sale_16($shop_id) {
        //共2次购买，且近6个月至少一次购买，且订单价在店铺订单价0.8倍以上,>=0.8
        $time = strtotime(date("Y-m-d"));
        $dayBefore180 = $time - 180 * 86400;
        $finishOrders = 2;
        $finishOrdersSecond = 3;
        $finishOrdersThird = 4;
        $ratio = 0.8;
        $ratioSecond = 1.5;
        $sql = "SELECT
                  Z.member_id 
                FROM
                   (SELECT
                      A.member_id
                    FROM
                      `sdb_taocrm_member_analysis` A LEFT JOIN `sdb_ecorder_shop_analysis` B ON A.shop_id = B.shop_id
                    WHERE
                      A.first_buy_time > {$dayBefore180}
                    AND
                      A.finish_orders = {$finishOrders}
                    AND
                      A.shop_id = '{$shop_id}'
                    AND
                      A.total_per_amount >= {$ratio} * B.finish_per_amount
                    UNION
                    SELECT
                      C.member_id
                    FROM
                      `sdb_taocrm_member_analysis` C LEFT JOIN `sdb_ecorder_shop_analysis` D ON C.shop_id = D.shop_id
                    WHERE
                      C.first_buy_time > {$dayBefore180}
                    AND
                      C.finish_orders = {$finishOrdersSecond}
                    AND
                      C.shop_id = '{$shop_id}'
                    AND
                      C.total_per_amount < {$ratioSecond} * D.finish_per_amount
                    UNION
                    SELECT
                      F.member_id
                    FROM
                      `sdb_taocrm_member_analysis` F LEFT JOIN `sdb_ecorder_shop_analysis` G ON F.shop_id = G.shop_id
                    WHERE
                      F.finish_orders >= {$finishOrdersThird}
                    AND
                      F.first_buy_time > {$dayBefore180}
                    AND
                      F.shop_id = '{$shop_id}'
                    AND
                      F.total_per_amount < {$ratio} * G.finish_per_amount
                    ) Z";
          return $this->callSqlData($sql);
    }
    
    //VIP====客户粘合度较高
    function member_grade_sale_17($shop_id) {
        //共3次购买，且近6个月至少一次购买，且订单价在店铺订单价1.5倍以上,>=1.5
        $time = strtotime(date("Y-m-d"));
        $dayBefore180 = $time - 180 * 86400;
        $finishOrders = 3;
        $finishOrdersSecond = 4;
        $ratio = 0.8;
        $ratioSecond = 1.5;
        $sql = "SELECT
                  Z.member_id 
                FROM
                   (SELECT
                      A.member_id
                    FROM
                      `sdb_taocrm_member_analysis` A LEFT JOIN `sdb_ecorder_shop_analysis` B ON A.shop_id = B.shop_id
                    WHERE
                      A.first_buy_time > {$dayBefore180}
                    AND
                      A.finish_orders = {$finishOrders}
                    AND
                      A.shop_id = '{$shop_id}'
                    AND
                      A.total_per_amount >= {$ratioSecond} * B.finish_per_amount
                    UNION
                    SELECT
                      C.member_id
                    FROM
                      `sdb_taocrm_member_analysis` C LEFT JOIN `sdb_ecorder_shop_analysis` D ON C.shop_id = D.shop_id
                    WHERE
                      C.first_buy_time > {$dayBefore180}
                    AND
                      C.finish_orders >= {$finishOrdersSecond}
                    AND
                      C.shop_id = '{$shop_id}'
                    AND
                      C.total_per_amount >= {$ratio} * D.finish_per_amount
                    AND
                      C.total_per_amount < {$ratioSecond} * D.finish_per_amount
                    ) Z";
          return $this->callSqlData($sql);
    }
    
    //至尊VIP====客户粘合度高
    function member_grade_sale_18($shop_id) {
        //共4次及以上购买，且近6个月至少一次购买，且订单价在店铺订单价1.5倍以上,>=1.5
        $time = strtotime(date("Y-m-d"));
        $dayBefore180 = $time - 180 * 86400;
        $finishOrders = 4;
        $ratio = 1.5;
        $sql = "SELECT
                  A.member_id
                FROM
                  `sdb_taocrm_member_analysis` A LEFT JOIN `sdb_ecorder_shop_analysis` B ON A.shop_id = B.shop_id
                WHERE
                  A.first_buy_time > {$dayBefore180}
                AND
                  A.finish_orders = {$finishOrders}
                AND
                  A.shop_id = '{$shop_id}'
                AND
                  A.total_per_amount >= {$ratio} * B.finish_per_amount";
          return $this->callSqlData($sql);
    }
    
    protected function countSqlFormat($sql) {
        $search = "/SELECT([\s]*)(.*)([\s]*)FROM/i";
//        $search = "/SELECT\s+([\w\s\'\.]*?)\s+FROM/i";
        $countsql = preg_replace($search, "SELECT count(\$2)  as _count FROM", $sql, 1);
        return $countsql;
    }
    
    protected function countNum($sql) {
        $countsql = $this->countSqlFormat($sql);
        $data = $this->db->select($countsql);
        return $data[0]['_count'];
    }
    
    protected function callSqlData($sql) {
        return array('sql' => $sql, 'count' => $this->countNum($sql));
    }
    
    protected function callPercentSqlData($sql, $startPercent, $endPecent = 1) {
        $count = $this->countNum($sql);
        $percentCount = ceil($count * $endPecent);
        $offset = ceil($count * $startPercent);
        $limit = $percentCount - $offset;
        $sql .= ' limit ' . $offset . ' , ' . $limit;
        $res = array('sql' => $sql, 'count' => $limit);
        return $res;
    }
    
    //每年父亲节算法
    protected function getFathersDay($year) {
        //6月的第三个星期日是父亲节
        $june1stTime = mktime(0, 0, 0, 6, 1, $year);;
        $week = date("w", $june1stTime);
        $spare = 0;
        if ($week != 0) {
            $spare = 7 - $week;
        }
        
        $spare += 15;
        //2012年6月17日 星期日
        //2011年6月19日
        //2010年6月20日
        $fathersDayTime = mktime(0, 0, 0, 6, $spare, $year);
        return $fathersDayTime;
    }
    
    //活跃度较低购物能力较弱的客户--近3个月活跃客户--活跃客户营销
    function active_member_sale_19($shop_id) {
        //消费能力弱=这群人中订单价排名50%-100%，
        $time = strtotime(date("Y-m-d"));
        $orderNum = 1;
        $dayBefore90 = $time - 90 * 86400;
        $startPercent = 0.5;
        $endPercent = 1;
        $sql = "SELECT
                  Z.member_id
                FROM
                   (
                    SELECT
                      member_id
                    FROM
                      `sdb_ecorder_orders`
                    WHERE
                      createtime >= {$dayBefore90}
                    AND
                      shop_id = '{$shop_id}'
                    GROUP BY member_id HAVING COUNT(member_id) = {$orderNum}
                    ORDER BY payed DESC
                    ) Z";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //活跃度较低购物能力一般的客户
    function active_member_sale_20($shop_id) {
        //消费能力一般=这群人中订单价排名20%-50%，
        $time = strtotime(date("Y-m-d"));
        $orderNum = 1;
        $dayBefore90 = $time - 90 * 86400;
        $startPercent = 0.2;
        $endPercent = 0.5;
        $sql = "SELECT
                  Z.member_id
                FROM
                   (
                    SELECT
                      member_id
                    FROM
                      `sdb_ecorder_orders`
                    WHERE
                      createtime >= {$dayBefore90}
                    AND
                      shop_id = '{$shop_id}'
                    GROUP BY member_id HAVING COUNT(member_id) = {$orderNum}
                    ORDER BY payed DESC
                    ) Z";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //活跃度较低但购物能力较强的客户
    function active_member_sale_21($shop_id) {
        //消费能力强=这群人中订单价排名0%-20%
        $time = strtotime(date("Y-m-d"));
        $orderNum = 1;
        $dayBefore90 = $time - 90 * 86400;
        $startPercent = 0;
        $endPercent = 0.2;
        $sql = "SELECT
                  Z.member_id
                FROM
                   (
                    SELECT
                      member_id
                    FROM
                      `sdb_ecorder_orders`
                    WHERE
                      createtime >= {$dayBefore90}
                    AND
                      shop_id = '{$shop_id}'
                    GROUP BY member_id HAVING COUNT(member_id) = {$orderNum}
                    ORDER BY payed DESC
                    ) Z";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //活跃度一般购物能力较弱的客户
    function active_member_sale_22($shop_id) {
        //活跃度一般=近3个月下单2次或者3次，把这群人找出来后根据订单价从高到低排序,
        //活跃度一般，消费能力弱=这群人中订单价排名50%-100%
        $time = strtotime(date("Y-m-d"));
        $orderNum = 2;
        $orderNumSecond = 3;
        $dayBefore90 = $time - 90 * 86400;
        $startPercent = 0.5;
        $endPercent = 1;
        $sql = "SELECT
                  Z.member_id
                FROM
                   (
                    SELECT
                      member_id
                    FROM
                      `sdb_ecorder_orders`
                    WHERE
                      createtime >= {$dayBefore90}
                    AND
                      shop_id = '{$shop_id}'
                    GROUP BY member_id HAVING COUNT(member_id) IN ({$orderNum}, $orderNumSecond)
                    ORDER BY payed DESC
                    ) Z";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //活跃度一般购物能力也一般的客户 
    function active_member_sale_23($shop_id) {
        //活跃度一般，消费能力一般=这群人中订单价排名20%-50%
        $time = strtotime(date("Y-m-d"));
        $orderNum = 2;
        $orderNumSecond = 3;
        $dayBefore90 = $time - 90 * 86400;
        $startPercent = 0.2;
        $endPercent = 0.5;
        $sql = "SELECT
                  Z.member_id
                FROM
                   (
                    SELECT
                      member_id
                    FROM
                      `sdb_ecorder_orders`
                    WHERE
                      createtime >= {$dayBefore90}
                    AND
                      shop_id = '{$shop_id}'
                    GROUP BY member_id HAVING COUNT(member_id) IN ({$orderNum}, $orderNumSecond)
                    ORDER BY payed DESC
                    ) Z";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //活跃度一般但购物能力较强的客户
    function active_member_sale_24($shop_id) {
        //活跃度一般，消费能力强=这群人中订单价排名0%-20%
        $time = strtotime(date("Y-m-d"));
        $orderNum = 2;
        $orderNumSecond = 3;
        $dayBefore90 = $time - 90 * 86400;
        $startPercent = 0;
        $endPercent = 0.2;
        $sql = "SELECT
                  Z.member_id
                FROM
                   (
                    SELECT
                      member_id
                    FROM
                      `sdb_ecorder_orders`
                    WHERE
                      createtime >= {$dayBefore90}
                    AND
                      shop_id = '{$shop_id}'
                    GROUP BY member_id HAVING COUNT(member_id) IN ({$orderNum}, $orderNumSecond)
                    ORDER BY payed DESC
                    ) Z";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //活跃度较高购物能力较弱的客户
    function active_member_sale_25($shop_id) {
        //活跃度高=近3个月下单者3次以上，把这群人找出来后根据订单价从高到低排序
        //活跃度高，消费能力弱=这群人中订单价排名50%-100%
        $time = strtotime(date("Y-m-d"));
        $orderNum = 3;
        $dayBefore90 = $time - 90 * 86400;
        $startPercent = 0.5;
        $endPercent = 1;
        $sql = "SELECT
                  Z.member_id
                FROM
                   (
                    SELECT
                      member_id
                    FROM
                      `sdb_ecorder_orders`
                    WHERE
                      createtime >= {$dayBefore90}
                    AND
                      shop_id = '{$shop_id}'
                    GROUP BY member_id HAVING COUNT(member_id) >= {$orderNum}
                    ORDER BY payed DESC
                    ) Z";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //活跃度较高购物能力一般的客户
    function active_member_sale_26($shop_id) {
        //活跃度高，消费能力一般=这群人中订单价排名20%-50%
        $time = strtotime(date("Y-m-d"));
        $orderNum = 3;
        $dayBefore90 = $time - 90 * 86400;
        $startPercent = 0.2;
        $endPercent = 0.5;
        $sql = "SELECT
                  Z.member_id
                FROM
                   (
                    SELECT
                      member_id
                    FROM
                      `sdb_ecorder_orders`
                    WHERE
                      createtime >= {$dayBefore90}
                    AND
                      shop_id = '{$shop_id}'
                    GROUP BY member_id HAVING COUNT(member_id) >= {$orderNum}
                    ORDER BY payed DESC
                    ) Z";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //活跃度较高且购物能力较强的客户
    function active_member_sale_27($shop_id) {
        //活跃度高，消费能力强=这群人中订单价排名0%-20%
        $time = strtotime(date("Y-m-d"));
        $orderNum = 3;
        $dayBefore90 = $time - 90 * 86400;
        $startPercent = 0;
        $endPercent = 0.2;
        $sql = "SELECT
                  Z.member_id
                FROM
                   (
                    SELECT
                      member_id
                    FROM
                      `sdb_ecorder_orders`
                    WHERE
                      createtime >= {$dayBefore90}
                    AND
                      shop_id = '{$shop_id}'
                    GROUP BY member_id HAVING COUNT(member_id) >= {$orderNum}
                    ORDER BY payed DESC
                    ) Z";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //活跃度较低购物能力较弱的客户
    function active_member_sale_28($shop_id) {
        //活跃度低=近6个月只下单一次，把这群人找出来后根据订单价从高到低排序
        //活跃度低，消费能力弱=这群人中订单价排名50%-100%
        $time = strtotime(date("Y-m-d"));
        $orderNum = 1;
        $dayBefore180 = $time - 180 * 86400;
        $startPercent = 0.5;
        $endPercent = 1;
        $sql = "SELECT
                  Z.member_id
                FROM
                   (
                    SELECT
                      member_id
                    FROM
                      `sdb_ecorder_orders`
                    WHERE
                      createtime >= {$dayBefore180}
                    AND
                      shop_id = '{$shop_id}'
                    GROUP BY member_id HAVING COUNT(member_id) = {$orderNum}
                    ORDER BY payed DESC
                    ) Z";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //活跃度较低购物能力一般的客户
    function active_member_sale_29($shop_id) {
        //活跃度低，消费能力一般=这群人中订单价排名20%-50%
        $time = strtotime(date("Y-m-d"));
        $orderNum = 1;
        $dayBefore180 = $time - 180 * 86400;
        $startPercent = 0.2;
        $endPercent = 0.5;
        $sql = "SELECT
                  Z.member_id
                FROM
                   (
                    SELECT
                      member_id
                    FROM
                      `sdb_ecorder_orders`
                    WHERE
                      createtime >= {$dayBefore180}
                    AND
                      shop_id = '{$shop_id}'
                    GROUP BY member_id HAVING COUNT(member_id) = {$orderNum}
                    ORDER BY payed DESC
                    ) Z";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //活跃度较低但购物能力较强的客户
    function active_member_sale_30($shop_id) {
        //活跃度低，消费能力强=这群人中订单价排名0%-20%
        $time = strtotime(date("Y-m-d"));
        $orderNum = 1;
        $dayBefore180 = $time - 180 * 86400;
        $startPercent = 0;
        $endPercent = 0.2;
        $sql = "SELECT
                  Z.member_id
                FROM
                   (
                    SELECT
                      member_id
                    FROM
                      `sdb_ecorder_orders`
                    WHERE
                      createtime >= {$dayBefore180}
                    AND
                      shop_id = '{$shop_id}'
                    GROUP BY member_id HAVING COUNT(member_id) = {$orderNum}
                    ORDER BY payed DESC
                    ) Z";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //活跃度一般购物能力较弱的客户
    function active_member_sale_31($shop_id) {
        //活跃度一般=近6个月下单2次或者3次，把这群人找出来后根据订单价从高到低排序
        //活跃度一般，消费能力弱=这群人中订单价排名50%-100%
        $time = strtotime(date("Y-m-d"));
        $orderNum = 2;
        $orderNumSecond = 3;
        $dayBefore180 = $time - 180 * 86400;
        $startPercent = 0.5;
        $endPercent = 1;
        $sql = "SELECT
                  Z.member_id
                FROM
                   (
                    SELECT
                      member_id
                    FROM
                      `sdb_ecorder_orders`
                    WHERE
                      createtime >= {$dayBefore180}
                    AND
                      shop_id = '{$shop_id}'
                    GROUP BY member_id HAVING COUNT(member_id) IN ({$orderNum}, {$orderNumSecond})
                    ORDER BY payed DESC
                    ) Z";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //活跃度一般购物能力也一般的客户
    function active_member_sale_32($shop_id) {
        //活跃度一般，消费能力一般=这群人中订单价排名20%-50%
        $time = strtotime(date("Y-m-d"));
        $orderNum = 2;
        $orderNumSecond = 3;
        $dayBefore180 = $time - 180 * 86400;
        $startPercent = 0.2;
        $endPercent = 0.5;
        $sql = "SELECT
                  Z.member_id
                FROM
                   (
                    SELECT
                      member_id
                    FROM
                      `sdb_ecorder_orders`
                    WHERE
                      createtime >= {$dayBefore180}
                    AND
                      shop_id = '{$shop_id}'
                    GROUP BY member_id HAVING COUNT(member_id) IN ({$orderNum}, {$orderNumSecond})
                    ORDER BY payed DESC
                    ) Z";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //活跃度一般但购物能力较强的客户
    function active_member_sale_33($shop_id) {
        //活跃度一般，消费能力强=这群人中订单价排名0%-20%
        $time = strtotime(date("Y-m-d"));
        $orderNum = 2;
        $orderNumSecond = 3;
        $dayBefore180 = $time - 180 * 86400;
        $startPercent = 0;
        $endPercent = 0.2;
        $sql = "SELECT
                  Z.member_id
                FROM
                   (
                    SELECT
                      member_id
                    FROM
                      `sdb_ecorder_orders`
                    WHERE
                      createtime >= {$dayBefore180}
                    AND
                      shop_id = '{$shop_id}'
                    GROUP BY member_id HAVING COUNT(member_id) IN ({$orderNum}, {$orderNumSecond})
                    ORDER BY payed DESC
                    ) Z";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //活跃度较高购物能力较弱的客户
    function active_member_sale_34($shop_id) {
        //活跃度高=近6个月下单者3次以上，把这群人找出来后根据订单价从高到低排序
        //活跃度高，消费能力弱=这群人中订单价排名50%-100%
        $time = strtotime(date("Y-m-d"));
        $orderNum = 3;
        $dayBefore180 = $time - 180 * 86400;
        $startPercent = 0.5;
        $endPercent = 1;
        $sql = "SELECT
                  Z.member_id
                FROM
                   (
                    SELECT
                      member_id
                    FROM
                      `sdb_ecorder_orders`
                    WHERE
                      createtime >= {$dayBefore180}
                    AND
                      shop_id = '{$shop_id}'
                    GROUP BY member_id HAVING COUNT(member_id) >= {$orderNum}
                    ORDER BY payed DESC
                    ) Z";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //活跃度较高购物能力一般的客户
    function active_member_sale_35($shop_id) {
        //活跃度高，消费能力一般=这群人中订单价排名20%-50%
        $time = strtotime(date("Y-m-d"));
        $orderNum = 3;
        $dayBefore180 = $time - 180 * 86400;
        $startPercent = 0.2;
        $endPercent = 0.5;
        $sql = "SELECT
                  Z.member_id
                FROM
                   (
                    SELECT
                      member_id
                    FROM
                      `sdb_ecorder_orders`
                    WHERE
                      createtime >= {$dayBefore180}
                    AND
                      shop_id = '{$shop_id}'
                    GROUP BY member_id HAVING COUNT(member_id) >= {$orderNum}
                    ORDER BY payed DESC
                    ) Z";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //活跃度较高且购物能力较强的客户
    function active_member_sale_36($shop_id) {
        //活跃度高，消费能力强=这群人中订单价排名0%-20%
        $time = strtotime(date("Y-m-d"));
        $orderNum = 3;
        $dayBefore180 = $time - 180 * 86400;
        $startPercent = 0;
        $endPercent = 0.2;
        $sql = "SELECT
                  Z.member_id
                FROM
                   (
                    SELECT
                      member_id
                    FROM
                      `sdb_ecorder_orders`
                    WHERE
                      createtime >= {$dayBefore180}
                    AND
                      shop_id = '{$shop_id}'
                    GROUP BY member_id HAVING COUNT(member_id) >= {$orderNum}
                    ORDER BY payed DESC
                    ) Z";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //活跃度较低购物能力较弱的客户
    function active_member_sale_37($shop_id) {
        /**
         * 只计算订单创建时间在近12个月的所有订单
         * 活跃度低=近12个月只下单一次，把这群人找出来后根据订单价从高到低排序
         * 活跃度低，消费能力弱=这群人中订单价排名50%-100%，
         */
        $time = strtotime(date("Y-m-d"));
        $orderNum = 1;
        $dayBefore360 = $time - 360 * 86400;
        $startPercent = 0.5;
        $endPercent = 1;
        $sql = "SELECT
                  Z.member_id
                FROM
                   (
                    SELECT
                      member_id
                    FROM
                      `sdb_ecorder_orders`
                    WHERE
                      createtime >= {$dayBefore360}
                    AND
                      shop_id = '{$shop_id}'
                    GROUP BY member_id HAVING COUNT(member_id) = {$orderNum}
                    ORDER BY payed DESC
                    ) Z";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //活跃度较低购物能力一般的客户
    function active_member_sale_38($shop_id) {
        //活跃度低，消费能力一般=这群人中订单价排名20%-50%
        $time = strtotime(date("Y-m-d"));
        $orderNum = 1;
        $dayBefore360 = $time - 360 * 86400;
        $startPercent = 0.2;
        $endPercent = 0.5;
        $sql = "SELECT
                  Z.member_id
                FROM
                   (
                    SELECT
                      member_id
                    FROM
                      `sdb_ecorder_orders`
                    WHERE
                      createtime >= {$dayBefore360}
                    AND
                      shop_id = '{$shop_id}'
                    GROUP BY member_id HAVING COUNT(member_id) = {$orderNum}
                    ORDER BY payed DESC
                    ) Z";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //活跃度较低但购物能力较强的客户
    function active_member_sale_39($shop_id) {
        //活跃度低，消费能力强=这群人中订单价排名0%-20%
        $time = strtotime(date("Y-m-d"));
        $orderNum = 1;
        $dayBefore360 = $time - 360 * 86400;
        $startPercent = 0;
        $endPercent = 0.2;
        $sql = "SELECT
                  Z.member_id
                FROM
                   (
                    SELECT
                      member_id
                    FROM
                      `sdb_ecorder_orders`
                    WHERE
                      createtime >= {$dayBefore360}
                    AND
                      shop_id = '{$shop_id}'
                    GROUP BY member_id HAVING COUNT(member_id) = {$orderNum}
                    ORDER BY payed DESC
                    ) Z";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //活跃度一般购物能力较弱的客户
    function active_member_sale_40($shop_id) {
        //活跃度一般=近12个月下单2次或者3次，把这群人找出来后根据订单价从高到低排序
        //活跃度一般，消费能力弱=这群人中订单价排名50%-100%
        $time = strtotime(date("Y-m-d"));
        $orderNum = 2;
        $orderNumSecond = 3;
        $dayBefore360 = $time - 360 * 86400;
        $startPercent = 0.5;
        $endPercent = 1;
        $sql = "SELECT
                  Z.member_id
                FROM
                   (
                    SELECT
                      member_id
                    FROM
                      `sdb_ecorder_orders`
                    WHERE
                      createtime >= {$dayBefore360}
                    AND
                      shop_id = '{$shop_id}'
                    GROUP BY member_id HAVING COUNT(member_id) IN ({$orderNum}, {$orderNumSecond})
                    ORDER BY payed DESC
                    ) Z";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //活跃度一般购物能力也一般的客户 
    function active_member_sale_41($shop_id) {
        //活跃度一般，消费能力一般=这群人中订单价排名20%-50%
        $time = strtotime(date("Y-m-d"));
        $orderNum = 2;
        $orderNumSecond = 3;
        $dayBefore360 = $time - 360 * 86400;
        $startPercent = 0.2;
        $endPercent = 0.5;
        $sql = "SELECT
                  Z.member_id
                FROM
                   (
                    SELECT
                      member_id
                    FROM
                      `sdb_ecorder_orders`
                    WHERE
                      createtime >= {$dayBefore360}
                    AND
                      shop_id = '{$shop_id}'
                    GROUP BY member_id HAVING COUNT(member_id) IN ({$orderNum}, {$orderNumSecond})
                    ORDER BY payed DESC
                    ) Z";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //活跃度一般但购物能力较强的客户
    function active_member_sale_42($shop_id) {
        //活跃度一般，消费能力强=这群人中订单价排名0%-20%
        $time = strtotime(date("Y-m-d"));
        $orderNum = 2;
        $orderNumSecond = 3;
        $dayBefore360 = $time - 360 * 86400;
        $startPercent = 0;
        $endPercent = 0.2;
        $sql = "SELECT
                  Z.member_id
                FROM
                   (
                    SELECT
                      member_id
                    FROM
                      `sdb_ecorder_orders`
                    WHERE
                      createtime >= {$dayBefore360}
                    AND
                      shop_id = '{$shop_id}'
                    GROUP BY member_id HAVING COUNT(member_id) IN ({$orderNum}, {$orderNumSecond})
                    ORDER BY payed DESC
                    ) Z";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //活跃度较高购物能力较弱的客户
    function active_member_sale_43($shop_id) {
        //活跃度高=近12个月下单者3次以上，把这群人找出来后根据订单价从高到低排序
        //活跃度高，消费能力弱=这群人中订单价排名50%-100%
        $time = strtotime(date("Y-m-d"));
        $orderNum = 3;
        $dayBefore360 = $time - 360 * 86400;
        $startPercent = 0.5;
        $endPercent = 1;
        $sql = "SELECT
                  Z.member_id
                FROM
                   (
                    SELECT
                      member_id
                    FROM
                      `sdb_ecorder_orders`
                    WHERE
                      createtime >= {$dayBefore360}
                    AND
                      shop_id = '{$shop_id}'
                    GROUP BY member_id HAVING COUNT(member_id) >= {$orderNum}
                    ORDER BY payed DESC
                    ) Z";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //活跃度较高购物能力一般的客户
    function active_member_sale_44($shop_id) {
        //活跃度高，消费能力一般=这群人中订单价排名20%-50%
        $time = strtotime(date("Y-m-d"));
        $orderNum = 3;
        $dayBefore360 = $time - 360 * 86400;
        $startPercent = 0.2;
        $endPercent = 0.5;
        $sql = "SELECT
                  Z.member_id
                FROM
                   (
                    SELECT
                      member_id
                    FROM
                      `sdb_ecorder_orders`
                    WHERE
                      createtime >= {$dayBefore360}
                    AND
                      shop_id = '{$shop_id}'
                    GROUP BY member_id HAVING COUNT(member_id) >= {$orderNum}
                    ORDER BY payed DESC
                    ) Z";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //活跃度较高且购物能力较强的客户
    function active_member_sale_45($shop_id) {
        //活跃度高，消费能力强=这群人中订单价排名0%-20%
        $time = strtotime(date("Y-m-d"));
        $orderNum = 3;
        $dayBefore360 = $time - 360 * 86400;
        $startPercent = 0;
        $endPercent = 0.2;
        $sql = "SELECT
                  Z.member_id
                FROM
                   (
                    SELECT
                      member_id
                    FROM
                      `sdb_ecorder_orders`
                    WHERE
                      createtime >= {$dayBefore360}
                    AND
                      shop_id = '{$shop_id}'
                    GROUP BY member_id HAVING COUNT(member_id) >= {$orderNum}
                    ORDER BY payed DESC
                    ) Z";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近1个月下单未交易成功的客户
    function not_trade_member_sale_46($shop_id) {
        $time = strtotime(date("Y-m-d"));
        $dayBefore30 = $time - 30 * 86400;;
        $sql = "SELECT
                  B.member_id
                FROM(
                       SELECT 
                          A.member_id
                        FROM(
                              SELECT
                                member_id, `status`,COUNT(`status`) as count_status
                              FROM
                                `sdb_ecorder_orders`
                              WHERE
                                createtime >= {$dayBefore30}
                              AND 
                                shop_id = '{$shop_id}'
                              GROUP BY member_id, `status`
                             ) A
                        GROUP BY member_id HAVING COUNT(1) = 1 AND MAX(`status`) = 'dead'
                ) B";
        return $this->callSqlData($sql);
//        $sql = "SELECT
//                  member_id
//                FROM
//                  sdb_ecorder_orders
//                WHERE
//                  `sdb_ecorder_orders`.status = 'dead'
//                AND member_id NOT IN(
//                      SELECT
//                         member_id
//                      FROM
//                        sdb_ecorder_orders
//                      WHERE
//                        `sdb_ecorder_orders`.status != 'dead' 
//                      AND
//                        createtime >= ". $dayBefore30  .")
//               AND
//                 createtime >=". $dayBefore30 ."
//               AND 
//                 shop_id = '" .$shop_id. "'";
//        $count = $this->countNum($sql);
//        $res = array('sql' => $sql, 'count' => $count);
//        return $res;
    }
    
    //下单3天内还未付款的客户
    function not_trade_member_sale_47($shop_id) {
        //近3天催付：取订单创建时间近3天的订单，取未付款的订单
        $time = strtotime(date("Y-m-d"));
        $dayBefore3 = $time - 3 * 86400;
        $sql = "SELECT
                  member_id
                FROM
                  sdb_ecorder_orders
                WHERE
                  pay_status = 0
                AND
                   createtime >= ". $dayBefore3 ."
                AND
                   shop_id = '" .$shop_id. "'";
        $count = $this->countNum($sql);
        $res = array('sql' => $sql, 'count' => $count);
        return $res;
    }
    
    protected function getRegionStateList($stateName) {
        $stateRegionId = array(
            '辽宁' => 1874,
            '吉林' => 1573,
            '黑龙江' => 1176,
            '新疆' => 2873,
            '西藏' => 2792,
            '甘肃' => 322,
            '青海' => 2130,
            '宁夏' => 2103,
            '陕西' => 2471,
            '内蒙古' => 1989,
            '北京' => 1,
            '天津' => 42,
            '河北' => 814,
            '山西' => 2340,
            '河南' => 998,
            '山东' => 2182
        );
        $southRegionId = array(
            '上海' => 21,
            '浙江' => 3133,
            '江苏' => 1643,
            '安徽' => 104,
            '江西' => 1763,
            '湖南' => 1436,
            '湖北' => 1320,
            '重庆' => 62,
            '四川' => 2589,
            '福建' => 227,
            '广东' => 423,
            '广西' => 566,
            '海南' => 788,
            '贵州' => 690,
            '云南' => 2987,
            '香港' => 3235
        );
        $jiangZheHuRegionId = array(
            '上海' => 21,
            '浙江' => 3133,
            '江苏' => 1643
        );
        $beiJingAroundRegionId = array(
           '北京' =>  1,
           '天津' => 42,
           '河北' => 814,
           '河南' => 998
        );
        $guangdongAroungRegionId = array(
            '福建' => 227,
            '广东' => 423,
            '广西' => 566,
            '云南' => 2987
        );
        $stateName = strtolower($stateName);
        $stateRegionList = array(
            'north' => $stateRegionId,
            'south' => $southRegionId,
            'jiangzhehu' => $jiangZheHuRegionId,
            'beijingaround' => $beiJingAroundRegionId,
            'guangdongaround' => $guangdongAroungRegionId
        );
        $selectRegion = $stateRegionList[$stateName];
        return implode(',', $selectRegion);
    }
    
    //近3个月活跃度较低购物能力一般的客户
    function area_sale_48($shop_id) {
        //近3个月：只取近3个月的订单
        //近3个月活跃度低=近3个月下单次数=1，找出这群人，按订单价从高到低排名
        //近3个月活跃度低消费能力一般=订单价排名30%-100%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore90 = $time - 90 * 86400;
        $orderCount = 1;
        $startPercent = 0.3;
        $endPercent = 1;
        $areaName = 'north';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore90}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) = {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近3个月活跃度较低但购物能力较强的客户
    function area_sale_49($shop_id) {
        //近3个月活跃度低消费能力强=订单价排名0%-30%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore90 = $time - 90 * 86400;
        $orderCount = 1;
        $startPercent = 0;
        $endPercent = 0.3;
        $areaName = 'north';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore90}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) = {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近3个月活跃度较高购物能力一般的客户
    function area_sale_50($shop_id) {
        //近3个月活跃度高=近3个月下单次数>1，找出这群人，按订单价从高到低排名
        //近3个月活跃度高消费能力一般=订单价排名30%-100%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore90 = $time - 90 * 86400;
        $orderCount = 1;
        $startPercent = 0.3;
        $endPercent = 1;
        $areaName = 'north';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore90}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) > {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近3个月活跃度较高且购物能力较强的客户
    function area_sale_51($shop_id) {
        //近3个月活跃度高消费能力强=订单价排名0%-30%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore90 = $time - 90 * 86400;
        $orderCount = 1;
        $startPercent = 0;
        $endPercent = 0.3;
        $areaName = 'north';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore90}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) > {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近6个月活跃度较低购物能力一般的客户
    function area_sale_52($shop_id) {
        //近6个月：只取近6个月的订单
        //近6个月活跃度低=近6个月下单次数=1，找出这群人，按订单价从高到低排名
        //近6个月活跃度低消费能力一般=订单价排名30%-100%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore180 = $time - 180 * 86400;
        $orderCount = 1;
        $startPercent = 0.3;
        $endPercent = 1;
        $areaName = 'north';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore180}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) = {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近6个月活跃度较低但购物能力较强的客户
    function area_sale_53($shop_id) {
        //近6个月活跃度低消费能力强=订单价排名0%-30%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore180 = $time - 180 * 86400;
        $orderCount = 1;
        $startPercent = 0;
        $endPercent = 0.3;
        $areaName = 'north';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore180}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) = {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近6个月活跃度较高购物能力一般的客户
    function area_sale_54($shop_id) {
        //近6个月活跃度高=近6个月下单次数>1，找出这群人，按订单价从高到低排名
        //近6个月活跃度高消费能力一般=订单价排名30%-100%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore180 = $time - 180 * 86400;
        $orderCount = 1;
        $startPercent = 0.3;
        $endPercent = 1;
        $areaName = 'north';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore180}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) > {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近6个月活跃度较高且购物能力较强的客户
    function area_sale_55($shop_id) {
        //近6个月活跃度高消费能力强=订单价排名0%-30%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore180 = $time - 180 * 86400;
        $orderCount = 1;
        $startPercent = 0;
        $endPercent = 0.3;
        $areaName = 'north';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore180}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) > {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近12个月活跃度较低购物能力一般的客户
    function area_sale_56($shop_id) {
        //近12个月：只取近12个月的订单
        //近12个月活跃度低=近12个月下单次数=1，找出这群人，按订单价从高到低排名
        //近12个月活跃度低消费能力一般=订单价排名30%-100%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore360 = $time - 360 * 86400;
        $orderCount = 1;
        $startPercent = 0.3;
        $endPercent = 1;
        $areaName = 'north';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore360}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) = {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近12个月活跃度较低但购物能力较强的客户
    function area_sale_57($shop_id) {
        //近12个月活跃度低消费能力强=订单价排名0%-30%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore360 = $time - 360 * 86400;
        $orderCount = 1;
        $startPercent = 0;
        $endPercent = 0.3;
        $areaName = 'north';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore360}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) = {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近12个月活跃度较高购物能力一般的客户
    function area_sale_58($shop_id) {
        //近12个月活跃度高=近12个月下单次数>1，找出这群人，按订单价从高到低排名
        //近12个月活跃度高消费能力一般=订单价排名30%-100%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore360 = $time - 360 * 86400;
        $orderCount = 1;
        $startPercent = 0.3;
        $endPercent = 1;
        $areaName = 'north';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore360}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) > {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近12个月活跃度较高且购物能力较强的客户
    function area_sale_59($shop_id) {
        //近12个月活跃度高消费能力强=订单价排名0%-30%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore360 = $time - 360 * 86400;
        $orderCount = 1;
        $startPercent = 0;
        $endPercent = 0.3;
        $areaName = 'north';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore360}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) > {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近3个月活跃度较低购物能力一般的客户---南方地区--地区营销
    function area_sale_60($shop_id) {
        //近3个月：只取近3个月的订单
        //近3个月活跃度低=近3个月下单次数=1，找出这群人，按订单价从高到低排名
        //近3个月活跃度低消费能力一般=订单价排名30%-100%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore90 = $time - 90 * 86400;
        $orderCount = 1;
        $startPercent = 0.3;
        $endPercent = 1;
        $areaName = 'south';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore90}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) = {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近3个月活跃度较低但购物能力较强的客户
    function area_sale_61($shop_id) {
        //近3个月活跃度低消费能力强=订单价排名0%-30%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore90 = $time - 90 * 86400;
        $orderCount = 1;
        $startPercent = 0;
        $endPercent = 0.3;
        $areaName = 'south';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore90}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) = {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近3个月活跃度较高购物能力一般的客户
    function area_sale_62($shop_id) {
        //近3个月活跃度高=近3个月下单次数>1，找出这群人，按订单价从高到低排名
        //近3个月活跃度高消费能力一般=订单价排名30%-100%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore90 = $time - 90 * 86400;
        $orderCount = 1;
        $startPercent = 0.3;
        $endPercent = 1;
        $areaName = 'south';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore90}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) > {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近3个月活跃度较高且购物能力较强的客户
    function area_sale_63($shop_id) {
        //近3个月活跃度高消费能力强=订单价排名0%-30%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore90 = $time - 90 * 86400;
        $orderCount = 1;
        $startPercent = 0;
        $endPercent = 0.3;
        $areaName = 'south';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore90}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) > {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近6个月活跃度较低购物能力一般的客户
    function area_sale_64($shop_id) {
        //近6个月：只取近6个月的订单
        //近6个月活跃度低=近6个月下单次数=1，找出这群人，按订单价从高到低排名
        //近6个月活跃度低消费能力一般=订单价排名30%-100%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore180 = $time - 180 * 86400;
        $orderCount = 1;
        $startPercent = 0.3;
        $endPercent = 1;
        $areaName = 'south';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore180}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) = {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近6个月活跃度较低但购物能力较强的客户
    function area_sale_65($shop_id) {
        //近6个月活跃度低消费能力强=订单价排名0%-30%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore180 = $time - 180 * 86400;
        $orderCount = 1;
        $startPercent = 0;
        $endPercent = 0.3;
        $areaName = 'south';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore180}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) = {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近6个月活跃度较高购物能力一般的客户
    function area_sale_66($shop_id) {
        //近6个月活跃度高=近6个月下单次数>1，找出这群人，按订单价从高到低排名
        //近6个月活跃度高消费能力一般=订单价排名30%-100%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore180 = $time - 180 * 86400;
        $orderCount = 1;
        $startPercent = 0.3;
        $endPercent = 1;
        $areaName = 'south';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore180}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) > {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近6个月活跃度较高且购物能力较强的客户
    function area_sale_67($shop_id) {
        //近6个月活跃度高消费能力强=订单价排名0%-30%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore180 = $time - 180 * 86400;
        $orderCount = 1;
        $startPercent = 0;
        $endPercent = 0.3;
        $areaName = 'south';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore180}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) > {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近12个月活跃度较低购物能力一般的客户
    function area_sale_68($shop_id) {
        //近12个月：只取近12个月的订单
        //近12个月活跃度低=近12个月下单次数=1，找出这群人，按订单价从高到低排名
        //近12个月活跃度低消费能力一般=订单价排名30%-100%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore360 = $time - 360 * 86400;
        $orderCount = 1;
        $startPercent = 0.3;
        $endPercent = 1;
        $areaName = 'south';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore360}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) = {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近12个月活跃度较低但购物能力较强的客户
    function area_sale_69($shop_id) {
        //近12个月活跃度低消费能力强=订单价排名0%-30%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore360 = $time - 360 * 86400;
        $orderCount = 1;
        $startPercent = 0;
        $endPercent = 0.3;
        $areaName = 'south';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore360}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) = {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近12个月活跃度较高购物能力一般的客户
    function area_sale_70($shop_id) {
        //近12个月活跃度高=近12个月下单次数>1，找出这群人，按订单价从高到低排名
        //近12个月活跃度高消费能力一般=订单价排名30%-100%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore360 = $time - 360 * 86400;
        $orderCount = 1;
        $startPercent = 0.3;
        $endPercent = 1;
        $areaName = 'south';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore360}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) > {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近12个月活跃度较高且购物能力较强的客户
    function area_sale_71($shop_id) {
        //近12个月活跃度高消费能力强=订单价排名0%-30%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore360 = $time - 360 * 86400;
        $orderCount = 1;
        $startPercent = 0;
        $endPercent = 0.3;
        $areaName = 'south';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore360}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) > {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近3个月活跃度较低购物能力一般的客户---江浙沪地区---地区营销
    function area_sale_72($shop_id) {
        //近3个月：只取近3个月的订单
        //近3个月活跃度低=近3个月下单次数=1，找出这群人，按订单价从高到低排名
        //近3个月活跃度低消费能力一般=订单价排名30%-100%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore90 = $time - 90 * 86400;
        $orderCount = 1;
        $startPercent = 0.3;
        $endPercent = 1;
        $areaName = 'jiangzhehu';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore90}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) = {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近3个月活跃度较低但购物能力较强的客户
    function area_sale_73($shop_id) {
        //近3个月活跃度低消费能力强=订单价排名0%-30%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore90 = $time - 90 * 86400;
        $orderCount = 1;
        $startPercent = 0;
        $endPercent = 0.3;
        $areaName = 'jiangzhehu';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore90}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) = {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近3个月活跃度较高购物能力一般的客户
    function area_sale_74($shop_id) {
        //近3个月活跃度高=近3个月下单次数>1，找出这群人，按订单价从高到低排名
        //近3个月活跃度高消费能力一般=订单价排名30%-100%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore90 = $time - 90 * 86400;
        $orderCount = 1;
        $startPercent = 0.3;
        $endPercent = 1;
        $areaName = 'jiangzhehu';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore90}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) > {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近3个月活跃度较高且购物能力较强的客户
    function area_sale_75($shop_id) {
        //近3个月活跃度高消费能力强=订单价排名0%-30%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore90 = $time - 90 * 86400;
        $orderCount = 1;
        $startPercent = 0;
        $endPercent = 0.3;
        $areaName = 'jiangzhehu';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore90}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) > {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近6个月活跃度较低购物能力一般的客户
    function area_sale_76($shop_id) {
        //近6个月：只取近6个月的订单
        //近6个月活跃度低=近6个月下单次数=1，找出这群人，按订单价从高到低排名，
        //近6个月活跃度低消费能力一般=订单价排名30%-100%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore180 = $time - 180 * 86400;
        $orderCount = 1;
        $startPercent = 0.3;
        $endPercent = 1;
        $areaName = 'jiangzhehu';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore180}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) = {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近6个月活跃度较低但购物能力较强的客户
    function area_sale_77($shop_id) {
        //近6个月活跃度低消费能力强=订单价排名0%-30%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore180 = $time - 180 * 86400;
        $orderCount = 1;
        $startPercent = 0;
        $endPercent = 0.3;
        $areaName = 'jiangzhehu';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore180}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) = {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近6个月活跃度较高购物能力一般的客户
    function area_sale_78($shop_id) {
        //近6个月活跃度高=近6个月下单次数>1，找出这群人，按订单价从高到低排名，
        //近6个月活跃度高消费能力一般=订单价排名30%-100%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore180 = $time - 180 * 86400;
        $orderCount = 1;
        $startPercent = 0.3;
        $endPercent = 1;
        $areaName = 'jiangzhehu';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore180}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) > {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近6个月活跃度较高且购物能力较强的客户
    function area_sale_79($shop_id) {
        //近6个月活跃度高消费能力强=订单价排名0%-30%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore180 = $time - 180 * 86400;
        $orderCount = 1;
        $startPercent = 0;
        $endPercent = 0.3;
        $areaName = 'jiangzhehu';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore180}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) > {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近12个月活跃度较低购物能力一般的客户
    function area_sale_80($shop_id) {
        //近12个月：只取近12个月的订单
        //近12个月活跃度低=近12个月下单次数=1，找出这群人，按订单价从高到低排名
        //近12个月活跃度低消费能力一般=订单价排名30%-100%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore360 = $time - 360 * 86400;
        $orderCount = 1;
        $startPercent = 0.3;
        $endPercent = 1;
        $areaName = 'jiangzhehu';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore360}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) = {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近12个月活跃度较低但购物能力较强的客户
    function area_sale_81($shop_id) {
        //近12个月活跃度低消费能力强=订单价排名0%-30%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore360 = $time - 360 * 86400;
        $orderCount = 1;
        $startPercent = 0;
        $endPercent = 0.3;
        $areaName = 'jiangzhehu';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore360}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) = {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近12个月活跃度较高购物能力一般的客户
    function area_sale_82($shop_id) {
        //近12个月活跃度高=近12个月下单次数>1，找出这群人，按订单价从高到低排名，
        //近12个月活跃度高消费能力一般=订单价排名30%-100%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore360 = $time - 360 * 86400;
        $orderCount = 1;
        $startPercent = 0.3;
        $endPercent = 1;
        $areaName = 'jiangzhehu';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore360}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) > {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近12个月活跃度较高且购物能力较强的客户
    function area_sale_83($shop_id) {
        //近12个月活跃度高消费能力强=订单价排名0%-30%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore360 = $time - 360 * 86400;
        $orderCount = 1;
        $startPercent = 0;
        $endPercent = 0.3;
        $areaName = 'jiangzhehu';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore360}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) > {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近3个月活跃度较低购物能力一般的客户---北京周边地区---地区营销
    function area_sale_84($shop_id) {
        //近3个月：只取近3个月的订单
        //近3个月活跃度低=近3个月下单次数=1，找出这群人，按订单价从高到低排名，
        //近3个月活跃度低消费能力一般=订单价排名30%-100%的，
        $time = strtotime(date("Y-m-d"));
        $dayBefore90 = $time - 90 * 86400;
        $orderCount = 1;
        $startPercent = 0.3;
        $endPercent = 1;
        $areaName = 'beijingaround';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore90}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) = {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近3个月活跃度较低但购物能力较强的客户
    function area_sale_85($shop_id) {
        //近3个月活跃度低消费能力强=订单价排名0%-30%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore90 = $time - 90 * 86400;
        $orderCount = 1;
        $startPercent = 0;
        $endPercent = 0.3;
        $areaName = 'beijingaround';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore90}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) = {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近3个月活跃度较高购物能力一般的客户
    function area_sale_86($shop_id) {
        //近3个月活跃度高=近3个月下单次数>1，找出这群人，按订单价从高到低排名
        //近3个月活跃度高消费能力一般=订单价排名30%-100%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore90 = $time - 90 * 86400;
        $orderCount = 1;
        $startPercent = 0.3;
        $endPercent = 1;
        $areaName = 'beijingaround';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore90}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) > {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近3个月活跃度较高且购物能力较强的客户
    function area_sale_87($shop_id) {
        //近3个月活跃度高消费能力强=订单价排名0%-30%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore90 = $time - 90 * 86400;
        $orderCount = 1;
        $startPercent = 0;
        $endPercent = 0.3;
        $areaName = 'beijingaround';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore90}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) > {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近6个月活跃度较低购物能力一般的客户
    function area_sale_88($shop_id) {
        //近6个月：只取近6个月的订单
        //近6个月活跃度低=近6个月下单次数=1，找出这群人，按订单价从高到低排名
        //近6个月活跃度低消费能力一般=订单价排名30%-100%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore180 = $time - 180 * 86400;
        $orderCount = 1;
        $startPercent = 0.3;
        $endPercent = 1;
        $areaName = 'beijingaround';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore180}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) = {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近6个月活跃度较低但购物能力较强的客户
    function area_sale_89($shop_id) {
        //近6个月活跃度低消费能力强=订单价排名0%-30%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore180 = $time - 180 * 86400;
        $orderCount = 1;
        $startPercent = 0;
        $endPercent = 0.3;
        $areaName = 'beijingaround';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore180}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) = {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近6个月活跃度较高购物能力一般的客户
    function area_sale_90($shop_id) {
        //近6个月活跃度高=近6个月下单次数>1，找出这群人，按订单价从高到低排名
        //近6个月活跃度高消费能力一般=订单价排名30%-100%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore180 = $time - 180 * 86400;
        $orderCount = 1;
        $startPercent = 0.3;
        $endPercent = 1;
        $areaName = 'beijingaround';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore180}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) > {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近6个月活跃度较高且购物能力较强的客户
    function area_sale_91($shop_id) {
        //近6个月活跃度高消费能力强=订单价排名0%-30%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore180 = $time - 180 * 86400;
        $orderCount = 1;
        $startPercent = 0;
        $endPercent = 0.3;
        $areaName = 'beijingaround';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore180}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) > {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近12个月活跃度较低购物能力一般的客户
    function area_sale_92($shop_id) {
        //近12个月：只取近12个月的订单
        //近12个月活跃度低=近12个月下单次数=1，找出这群人，按订单价从高到低排名
        //近12个月活跃度低消费能力一般=订单价排名30%-100%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore360 = $time - 360 * 86400;
        $orderCount = 1;
        $startPercent = 0.3;
        $endPercent = 1;
        $areaName = 'beijingaround';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore360}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) = {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近12个月活跃度较低但购物能力较强的客户
    function area_sale_93($shop_id) {
        //近12个月活跃度低消费能力强=订单价排名0%-30%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore360 = $time - 360 * 86400;
        $orderCount = 1;
        $startPercent = 0;
        $endPercent = 0.3;
        $areaName = 'beijingaround';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore360}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) = {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近12个月活跃度较高购物能力一般的客户
    function area_sale_94($shop_id) {
        //近12个月活跃度高=近12个月下单次数>1，找出这群人，按订单价从高到低排名
        //近12个月活跃度高消费能力一般=订单价排名30%-100%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore360 = $time - 360 * 86400;
        $orderCount = 1;
        $startPercent = 0.3;
        $endPercent = 1;
        $areaName = 'beijingaround';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore360}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) > {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近12个月活跃度较高且购物能力较强的客户
    function area_sale_95($shop_id) {
        //近12个月活跃度高消费能力强=订单价排名0%-30%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore360 = $time - 360 * 86400;
        $orderCount = 1;
        $startPercent = 0;
        $endPercent = 0.3;
        $areaName = 'beijingaround';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore360}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) > {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近3个月活跃度较低购物能力一般的客户
    function area_sale_96($shop_id) {
        //计算所有状态的订单，然后取客户的地区在北方的订单
        //('福建省','广东省','广西壮族自治区','云南省')
        //近3个月：只取近3个月的订单
        //近3个月活跃度低=近3个月下单次数=1，找出这群人，按订单价从高到低排名，
        //近3个月活跃度低消费能力一般=订单价排名30%-100%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore90 = $time - 90 * 86400;
        $orderCount = 1;
        $startPercent = 0.3;
        $endPercent = 1;
        $areaName = 'guangdongaround';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore90}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) = {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
        
    }
    
    //近3个月活跃度较低但购物能力较强的客户
    function area_sale_97($shop_id) {
        //近3个月活跃度低消费能力一般=订单价排名30%-100%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore90 = $time - 90 * 86400;
        $orderCount = 1;
        $startPercent = 0;
        $endPercent = 0.3;
        $areaName = 'guangdongaround';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore90}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) = {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近3个月活跃度较高购物能力一般的客户
    function area_sale_98($shop_id) {
        //近3个月活跃度低消费能力强=订单价排名0%-30%的，
        //近3个月活跃度高=近3个月下单次数>1，找出这群人，按订单价从高到低排名
        //近3个月活跃度高消费能力一般=订单价排名30%-100%的，
        $time = strtotime(date("Y-m-d"));
        $dayBefore90 = $time - 90 * 86400;
        $orderCount = 1;
        $startPercent = 0.3;
        $endPercent = 1;
        $areaName = 'guangdongaround';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore90}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) > {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近3个月活跃度较高且购物能力较强的客户
    function area_sale_99($shop_id) {
        //近3个月活跃度高消费能力强=订单价排名0%-30%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore90 = $time - 90 * 86400;
        $orderCount = 1;
        $startPercent = 0;
        $endPercent = 0.3;
        $areaName = 'guangdongaround';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore90}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) > {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近6个月活跃度较低购物能力一般的客户
    function area_sale_100($shop_id) {
        //近6个月：只取近6个月的订单
        //近6个月活跃度低=近6个月下单次数=1，找出这群人，按订单价从高到低排名，
        //近6个月活跃度低消费能力一般=订单价排名30%-100%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore180 = $time - 180 * 86400;
        $orderCount = 1;
        $startPercent = 0.3;
        $endPercent = 1;
        $areaName = 'guangdongaround';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore180}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) = {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近6个月活跃度较低但购物能力较强的客户
    function area_sale_101($shop_id) {
        //近6个月活跃度低消费能力强=订单价排名0%-30%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore180 = $time - 180 * 86400;
        $orderCount = 1;
        $startPercent = 0;
        $endPercent = 0.3;
        $areaName = 'guangdongaround';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore180}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) = {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近6个月活跃度较高购物能力一般的客户
    function area_sale_102($shop_id) {
        //近6个月活跃度高=近6个月下单次数>1，找出这群人，按订单价从高到低排名
        //近6个月活跃度高消费能力一般=订单价排名30%-100%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore180 = $time - 180 * 86400;
        $orderCount = 1;
        $startPercent = 0.3;
        $endPercent = 1;
        $areaName = 'guangdongaround';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore180}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) > {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近6个月活跃度较高且购物能力较强的客户
    function area_sale_103($shop_id) {
        //近6个月活跃度高消费能力强=订单价排名0%-30%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore180 = $time - 180 * 86400;
        $orderCount = 1;
        $startPercent = 0;
        $endPercent = 0.3;
        $areaName = 'guangdongaround';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore180}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) > {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近12个月活跃度较低购物能力一般的客户
    function area_sale_104($shop_id) {
        //近12个月：只取近12个月的订单
        //近12个月活跃度低=近12个月下单次数=1，找出这群人，按订单价从高到低排名，
        //近12个月活跃度低消费能力一般=订单价排名30%-100%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore360 = $time - 360 * 86400;
        $orderCount = 1;
        $startPercent = 0.3;
        $endPercent = 1;
        $areaName = 'guangdongaround';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore360}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) = {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近12个月活跃度较低但购物能力较强的客户
    function area_sale_105($shop_id) {
        //近12个月活跃度低消费能力强=订单价排名0%-30%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore360 = $time - 360 * 86400;
        $orderCount = 1;
        $startPercent = 0;
        $endPercent = 0.3;
        $areaName = 'guangdongaround';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore360}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) = {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近12个月活跃度较高购物能力一般的客户
    function area_sale_106($shop_id) {
        //近12个月活跃度高=近12个月下单次数>1，找出这群人，按订单价从高到低排名
        //近12个月活跃度高消费能力一般=订单价排名30%-100%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore360 = $time - 360 * 86400;
        $orderCount = 1;
        $startPercent = 0.3;
        $endPercent = 1;
        $areaName = 'guangdongaround';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore360}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) > {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //近12个月活跃度较高且购物能力较强的客户
    function area_sale_107($shop_id) {
        //近12个月活跃度高消费能力强=订单价排名0%-30%的
        $time = strtotime(date("Y-m-d"));
        $dayBefore360 = $time - 360 * 86400;
        $orderCount = 1;
        $startPercent = 0;
        $endPercent = 0.3;
        $areaName = 'guangdongaround';
        $regionIds = $this->getRegionStateList($areaName);
        $sql = "SELECT
                  A.member_id
                FROM (
                       SELECT 
                         member_id
                       FROM
                         `sdb_ecorder_orders`
                       WHERE
                         createtime >= {$dayBefore360}
                       AND
                         shop_id = '{$shop_id}'
                       AND
                         state_id IN ({$regionIds})
                       GROUP BY member_id HAVING COUNT(member_id) > {$orderCount}
                       ORDER BY payed DESC
                      ) A
                ";
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //元旦活动--对价格敏感的客户
    function season_sale_108($shop_id) {
        //前年12月份28起始时间;去年1月6日结束时间
        $lastLastDecemberStartTime  = mktime(0, 0, 0, 12, 28, date("Y") - 2);
        $lastJanEndTime = mktime(0, 0, 0, 1, 6, date("Y") - 1);
        
        //去年12月28日起始时间;今年1月结束时间
        $currentTime = time();
        $lastDecemberStartTime  = mktime(0, 0, 0, 12, 28, date("Y") - 1);
        $janEndTime = mktime(0, 0, 0, 1, 6, date("Y"));
        if ($currentTime < $janEndTime) {
            $janEndTime = $currentTime;
        }
        
        //今年12月份时间
        $decEndTime = '';
        $decStartTime = mktime(0, 0, 0, 12, 28, date("Y"));
        if ($currentTime >= $decStartTime) {
            $decEndTime = $currentTime;
        }
        
        $sql = "SELECT
                  DISTINCT member_id
                FROM
                  `sdb_ecorder_orders`
                WHERE
                  ((createtime >= {$lastLastDecemberStartTime} AND createtime < {$lastJanEndTime})
                OR
                  (createtime >= {$lastDecemberStartTime} AND createtime  < {$janEndTime})
               ";
        if ($decEndTime) {
            $sql .= " OR (createtime >= {$decStartTime} AND createtime < {$decEndTime}))";
        }
        else {
            $sql .= " )";
        }
        $sql .= " AND shop_id = '{$shop_id}' ORDER BY payed DESC";
        
        $startPercent = 0.3;
        $endPercent = 1;
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //元旦活动--消费能力强的客户
    function season_sale_109($shop_id) {
        //前年12月份28起始时间;去年1月6日结束时间
        $lastLastDecemberStartTime  = mktime(0, 0, 0, 12, 28, date("Y") - 2);
        $lastJanEndTime = mktime(0, 0, 0, 1, 6, date("Y") - 1);
        
        //去年12月28日起始时间;今年1月结束时间
        $currentTime = time();
        $lastDecemberStartTime  = mktime(0, 0, 0, 12, 28, date("Y") - 1);
        $janEndTime = mktime(0, 0, 0, 1, 6, date("Y"));
        if ($currentTime < $janEndTime) {
            $janEndTime = $currentTime;
        }
        
        //今年12月份时间
        $decEndTime = '';
        $decStartTime = mktime(0, 0, 0, 12, 28, date("Y"));
        if ($currentTime >= $decStartTime) {
            $decEndTime = $currentTime;
        }
        
        $sql = "SELECT
                  DISTINCT member_id
                FROM
                  `sdb_ecorder_orders`
                WHERE
                  ((createtime >= {$lastLastDecemberStartTime} AND createtime < {$lastJanEndTime})
                OR
                  (createtime >= {$lastDecemberStartTime} AND createtime  < {$janEndTime})
               ";
        if ($decEndTime) {
            $sql .= " OR (createtime >= {$decStartTime} AND createtime < {$decEndTime}))";
        }
        else {
            $sql .= " )";
        }
        $sql .= " AND shop_id = '{$shop_id}' ORDER BY payed DESC";
        
        $startPercent = 0;
        $endPercent = 0.3;
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //2月份    情人节活动  对价格敏感的客户
    function season_sale_110($shop_id) {
        //每年2-11到2-16
        //前年2月份11起始时间;前年2月16日结束时间
        $lastLastFebStartTime  = mktime(0, 0, 0, 2, 11, date("Y") - 2);
        $lastLastFebEndTime = mktime(0, 0, 0, 2, 17, date("Y") - 2);
        
        //去年2月份11起始时间;去年2月16日结束时间
        $lastFebStartTime   = mktime(0, 0, 0, 2, 11, date("Y") - 1);
        $lastFebEntTime   = mktime(0, 0, 0, 2, 17, date("Y") - 1);
        
        //今年2-11到2-16
        $currentTime = time();
        $febStartTime = '';
        $febEndTime = '';
        $Feb11Time = mktime(0, 0, 0, 2, 11, date("Y"));
        if ($currentTime >= $Feb11Time) {
            $febStartTime = $Feb11Time;
        }
        
        $Feb17Time = mktime(0, 0, 0, 2, 17, date("Y"));
        if ($currentTime >= $Feb17Time) {
            $febEndTime = $Feb17Time;
        }
        elseif ($febStartTime) {
            $febEndTime = $currentTime;
        }
        $sql = "SELECT
                  DISTINCT member_id
                FROM
                  `sdb_ecorder_orders`
                WHERE
                  ((createtime >= {$lastLastFebStartTime} AND createtime < {$lastLastFebEndTime})
                OR
                  (createtime >= {$lastFebStartTime} AND createtime  < {$lastFebEntTime})
               ";
        if ($febEndTime) {
            $sql .= " OR (createtime >= {$febStartTime} AND createtime < {$febEndTime}))";
        }
        else {
            $sql .= " )";
        }
        $sql .= " AND shop_id = '{$shop_id}' ORDER BY payed DESC";
        
        $startPercent = 0.3;
        $endPercent = 1;
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    ///2月份    情人节活动  消费能力强的客户
    function season_sale_111($shop_id) {
        //每年2-11到2-16
        //前年2月份11起始时间;前年2月16日结束时间
        $lastLastFebStartTime  = mktime(0, 0, 0, 2, 11, date("Y") - 2);
        $lastLastFebEndTime = mktime(0, 0, 0, 2, 17, date("Y") - 2);
        
        //去年2月份11起始时间;去年2月16日结束时间
        $lastFebStartTime   = mktime(0, 0, 0, 2, 11, date("Y") - 1);
        $lastFebEntTime   = mktime(0, 0, 0, 2, 17, date("Y") - 1);
        
        //今年2-11到2-16
        $currentTime = time();
        $febStartTime = '';
        $febEndTime = '';
        $Feb11Time = mktime(0, 0, 0, 2, 11, date("Y"));
        if ($currentTime >= $Feb11Time) {
            $febStartTime = $Feb11Time;
        }
        
        $Feb17Time = mktime(0, 0, 0, 2, 17, date("Y"));
        if ($currentTime >= $Feb17Time) {
            $febEndTime = $Feb17Time;
        }
        elseif ($febStartTime) {
            $febEndTime = $currentTime;
        }
        $sql = "SELECT
                  DISTINCT member_id
                FROM
                  `sdb_ecorder_orders`
                WHERE
                  ((createtime >= {$lastLastFebStartTime} AND createtime < {$lastLastFebEndTime})
                OR
                  (createtime >= {$lastFebStartTime} AND createtime  < {$lastFebEntTime})
               ";
        if ($febEndTime) {
            $sql .= " OR (createtime >= {$febStartTime} AND createtime < {$febEndTime}))";
        }
        else {
            $sql .= " )";
        }
        $sql .= " AND shop_id = '{$shop_id}' ORDER BY payed DESC";
        
        $startPercent = 0;
        $endPercent = 0.3;
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //3月份    妇女节活动   对价格敏感的客户
    function season_sale_112($shop_id) {
        //每年3-5到3-10
        //前年3月份5起始时间;前年3月11日结束时间
        $lastLastStartTime  = mktime(0, 0, 0, 3, 5, date("Y") - 2);
        $lastLastEndTime = mktime(0, 0, 0, 3, 11, date("Y") - 2);
        
        //去年3月5起始时间;去年3月11日结束时间
        $lastStartTime   = mktime(0, 0, 0, 3, 5, date("Y") - 1);
        $lastEndTime   = mktime(0, 0, 0, 3, 11, date("Y") - 1);
        
        //今年3-5到3-11
        $currentTime = time();
        $startTime = '';
        $endTime = '';
        $startAlongTime = mktime(0, 0, 0, 3, 5, date("Y"));
        if ($currentTime >= $startAlongTime) {
            $startTime = $startAlongTime;
        }
        
        $endAlongTime = mktime(0, 0, 0, 3, 11, date("Y"));
        if ($currentTime >= $endAlongTime) {
            $endTime = $endAlongTime;
        }
        elseif ($startTime) {
            $endTime = $currentTime;
        }
        $sql = "SELECT
                  DISTINCT member_id
                FROM
                  `sdb_ecorder_orders`
                WHERE
                  ((createtime >= {$lastLastStartTime} AND createtime < {$lastLastEndTime})
                OR
                  (createtime >= {$lastStartTime} AND createtime  < {$lastEndTime})
               ";
        if ($endTime) {
            $sql .= " OR (createtime >= {$startTime} AND createtime < {$endTime}))";
        }
        else {
            $sql .= " )";
        }
        $sql .= " AND shop_id = '{$shop_id}' ORDER BY payed DESC";
        
        $startPercent = 0.3;
        $endPercent = 1;
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //3月份    妇女节活动   消费能力强的客户
    function season_sale_113($shop_id) {
        //每年3-5到3-10
        //前年3月份5起始时间;前年3月11日结束时间
        $lastLastStartTime  = mktime(0, 0, 0, 3, 5, date("Y") - 2);
        $lastLastEndTime = mktime(0, 0, 0, 3, 11, date("Y") - 2);
        
        //去年3月5起始时间;去年3月11日结束时间
        $lastStartTime   = mktime(0, 0, 0, 3, 5, date("Y") - 1);
        $lastEndTime   = mktime(0, 0, 0, 3, 11, date("Y") - 1);
        
        //今年3-5到3-11
        $currentTime = time();
        $startTime = '';
        $endTime = '';
        $startAlongTime = mktime(0, 0, 0, 3, 5, date("Y"));
        if ($currentTime >= $startAlongTime) {
            $startTime = $startAlongTime;
        }
        
        $endAlongTime = mktime(0, 0, 0, 3, 11, date("Y"));
        if ($currentTime >= $endAlongTime) {
            $endTime = $endAlongTime;
        }
        elseif ($startTime) {
            $endTime = $currentTime;
        }
        $sql = "SELECT
                  DISTINCT member_id
                FROM
                  `sdb_ecorder_orders`
                WHERE
                  ((createtime >= {$lastLastStartTime} AND createtime < {$lastLastEndTime})
                OR
                  (createtime >= {$lastStartTime} AND createtime  < {$lastEndTime})
               ";
        if ($endTime) {
            $sql .= " OR (createtime >= {$startTime} AND createtime < {$endTime}))";
        }
        else {
            $sql .= " )";
        }
        $sql .= " AND shop_id = '{$shop_id}' ORDER BY payed DESC";
        
        $startPercent = 0;
        $endPercent = 0.3;
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    
    //3月份   白色情人节活动  对价格敏感的客户
    function season_sale_114($shop_id) {
        //每年3-11到3-16
        $startDay = 11;
        $startMonth = 3;
        $endDay = 17;
        $endMonth = 3;
        //前年3月份11起始时间;前年3月17日结束时间
        $lastLastStartTime  = mktime(0, 0, 0, $startMonth, $startDay, date("Y") - 2);
        $lastLastEndTime = mktime(0, 0, 0, $endMonth, $endDay, date("Y") - 2);
        
        //去年3月11起始时间;去年3月17日结束时间
        $lastStartTime   = mktime(0, 0, 0, $startMonth, $startDay, date("Y") - 1);
        $lastEndTime   = mktime(0, 0, 0, $endMonth, $endDay, date("Y") - 1);
        //今年3-11到3-17
        $currentTime = time();
        $startTime = '';
        $endTime = '';
        $startAlongTime = mktime(0, 0, 0, $startMonth, $startDay, date("Y"));
        if ($currentTime >= $startAlongTime) {
            $startTime = $startAlongTime;
        }
        
        $endAlongTime = mktime(0, 0, 0, $endMonth, $endDay, date("Y"));
        if ($currentTime >= $endAlongTime) {
            $endTime = $endAlongTime;
        }
        elseif ($startTime) {
            $endTime = $currentTime;
        }
        $sql = "SELECT
                  DISTINCT member_id
                FROM
                  `sdb_ecorder_orders`
                WHERE
                  ((createtime >= {$lastLastStartTime} AND createtime < {$lastLastEndTime})
                OR
                  (createtime >= {$lastStartTime} AND createtime  < {$lastEndTime})
               ";
        if ($endTime) {
            $sql .= " OR (createtime >= {$startTime} AND createtime < {$endTime}))";
        }
        else {
            $sql .= " )";
        }
        $sql .= " AND shop_id = '{$shop_id}' ORDER BY payed DESC";
        
        $startPercent = 0.3;
        $endPercent = 1;
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //3月份   白色情人节活动  消费能力强的客户
    function season_sale_115($shop_id) {
        //每年3-11到3-16
        $startDay = 11;
        $startMonth = 3;
        $endDay = 17;
        $endMonth = 3;
        //前年3月份11起始时间;前年3月17日结束时间
        $lastLastStartTime  = mktime(0, 0, 0, $startMonth, $startDay, date("Y") - 2);
        $lastLastEndTime = mktime(0, 0, 0, $endMonth, $endDay, date("Y") - 2);
        
        //去年3月11起始时间;去年3月17日结束时间
        $lastStartTime   = mktime(0, 0, 0, $startMonth, $startDay, date("Y") - 1);
        $lastEndTime   = mktime(0, 0, 0, $endMonth, $endDay, date("Y") - 1);
        //今年3-11到3-17
        $currentTime = time();
        $startTime = '';
        $endTime = '';
        $startAlongTime = mktime(0, 0, 0, $startMonth, $startDay, date("Y"));
        if ($currentTime >= $startAlongTime) {
            $startTime = $startAlongTime;
        }
        
        $endAlongTime = mktime(0, 0, 0, $endMonth, $endDay, date("Y"));
        if ($currentTime >= $endAlongTime) {
            $endTime = $endAlongTime;
        }
        elseif ($startTime) {
            $endTime = $currentTime;
        }
        $sql = "SELECT
                  DISTINCT member_id
                FROM
                  `sdb_ecorder_orders`
                WHERE
                  ((createtime >= {$lastLastStartTime} AND createtime < {$lastLastEndTime})
                OR
                  (createtime >= {$lastStartTime} AND createtime  < {$lastEndTime})
               ";
        if ($endTime) {
            $sql .= " OR (createtime >= {$startTime} AND createtime < {$endTime}))";
        }
        else {
            $sql .= " )";
        }
        $sql .= " AND shop_id = '{$shop_id}' ORDER BY payed DESC";
        
        $startPercent = 0;
        $endPercent = 0.3;
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //4月份    愚人节活动  对价格敏感的客户
    function season_sale_116($shop_id) {
        //每年3-29到4-3
        $startDay = 29;
        $startMonth = 3;
        $endDay = 3;
        $endMonth = 4;
        $lastLastStartTime  = mktime(0, 0, 0, $startMonth, $startDay, date("Y") - 2);
        $lastLastEndTime = mktime(0, 0, 0, $endMonth, $endDay, date("Y") - 2);
        
        $lastStartTime   = mktime(0, 0, 0, $startMonth, $startDay, date("Y") - 1);
        $lastEndTime   = mktime(0, 0, 0, $endMonth, $endDay, date("Y") - 1);
        
        $currentTime = time();
        $startTime = '';
        $endTime = '';
        $startAlongTime = mktime(0, 0, 0, $startMonth, $startDay, date("Y"));
        if ($currentTime >= $startAlongTime) {
            $startTime = $startAlongTime;
        }
        
        $endAlongTime = mktime(0, 0, 0, $endMonth, $endDay, date("Y"));
        if ($currentTime >= $endAlongTime) {
            $endTime = $endAlongTime;
        }
        elseif ($startTime) {
            $endTime = $currentTime;
        }
        $sql = "SELECT
                  DISTINCT member_id
                FROM
                  `sdb_ecorder_orders`
                WHERE
                  ((createtime >= {$lastLastStartTime} AND createtime < {$lastLastEndTime})
                OR
                  (createtime >= {$lastStartTime} AND createtime  < {$lastEndTime})
               ";
        if ($endTime) {
            $sql .= " OR (createtime >= {$startTime} AND createtime < {$endTime}))";
        }
        else {
            $sql .= " )";
        }
        $sql .= " AND shop_id = '{$shop_id}' ORDER BY payed DESC";
        
        $startPercent = 0.3;
        $endPercent = 1;
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //4月份    愚人节活动  消费能力强的客户
    function season_sale_117($shop_id) {
        //每年3-29到4-3
        $startDay = 29;
        $startMonth = 3;
        $endDay = 3;
        $endMonth = 4;
        $lastLastStartTime  = mktime(0, 0, 0, $startMonth, $startDay, date("Y") - 2);
        $lastLastEndTime = mktime(0, 0, 0, $endMonth, $endDay, date("Y") - 2);
        
        $lastStartTime   = mktime(0, 0, 0, $startMonth, $startDay, date("Y") - 1);
        $lastEndTime   = mktime(0, 0, 0, $endMonth, $endDay, date("Y") - 1);
        
        $currentTime = time();
        $startTime = '';
        $endTime = '';
        $startAlongTime = mktime(0, 0, 0, $startMonth, $startDay, date("Y"));
        if ($currentTime >= $startAlongTime) {
            $startTime = $startAlongTime;
        }
        
        $endAlongTime = mktime(0, 0, 0, $endMonth, $endDay, date("Y"));
        if ($currentTime >= $endAlongTime) {
            $endTime = $endAlongTime;
        }
        elseif ($startTime) {
            $endTime = $currentTime;
        }
        $sql = "SELECT
                  DISTINCT member_id
                FROM
                  `sdb_ecorder_orders`
                WHERE
                  ((createtime >= {$lastLastStartTime} AND createtime < {$lastLastEndTime})
                OR
                  (createtime >= {$lastStartTime} AND createtime  < {$lastEndTime})
               ";
        if ($endTime) {
            $sql .= " OR (createtime >= {$startTime} AND createtime < {$endTime}))";
        }
        else {
            $sql .= " )";
        }
        $sql .= " AND shop_id = '{$shop_id}' ORDER BY payed DESC";
        
        $startPercent = 0;
        $endPercent = 0.3;
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //5月份    劳动节活动   对价格敏感的客户
    function season_sale_118($shop_id) {
        //每年4-28到5-5
        $startDay = 28;
        $startMonth = 4;
        $endDay = 5;
        $endMonth = 5;
        $lastLastStartTime  = mktime(0, 0, 0, $startMonth, $startDay, date("Y") - 2);
        $lastLastEndTime = mktime(0, 0, 0, $endMonth, $endDay, date("Y") - 2);
        
        $lastStartTime   = mktime(0, 0, 0, $startMonth, $startDay, date("Y") - 1);
        $lastEndTime   = mktime(0, 0, 0, $endMonth, $endDay, date("Y") - 1);
        
        $currentTime = time();
        $startTime = '';
        $endTime = '';
        $startAlongTime = mktime(0, 0, 0, $startMonth, $startDay, date("Y"));
        if ($currentTime >= $startAlongTime) {
            $startTime = $startAlongTime;
        }
        
        $endAlongTime = mktime(0, 0, 0, $endMonth, $endDay, date("Y"));
        if ($currentTime >= $endAlongTime) {
            $endTime = $endAlongTime;
        }
        elseif ($startTime) {
            $endTime = $currentTime;
        }
        $sql = "SELECT
                  DISTINCT member_id
                FROM
                  `sdb_ecorder_orders`
                WHERE
                  ((createtime >= {$lastLastStartTime} AND createtime < {$lastLastEndTime})
                OR
                  (createtime >= {$lastStartTime} AND createtime  < {$lastEndTime})
               ";
        if ($endTime) {
            $sql .= " OR (createtime >= {$startTime} AND createtime < {$endTime}))";
        }
        else {
            $sql .= " )";
        }
        $sql .= " AND shop_id = '{$shop_id}' ORDER BY payed DESC";
        
        $startPercent = 0.3;
        $endPercent = 1;
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //5月份    劳动节活动   消费能力强的客户
    function season_sale_119($shop_id) {
        //每年4-28到5-5
        $startDay = 28;
        $startMonth = 4;
        $endDay = 5;
        $endMonth = 5;
        $lastLastStartTime  = mktime(0, 0, 0, $startMonth, $startDay, date("Y") - 2);
        $lastLastEndTime = mktime(0, 0, 0, $endMonth, $endDay, date("Y") - 2);
        
        $lastStartTime   = mktime(0, 0, 0, $startMonth, $startDay, date("Y") - 1);
        $lastEndTime   = mktime(0, 0, 0, $endMonth, $endDay, date("Y") - 1);
        
        $currentTime = time();
        $startTime = '';
        $endTime = '';
        $startAlongTime = mktime(0, 0, 0, $startMonth, $startDay, date("Y"));
        if ($currentTime >= $startAlongTime) {
            $startTime = $startAlongTime;
        }
        
        $endAlongTime = mktime(0, 0, 0, $endMonth, $endDay, date("Y"));
        if ($currentTime >= $endAlongTime) {
            $endTime = $endAlongTime;
        }
        elseif ($startTime) {
            $endTime = $currentTime;
        }
        $sql = "SELECT
                  DISTINCT member_id
                FROM
                  `sdb_ecorder_orders`
                WHERE
                  ((createtime >= {$lastLastStartTime} AND createtime < {$lastLastEndTime})
                OR
                  (createtime >= {$lastStartTime} AND createtime  < {$lastEndTime})
               ";
        if ($endTime) {
            $sql .= " OR (createtime >= {$startTime} AND createtime < {$endTime}))";
        }
        else {
            $sql .= " )";
        }
        $sql .= " AND shop_id = '{$shop_id}' ORDER BY payed DESC";
        
        $startPercent = 0;
        $endPercent = 0.3;
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //5-6月份   端午节活动   对价格敏感的客户
    function season_sale_120($shop_id) {
        //每年端午节
    }
    
    //5-6月份   端午节活动   消费能力强的客户
    function season_sale_121($shop_id) {
        //每年端午节
    }
    
    //6月份    父亲节活动  对价格敏感的客户
    function season_sale_122($shop_id) {
        //每年父亲节
        $lastLastFathersDay = $this->getFathersDay(date("Y") - 2);
        $lastLastStartTime  = $lastLastFathersDay;
        $lastLastEndTime = $lastLastFathersDay + 86400;
        
        $lastFathersDay = $this->getFathersDay(date("Y") - 1);
        $lastStartTime   = $lastFathersDay;
        $lastEndTime   = $lastFathersDay + 86400;
        
        $currentTime = time();
        $fathersDay = $this->getFathersDay(date("Y"));
        $startTime = '';
        $endTime = '';
        $startAlongTime = $fathersDay;
        if ($currentTime >= $startAlongTime) {
            $startTime = $startAlongTime;
        }
        
        $endAlongTime = $fathersDay + 86400;
        if ($currentTime >= $endAlongTime) {
            $endTime = $endAlongTime;
        }
        elseif ($startTime) {
            $endTime = $currentTime;
        }
        $sql = "SELECT
                  DISTINCT member_id
                FROM
                  `sdb_ecorder_orders`
                WHERE
                  ((createtime >= {$lastLastStartTime} AND createtime < {$lastLastEndTime})
                OR
                  (createtime >= {$lastStartTime} AND createtime  < {$lastEndTime})
               ";
        if ($endTime) {
            $sql .= " OR (createtime >= {$startTime} AND createtime < {$endTime}))";
        }
        else {
            $sql .= " )";
        }
        $sql .= " AND shop_id = '{$shop_id}' ORDER BY payed DESC";
        
        $startPercent = 0.3;
        $endPercent = 1;
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //6月份    父亲节活动   消费能力强的客户
    function season_sale_123($shop_id) {
        //每年父亲节
        $lastLastFathersDay = $this->getFathersDay(date("Y") - 2);
        $lastLastStartTime  = $lastLastFathersDay;
        $lastLastEndTime = $lastLastFathersDay + 86400;
        
        $lastFathersDay = $this->getFathersDay(date("Y") - 1);
        $lastStartTime   = $lastFathersDay;
        $lastEndTime   = $lastFathersDay + 86400;
        
        $currentTime = time();
        $fathersDay = $this->getFathersDay(date("Y"));
        $startTime = '';
        $endTime = '';
        $startAlongTime = $fathersDay;
        if ($currentTime >= $startAlongTime) {
            $startTime = $startAlongTime;
        }
        
        $endAlongTime = $fathersDay + 86400;
        if ($currentTime >= $endAlongTime) {
            $endTime = $endAlongTime;
        }
        elseif ($startTime) {
            $endTime = $currentTime;
        }
        $sql = "SELECT
                  DISTINCT member_id
                FROM
                  `sdb_ecorder_orders`
                WHERE
                  ((createtime >= {$lastLastStartTime} AND createtime < {$lastLastEndTime})
                OR
                  (createtime >= {$lastStartTime} AND createtime  < {$lastEndTime})
               ";
        if ($endTime) {
            $sql .= " OR (createtime >= {$startTime} AND createtime < {$endTime}))";
        }
        else {
            $sql .= " )";
        }
        $sql .= " AND shop_id = '{$shop_id}' ORDER BY payed DESC";
        
        $startPercent = 0;
        $endPercent = 0.3;
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //7月份   七夕节活动  对价格敏感的客户
    function season_sale_124($shop_id) {
        //每年七夕节
    }
    
    //7月份   七夕节活动  消费能力强的客户
    function season_sale_125($shop_id) {
        //每年七夕节
    }
    
    //8月份   无
    function season_sale_126($shop_id) {
    }
    
    //8月份   无
    function season_sale_127($shop_id) {
    }
    
    //9月份     中秋节活动    对价格敏感的客户
    function season_sale_128($shop_id) {
        //每年中秋节
    }
    
    //9月份     中秋节活动    消费能力强的客户
    function season_sale_129($shop_id) {
        //每年中秋节
    }
    
    //10月份   国庆节活动    对价格敏感的客户
    function season_sale_130($shop_id) {
        //每年9-28到10-9
        $startDay = 28;
        $startMonth = 9;
        $endDay = 10;
        $endMonth = 10;
        $lastLastStartTime  = mktime(0, 0, 0, $startMonth, $startDay, date("Y") - 2);
        $lastLastEndTime = mktime(0, 0, 0, $endMonth, $endDay, date("Y") - 2);
        
        $lastStartTime   = mktime(0, 0, 0, $startMonth, $startDay, date("Y") - 1);
        $lastEndTime   = mktime(0, 0, 0, $endMonth, $endDay, date("Y") - 1);
        
        $currentTime = time();
        $startTime = '';
        $endTime = '';
        $startAlongTime = mktime(0, 0, 0, $startMonth, $startDay, date("Y"));
        if ($currentTime >= $startAlongTime) {
            $startTime = $startAlongTime;
        }
        
        $endAlongTime = mktime(0, 0, 0, $endMonth, $endDay, date("Y"));
        if ($currentTime >= $endAlongTime) {
            $endTime = $endAlongTime;
        }
        elseif ($startTime) {
            $endTime = $currentTime;
        }
        $sql = "SELECT
                  DISTINCT member_id
                FROM
                  `sdb_ecorder_orders`
                WHERE
                  ((createtime >= {$lastLastStartTime} AND createtime < {$lastLastEndTime})
                OR
                  (createtime >= {$lastStartTime} AND createtime  < {$lastEndTime})
               ";
        if ($endTime) {
            $sql .= " OR (createtime >= {$startTime} AND createtime < {$endTime}))";
        }
        else {
            $sql .= " )";
        }
        $sql .= " AND shop_id = '{$shop_id}' ORDER BY payed DESC";
        
        $startPercent = 0.3;
        $endPercent = 1;
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //10月份   国庆节活动    消费能力强的客户
    function season_sale_131($shop_id) {
        //每年9-28到10-9
        $startDay = 28;
        $startMonth = 9;
        $endDay = 10;
        $endMonth = 10;
        $lastLastStartTime  = mktime(0, 0, 0, $startMonth, $startDay, date("Y") - 2);
        $lastLastEndTime = mktime(0, 0, 0, $endMonth, $endDay, date("Y") - 2);
        
        $lastStartTime   = mktime(0, 0, 0, $startMonth, $startDay, date("Y") - 1);
        $lastEndTime   = mktime(0, 0, 0, $endMonth, $endDay, date("Y") - 1);
        
        $currentTime = time();
        $startTime = '';
        $endTime = '';
        $startAlongTime = mktime(0, 0, 0, $startMonth, $startDay, date("Y"));
        if ($currentTime >= $startAlongTime) {
            $startTime = $startAlongTime;
        }
        
        $endAlongTime = mktime(0, 0, 0, $endMonth, $endDay, date("Y"));
        if ($currentTime >= $endAlongTime) {
            $endTime = $endAlongTime;
        }
        elseif ($startTime) {
            $endTime = $currentTime;
        }
        $sql = "SELECT
                  DISTINCT member_id
                FROM
                  `sdb_ecorder_orders`
                WHERE
                  ((createtime >= {$lastLastStartTime} AND createtime < {$lastLastEndTime})
                OR
                  (createtime >= {$lastStartTime} AND createtime  < {$lastEndTime})
               ";
        if ($endTime) {
            $sql .= " OR (createtime >= {$startTime} AND createtime < {$endTime}))";
        }
        else {
            $sql .= " )";
        }
        $sql .= " AND shop_id = '{$shop_id}' ORDER BY payed DESC";
        
        $startPercent = 0;
        $endPercent = 0.3;
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //11月份   双11活动     对价格敏感的客户
    function season_sale_132($shop_id) {
        //每年11-10到11-13
        $startDay = 10;
        $startMonth = 10;
        $endDay = 14;
        $endMonth = 10;
        $lastLastStartTime  = mktime(0, 0, 0, $startMonth, $startDay, date("Y") - 2);
        $lastLastEndTime = mktime(0, 0, 0, $endMonth, $endDay, date("Y") - 2);
        
        $lastStartTime   = mktime(0, 0, 0, $startMonth, $startDay, date("Y") - 1);
        $lastEndTime   = mktime(0, 0, 0, $endMonth, $endDay, date("Y") - 1);
        
        $currentTime = time();
        $startTime = '';
        $endTime = '';
        $startAlongTime = mktime(0, 0, 0, $startMonth, $startDay, date("Y"));
        if ($currentTime >= $startAlongTime) {
            $startTime = $startAlongTime;
        }
        
        $endAlongTime = mktime(0, 0, 0, $endMonth, $endDay, date("Y"));
        if ($currentTime >= $endAlongTime) {
            $endTime = $endAlongTime;
        }
        elseif ($startTime) {
            $endTime = $currentTime;
        }
        $sql = "SELECT
                  DISTINCT member_id
                FROM
                  `sdb_ecorder_orders`
                WHERE
                  ((createtime >= {$lastLastStartTime} AND createtime < {$lastLastEndTime})
                OR
                  (createtime >= {$lastStartTime} AND createtime  < {$lastEndTime})
               ";
        if ($endTime) {
            $sql .= " OR (createtime >= {$startTime} AND createtime < {$endTime}))";
        }
        else {
            $sql .= " )";
        }
        $sql .= " AND shop_id = '{$shop_id}' ORDER BY payed DESC";
        
        $startPercent = 0.3;
        $endPercent = 1;
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //11月份   双11活动     对价格敏感的客户
    function season_sale_133($shop_id) {
        //每年11-10到11-13
        $startDay = 10;
        $startMonth = 10;
        $endDay = 14;
        $endMonth = 10;
        $lastLastStartTime  = mktime(0, 0, 0, $startMonth, $startDay, date("Y") - 2);
        $lastLastEndTime = mktime(0, 0, 0, $endMonth, $endDay, date("Y") - 2);
        
        $lastStartTime   = mktime(0, 0, 0, $startMonth, $startDay, date("Y") - 1);
        $lastEndTime   = mktime(0, 0, 0, $endMonth, $endDay, date("Y") - 1);
        
        $currentTime = time();
        $startTime = '';
        $endTime = '';
        $startAlongTime = mktime(0, 0, 0, $startMonth, $startDay, date("Y"));
        if ($currentTime >= $startAlongTime) {
            $startTime = $startAlongTime;
        }
        
        $endAlongTime = mktime(0, 0, 0, $endMonth, $endDay, date("Y"));
        if ($currentTime >= $endAlongTime) {
            $endTime = $endAlongTime;
        }
        elseif ($startTime) {
            $endTime = $currentTime;
        }
        $sql = "SELECT
                  DISTINCT member_id
                FROM
                  `sdb_ecorder_orders`
                WHERE
                  ((createtime >= {$lastLastStartTime} AND createtime < {$lastLastEndTime})
                OR
                  (createtime >= {$lastStartTime} AND createtime  < {$lastEndTime})
               ";
        if ($endTime) {
            $sql .= " OR (createtime >= {$startTime} AND createtime < {$endTime}))";
        }
        else {
            $sql .= " )";
        }
        $sql .= " AND shop_id = '{$shop_id}' ORDER BY payed DESC";
        
        $startPercent = 0;
        $endPercent = 0.3;
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //12月份   双12活动       对价格敏感的客户
    function season_sale_134($shop_id) {
        //每年12-11到12-14
        $startDay = 11;
        $startMonth = 12;
        $endDay = 15;
        $endMonth = 12;
        $lastLastStartTime  = mktime(0, 0, 0, $startMonth, $startDay, date("Y") - 2);
        $lastLastEndTime = mktime(0, 0, 0, $endMonth, $endDay, date("Y") - 2);
        
        $lastStartTime   = mktime(0, 0, 0, $startMonth, $startDay, date("Y") - 1);
        $lastEndTime   = mktime(0, 0, 0, $endMonth, $endDay, date("Y") - 1);
        
        $currentTime = time();
        $startTime = '';
        $endTime = '';
        $startAlongTime = mktime(0, 0, 0, $startMonth, $startDay, date("Y"));
        if ($currentTime >= $startAlongTime) {
            $startTime = $startAlongTime;
        }
        
        $endAlongTime = mktime(0, 0, 0, $endMonth, $endDay, date("Y"));
        if ($currentTime >= $endAlongTime) {
            $endTime = $endAlongTime;
        }
        elseif ($startTime) {
            $endTime = $currentTime;
        }
        $sql = "SELECT
                  DISTINCT member_id
                FROM
                  `sdb_ecorder_orders`
                WHERE
                  ((createtime >= {$lastLastStartTime} AND createtime < {$lastLastEndTime})
                OR
                  (createtime >= {$lastStartTime} AND createtime  < {$lastEndTime})
               ";
        if ($endTime) {
            $sql .= " OR (createtime >= {$startTime} AND createtime < {$endTime}))";
        }
        else {
            $sql .= " )";
        }
        $sql .= " AND shop_id = '{$shop_id}' ORDER BY payed DESC";
        
        $startPercent = 0.3;
        $endPercent = 1;
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //12月份   双12活动       对价格敏感的客户
    function season_sale_135($shop_id) {
        //每年12-11到12-14
        $startDay = 11;
        $startMonth = 12;
        $endDay = 15;
        $endMonth = 12;
        $lastLastStartTime  = mktime(0, 0, 0, $startMonth, $startDay, date("Y") - 2);
        $lastLastEndTime = mktime(0, 0, 0, $endMonth, $endDay, date("Y") - 2);
        
        $lastStartTime   = mktime(0, 0, 0, $startMonth, $startDay, date("Y") - 1);
        $lastEndTime   = mktime(0, 0, 0, $endMonth, $endDay, date("Y") - 1);
        
        $currentTime = time();
        $startTime = '';
        $endTime = '';
        $startAlongTime = mktime(0, 0, 0, $startMonth, $startDay, date("Y"));
        if ($currentTime >= $startAlongTime) {
            $startTime = $startAlongTime;
        }
        
        $endAlongTime = mktime(0, 0, 0, $endMonth, $endDay, date("Y"));
        if ($currentTime >= $endAlongTime) {
            $endTime = $endAlongTime;
        }
        elseif ($startTime) {
            $endTime = $currentTime;
        }
        $sql = "SELECT
                  DISTINCT member_id
                FROM
                  `sdb_ecorder_orders`
                WHERE
                  ((createtime >= {$lastLastStartTime} AND createtime < {$lastLastEndTime})
                OR
                  (createtime >= {$lastStartTime} AND createtime  < {$lastEndTime})
               ";
        if ($endTime) {
            $sql .= " OR (createtime >= {$startTime} AND createtime < {$endTime}))";
        }
        else {
            $sql .= " )";
        }
        $sql .= " AND shop_id = '{$shop_id}' ORDER BY payed DESC";
        
        $startPercent = 0;
        $endPercent = 0.3;
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //12月份   圣诞节活动  对价格敏感的客户
    function season_sale_136($shop_id) {
        //每年12-21到12-27
        $startDay = 21;
        $startMonth = 12;
        $endDay = 28;
        $endMonth = 12;
        $lastLastStartTime  = mktime(0, 0, 0, $startMonth, $startDay, date("Y") - 2);
        $lastLastEndTime = mktime(0, 0, 0, $endMonth, $endDay, date("Y") - 2);
        
        $lastStartTime   = mktime(0, 0, 0, $startMonth, $startDay, date("Y") - 1);
        $lastEndTime   = mktime(0, 0, 0, $endMonth, $endDay, date("Y") - 1);
        
        $currentTime = time();
        $startTime = '';
        $endTime = '';
        $startAlongTime = mktime(0, 0, 0, $startMonth, $startDay, date("Y"));
        if ($currentTime >= $startAlongTime) {
            $startTime = $startAlongTime;
        }
        
        $endAlongTime = mktime(0, 0, 0, $endMonth, $endDay, date("Y"));
        if ($currentTime >= $endAlongTime) {
            $endTime = $endAlongTime;
        }
        elseif ($startTime) {
            $endTime = $currentTime;
        }
        $sql = "SELECT
                  DISTINCT member_id
                FROM
                  `sdb_ecorder_orders`
                WHERE
                  ((createtime >= {$lastLastStartTime} AND createtime < {$lastLastEndTime})
                OR
                  (createtime >= {$lastStartTime} AND createtime  < {$lastEndTime})
               ";
        if ($endTime) {
            $sql .= " OR (createtime >= {$startTime} AND createtime < {$endTime}))";
        }
        else {
            $sql .= " )";
        }
        $sql .= " AND shop_id = '{$shop_id}' ORDER BY payed DESC";
        
        $startPercent = 0.3;
        $endPercent = 1;
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
    
    //12月份   圣诞节活动  消费能力强的客户
    function season_sale_137($shop_id) {
        //每年12-21到12-27
        $startDay = 21;
        $startMonth = 12;
        $endDay = 28;
        $endMonth = 12;
        $lastLastStartTime  = mktime(0, 0, 0, $startMonth, $startDay, date("Y") - 2);
        $lastLastEndTime = mktime(0, 0, 0, $endMonth, $endDay, date("Y") - 2);
        
        $lastStartTime   = mktime(0, 0, 0, $startMonth, $startDay, date("Y") - 1);
        $lastEndTime   = mktime(0, 0, 0, $endMonth, $endDay, date("Y") - 1);
        
        $currentTime = time();
        $startTime = '';
        $endTime = '';
        $startAlongTime = mktime(0, 0, 0, $startMonth, $startDay, date("Y"));
        if ($currentTime >= $startAlongTime) {
            $startTime = $startAlongTime;
        }
        
        $endAlongTime = mktime(0, 0, 0, $endMonth, $endDay, date("Y"));
        if ($currentTime >= $endAlongTime) {
            $endTime = $endAlongTime;
        }
        elseif ($startTime) {
            $endTime = $currentTime;
        }
        $sql = "SELECT
                  DISTINCT member_id
                FROM
                  `sdb_ecorder_orders`
                WHERE
                  ((createtime >= {$lastLastStartTime} AND createtime < {$lastLastEndTime})
                OR
                  (createtime >= {$lastStartTime} AND createtime  < {$lastEndTime})
               ";
        if ($endTime) {
            $sql .= " OR (createtime >= {$startTime} AND createtime < {$endTime}))";
        }
        else {
            $sql .= " )";
        }
        $sql .= " AND shop_id = '{$shop_id}' ORDER BY payed DESC";
        
        $startPercent = 0;
        $endPercent = 0.3;
        return $this->callPercentSqlData($sql, $startPercent, $endPercent);
    }
}
