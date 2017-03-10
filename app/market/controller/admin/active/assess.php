<?php 
class market_ctl_admin_active_assess extends desktop_controller {

    public function set_date($par=array()){
        if (!empty($par)){
            $timeBtn = array(
                'date_from'=>$_POST['date_from'],
                'date_to'=>$_POST['date_to'],
                'shop_id'=>$_POST['shop_id']
             );
        
        }else {
            $timeBtn = array(
                'date_from'=>date('Y-m-d',time()-30*86400),
                'date_to'=>date('Y-m-d',time()),
                'shop_id'=>""
             );
        }
        return $timeBtn;
    }
    
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
            $baseFilter = array('shop_id|in' => $shops);
        }
        
        $sql = "UPDATE sdb_market_active_assess as a,sdb_market_active as b SET a.create_time=b.create_time WHERE ISNULL(a.create_time) AND a.active_id=b.active_id ";
        kernel::database()->exec($sql);
        
        $param=array(
            'title'=>'短信营销效果',
            'use_buildin_recycle' =>FALSE,
            'orderBy' => "exec_time desc", 
            'use_buildin_filter'=>true,
            'use_buildin_export'=>FALSE,
            'base_filter' => $baseFilter,
        );
        $this->finder('market_mdl_active_assess',$param);
    }
    
    public function ordermem_list($par=array()){
        $order_obj=&app::get('ecorder')->model('orders');
        $filter=array('createtime|between'=>array(time()-7*86400,time()));
        $order_memlist=$order_obj->getList('member_id,order_bn',$filter);
        return $order_memlist;
    }
    
     function _views(){
        $memberObj = &app::get('market')->model('active_assess');
        $shopObj = &app::get(ORDER_APP)->model('shop');
        $shopList = $shopObj->getList('shop_id,name');
        foreach((array)$shopList as $v){
            $shops[] = $v['shop_id'];
        }
        
        $base_filter=array();
          $sub_menu[] = array(
            'label'=> '全部',
            'filter'=> array('shop_id|in' => $shops),
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
            $sub_menu[$k]['href'] = 'index.php?app=market&ctl='.$_GET['ctl'].'&act='.$_GET['act'].'&view='.$i++;
        }
        return $sub_menu;
    }
}






