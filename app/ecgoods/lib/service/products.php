<?php

class ecgoods_service_products{

    /**
     * 对应店铺信息
     * @var Array
     */
    protected $_shopInfo = array();

    function __construct(){
        $this->app = app::get('ecgoods');
    }

    /**
     * 商品保存
     *
     * @param $shopId
     * @return bool
     */
    public function saveProducts($sdf) {

        app::get('ecgoods')->model('shop_products')->save($sdf);

        return $sdf['product_id'];
    }

    public function setTradeRate($pids) {
        if(!is_array($pids) || count($pids) <= 0)return false;

        $productObj = app::get('ecgoods')->model('shop_products');

        foreach($pids as $product_id){
            if(!$product_id)continue;
             
            $goods_evaluation = app::get('ecorder')->model('order_items')->count(array('product_id'=>$product_id,'evaluation'=>'good'));
            $bad_evaluation = app::get('ecorder')->model('order_items')->count(array('product_id'=>$product_id,'evaluation'=>'bad'));
            $neutral_evaluation = app::get('ecorder')->model('order_items')->count(array('product_id'=>$product_id,'evaluation'=>'neutral'));
            $productData = array(
                'product_id' => $product_id,
            	'good_ranks' => $goods_evaluation,
                'bad_ranks' => $bad_evaluation,
            	'neutral_ranks' => $neutral_evaluation,
            );
            $productObj->save($productData);
        }

        return true;
    }

    public function countProductBuys() {

        //屏蔽此方法
        //对应功能转到 ecgoods_rpc_request_taobao_goods->download()
        //统计商品销售额 ecorder_ctl_admin_download->update_member_products()
        return true;
        $sql = 'update sdb_ecorder_order_items as oi,sdb_ecgoods_shop_products as p
            set oi.goods_id=p.goods_id,oi.product_id=p.product_id 
            where p.outer_sku_id = oi.shop_product_id';
        kernel::database()->exec($sql);

        $sql = 'update sdb_ecorder_order_items as oi,sdb_ecgoods_shop_goods as g
            set oi.goods_id=g.goods_id  
            where g.outer_id = oi.shop_goods_id';
        kernel::database()->exec($sql);

        $sql = 'update sdb_ecgoods_shop_goods as a,(
        select sum(aa.nums) as num,sum(aa.amount) as amount,aa.goods_id 
        from sdb_ecorder_order_items as aa left join sdb_ecorder_orders as bb on aa.order_id=bb.order_id where bb.pay_status="1" and aa.goods_id>0 group by aa.goods_id
        ) as b set a.total_num=b.num,a.sale_money=b.amount
        where a.goods_id=b.goods_id 
        ';
        kernel::database()->exec($sql);
         
        return true;
         
        $pageNo = 0;
        $pageSize = 1000;
        $execCount = 1;
        $execTime = time();
        $productObj = app::get('ecgoods')->model('shop_products');
        while(true){
            $curTime = time();
            if($curTime >= $execTime + 30 ){
                kernel::database()->dbclose();
                $execTime = $curTime;
            }
            $rows = $productObj->getList('product_id','',$pageNo*$pageSize,$pageSize);
            if(!$rows){
                break;
            }
            foreach($rows as $row){
                //echo $row['product_id']."\n";
                $count = kernel::database()->selectrow('
            select sum(total_amount) as total_amount,count(*) as total_num
            from sdb_ecorder_orders as o 
            left join sdb_ecorder_order_items as oi 
            on o.order_id=oi.order_id 
            where oi.product_id='.$row['product_id']);
                $total_amount = $count['total_amount'] ? $count['total_amount'] : 0;
                $total_num = $count['total_num'];

                $count = kernel::database()->selectrow('
            select count(*) as total_num
            from sdb_ecorder_orders as o 
            left join sdb_ecorder_order_items as oi 
            on o.order_id=oi.order_id 
            where pay_status="5" and  oi.product_id='.$row['product_id']);
                $refund_num = $count['total_num'];

                $productData = array(
                'product_id' => $row['product_id'],
                'total_amount' => $total_amount,
                'total_num' => $total_num,
                'refund_num' => $refund_num,
                );
                $productObj->save($productData);
            }
            $pageNo++;

            sleep(1);
        }

        $this->countgoodsBuys();
        return true;
    }

    public function countgoodsBuys(){
        $pageNo = 0;
        $pageSize = 1000;
        $execCount = 1;
        $execTime = time();
        $productObj = app::get('ecgoods')->model('shop_products');
        while(true){
            //echo $pageNo."\n";
            $curTime = time();
            if($curTime >= $execTime + 30 ){
                kernel::database()->dbclose();
                $execTime = $curTime;
            }
            $sql='select goods_id,sum(total_amount) as total_amount, sum(total_num) as  total_num from sdb_ecgoods_shop_products group by goods_id order by goods_id  limit  '.$pageNo*$pageSize.','.$pageSize;

            $total_data=kernel::database()->select($sql);
            if (!$total_data){
                break;
            }
            foreach ($total_data as $k=>$v){
                kernel::database()->exec('update sdb_ecgoods_shop_goods set total_num=' . $v['total_num'] . ' , sale_money= '.$v['total_amount'].' where  goods_id='.$v['goods_id']);
            }
            $pageNo++;
            sleep(1);
        }
        return true;
    }





}