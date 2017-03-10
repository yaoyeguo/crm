<?php
class taocrm_search_engine{

    protected $selectFilter = array (
      'total_orders' => 
    array (
        'label' => '订单总数',
        'type' => 'int',
    ),
      'finish_orders' => 
    array (
        'label' => '成功的单数',
        'type' => 'int',
    ),
      'total_amount' => 
    array (
        'label' => '订单总金额',
        'type' => 'int',
    ),
      'total_per_amount' => 
    array (
        'label' => '订单客单价',
        'type' => 'int',
    ),
      'buy_freq' => 
    array (
        'label' => '购买频次(天)',
        'type' => 'int',
    ),
      'avg_buy_interval' => 
    array (
        'label' => '平均购买间隔(天)',
        'type' => 'int',
    ),
      'buy_month' => 
    array (
        'label' => '购买月数',
        'type' => 'int',
    ),
      'buy_products' => 
    array (
        'label' => '购买商品总数',
        'type' => 'int',
    ),
      'finish_total_amount' => 
    array (
        'label' => '成功的金额',
        'type' => 'int',
    ),
      'finish_per_amount' => 
    array (
        'label' => '成功的客单价',
        'type' => 'int',
    ),
      'unpay_orders' => 
    array (
        'label' => '未付款单数',
        'type' => 'int',
    ),
      'unpay_amount' => 
    array (
        'label' => '未付款金额',
        'type' => 'int',
    ),
      'refund_orders' => 
    array (
        'label' => '退款订单数',
        'type' => 'int',
    ),
      'first_buy_time' => 
    array (
        'label' => '第一次购买时间',
        'type' => 'int',
    ),
      'last_buy_time' => 
    array (
        'label' => '最后购买时间',
        'type' => 'int',
    ),
      'refund_amount' => 
    array (
        'label' => '退款总金额',
        'type' => 'int',
    ),
      'lv_id' => 
    array (
        'label' => '客户等级',
        'type' => 'int',
    ),
      'shop_evaluation' => 
    array (
        'label' => '店铺评价',
        'type' => 'int',
    ),
      'good_buy_date' => 
    array (
        'label' => '购买时间',
        'type' => 'int',
    ),
      'points' => 
    array (
        'label' => '积分',
        'type' => 'int',
    ),
      'birthday' => 
    array (
        'label' => '生日',
        'type' => 'int',
    ),
      'regions_id' => 
    array (
        'label' => '地区',
        'type' => 'int',
    ),
      'goods_id' => 
    array (
        'label' => '商品编号',
        'type' => 'int',
    ),
    );

    public  $shop_id;

    function count($filter){
        //echo count($this->selectFilter);exit;
        /*$list = array();
         foreach($this->selectFilter as $f=>$v){

         $list[$f] =  array('label'=>$v['label'],'type'=>'int');
         }

         var_export($list);exit;*/

        return count($this->parse($filter));
    }

    function getList(){

        return $this->parse($filter);
    }

    function parse($filter){
        if(!isset($filter['shop_id']))return array();
        $this->shop_id = $filter['shop_id'];
        unset($filter['shop_id']);

        $filter_clean = array();
        foreach ($filter as $key => $value) {
            if (isset($this->selectFilter[$key])) {
                if ( ( is_array($value) && $value['min_val'] ) || ( is_string($value) && $value ) || (is_array($value) && $value[0] != '')) {
                    $filter_clean[$key] = $value;
                }
            }
        }
        if(!$this->isValid($filter_clean))return array();

        /*$arr1 = array(1,2,3);
         $arr2 = array(3,4);
         $arr3 = array_intersect($arr1,$arr2);
         var_dump($arr3);exit;*/
        //$redis = kernel::single('taocrm_service_redis')->redis;
        $members_inter = array();
        foreach($filter_clean as $field=>$value){
            if(!empty($members_inter)){
                $members = $this->getFilterData($field,$value);
                $members_inter = array_intersect($members_inter, $members);
                //echo '<pre>'; var_export($members_inter);exit;
                 
            }else{
                $members_inter = $this->getFilterData($field,$value);
            }

            /*switch($k){
             case '';

             break;
             default:
             if(!empty($members_inter)){
             $members = $redis->ZRANGEBYSCORE($_SERVER['SERVER_NAME'].':taocrm:'.$shop_id.':total_amount',100,2200);
             $members_inter = array_intersect($members_inter, $members);
             }else{
             $members_inter = $redis->ZRANGEBYSCORE($_SERVER['SERVER_NAME'].':taocrm:'.$shop_id.':total_amount',100,2200);
             }

             break;


             }*/
        }

        return array_values($members_inter);
    }

