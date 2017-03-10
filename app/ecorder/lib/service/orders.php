<?php
class ecorder_service_orders {

    /**
     * 对应店铺信息
     * @var Array
     */
    protected $_shopInfo = array();

    function __construct(){
        $this->app = app::get('ecorder');
    }

    /**
     *
     * 保存关联商品
     *
     */
    public function countRelateProducts() {

        $sync_orders_relate_starttime = app::get(ORDER_APP)->getConf('sync_orders_relate_lasttime');
        $sync_orders_relate_starttime= $sync_orders_relate_starttime ? $sync_orders_relate_starttime : 0;
        $sync_orders_relate_endtime = time();

        $sql = 'select order_id,member_id
            from sdb_ecorder_orders 
            where pay_time >='.$sync_orders_relate_starttime .' 
            and pay_time <'.$sync_orders_relate_endtime .' 
            and pay_status="1"';
        $rows = kernel::database()->select($sql);
        //var_dump($rows);
        foreach($rows as $row){
            $ids = array();
            $orderItemList = kernel::database()->select('select product_id,goods_id,name
            from sdb_ecorder_order_items 
            where order_id ='.$row['order_id']);
            foreach ($orderItemList as $item) {
                if(empty($item['goods_id']) || empty($item['product_id'])){
                    continue;
                }

                if(!in_array($item['goods_id'],$ids)){
                    $ids[] = $item['goods_id'];
                }

                //统计客户购买商品次数
                $sdf = array('member_id' => $row['member_id'],
                    'product_id' => $item['product_id'],
                    'goods_id' => $item['goods_id'],
                    'name' => $item['name'],
                );
                $this->countMemberProducts($sdf);
            }
            //关联商品
            $this->relateProducts($ids);
        }

        app::get(ORDER_APP)->setConf('sync_orders_relate_lasttime',$sync_orders_relate_endtime);
         
    }

    /**
     * @Param 时间戳 $date
     * 类型 $type :modified|created
     */
    public function countBuys($shop_id='',$date='',$type='modified') {

        set_time_limit(60*10);

        $start_time = 0;
        $end_time = 0;
        if(!$date){
            $date = time();
        }

        $start_time = strtotime(date('Y-m-d 00:00:00',$date));
        $end_time = strtotime(date('Y-m-d 23:59:59',$date));

        if($shop_id) $where = " AND shop_id='$shop_id' ";

        $shopToChannel = array();
        $members = array();
        $shop_ids = array();

        //客户统计数据
        $pageSzie = 5000;//分页处理，每次5000
        $page = 0;
        while(true){
            if($type == 'modified'){
                $sql = 'SELECT member_id,shop_id  FROM `sdb_ecorder_orders` WHERE  f_modified >='. $start_time .' and f_modified <= '. $end_time .' '.$where.' LIMIT '. ($pageSzie * $page) .' , '.$pageSzie;
            }else{
                $sql = 'SELECT member_id,shop_id  FROM `sdb_ecorder_orders` WHERE  createtime >='. $start_time .' and createtime <= '. $end_time .' '.$where.' LIMIT '. ($pageSzie * $page) .' , '.$pageSzie;
            }
            
            $memberList= kernel::database()->select($sql);
            if(!$memberList) break;

            foreach($memberList as $v){
                $members[$v['shop_id'].'###'.$v['member_id']] = $v;
                $shop_ids[$v['shop_id']] = $v['shop_id'];
            }
            unset($memberList);
            $page++;
             
            if($members) {
                $execTime = time();
                foreach($members as $v){
                    $member_id = $v['member_id'];
                    $shop_id = $v['shop_id'];
                    kernel::single('taocrm_service_member')->countMemberBuys($member_id,$shop_id);

                    $curTime = time();
                    if($curTime >= $execTime + 25 ){
                        kernel::database()->dbclose();
                        $execTime = $curTime;
                    }
                }
                unset($members);
            }
        }

        //店铺统计数据
        if($shop_ids) {
            foreach($shop_ids as $v) {
                kernel::single('taocrm_service_shop')->countShopBuys($v);
                $shopInfo = $this->fetchShopInfo($v);
                if( !in_array($shopInfo['channel_id'], $shopToChannel) ){
                    $shopToChannel[] = $shopInfo['channel_id'];
                }
            }

            foreach($shopToChannel as $channel_id){
                kernel::single('taocrm_service_channel')->countChannelBuys($channel_id);
            }
        }

        return true;
    }

    protected function fetchShopInfo($shopId) {
        return app::get('ecorder')->model('shop')->dump(array('shop_id' => $shopId), '*');
    }

    protected function countMemberProducts($sdf){
        $curTime = time();
        $memberProductObj = app::get(ORDER_APP)->model('member_products');
        $row = kernel::database()->selectrow('select mp_id from sdb_ecorder_member_products where member_id = '.$sdf['member_id'].' and product_id = '.$sdf['product_id'].' ');
        if($row){
            kernel::database()->exec('update sdb_ecorder_member_products set buy_times=buy_times+1,last_time='.$curTime.' where mp_id = '.$row['mp_id']);
        }else{
            $sdf['buy_times'] = 1;
            $sdf['last_time'] = $curTime;
            $memberProductObj->save($sdf);
        }
    }

    protected function relateProducts($ids){
        if(!is_array($ids) || count($ids) < 2){
            return false;
        }

        asort($ids);
        if($ids){
            $curTime = time();
            $relateProductObj = app::get(ORDER_APP)->model('relate_products');
            $idsCount = count($ids);
            for($i = 0;$i < $idsCount;$i++){
                for($j = $i+1;$j < $idsCount;$j++){
                    $row = kernel::database()->selectrow('select relate_id from sdb_ecorder_relate_products where goods_a = "'.$ids[$i].'" and goods_b = "'.$ids[$j].'" ');
                    if($row){
                        kernel::database()->exec('update sdb_ecorder_relate_products set times=times+1,update_time='.$curTime.' where relate_id = '.$row['relate_id']);
                    }else{
                        $sdf = array('goods_a'=>$ids[$i],'goods_b'=>$ids[$j],'times'=>1,'create_time'=>$curTime,'update_time'=>$curTime);
                        $relateProductObj->save($sdf);
                    }
                }
            }
        }
    }

    public function get_orders_by_params_for_db($params)
    {
        $orders_mod = app::get('ecorder')->model('orders');
        !empty($params['start_time']) && $where['createtime|bthan'] = strtotime($params['start_time']);
        !empty($params['end_time']) && $where['createtime|sthan'] = strtotime($params['end_time']);
        $where = array_merge($where,$params['params']);
        $list = $orders_mod->getList('*',$where);
        return $list;
    }


    public function get_orders_by_params($params)
    {
        switch($params['type'])
        {
            case 'taobao':
                return kernel::single('ecorder_rpc_request_taobao_orders')->get_orders_by_params($params);
            break;
            case 'all':
                return  $this->get_orders_by_params_for_db($params);
            break;
        } 
    }

    /*订单统计
    1.更新全局表（db_taocrm_members）
    2.更新会员统计表（db_taocrm_member_analysis）
    */
    public function statistics_orders($member_id=0, $shop_id='')
    {
        if(!$member_id or !$shop_id){
            return array('re'=>false, 'err_msg'=>'客户ID和店铺ID不能为空');
        }
        
        $result = array();
        $db = kernel::database();
        
        //全局会员表
        $analysis = array(
            'order_total_num' => 0,
            'order_total_amount' => 0,
            'order_succ_num' => 0,
            'order_succ_amount' => 0,
            'order_first_time' => 0,
            'order_last_time' => 0,
            'member_id' => $member_id,
            //'shop_id' =>$shop_id,
            'update_time' => time()
        );
        
        //店铺会员表
        $analysis_shop = array(
            'total_orders' => 0,
            'total_amount' => 0,
            'total_per_amount' => 0,
            'refund_orders' => 0,
            'refund_amount' => 0,
            'finish_orders' => 0,
            'finish_total_amount' => 0,
            'finish_per_amount' => 0,
            'unpay_orders' => 0,
            'unpay_amount' => 0,
            'unpay_per_amount' => 0,
            'buy_freq' => 0,
            'buy_month' => 0,
            'buy_skus' => 0,
            'buy_products' => 0,
            'avg_buy_skus' => 0,
            'avg_buy_products' => 0,
            'first_buy_time' => 0,
            'last_buy_time' => 0,
            'update_time' => time(),
            'member_id' => $member_id,
            'shop_id' =>$shop_id
        );
        
        $order_ids_shop = array();
        $buy_month_shop = array();
        $sql = "select order_id,total_amount,pay_status,status,createtime,payed,item_num,skus,shop_id from sdb_ecorder_orders where member_id=".$member_id." ";
        $rs_orders = $db->select($sql);
        if($rs_orders){
            foreach($rs_orders as $v){
                //全局会员表
                if($analysis['order_first_time']==0) $analysis['order_first_time']=$v['createtime'];
                if($analysis['order_last_time']==0) $analysis['order_last_time']=$v['createtime'];
                $analysis['order_total_num'] ++;
                $analysis['order_total_amount'] += $v['total_amount'];
                $analysis['order_first_time'] = min($analysis['order_first_time'],$v['createtime']);
                $analysis['order_last_time'] = max($analysis['order_last_time'],$v['createtime']);
                if($v['status']=='finish' && $v['pay_status']=='1'){
                    $analysis['order_succ_num'] ++;
                    $analysis['order_succ_amount'] += $v['total_amount'];
                }
                $analysis['update_time'] = time();

                //店铺会员表
                if($shop_id == $v['shop_id']){
                    $analysis_shop = $this->count_order_data($analysis_shop,$v['createtime'],$v['item_num'],$v['skus'],$v['total_amount'],$v['status'],$v['pay_status']);
                    $buy_month_shop[date('Ym', $v['createtime'])] = 1;
                    $order_ids_shop[] = $v['order_id'];
                }
            }

            //店铺会员表
            $sql = "select count(*) as buy_skus from sdb_ecorder_order_items where order_id in (".implode(',', $order_ids_shop).") group by goods_id ";
            $rs_order_items_shop = $db->selectRow($sql);
            $analysis_shop['buy_skus'] = $rs_order_items_shop['buy_skus'];
            if($analysis_shop['total_orders'])
                $analysis_shop['buy_freq'] = round(($analysis_shop['last_buy_time'] - $analysis_shop['first_buy_time'])/($analysis_shop['total_orders']*86400), 2);
            if($analysis_shop['total_orders'])
                $analysis_shop['total_per_amount'] = round($analysis_shop['total_amount']/$analysis_shop['total_orders'], 2);
            if($analysis_shop['finish_orders'])
                $analysis_shop['finish_per_amount'] = round($analysis_shop['finish_total_amount']/$analysis_shop['finish_orders'], 2);
            if($analysis_shop['unpay_orders'])
                $analysis_shop['unpay_per_amount'] = round($analysis_shop['unpay_amount']/$analysis_shop['unpay_orders'], 2);
            if($analysis_shop['total_orders'])
                $analysis_shop['avg_buy_skus'] = round($analysis_shop['buy_skus']/$analysis_shop['total_orders'], 2);
            if($analysis_shop['total_orders'])
                $analysis_shop['avg_buy_products'] = round($analysis_shop['buy_products']/$analysis_shop['total_orders'], 2);
            $analysis_shop['buy_month'] = count($buy_month_shop);
        }
        
        //更新全局会员表
        $members = app::get('taocrm')->model('members');
        $result1 = $members->update($analysis, array('member_id'=>$member_id));

        //更新店铺会员表
        if($result1){
            $member_analysis = app::get('taocrm')->model('member_analysis');
            $result2 = $member_analysis->update($analysis_shop, array('member_id'=>$member_id, 'shop_id'=>$shop_id));
            if($result2 === false){
                $member_analysis->insert($analysis_shop);
            }
        }else{
            $err_msg = '更新全局会员表失败！';
            $result = array('re'=>false,'err_msg'=>$err_msg);
            return $result;
        }

        //更新会员等级
        $result3 = kernel::single('taocrm_service_member')->updateMemberLv($member_id, $shop_id);
        if( ! $result3){
            $err_msg = '更新会员等级表失败！';
            $result = array('re'=>false,'err_msg'=>$err_msg);
            return $result;
        }
        $result = array('re'=>true,'err_msg'=>'');
        return $result;
    }
    
    //计算统计会员数据
    protected function count_order_data($analysis,$createtime,$item_num,$skus,$total_amount,$status,$pay_status)
    {
        if($analysis['first_buy_time']==0) $analysis['first_buy_time']=$createtime;
        if($analysis['last_buy_time']==0) $analysis['last_buy_time']=$createtime;

        $analysis['total_orders'] ++;
        $analysis['buy_products'] += $item_num;
        $analysis['buy_skus'] += $skus;
        $analysis['total_amount'] += $total_amount;
        $analysis['first_buy_time'] = min($analysis['first_buy_time'],$createtime);
        $analysis['last_buy_time'] = max($analysis['last_buy_time'],$createtime);

        if($status=='finish'){
            $analysis['finish_orders'] ++;
            $analysis['finish_total_amount'] += $total_amount;
        }

        if($pay_status=='5'){
            $analysis['refund_orders'] ++;
            $analysis['refund_amount'] += $total_amount;
        }elseif($pay_status=='0'){
            $analysis['unpay_orders'] ++;
            $analysis['unpay_amount'] += $total_amount;
        }
        return $analysis;
    }
    
}
