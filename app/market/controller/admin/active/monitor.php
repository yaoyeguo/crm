<?php
//营销效果监控评估
class market_ctl_admin_active_monitor extends desktop_controller {

    function index()
    {
        $shopObj = &app::get(ORDER_APP)->model('shop');
        $shopList = $shopObj->getList('shop_id,name');
        foreach((array)$shopList as $v){
            $shops[] = $v['shop_id'];
        }

        $view = (isset($_GET['view']) && intval($_GET['view']) >= 0 ) ? intval($_GET['view']) : 0;
        $shop_id = $shops[$view];

        //将shop_id转换成view
        if($_GET['shop_id'] && $view==0) {
            $shop_id = $_GET['shop_id'];
            $_GET['view'] = array_search($shop_id,$shops) + 1;
        }

        $baseFilter = array();
        if ($view == 0) {
            //$baseFilter = array('shop_id|in' => $shops);
        }

        $sql = "UPDATE sdb_market_active_assess as a,sdb_market_active as b SET a.create_time=b.create_time WHERE ISNULL(a.create_time) AND a.active_id=b.active_id ";
        //kernel::database()->exec($sql);

        $actions = array();
        $days = array(3,7,15,30);
        foreach($days as $v){
            $actions[] = array(
                'label'=>$v.'天效果',
                'submit'=>'index.php?app=market&ctl=admin_active_monitor&act=detail&days='.$v,
                'target'=>'dialog::{width:700,height:355,title:\'营销效果监控评估\'}'
            );
        }

        $param=array(
            'title'=>'营销效果监控评估',
            'actions' => $actions,
            'use_buildin_recycle' =>FALSE,
            'orderBy' => "exec_time desc",
            'use_buildin_filter'=>true,
            'use_buildin_export'=>FALSE,
            'base_filter' => $baseFilter,
        );
        $this->finder('market_mdl_active_assess',$param);
    }

    function _views()
    {
        $memberObj = &app::get('market')->model('active_assess');
        $shopObj = &app::get(ORDER_APP)->model('shop');
        $shopList = $shopObj->getList('shop_id,name');
        foreach((array)$shopList as $v){
            $shops[] = $v['shop_id'];
        }

        $base_filter=array();
          $sub_menu[] = array(
            'label'=> '全部',
            //'filter'=> array('shop_id|in' => $shops),
            'optional'=>false,
        );
        foreach($shopList as $shop){
            $sub_menu[] = array(
                'label'=>$shop['name'],
                'filter'=>array('shop_id'=>$shop['shop_id']),
                'optional'=>false,
            );
        }
        $i=0;
        foreach($sub_menu as $k=>$v){
            if (!IS_NULL($v['filter'])){
                $v['filter'] = array_merge($v['filter'],$base_filter);
            }
           $count =$memberObj->count($v['filter']);
            $sub_menu[$k]['filter'] = $v['filter']?$v['filter']:null;
            $sub_menu[$k]['addon'] = $count;
            $sub_menu[$k]['href'] = 'index.php?app='.$_GET['app'].'&ctl='.$_GET['ctl'].'&act='.$_GET['act'].'&view='.$i++;
        }
        return $sub_menu;
    }

