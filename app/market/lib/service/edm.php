<?php
class market_service_edm{

    public function select_member($_POST,$type='list'){
    
		$memberanaly_obj = &app::get('taocrm')->model('member_analysis');
		$member_analysis=$memberanaly_obj->member_analysis($_POST,$type);
        
        
		if($_POST['group_id']){
            $group_data = app::get('taocrm')->model('member_group');
            $group_member = $group_data->member_group($_POST);
        }else{
            $group_member = $member_analysis;
        }
        
        //if($type=='count') return $member_analysis;
        
		$mem_obj = app::get('taocrm')->model('members');
		$members=$mem_obj->mem_regb($_POST);
        
		$product_obj = app::get('ecorder')->model('member_products');
		$product=$product_obj->member_product($_POST);//购买某种商品的客户信息
        
		$intersect_data=array_intersect($product,$members,$member_analysis,$group_member);
		return $intersect_data;
	}
	
	function edm_execute($active_id,$msgid=""){
		$rs = $this->edm_save($active_id,$msgid);
		return $rs;
	}
    
    function get_active_filter($active_id){
    
        $oActive = &app::get('market')->model('active');
        $oMemberGroup = &app::get('taocrm')->model('member_group');
        
        $rs = $oActive->dump(array('active_id'=>$active_id));
        if($rs){
            $member_list = unserialize($rs['member_list']);
            if(stristr($member_list[0],'group_id')){
                $group_id = str_replace('group_id:','',$member_list[0]);
                // 获取自定义分组
                $group_filter = $oMemberGroup->getMemberList($group_id,'filter');
            }
        }
        //var_dump($rs);die();
        return $group_filter;
    }

    //计算活动包含的总人数
    function count_active_member($active_id){
    
        $count = false;
        if(!$active_id) return false;
        
        $oMemberAnalysis = &app::get('taocrm')->model('member_analysis');
        $group_filter = $this->get_active_filter($active_id);
        if(!$group_filter) return false;
        $count = $oMemberAnalysis->count($group_filter);
        return $count;
    }
	
	//把数据插入到邮件表和邮件日志表
    function edm_save($active_id,$msgid=""){
        set_time_limit(0);
		$active_obj = &app::get('market')->model('active');
		$members_obj = &app::get('taocrm')->model('members');
		$templates_obj = &app::get('market')->model('edm_templates');
		$edmlog_obj = &app::get('market')->model('edm_log');
		$edm_obj = &app::get('market')->model('edm');
       
        //var_dump(111111111);die();
        
        //大分页,每次1w数据
        $page_size = 1000;
        $page = intval($_POST['page']);
        $oMemberAnalysis = &app::get('taocrm')->model('member_analysis');
        $oMemberGroup = &app::get('taocrm')->model('member_group');
        $group_filter = $this->get_active_filter($active_id);
        
        if(!$group_filter){//获取过滤条件
            $mem_filter=$active_obj->dump(array('active_id'=>$active_id));
            $filter=unserialize($mem_filter['filter_mem']);
            $group_filter = $oMemberGroup->buildFilter($filter['filter'],$mem_filter['shop_id']);
        }
        
        unset($memdata);
        $rs = $oMemberAnalysis->getList('member_id',$group_filter,$page*$page_size,$page_size); 
        if(!$rs) die();
        foreach($rs as $v){
            $memdata[] = $v['member_id'];
        }
        unset($rs);
        //var_dump($memdata);die();
        //error_log(var_export($memdata,1),3,'11111111111111111111.php');
            
        //从客户表中，取出所选客户的条件
        $mem_filter=$active_obj->dump(array('active_id'=>$active_id));
        if ($mem_filter['control_group']=='no'){
            if (!empty($mem_filter['member_list'])){
                if(!$memdata) $memdata=unserialize($mem_filter['member_list']);
                $this->unconrol_aessess($active_id,$memdata,$msgid);
            }else {
                $filter=unserialize($mem_filter['filter_mem']);
                if(!$memdata) $memdata=$this->select_member($filter);//选择发送的客户数
                $this->unconrol_aessess($active_id,$memdata,$msgid);
            }
            
        }elseif($mem_filter['control_group']=='yes') {//开启对照组
            if (!empty($mem_filter['member_list'])){
                $memdata1=unserialize($mem_filter['member_list']);
                $this->control_group($active_id,$memdata1,$msgid);
                $memdata=$this->send_members_data($memdata1);
            }else {
                $filter=unserialize($mem_filter['filter_mem']);
                $memdata1=$this->select_member($filter);//选择发送的客户数
                $this->control_group($active_id,$memdata1,$msgid);
                $memdata=$this->send_members_data($memdata1);
            }
        }
        if ($mem_filter['type']=="edm"){
            $this->edm_send_fn($active_id, $memdata,$msgid);
        }
    }

