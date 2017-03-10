<?php
class ecorder_ctl_admin_shop_lv extends desktop_controller{
    var $workground = 'taocrm.shop';
    
    public function index()
    {
        $memberObj = app::get('taocrm')->model('members');
        $shopObj = app::get(ORDER_APP)->model('shop');
        $shopList = $shopObj->get_shops('no_fx');
        foreach((array)$shopList as $v){
            $shops[] = $v['shop_id'];
        }
        $base_filter = array();
        $view = (isset($_GET['view']) && intval($_GET['view']) >= 0 ) ? intval($_GET['view']) : 0;
        if ($view == 0) {
            $base_filter = array('shop_id|in' => $shops);
        }

        /*
        $view = (isset($_GET['view']) && intval($_GET['view']) >= 0 ) ? intval($_GET['view']) : 0;
        if($view>0) 
        $base_filter = array('shop_id' => $shopList[$view]['shop_id']);
        */

        $this->finder('ecorder_mdl_shop_lv',array(
            'title'=>'客户等级设置',
            'actions'=>array(
                    array(
                        'label'=>'添加客户等级',
                        'href'=>'index.php?app=ecorder&ctl=admin_shop_lv&act=addnew&shop_id='.$_GET['shop_id'],
                        'target'=>'dialog::{width:680,height:270,title:\'添加客户等级\'}'
                    ),
                ),
            'base_filter'=>$base_filter,
            'orderBy' => 'shop_id asc,lv_id asc',
        ));
    }

    function _views()
    {
        $shopLvObj = $this->app->model('shop_lv');
        $shopObj = app::get(ORDER_APP)->model('shop');
        $shopList = $shopObj->get_shops('no_fx');
        foreach((array)$shopList as $v){
            $shops[] = $v['shop_id'];
        }
        $base_filter = array('shop_id|in' => $shops);
        
        $sub_menu[] = array(
            'label'=>'全部',
            'filter'=>array(),
            'optional'=>false
        );


        foreach($shopList as $shop){
            $sub_menu[] = array(
                'label'=>$shop['name'],
                'filter'=>array('shop_id'=>$shop['shop_id']),
                'optional'=>false
            );
        }

        $i=0;
        foreach((array)$shopList as $v){
            $shops[] = $v['shop_id'];
        }
        if ($i == 0) {
            $base_filter = array('shop_id|in' => $shops);
        }
        foreach($sub_menu as $k=>$v){
            if (!IS_NULL($v['filter'])){
                $v['filter'] = array_merge($v['filter'], $base_filter);
            }
            $sub_menu[$k]['filter'] = $v['filter']?$v['filter']:null;
            $sub_menu[$k]['addon'] = $shopLvObj->count($v['filter']);
            $sub_menu[$k]['href'] = 'index.php?app=ecorder&ctl='.$_GET['ctl'].'&act='.$_GET['act'].'&view='.$i++.'&shop_id='.$v['filter']['shop_id'];
        }
        return $sub_menu;
    }
    
    public function addnew($lv_id=null)
    {
    	$select_sign=array(
    		 	'unlimited' => '无限制',
    			/*
                'gthan' => '大于',
                'sthan' => '小于',
                'equal' => '等于',
                'gethan' => '大于等于',
        		'sethan' => '小于等于',
        		*/
    			'between' => '介于'
    	);
        $aLv['default_lv_options'] = array('1'=>'是','0'=>'否');
        $aLv['is_default'] = '0';
        $aLv['lv_type_options'] = array('retail'=>'普通零售客户等级','wholesale'=>'批发代理客户等级');
        $aLv['lv_type'] = 'retail';
        $aLv['shop_id'] = $_GET['shop_id'];
        $this->pagedata['levelSwitch']= $this->app->getConf('site.level_switch');
        $this->pagedata['select_sign'] = $select_sign;
        $this->pagedata['lv'] = $aLv;

        if($lv_id!=null){
            $shopLvObj = $this->app->model('shop_lv');
            $aLv = $shopLvObj->dump($lv_id);
            $aLv['default_lv_options'] = array('1'=>'是','0'=>'否');
            $this->pagedata['lv'] = $aLv;
        }
        
        $rs = $this->app->model('shop')->get_shops('no_fx');
        if($rs) {
            foreach($rs as $v) {
                $shops[$v['shop_id']] = $v['name'];
            }
        }
        $this->pagedata['shops'] = $shops;
        if($_GET['p'][0]){
        	$this->pagedata['res'] = 1;
        }else{
        	$this->pagedata['res'] = 2;
        }
        $this->display('admin/shop/lv.html');
    }

