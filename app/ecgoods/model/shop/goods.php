<?php 

class ecgoods_mdl_shop_goods extends dbeav_model{

    public function get_filter_member()
    {
        $member_ids = array();
        $goods_id = $_GET['goods_id'];
        $shop_id = $_GET['shop_id'];
        $pay_status = $_GET['pay_status'];
        $has_buy = intval($_GET['has_buy']);
        $all_buy = intval($_GET['all_buy']);
        
        $date_from = strtotime($_GET['date_from'].' 00:00:00');
        $date_to = strtotime($_GET['date_to'].' 23:59:59');
        $quantity = intval($_GET['quantity']);
        
        if($pay_status=='all'){
            $pay_status = '';
        }else{
            $pay_status = " and b.pay_status='$pay_status' ";
        }
        
        //购买过商品ID的所有客户id
        $sql = "
            select b.member_id,sum(nums) as nums,a.goods_id from sdb_ecorder_order_items as a
            left join sdb_ecorder_orders as b on a.order_id=b.order_id
            where
            (a.create_time between $date_from and $date_to) 
            and a.goods_id in (".implode($goods_id,',').")
            and a.shop_id='$shop_id'   
            and b.status in ('active','finish') $pay_status
            group by b.member_id,a.goods_id
        ";
        $rs = kernel::database()->select($sql);
        $member_buy = array();
        foreach((array)$rs as $v){
            $member_buy[$v['member_id']] = $v['member_id'];
            $member_goods[$v['member_id']][$v['goods_id']] = $v;
        }
        unset($rs);
        //var_dump($sql);

        if($has_buy == 0){
            $sql = "
                select b.member_id from sdb_ecorder_order_items as a
                left join sdb_ecorder_orders as b on a.order_id=b.order_id
                where
                (a.create_time between $date_from and $date_to)
                and a.shop_id='$shop_id'
                and b.status in ('active','finish') $pay_status
                group by b.member_id
            ";
            $rs = kernel::database()->select($sql);
            $member_all = array();
            foreach((array)$rs as $v){
                $member_all[] = $v['member_id'];
            }
            unset($rs);
            $final_members = array_diff($member_all,$member_buy);
            
        }elseif($has_buy == 1){
            $final_members = &$member_buy;
            if($all_buy == 1){
                foreach($final_members as $k=>$v){
                    foreach($goods_id as $vv){
                        if(!$member_goods[$v][$vv]){
                            unset($final_members[$k]);
                            continue 2;
                        }
                    }
                }
            }
        }
        
        $user_id = kernel::single('desktop_user')->get_id();
        base_kvstore::instance('analysis')->store('filter_member_'.$user_id,implode(',',$final_members));
        
        $final_filter['member_id'] = $final_members;
        $final_filter['total'] = sizeof($final_members);
        $final_filter['params'] = array(
            'goods_id' => $_GET['goods_id'],
            'pay_status' => $_GET['pay_status'],
            'date_from' => $_GET['date_from'],
            'date_to' => $_GET['date_to'],
            'has_buy' => $_GET['has_buy'],
            'all_buy' => $_GET['all_buy'],
            'quantity' => $_GET['quantity'],
        );
        return $final_filter;
    }

}
