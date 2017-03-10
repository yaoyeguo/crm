<?php 

class ecgoods_ctl_admin_goods_buy extends desktop_controller{

    var $workground = 'ecgoods.goods';
    
    public function __construct($app)
    {
        parent::__construct($app);        
        $timeBtn = array(
            'today' => date("Y-m-d"),
            'yesterday' => date("Y-m-d", time()-86400),
            
            'this_month_from' => date("Y-m-" . 01),
            'this_month_to' => date("Y-m-d"),
            
            'this_3month_from' => date("Y-m-d", time()-90*86400),
            'this_3month_to' => date("Y-m-d"),
            
            'this_6month_from' => date("Y-m-d", time()-180*86400),
            'this_6month_to' => date("Y-m-d"),
            
            'this_week_from' => date("Y-m-d", time()-(date('w')?date('w')-1:6)*86400),
            'this_week_to' => date("Y-m-d"),
            
            'this_7days_from' => date("Y-m-d", time()-6*86400),
            'this_7days_to' => date("Y-m-d"),
            
            'next_3days_from' => date("Y-m-d", strtotime('+1 days')),
            'next_3days_to' => date("Y-m-d", strtotime('+3 days')),
            
            'next_7days_from' => date("Y-m-d", strtotime('+1 days')),
            'next_7days_to' => date("Y-m-d", strtotime('+7 days')),
        );
        $this->pagedata['timeBtn'] = $timeBtn;
    }

    function index()
    {
        $extra_view = array('ecgoods'=>'finder/buy_times.html');
        $actions = array();
        $base_filter = array('no_use'=>0);
        
        //搜索参数
        if(!isset($_POST['s'])){
            $_POST['s']['time_from'] = $this->pagedata['timeBtn']['this_7days_from'];
            $_POST['s']['time_to'] = $this->pagedata['timeBtn']['this_7days_to'];
        }
        $this->pagedata['s'] = $_POST['s'];
        
        $actions[] = array(
            'label'=>'商品过滤',
            'href'=>'index.php?app=ecgoods&ctl=admin_shop_goods&act=index_filter',
        );
    
        $this->finder('ecgoods_mdl_buy_times',array(
            'title'=>'商品重复购买率',
            'actions'=>$actions,
            'base_filter'=>$base_filter,
            'top_extra_view' => $extra_view, 
            'use_buildin_set_tag'=>false,
            'use_buildin_import'=>false,
            'use_buildin_export'=>false,
            'use_buildin_recycle'=>false,
            'use_buildin_filter'=>false,
            'use_buildin_tagedit'=>true,
            'use_view_tab'=>true,
            'use_buildin_setcol'=>false,//列配置
            'use_buildin_refresh'=>false,//刷新
        ));
    }
    
    function _views()
    {
        $oGoods = $this->app->model('shop_goods');
        $base_filter = array();

        $sub_menu[] = array(
            'label'=>'全部',
            'filter'=>array(),
            'optional'=>false
        );

        $shopObj = &app::get(ORDER_APP)->model('shop');
        $shopList = $shopObj->getList('shop_id,name');
        foreach($shopList as $shop){
            $sub_menu[] = array(
                'label'=>$shop['name'],
                'filter'=>array('shop_id'=>$shop['shop_id'],'no_use'=>'0'),
                'optional'=>false
            );
            $shop_id_arr[] = $shop['shop_id'];
        }

        $sub_menu[0]['filter'] = array('shop_id'=>$shop_id_arr,'no_use'=>'0');

        $i=0;
        foreach($sub_menu as $k=>$v){
            if (!IS_NULL($v['filter'])){
                $v['filter'] = array_merge($v['filter'], $base_filter);
            }else{
                $v['filter'] = array('shop_id'=>$shop_id_arr); 
            }
            $sub_menu[$k]['filter'] = $v['filter']?$v['filter']:null;
            $sub_menu[$k]['addon'] = $oGoods->count($v['filter']);
            $sub_menu[$k]['href'] = 'index.php?app=ecgoods&ctl='.$_GET['ctl'].'&act='.$_GET['act'].'&view='.$i++;
        }
        return $sub_menu;
    }
}
