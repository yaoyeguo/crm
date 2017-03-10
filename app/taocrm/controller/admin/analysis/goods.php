<?php
class taocrm_ctl_admin_analysis_goods extends desktop_controller {
    var $workground = 'taocrm.analysts';

    public function __construct($app){
        parent::__construct($app);
        $timeBtn = array(
            'today' => date("Y-m-d"),
            'yesterday' => date("Y-m-d", strtotime('-1 days')),
            'this_month_from' => date("Y-m-" . 01),
            'this_month_to' => date("Y-m-d"),
            'this_week_from' => date("Y-m-d", time()-(date('w')?date('w')-1:6)*86400),
            'this_week_to' => date("Y-m-d"),
            'sevenday_from' => date("Y-m-d", time()-6*86400),
            'sevenday_to' => date("Y-m-d"),
        );
        $this->pagedata['timeBtn'] = $timeBtn;
        
        if($_POST['date_from']==$_POST['date_to']){
            $_POST['date_to'] = date('Y-m-d', strtotime('+1 days', strtotime($_POST['date_from'])));
        }
        
        //初始化统计时间段
        if($_POST['date_from'] && $_POST['date_to']){
            base_kvstore::instance('analysis')->
                store('analysis_date_from',$_POST['date_from']);
            base_kvstore::instance('analysis')->
                store('analysis_date_to',$_POST['date_to']);
        }
        if($_POST['shop_id']) 
            base_kvstore::instance('analysis')->store('analysis_shop_id',$_POST['shop_id']);
            base_kvstore::instance('analysis')->fetch('analysis_shop_id',$this->shop_id);
        base_kvstore::instance('analysis')->
            fetch('analysis_date_from',$this->date_from);
        base_kvstore::instance('analysis')->
            fetch('analysis_date_to',$this->date_to);
        if(!$this->date_from) 
            $this->date_from = date('Y-m-d',(time()-86400*7));
        if(!$this->date_to)
            $this->date_to = date('Y-m-d',(time()-86400*1));
    }

    public function sales_rank(){
      
        $shopobj = app::get('ecorder')->model('shop');
        $args['shop_id'] = $this->shop_id;
        if ($_POST){
            $nodetype=$shopobj->dump(array('shop_id'=>$args['shop_id']),"node_type");
            $nodet=$nodetype['node_type'];
            if ($nodet != 'taobao'){
                 $this->pagedata['nodetype']= true;
            }
        }  
        $rs = $shopobj->getList('*');
        foreach($rs as $v){
            if($v['node_type'] != 'taobao')continue;
            
            if(!$args['shop_id']) 
                $args['shop_id'] = $v['shop_id'];
            $shops[$v['shop_id']] = $v['name'];
        }
    
        $args['date_from'] = $this->date_from;
        $args['date_to'] = $this->date_to;
        $args['count_by'] = $_POST['count_by'] ? $_POST['count_by'] : 'date'; 
        
        //对比时间段
        $args['c_date_from'] = $_POST['c_date_from'];
        $args['c_date_to'] = date('Y-m-d',strtotime($args['c_date_from'])+strtotime($args['date_to'])-strtotime($args['date_from']));

        $oAnalysisDay = kernel::single('taocrm_analysis_day');
        $all_sales_data = $oAnalysisDay->get_goods_rank($args);
      
        $this->pagedata['sales_data'] = $all_sales_data['sales_data'];
        $this->pagedata['analysis_data'] = $all_sales_data['analysis_data'];
        $this->pagedata['compare_data'] = $all_sales_data['compare_data'];
        $this->pagedata['total_data'] = $all_sales_data['total_data'];

        $this->pagedata['shops']= $shops;
        $this->pagedata['shop_id']= $args['shop_id'];
        $this->pagedata['path']= '销售统计';
        $this->pagedata['service'] = 'taocrm_analysis_sales';
        $this->pagedata['date_from'] = $args['date_from'];
        $this->pagedata['date_to'] = $args['date_to'];
        $this->pagedata['c_date_from'] = $args['c_date_from'];
        $this->pagedata['c_date_to'] = $args['c_date_to'];
        $this->pagedata['count_by'] = $args['count_by'];
        $this->pagedata['form_action'] = 'index.php?app=taocrm&ctl=admin_analysis_goods&act=sales_rank';
        $this->pagedata['line_shop'] = 'false';
        $this->page('admin/analysis/sales/goods_rank.html');
    }

}

