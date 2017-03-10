<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class taocrm_mdl_analysis_logs extends dbeav_model {
    var $am_map_setting = array(
        '安徽' => 'CN_34',
        '浙江' => 'CN_33',
        '江西' => 'CN_36',
        '江苏' => 'CN_32',
        '吉林' => 'CN_22',
        '青海' => 'CN_63',
        '福建' => 'CN_35',
        '黑龙江' => 'CN_23',
        '河南' => 'CN_41',
        '河北' => 'CN_13',
        '湖南' => 'CN_43',
        '湖北' => 'CN_42',
        '新疆' => 'CN_65',
        '西藏' => 'CN_54',
        '甘肃' => 'CN_62',
        '广西' => 'CN_45',
        '贵州' => 'CN_52',
        '辽宁' => 'CN_21',
        '内蒙古' => 'CN_15',
        '宁夏' => 'CN_64',
        '北京' => 'CN_11',
        '上海' => 'CN_31',
        '陕西' => 'CN_61',
        '山东' => 'CN_37',
        '山西' => 'CN_14',
        '天津' => 'CN_12',
        '云南' => 'CN_53',
        '广东' => 'CN_44',
        '海南' => 'CN_46',
        '四川' => 'CN_51',
        '重庆' => 'CN_50',
        '香港' => 'CN_91',
        '澳门' => 'CN_92',
        '台湾' => 'TW'
    );

    /**
     * 获取销售统计
     *
     * @param array $args 查询条件参数
     * @return $data 查询结果
     */
    public function get_sales_data($args = null) {

        /**
         * 状态
         *  1 : 已支付的订单金额
         *  2 : 未支付的订单金额
         *  3 : 成功定单销售金额
         *  4 : 取消金额
         *  5 : 定单总量
         *  6 : 成功订单量
         *  7 : 未支付订单量
         *  8 : 取消订单量
         *  默认 : 订单总额
         */

        $where = ' WHERE 1 ';
        $tables = ' sdb_ecorder_orders  AS o ';
        $cols = array();

        if($args['start_time']){
            $where .= ' AND  '. strtotime($args['start_time']) .' <= o.createtime' ;
        }
        if($args['end_time']){
            $where .= '  AND o.createtime < '. (strtotime($args['end_time']) + 86400);
        }
        
        //客户销售
        if($args['member_buy'] == true) {
            $cols[] = 'm.shop_lv_id';
            $cols[] = 'o.shop_id';
            $tables .= ' , sdb_taocrm_members AS m ';
            $where  .= ' AND o.member_id  = m.member_id ';
            $order .= ' GROUP BY m.member_id ';
        }

        //商店客户等级销售
        if($args['shop_member_buy'] == true) {
            $cols[] = 'm.shop_lv_id';
            $cols[] = 'o.shop_id';
            $tables .= ' , sdb_taocrm_members AS m ';
            $where  .= ' AND o.member_id  = m.member_id ';
            $order .= ' GROUP BY m.shop_lv_id ';
        }
       

        //客户等级销售
        if($args['member'] == true) {
            $tables .= ' , sdb_taocrm_members AS m ';
            $where  .= ' AND o.member_id  = m.member_id ';
            $order .= ' GROUP BY m.shop_lv_id ';
        }

        //客户销售
        if($args['shop_lv_id']) {
            $tables .= ' , sdb_taocrm_members AS m ';
            $where  .= ' AND o.member_id  = m.member_id ';
            $where  .= ' AND m.shop_lv_id  = ' .$args['shop_lv_id'];
        }

        if($args['area']) {
            $where .= " AND o.shop_id = '".$args['shop_id']." ' ";
        }

        if($args['ship_area']) {
            $where .= " AND o.ship_area like 'mainland:".$args['ship_area']."%' ";
        }

        //店铺
        if($args['shop_id']) {
            $where .= " AND o.shop_id = '".$args['shop_id']." ' ";
        }

        $filter = array();

        switch ($args['type']) {
            case 1:
                $where .= '  AND o.pay_status = 1 ';
                $cols[] = 'SUM(total_amount) AS total';
                break;
            case 2:
                $where .= '  AND o.pay_status = 0 ';
                $cols[] = 'SUM(total_amount) AS total';
                break;
            case 3:
                $where .= ' AND o.status = "finish" ';
                $cols[] = 'SUM(total_amount) AS total';
                break;
            case 4:
                $where .= ' AND o.status = "dead" ';
                $cols[] = 'SUM(total_amount) AS total';
                break;
            case 5:
                $cols[] = 'COUNT(*)  AS count';
                break;
            case 6:
                $where .= ' AND o.status = "finish" ';
                $cols[] = 'COUNT(*) AS count';
                break;
            case 7:
                $where .= ' AND o.pay_status = 0 ';
                $cols[] = 'COUNT(*) AS count';
                break;
            case 8:
                $where .= ' AND o.status = "dead" ';
                $cols[] = 'COUNT(*) AS count';
                break;
            default:
                $cols[] = 'SUM(total_amount) AS total ';
                break;
        }

        $col = implode(",", $cols);
        $sql = "SELECT $col  FROM $tables $where $order ";
        $data = $this->db->select($sql);

        return count($data) > 1?$data:$data[0];
    }

    public function get_sales_count($args = null){
        $data =  $this->get_sales_data($args);
        return count($data) == 1 ? array_pop($data) : $data[0][0];
    }

    public function get_all_sales_data($args = null) {
        //定单总额
        $data['order_salse_total'] = $this->get_sales_data($args);
        //已经支付的定单总额
        $args['type'] = 1;
        $data['order_pay_total'] = $this->get_sales_data($args);
        //未支付的定单总额
        $args['type'] = 2;
        $data['order_nopay_total'] = $this->get_sales_data($args);
        //成功定单总额
        $args['type'] = 3;
        $data['order_finish_total'] = $this->get_sales_data($args);
        //未成功定单总额
        $args['type'] = 4;
        $data['order_dead_total'] = $this->get_sales_data($args);
        //定单总量
        $args['type'] = 5;
        $data['order_count'] = $this->get_sales_data($args);
        //成功定单总量
        $args['type'] = 6;
        $data['order_finish_count'] = $this->get_sales_data($args);
        //未支付定单总量
        $args['type'] = 7;
        $data['order_nopay_count'] = $this->get_sales_data($args);
        //取消定单量
        $args['type'] = 8;
        $data['order_dead_count'] = $this->get_sales_data($args);
        return $data;
    }

    //按级别获取客户
    public function get_member($args = null) {
        $where = ' WHERE 1 ';

        if($args['area']) {
            $where .= ' AND area like "mainland:'.$args['area'].'%" ';
        }

        if($args['shop_id']) {
            $where .= " AND shop_id = '".$args['shop_id']." ' ";
        }

        if($args['start_time']){
            $where .= ' AND '. strtotime($args['start_time']) .' <= regtime ';
        }
        if($args['end_time']){
            $where .= '  AND regtime < '. (strtotime($args['end_time']) + 86400);
        }
        
        $sql = "SELECT COUNT(*) as count,shop_lv_id FROM sdb_taocrm_members $where GROUP BY shop_lv_id ";

        $tmp = $this->db->select($sql) ;

        $shopLvObj = &$this->app->model('shop_lv');
        $shopLv = $this->pagedata['shop_level'] = $shopLvObj->getList('shop_lv_id,name',array('shop_id'=>$args['shop_id']));

        foreach ($tmp as $v) {
            $data[$v['shop_lv_id']] = $v['count'];
        }

        foreach($shopLv as $v) {
            $data2[$v['name']]['count'] = $data[$v['shop_lv_id']]?$data[$v['shop_lv_id']]:0;
            $data2[$v['name']]['name'] = $v['name'];
        }

        return $data2;
    }

    //按级别获取客户
    /*public function get_member($args = null) {
        $where = ' WHERE 1 ';

        if($args['area']) {
            $where .= ' AND area like "mainland:'.$args['area'].'%" ';
        }

        if($args['shop_id']) {
            $where .= " AND shop_id = '".$args['shop_id']." ' ";
        }

        if($args['start_time']){
            $where .= ' AND   '. strtotime($args['start_time']) .' < regtime ';
        }
        if($args['end_time']){
            $where .= '  AND regtime > '. strtotime($args['end_time']) + 86400;
        }

        $sql = "SELECT COUNT(*) as count,member_lv_id  FROM sdb_taocrm_members $where GROUP BY member_lv_id ";

        $tmp = $this->db->select($sql) ;

        $memberLvObj = &$this->app->model('member_lv');
        $memberLv = $this->pagedata['member_level'] = $memberLvObj->getList('member_lv_id,name');

        foreach ($tmp as $v) {
            $data[$v['member_lv_id']] = $v['count'];
        }

        foreach($memberLv as $v) {
            $data2[$v['name']]['count'] = $data[$v['member_lv_id']]?$data[$v['member_lv_id']]:0;
            $data2[$v['name']]['name'] = $v['name'];
        }

        return $data2;
    }*/

    //获取客户数量 
    public function get_member_count($args) {
        $where .= 'WHERE 1';
        
        if($args['start_time']){
        $where .= ' AND  '. strtotime($args['start_time']) .' < regtime' ;
        }
        if($args['end_time']){
            $where .= '  AND regtime > '. (strtotime($args['end_time']) + 86400);
        }
        
        if($args['area']){
            $where .= ' AND area like "mainland:'.$args['area'].'%"';
        }
        
        $sql = "SELECT COUNT(*) as count  FROM sdb_taocrm_members $where  ";
        $data = $this->db->select($sql) ;
        return $data;
    }
    
    //按商店获取客户
    public function get_member_by_shop($args) {
        $where = 'WHERE 1';
        
        if($args['start_time']){
        $where .= '  AND  regtime >= '. strtotime($args['start_time'])  ;
        }
        if($args['end_time']){
            $where .= '  AND regtime < '. (strtotime($args['end_time']) + 86400);
        }

        if($args['area']){
            $where .= ' AND area like "mainland:'.$args['area'].'%"';
        }
        
        $sql = "SELECT COUNT(*) as count,shop_id FROM sdb_taocrm_members $where GROUP BY shop_id ";

        $tmp = $this->db->select($sql) ;

        $shopObj = &app::get(ORDER_APP)->model('shop');
        $shopList = $shopObj->getList('shop_id,name');

        foreach ($tmp as $v) {
            $data[$v['shop_id']] = $v['count'];
        }

        foreach($shopList as $v) {
            $data2[$v['name']]['count'] = $data[$v['shop_id']]?$data[$v['shop_id']]:0;
            $data2[$v['name']]['name'] = $v['name'];
            $data2[$v['name']]['shop_id'] = $v['shop_id'];
        }
        return $data2;
    }

    //获取每个客户购买单价
    public function get_member_avg($args = null) {
	    $where = '  WHERE o.member_id  = m.member_id ';
	    if($args['start_time']){
	        $where .= '  AND  regtime >= '. strtotime($args['start_time'])  ;
	    }
	    if($args['end_time']){
	        $where .= '  AND regtime < '. (strtotime($args['end_time']) + 86400);
	    }
	    if($args['shop_id']) {
	        $where .= " AND m.shop_id = '".$args['shop_id']." ' ";
	    }
	    
	    $sql = 'SELECT AVG(o.total_amount) AS AVG, m.shop_id '
			 . 'FROM sdb_ecorder_orders AS o, sdb_taocrm_members AS m '
			 . $where
			 . ' GROUP BY m.shop_id';

	    $data = $this->db->select($sql) ;
	    return $data;
    }
    
    //获取每个客户等级购买单价
    public function get_member_lv_avg($args = null) {
	    $where = '  WHERE  o.member_id  = m.member_id ';
	    if($args['start_time']){
	        $where .= '  AND  regtime >= '. strtotime($args['start_time'])  ;
	    }
	    if($args['end_time']){
	        $where .= '  AND regtime < '. (strtotime($args['end_time']) + 86400);
	    }
	    if($args['shop_id']) {
	        $where .= " AND m.shop_id = '".$args['shop_id']." ' ";
	    }
	
	    $sql = '  SELECT ( sum( o.total_amount ) / count( * )) AS avg,
	                                    count( * ) AS count,
	                                    sum( o.total_amount ) AS sum,
	                                    m.shop_lv_id
	                                FROM sdb_ecorder_orders as o, sdb_taocrm_members AS m
	                               '. $where .'
	                                GROUP BY o.member_id';
//	   	echo $sql . ";<br>";
	    $data = $this->db->select($sql) ;
	    return $data;
    }    

    //新增客户平均购买单价和数量 
    public function get_member_total_avg($args){

        $where = '  WHERE 1 ';
        if($args['start_time']){
            $where .= ' AND  '. strtotime($args['start_time']) .' <= o.createtime' ;
        }
        if($args['end_time']){
            $where .= '  AND o.createtime < '. (strtotime($args['end_time']) + 86400);
        }

        $sql = 'SELECT  AVG(total_amount) AS price,  AVG(itemnum) as count , o.shop_id
                    FROM sdb_ecorder_orders as o ' .$where. '
                    GROUP BY o.shop_id ' ;
        
        $data = $this->db->select($sql) ;

        $shopObj = &app::get(ORDER_APP)->model('shop');
        $shopList = $shopObj->getList('shop_id,name');

        

        foreach($shopList as $key=>$value) {
            if($data){
                foreach($data as $k=>$v){
                    if(in_array($value['shop_id'],$v)){
                        $in = true;
                        $tmp[$value['shop_id']] = $v;
                        $tmp[$value['shop_id']]['name'] = $value['name'];
                    }
                }
            }
            if(!$in){
                $tmp[$value['shop_id']]['name'] = $value['name'];
                $tmp[$value['shop_id']]['count'] = 0;
                $tmp[$value['shop_id']]['price'] = 0;
                $tmp[$value['shop_id']]['shop_id'] = $value['shop_id'];
            }
                
            $in = false;
        }
        
        return $tmp;
    }

    public function getCouponOrder($filter){
        $where = ' WHERE 1 ';
        if($filter['member_id']){
            $where .= ' AND member_id IN ('.implode(',', $filter['member_id']).')';
        }

        $sql = 'SELECT sum(total_amount) AS amount, count(*) as count FROM sdb_ecorder_orders where ' . $where;
        
        $data = $this->db->select($sql);
        return $data[0];
    }

    public function getMapData($maptype,$filter){
        $memberLvObj = &$this->app->model('member_lv');
        $this->pagedata['member_level'] = $memberLvObj->getList('member_lv_id,name');

        foreach($this->am_map_setting as $key=>$value) {
            $args['type'] = $filter['data_type'];
            $args['ship_area'] = $key;
            $data = $this->get_sales_data($args);

            $map_data[] = array(
                'title'=> $key,
                'code' => $value,
                'value' => $data['total']?$data['total']:$data['count'],
                'description' => $data['total']?$data['total']:$data['count']
            );
        }
        return $this->craeteMapData($map_data);
    }

    public function craeteMapData($data){
       $str = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
       $str .= '<map map_file="maps/china.swf" tl_long="73.620045" tl_lat="53.553745" br_long="134.768463" br_lat="18.168882" zoom="95%" zoom_x="3.63%" zoom_y="2.68%">'."\n";
       $str .= "<areas>\n";
       $movie_str = '';
       foreach($data as $v){
           if($v['title']=='香港'){
                $movie_str .= '<movie title="'.$v['title'].'" file="circle" color="#000000" width="5" height="5" long="114.153542" lat="22.411249" fixed_size="true"><description><![CDATA['.$v['description'].']]></description></movie>'."\n";
           }elseif($v['title']=='澳门'){
                $movie_str .= '<movie title="'.$v['title'].'" file="circle" color="#000000" width="5" height="5" long="113.5502" lat="22.1636915147872" fixed_size="true"><description><![CDATA['.$v['description'].']]></description></movie>'."\n";
           }else{
                $str .= '<area mc_name="'.$v['code'].'" title="'.$v['title'].'" value="'.$v['value'].'"><description><![CDATA['.$v['description'].']]></description></area>'."\n";
           }
       }
       $str .= '<area mc_name="borders" title="borders" color="#FFFFFF" balloon="false"></area>'."\n";
       $str .= '</areas>'."\n";
       $str .= '<movies>'."\n";
       $str .= $movie_str;
       $str .= '</movies>'."\n";
       $str .= '</map>';
       return $str;
   }
}