    // 新增客户等级规则
    /**
     * Enter description here ...
     */
    function save(){
        $this->begin("index.php?app=ecorder&ctl=admin_shop_lv&act=index");
        $shopLvObj = $this->app->model('shop_lv');
        $lvData = $_POST;
        if(!$lvData['lv_id']){
        	$nums = $shopLvObj ->getList("count(*) as num",array('shop_id'=>$lvData['shop_id'],'name'=>$lvData['name']));
        }else{
        	$nums = $shopLvObj ->getList("count(*) as num",array('shop_id'=>$lvData['shop_id'],'name'=>$lvData['name'],'lv_id|nohas'=>$lvData['lv_id']));
        }
		if($nums[0]['num']){
			$this->end(false,'等级名称已存在');
		}
		if(!$_POST['lv_id']){
       		$lvData['create_time'] = time();
		}
        $lvData['amount_symbol'] = $lvData['filter']['total_amount']['sign'];
        
        if($lvData['amount_symbol'] == 'between' && !$lvData['max_amount']){
        	$lvData['max_amount'] = 999999;
        }
        
        $lvData['buy_times_symbol'] = $lvData['filter']['buy_times']['sign'];
    	if($lvData['buy_times_symbol'] == 'between' && !$lvData['max_buy_times']){
        	$lvData['max_buy_times'] = 999999;
        }
        unset($lvData['filter']);
        
        
        if($shopLvObj->valid_data($lvData,$msg,$_POST['default'])){
        	if(empty($lvData['lv_id'])){
        		if($shopLvObj->save($lvData)){
        			$this->end(true,'添加成功');
        		}else{
        			$this->end(false,'添加失败');
        		}
        	}else{
	        	if($shopLvObj->save($lvData)){
                    //等级规则更新后，不更新客户等级
                    $this->end(true,'保存成功');
	                $memberObj = &app::get('taocrm')->model('members');
	                if($memberObj->shop_lv_change($lvData)){
	                    $this->end(true,'保存成功');
	                }else{
	                    $this->end(false,'对应客户的等级更新失败');
	                }
	            }else{
	                $this->end(false,'保存失败');
	            }
        	}
        }else{
            $this->end(false,$msg);
        }
    }

    public function getShopLv($shop_id){
        $shopLvObj = &$this->app->model('shop_lv');
        $member_level = $shopLvObj->getList('shop_lv_id,name',array('shop_id'=>$shop_id));
        if($member_level){
            $this->pagedata['member_level'] = $member_level;
            echo $this->fetch('admin/shop/shop_lv.html');
        }else{
            echo '<span class="red">此店铺没有添加客户等级</span>';
        }
    }
    