    protected function getFilterData($field, $value)
    {
        $type = $this->selectFilter[$field]['type'];
        switch ($type) {
            case 'time':
            case 'int':
                $result = $this->getIntAndTimeFilter($field, $value);
                break;
                /*case 'select':
                 $result = $this->setSelectFilter($field, $value, $tableName);
                 break;
                 case 'array':
                 $result = $this->setArrayFilter($field, $value, $tableName);
                 break;
                 case 'timeselect':
                 $result = $this->setTimeSelectFilter($field, $value, $tableName);
                 break;*/
        }
        return $result;
    }

    protected function getIntAndTimeFilter($field, $value )
    {
        $redis = kernel::single('taocrm_service_redis')->redis;
        $key = $_SERVER['SERVER_NAME'].':taocrm:'.$this->shop_id.':'.$field;
        $members = array();
        switch ($value['sign']) {
            case 'nequal':
                $members = $redis->ZRANGEBYSCORE($key,$value['min_val'],$value['min_val']);
                break;
            case 'sthan':
                $members = $redis->ZRANGEBYSCORE($key,$value['min_val'],0,$value['min_val']);
                break;
            case 'bthan':
                $members = $redis->ZRANGEBYSCORE($key,$value['min_val'],'+inf');
                break;
            case 'between':
                $members = $redis->ZRANGEBYSCORE($key,$value['min_val'],$value['max_val']);
                break;
            case 'than':
                $members = $redis->ZRANGEBYSCORE($key,$value['min_val'],($value['min_val']-1),'+inf');
                break;
            case 'lthan':
                $members = $redis->ZRANGEBYSCORE($key,$value['min_val'],0,($value['min_val']-1));
                break;
        }
        return $members;
    }


    function isValid($filter){
        if(is_array($filter) && !empty($filter) ){
             
            return true;
        }else{
            return false;
        }
    }

    function toRedis(){
        $db = kernel::database();
        $page = 0;
        $page_size = 1000;
        $redis = kernel::single('taocrm_service_redis')->redis;
        while(true){
            echo $page."\n";
            $rows = $db->select('select  * from sdb_taocrm_member_analysis order by id limit '.($page_size * $page).','.$page_size);
            //$rows = $db->selectrow('select * from sdb_taocrm_member_analysis where member_id=351906');
            //var_dump($rows);exit;
            //$rows = array($rows);
            if(!$rows)break;


            foreach($rows as $row){
                if($row['shop_id']){
                    /* foreach($row as $f=>$v){
                     switch($f){
                     case 'id':
                     case 'member_id':
                     case 'member_id':
                     case 'member_id':
                     case 'member_id':


                     }
                     }*/
                    //echo $row['total_amount']."\n";;
                    ///$row['total_amount'] = '99';
                    //var_dump($row['total_amount']);exit;
                    //$redis->ZADD($_SERVER['SERVER_NAME'].':taocrm:total_amount',$row['total_amount'],$row['member_id']);
                    $redis->ZADD($_SERVER['SERVER_NAME'].':taocrm:'.$row['shop_id'].':total_amount',$row['total_amount'],$row['member_id']);
                    $redis->ZADD($_SERVER['SERVER_NAME'].':taocrm:'.$row['shop_id'].':total_orders',$row['total_orders'],$row['member_id']);
                }
            }
            $page++;
        }
    }

}