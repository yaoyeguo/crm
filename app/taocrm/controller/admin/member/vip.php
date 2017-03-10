<?php
class taocrm_ctl_admin_member_vip extends desktop_controller {
    
    public function index()
    {
        $shopObj = app::get(ORDER_APP)->model('shop');
        $shopList = $shopObj->getList('shop_id,name');
        foreach($shopList as $v){
            $shops[] = $v['shop_id'];
        }
        $view = (isset($_GET['view']) && intval($_GET['view']) >= 0 ) ? intval($_GET['view']) : 0;
        if ($_GET['view']!=0){
            $view=$view-1;
            $shop_id =$shops[$view];
        }

        //将shop_id转换成view
        if($_GET['shop_id'] && $view==0) {
            $shop_id = $_GET['shop_id'];
            $_GET['view'] = array_search($shop_id,$shops) + 1;
        }

        $actions =  array(
       			array(
                    'label'=>'删除',
                    'submit'=>'index.php?app=taocrm&ctl=admin_member_vip&act=vip_del',
                 ),
        );
        
        if($_GET['view']){
            array_push($actions,array(
                'label'=>'快捷营销',
                'submit'=>'index.php?app=market&ctl=admin_active_sms&act=create_active&send_method=sms&memlist=1&resource=vip&&shop_id= '. trim($shop_id),
                'target'=>'dialog::{width:700,height:350,title:\'创建短信活动\'}'
            ));
        }
       
       if ($view == 0) {
           $this->base_filter=array('is_vip'=>'true', 'shop_id|in' => $shops);
       }
       else {
           $this->base_filter = array('is_vip'=>'true');
       }
	   $this->finder('taocrm_mdl_member_analysis',array(
			'title'=>'贵宾客户组',
			'actions'=>$actions,
            'use_buildin_export'=>false,//导出
            'use_buildin_import'=>false,//导入
            'use_buildin_recycle'=>false,
			'base_filter' =>$this->base_filter,
	  		'use_buildin_tagedit'=>true,
            'use_view_tab'=>true,
            ));
    }
    
    
 	function _views(){
        $memberObj = $this->app->model('member_analysis');
        $base_filter = array('is_vip'=>'true');
        $shop_id = trim($_GET['shop_id']);
        $active_id=trim($_GET['active_id']);
        $sub_menu[] = array(
            'label'=> '全部',
            'filter'=> array(),
            'optional'=>false,	
        );

        $shopObj = app::get(ORDER_APP)->model('shop');
        $shopList = $shopObj->getList('shop_id,name');
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
            $sub_menu[$k]['href'] = 'index.php?app=taocrm&ctl='.$_GET['ctl'].'&act='.$_GET['act'].'&view='.$i++;  
        }
        return $sub_menu;
    }
    
	//删除贵宾客户
	function vip_del(){
		$this->begin('index.php?app=taocrm&ctl=admin_member_vip&act=index');
		$memberObj = app::get('taocrm')->model('member_analysis');
		$nums = count($_POST['id']);
		if($nums > 1){
			foreach($_POST['id'] as $v){
				$rs=$memberObj->update(array('is_vip'=>'false'),array('id'=>$v));
			}
		}else{
			$rs=$memberObj->update(array('is_vip'=>'false'),array('id'=>$_POST['id'][0]));
		}
		
		if ($rs){
			 $this->end(true,'贵宾客户删除成功');
		}
	}
}