    public function send_members_data($memdata){
		$lengh=(count($memdata)/2);
		if ( ceil($lengh) != $lengh) {
			$lengh=ceil($lengh);
		}
		$memdata=array_slice($memdata,0,$lengh);
		return $memdata;
    }
    
	//对照组
	public function control_group($active_id,$members ,$msgid=""){
		$acassess_obj = &app::get('market')->model('active_assess');
		$active_obj = &app::get('market')->model('active');
		$active_dat=$active_obj->dump(array('active_id'=>$active_id));
		$extime=$active_dat['sent_time']?$active_dat['sent_time']:time();
		$lengh=(count($members)/2);
		if ( ceil($lengh) != $lengh) {
			$lengh=ceil($lengh);
		}
		$conactiveids=serialize(array_slice($members,0,$lengh));
		$noactiveids=serialize(array_slice($members,$lengh));
		if (empty($msgid)){$status='finish';}else {$status='unfinish';};
		$assess_array=array(
			'active_id'=>$active_id,
			'active_name'=>$active_dat['active_name'],
			'active_members'=>$conactiveids,
			'shop_id'=>$active_dat['shop_id'],
			'con_members'=>$noactiveids,
			'create_time'=>$active_dat['create_time'],
			'end_time'=>$active_dat['end_time'],
			'exec_time'=>$extime,
			'msgid'=>$msgid,
			'state'=>$status,
		);
		$acassess_obj->save($assess_array);
	}

	//无对照组的活动评估
	public function unconrol_aessess($active_id,$data,$msgid=''){
    
		$acassess_obj = &app::get('market')->model('active_assess');
		$active_obj = &app::get('market')->model('active');
		$active_dat=$active_obj->dump(array('active_id'=>$active_id));
		$extime=$active_dat['sent_time']?$active_dat['sent_time']:time();
		if (empty($msgid)){$status='finish';}else {$status='unfinish';};
		$assess_array=array(
			'active_id'=>$active_id,
			'active_name'=>$active_dat['active_name'],
			'active_members'=>serialize($data),
			'shop_id'=>$active_dat['shop_id'],
			'con_members'=>"",
			'create_time'=>$active_dat['create_time'],
			'end_time'=>$active_dat['end_time'],
			'exec_time'=>$extime,
			'msgid'=>$msgid,
			'state'=>$status,
		);
		$acassess_obj->save($assess_array);
	}

	//邮件发送
    public function edm_send_fn($active_id,$memlistdata,$msgid=""){
        base_kvstore::instance('market')->fetch('account', $arr);
        $arr= unserialize($arr);
        $shopRs = &app::get('ecorder')->model('shop');
        $members_obj = &app::get('taocrm')->model('members');
        $templates_obj = &app::get('market')->model('edm_templates');
        $edm_obj = &app::get('market')->model('edm');
        $active_obj = &app::get('market')->model('active');
        $edmlog_obj = &app::get('market')->model('edm_log');
        $active_data=$active_obj->dump(array('active_id'=>$active_id));
        $plant_sent_time=$active_data['sent_time']?$active_data['sent_time']:"";
        $create_time=$active_data['create_time']?$active_data['create_time']:"";
        $edm_content=$templates_obj->dump(array('template_id'=>$active_data['template_id']));
        $shop_name=$shopRs->dump(array('shop_id'=>$active_data['shop_id']),'name');
        
		$edm_array=array();
		$edm_array['template_id']=$active_data['template_id'];
		$edm_array['active_name']=$active_data['active_name'];
		$edm_array['active_id']=$active_id;
		$edm_array['edm_type']='active';
		$edm_array['create_time']=time();
		$edm_array['plan_send_time']=$plant_sent_time;
		$edm_array['total_num']=count($memlistdata);
		$edm_array['shop_id']=$active_data['shop_id'];
		$edm_array['success_num']=0;//shop_id
		$edm_array['is_send']='unsend';
		$edm_rs=$edm_obj->save($edm_array);
		

		$edm_id=$edm_obj->dump(array('active_id'=>$active_id),'edm_id');
		//用户的邮件配置状态
		$jobarray=array();
		$jobarray['plan_send_time']=$plant_sent_time;//计划发送时间
		$jobarray['batch_no']=$msgid;
		$jobarray['edm_id']=$edm_id['edm_id'];
		$jobarray['active_id']=$active_id;
		$jobarray['shop_id']=$active_data['shop_id'];
		
		$jobarray['edm_config']['entid']=$arr['entid'];
		$jobarray['edm_config']['password']=$arr['password'];
		$jobarray['edm_config']['source']=APP_SOURCE;//app_token
		$jobarray['edm_config']['app_token']=APP_TOKEN;
		$jobarray['edm_config']['license']=base_certificate::get('certificate_id') ? base_certificate::get('certificate_id') : 1;
		$n=1;
		$page_size = 200;
		$pages = count($memlistdata)/$page_size;
		for($i=0;$i<=$pages;$i++){
			$member_rows = array_slice($memlistdata,($i*$page_size),$page_size);
			if(!$member_rows) break;
			$edm_list = array();
			foreach ($member_rows as $key=>$value) {
				$mobile=$members_obj->dump(array('member_id'=>$value),'mobile,uname');
				$save_data = array();
				$save_data['member_id']=$value;
				$save_data1['uname']=$mobile['account']['uname'];
				$save_data['phones']=$mobile['contact']['phone']['mobile'];//程序用
				$msgContent = str_replace(array('<{用户名}>','<{店铺}>'), array($mobile['account']['uname'],$shop_name['name']),$edm_content['content']);
				$save_data['content']=$msgContent;
				$edm_list[]=$save_data;
			}
            $jobarray['edm_list'] = $edm_list;
            $jobarray['edm_batch_no'] = $edm_id['edm_id']."_".$n;
            kernel::single('taocrm_service_gearman')->addJob('edm_send',$jobarray);
            $n++; 
		}
	}
	
