<?php 
class taocrm_ctl_admin_analysis_profitmodel extends desktop_controller {
	function index(){
        $shop_obj = app::get('ecorder')->model('shop');
		$shoplist=$shop_obj->getList("shop_id,name");
		$this->pagedata['shoplist']=$shoplist;
        $this->page("admin/analysis/profit/pro_model.html");
	}
	
	function pro_char(){
        
        //$orders_obj=&app::get("ecorder")->model('orders');
        $data_array = kernel::single('taocrm_analysis_day')->get_profit_data();
        
		$chartData1=array();
		foreach ($data_array as $k=>$v){
				$chartData1[] = '{date:"'.$v['date'].'",'.'distance:'.$v['distance'].','.'duration:'.$v['duration'].'}';
		}
        $chartData= "[".implode(',' , $chartData1)."]" ;
        
        $this->pagedata['chartData'] = $chartData;
		$this->display("admin/analysis/chart_type/lineWithDurationOnValueAxis.html");
	}

	public function ajaxGet(){
		$shop_id=$_GET['shop_id'];
		$taocrm_obj=&app::get("taocrm")->model('member_analysis');
		$cout=$taocrm_obj->count(array('shop_id'=>$shop_id));
		echo $cout;
	}

}

