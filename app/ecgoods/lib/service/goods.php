<?php

class ecgoods_service_goods{

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
    public function saveGoods($sdf)
    {
        $this->app->model('shop_goods')->save($sdf);
        $outer_id=$sdf['outer_id'];//outer_id
        //$this->counttotal_num($outer_id);
        return $sdf['goods_id'];
    }

    //购买人次
    public function counttotal_num($outer_id)
    {
        $db = kernel::database();

        //购买人次
        $buy_persons = $db->selectrow('select count(distinct (order_id)) as buy_persons  from sdb_ecorder_order_items where shop_goods_id='.$outer_id);
        $buyperson=  !empty($buy_persons['buy_persons']) ? intval($buy_persons['buy_persons']) : 0;

        //近3个月热销商品排名
        $start_date = strtotime('-90 days');
        $end_date = time();
        $sql = "select sum(a.nums) as nums,sum(a.amount) as amount from sdb_ecorder_order_items as a
        inner join sdb_ecorder_orders as b on a.order_id=b.order_id
        where b.createtime>={$start_date} and b.createtime<={$end_date} and a.shop_goods_id={$outer_id} and b.pay_status='1' ";
        $rs = $db->selectrow($sql);
        $month3_paid_amount = floatval($rs['amount']);
        $month3_paid_num = floatval($rs['nums']);
        //var_dump($sql);die();

        kernel::database()->exec('update sdb_ecgoods_shop_goods set buyperson='.$buyperson.',month3_paid_amount='.$month3_paid_amount.',month3_paid_num='.$month3_paid_num.' where  outer_id='.$outer_id);
        return true;
    }

    public function getProductsByName($shopId,$p)
    {
        $db = kernel::database();
        return $db->select('select outer_id,name,pic_url from sdb_ecgoods_shop_goods where shop_id="'.$shopId.'" and name like "%'.$p.'%" order by goods_id limit 0,20');
    }

    //商品统计数据
    public function run_analysis()
    {
        $last_modify = time();
    
        $db = kernel::database();
        $sql = "select goods_id,shop_id from sdb_ecgoods_shop_goods where no_use=0 ";
        $rs_goods = $db->select($sql);
        foreach($rs_goods as $v){
            $goods_id = $v['goods_id'];
            $shop_id = $v['shop_id'];
            $sql2 = "select count(distinct b.member_id) as buyperson,sum(a.nums) as total_num,sum(a.amount) as sale_money
            from sdb_ecorder_order_items as a
            left join sdb_ecorder_orders as b on a.order_id=b.order_id
            where a.goods_id={$goods_id} and a.shop_id='{$shop_id}' and b.pay_status='1' ";
            $rs = $db->selectRow($sql2);
            if($rs){
                $buyperson = floatval($rs['buyperson']);
                $total_num = floatval($rs['total_num']);
                $sale_money = floatval($rs['sale_money']);
            }else{
                $buyperson = 0;
                $total_num = 0;
                $sale_money = 0;
            }         
            $sql3 = "update sdb_ecgoods_shop_goods set buyperson={$buyperson},total_num={$total_num},sale_money={$sale_money},last_modify={$last_modify} where goods_id={$goods_id} ";
            $db->exec($sql3);
        }
    }
    
    //根据订单明细表创建新的商品
    public function create_item_goods()
    {    
        $oShopGoods = app::get('ecgoods')->model('shop_goods');
        
        $sql = "SELECT name,shop_goods_id,bn,price,shop_id FROM sdb_ecorder_order_items WHERE goods_id=0 ";
        $sql .= " GROUP BY name ";
        $rs = $oShopGoods->db->select($sql);
        if($rs){
            foreach($rs as $v){
                $goods_id = 0;
                $name = $v['name'];
                if($name=='') continue;
                
                $rs_goods = $oShopGoods->dump(array('name'=>$name), 'goods_id');
                if($rs_goods){
                    $goods_id = $rs_goods['goods_id'];
                }else{
                    $arr = array();
                    $arr['outer_id'] = (string)$v['shop_goods_id'];
                    $arr['bn'] = (string)$v['bn'];
                    $arr['name'] = trim($name);
                    $arr['price'] = floatval($v['price']);
                    $arr['shop_id'] = $v['shop_id'];
                    
                    $arr['create_time'] = time();
                    $arr['last_modify'] = time();
                    $arr['disabled'] = 'false';
                    $oShopGoods->insert($arr);
                    
                    $goods_id = $arr['goods_id'];
                }

                if($goods_id){//创建商品
                    $name = str_replace("'","''",$name);
                    $sql = "update sdb_ecorder_order_items set goods_id=$goods_id where goods_id=0 AND name='$name' ";
                    $oShopGoods->db->exec($sql);
                }else{
                    die('system error....');
                }
                $arr = null;
            }
        }else{
            die('finish');
        }
    }
}