	//邮件发送任务
	public function importQueue($fileName,$data){
		if($data){
			$queueObj = app::get('base')->model('queue');
			base_kvstore::instance('market_edm')->store($fileName,serialize($data));
			$queueData = array(
                'queue_title'=>'邮件发送',
                'start_time'=>time(),
                'params'=>array(
                    'app' => 'market',
                    'mdl' => 'edm',
                    'file_name' => $fileName
			),
                'worker'=>'market_edm_send.edm_send',
			);
			$queueObj->save($queueData);
			return true;
		}else{
			return false;
		}
	}

	//优惠券发送
	public function coupon_send_fn($active_id,$memdata){
    
        $page_size = 100;//优惠券每次只发送100张
        
        $db = kernel::database();
		$mem_obj = app::get('taocrm')->model('members');

		$coupons_obj = &app::get('market')->model('coupons');
		$active_data = $db->selectRow("select * from sdb_market_active where active_id = $active_id ");
        if(!$active_data) return false;
		$coupon_id=$active_data['coupon_id'];
		$shop_id = $active_data['shop_id'];
        
		//$coupon_name=$coupons_obj->dump(array('coupon_id'=>$coupon_id),'coupon_name');
		$memlist_array=array('member_id|in'=>$memdata);
		$memlist_uname=$mem_obj->getList("uname",$memlist_array);
		$n=1;
		foreach ($memlist_uname as $k=>$v){
            $uname_arr[] = $v['uname'];
		}
        
        // 分批次发送邮件
        for($i=0;$i<=(sizeof($uname_arr)/$page_size);$i++){            
        
            $content = array_slice($uname_arr,($i*$page_size),$page_size);
            if(!$content) break;
            
            $coupon_array=array(
                'shop_id'=>$shop_id,
                'coupon_id'=>$coupon_id,
                'buyer_nick'=>$content
            );
           //$filter_name='market_coupon_'.$n.'_'.$active_id;
            $this->coupon_send_queue($coupon_array);
            
        }
        unset($coupon_array,$uname_arr);
        return true;
    }
    
    
    
    //优惠券发送任务
	public function coupon_send_queue(&$data){
        
        $shopInfo = app::get('ecorder')->model('shop')->dump(array('shop_id'=>$data['shop_id']),'*');
        if(!$shopInfo['addon'] || empty($shopInfo['addon']['session'])){
            return false;
        }

        $jobarray = array(
            'order_id'=>$data['order_id'],
            'shop_id'=>$data['shop_id'],
            'coupon_id'=>$data['coupon_id'],
            'buyer_nick'=>$data['buyer_nick'],
            'session'=>$shopInfo['addon']['session']
        );
        kernel::single('taocrm_service_gearman')->addJob('coupon_send',$jobarray);
        return true;
    }

	//优惠券发送任务
    /*
	public function coupon_importQueue($fileName,$data){
		if($data){
			$queueObj = app::get('base')->model('queue');
			base_kvstore::instance('market_edm')->store($fileName,serialize($data));
			$queueData = array(
                'queue_title'=>'优惠券发送',
                'start_time'=>time(),
                'params'=>array(
                    'app' => 'market',
                    'mdl' => 'edm',
                    'file_name' => $fileName
			),
                'worker'=>'market_coupon_send.coupon_send',
			);
			$queueObj->save($queueData);
			return true;
		}else{
			return false;
		}
	}
    */
    
}