    //检查消费金额是否冲突
    public function check_amount_lv(){
    	$shopLvObj = $this->app->model('shop_lv');
    	$min_amount = $_POST['min_amount'];
    	$max_amount = $_POST['max_amount'];
    	$flag = 0;
    	if($_POST['res'] == 1){
    		$lv_id = $_POST['lv_id'];
    		$member_level = $shopLvObj->getList('lv_id,min_amount,max_amount',array('shop_id'=>$_POST['shop_id'],'amount_symbol'=>'between'));
	        foreach($member_level as $v){
	        	if(($min_amount >= $v['min_amount'] && $min_amount < $v['max_amount'] && $lv_id != $v['lv_id'])
	        		|| ($max_amount > $v['min_amount'] && $min_amount < $v['max_amount'] && $lv_id != $v['lv_id'])){
	        		$flag = 4;
	        	}
	        }
	        $data = $shopLvObj->getList('max_amount,lv_id',array('shop_id'=>$_POST['shop_id'],'amount_symbol'=>'between'),0,1,'max_amount desc');
	        
	        if($min_amount < $data[0]['max_amount'] && $max_amount >= $data[0]['max_amount'] && $lv_id != $data[0]['lv_id']){
	        	$flag = 5;
	        }else if((!$max_amount && $data[0]['max_amount'] == 999999 && $lv_id != $data[0]['lv_id'])
	        	|| (!$max_amount && $min_amount < $data[0]['max_amount'] && $lv_id != $data[0]['lv_id'])){
	        	$flag = 6;
	        }else if($max_amount && $max_amount <= $min_amount){
	        	$flag = 2;
	        }
    	}else{
    	  	$member_level = $shopLvObj->getList('MAX(max_amount) as max_amount',array('shop_id'=>$_POST['shop_id'],'amount_symbol'=>'between'));
	        if($min_amount < $member_level[0]['max_amount']){
	        	$flag = 1;
	        }else if($min_amount >= $max_amount && $max_amount){
	        	$flag = 2;
	        }else if(!$max_amount && $min_amount < 999999){
	        	$flag = 3;
	        }else if($min_amount >= 999999 && !$max_amount){
	        	$flag = 2;	
	        }
    	}
        echo $flag;
    }
    
    //检查消费次数是否冲突
    public function check_times_lv(){
    	$shopLvObj = $this->app->model('shop_lv');
		$min_buy_times = $_POST['min_buy_times'];
		$max_buy_times = $_POST['max_buy_times'];
		$flag = 0;
		if($_POST['res'] == 1){
			$lv_id = $_POST['lv_id'];
			$member_level = $shopLvObj->getList('lv_id,min_buy_times,max_buy_times',array('shop_id'=>$_POST['shop_id'],'buy_times_symbol'=>'between'));
			foreach($member_level as $v){
				if(($min_buy_times >= $v['min_buy_times'] && $min_buy_times < $v['max_buy_times'] && $lv_id != $v['lv_id'])
					|| ($max_buy_times > $v['min_buy_times'] && $min_buy_times < $v['max_buy_times'] && $lv_id != $v['lv_id'])){
					$flag = 4;
				}
			}
			$data = $shopLvObj->getList('max_buy_times,lv_id',array('shop_id'=>$_POST['shop_id'],'buy_times_symbol'=>'between'),0,1,'max_buy_times desc');
			
			if($min_buy_times < $data[0]['max_buy_times'] && $max_buy_times >= $data[0]['max_buy_times'] && $lv_id != $data[0]['lv_id']){
				$flag = 5;
			}else if((!$max_buy_times && $data[0]['max_buy_times'] == 999999 && $lv_id != $data[0]['lv_id'])
				|| (!$max_buy_times && $min_buy_times < $data[0]['max_buy_times'] && $lv_id != $data[0]['lv_id'])){
				$flag = 6;
			}else if($max_buy_times && $max_buy_times <= $min_buy_times){
				$flag = 2;
			}
		}else{
			$member_level = $shopLvObj->getList('MAX(max_buy_times) as max_buy_times',array('shop_id'=>$_POST['shop_id'],'buy_times_symbol'=>'between'));
			if($min_buy_times < $member_level[0]['max_buy_times']){
				$flag = 1;
			}else if($min_buy_times >= $max_buy_times && $max_buy_times){
				$flag = 2;
			}else if(!$max_buy_times && $min_buy_times < 999999){
				$flag = 3;
			}else if($min_buy_times >= 999999 && !$max_buy_times){
				$flag = 2;	
			}
		}
		echo $flag;
    }
}