    public function detail()
    {
        $days = intval($_GET['days']);
        $id = $_POST['id'];

        if(count($id)!=1){
            die('<p align="center">只能选择一个营销活动</p>');
        }

        $this->pagedata['id'] = $id[0];
        $rs_active_assess = $this->app->model('active_assess')->dump(intval($id[0]));

        $active_id = intval($rs_active_assess['active_id']);
        $rs_active = $this->app->model('active')->dump($active_id);
        
        if($_POST['dead_date']){
            $dead_date = date('Y-m-d', strtotime($_POST['dead_date']));
        }else{
            $dead_date = date('Y-m-d', strtotime("+$days  days", $rs_active['exec_time']));
        }
        $rs_active['exec_time'] = date('Y-m-d H:i:s', $rs_active['exec_time']);

        //综合
        $all_data['targets'] = 830013;
        $all_data['start_time'] = $rs_active['start_time'];
        $all_data['end_time'] = strtotime($dead_date);
        $all_res = kernel::single('taocrm_middleware_connect')->createCallplanActiveOrder($id[0],$all_data);
        $all_item = $all_res[$all_data['targets']]['data'];
        $final_data = array();
        
        //累加AB数据，不含C
        foreach($all_item as $k=>$v){
            if($k != 'C'){
                foreach($v as $kk=>$vv){
                    $final_data[$kk] += $vv;
                }
            }
        }

        //下单客户占活动客户比例=下单客户数/活动目标客户数
        $final_data['order_member_ratio'] = round($final_data['TotalMembers']*100 / $final_data['MemberCount'],2);

        //付款客户占活动客户比例=付款客户数/活动目标客户数
        $final_data['pay_member_ratio'] = round($final_data['PayMembers']*100 / $final_data['MemberCount'],2);

        //多笔付款客户占付款客户比例=多笔付款客户数/付款客户数
        $final_data['muti_pay_member_ratio'] = round($final_data['MutiPayMembers']*100 / $final_data['PayMembers'],2);

        //人均下单订单数=下单订单数/下单客户数
        $final_data['per_capita_placing_orders'] = round($final_data['TotalOrders'] / $final_data['TotalMembers'],4);

        //人均下单金额=下单金额/下单客户数
        $final_data['per_capita_amount_order'] = round($final_data['TotalAmount'] / $final_data['TotalMembers'],4);

        //人均付款订单数=付款订单数/付款客户数
        $final_data['per_capita_payment_orders'] = round($final_data['PayOrders'] / $final_data['PayMembers'],4);

        //人均付款金额=付款金额/付款客户数
        $final_data['per_capita_amount_payment'] = round($final_data['PayAmount'] / $final_data['PayMembers'],4);

        //人均付款商品数=付款订单商品数/付款客户数
        $final_data['payment_goods_per_person'] = round($final_data['TotalGoods'] / $final_data['PayMembers'],4);

        //平均订单付款商品数=付款订单商品数/付款订单数
        $final_data['average_number_order_payment_goods'] = round($final_data['TotalGoods'] / $final_data['PayOrders'],4);

        //投入回报比例=（活动目标客户*0.05）/下单金额
        $final_data['investment_returns_ratio'] = '1 : '.round( $final_data['TotalAmount']/($final_data['MemberCount'] * 0.05) ,1);
        $this->pagedata['all_item'] = $final_data;

        //商品分析
        $params = array();
        $params['targets'] = 830014;
        $params['start_time'] = $rs_active['start_time'];
        $params['end_time'] = strtotime($dead_date);
        $res = kernel::single('taocrm_middleware_connect')->createCallplanActiveOrder($id[0],$params);
        $res = $res[$params['targets']]['data'];
        $final_data = array();
        foreach($res as $k=>$v){
            if($k != 'C'){
                unset($v['count']);
                foreach($v as $kk=>$vv){
                    foreach($vv as $kkk=>$vvv){
                        $final_data[$kk][$kkk] += $vvv;
                    }                    
                }
            }
        }
        
        $rs_goods = app::get('ecgoods')->model('shop_goods')->getList('goods_id,outer_id,name',array_keys($final_data));
        foreach($rs_goods as $v){
            $goods_list[$v['goods_id']] = $v;
        }

        foreach($final_data as $k=>$v){
            $final_data[$k]['outer_id'] = $goods_list[$k]['outer_id'];
            $final_data[$k]['name'] = $goods_list[$k]['name'];
        }
        
        //按商品销量倒序
        $final_data = $this->array_sort($final_data,'TotalGoods','desc');
        
        $this->pagedata['goods_list'] = $final_data;
        $this->pagedata['active'] = $rs_active;
        $this->pagedata['dead_date'] = $dead_date;
        $this->display('admin/active/monitor/pop_detail.html');
    }
    
    function array_sort($arr,$keys,$type='desc'){ 
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
        return $new_array; 
    }    
}
