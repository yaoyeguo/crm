<?php
/**
 *=================================
 *订单归属处理类
 *=================================
 */
class ecorder_service_incremental{
    /**
	   *计算订单的归属(订单数据在插入order表的时候调用本方法)
	   *@param $order Array订单数据
	   */
    function getOrderIncremental($order = array()){ 
		$orderMdl = &app::get('ecorder')->model('orders');//订单表
		$sessionMdl = kernel::single('taobukpi_mdl_session');//客服会话表
		$accountMdl = kernel::single('pam_mdl_account');//客服id对应表
		$paymentsMdl = kernel::single('ecorder_mdl_payments');//订单支付表
    $customerMdl = kernel::single('taobukpi_mdl_customer');//客户旺旺id对应表
    $orderqueMdl = kernel::single('taobukpi_mdl_order_queue');//订单队列表
    $sessOrderMdl = kernel::single('taobukpi_mdl_session_order');//会话与订单的关系表   

		//通过order表中的order_bn得到order_id	 
		$order_id = $orderMdl->dump(array('order_bn'=>$order['order_bn']),'order_id');
		//通过order_id得到payments表中的支付账户
    $pay_account = $paymentsMdl->dump($order_id,'pay_account');
		//通过支付账户去customer表中得到customer_id
		$customer_id = $customerMdl->dump(array('customer_ww_nick'=>$pay_account['pay_account']),'customer_id');
		$orderRemark=array();//订单备注
		//将备注信息反序列化(注：一个订单编号只能对应一个订单备注)
    $orderRemark=unserialize($order['mark_text']);
		$name = $this->getName($orderRemark[0]['op_content']);//客服名称
		
		 //TODO 从数据库中取出用户选择的配置方式  根据用户选择的配置方式进行不同的处理
	    if($name)//订单备注规则
		  {     
		       $cf_id = $accountMdl->dump(array('login_name'=>$name),'account_id');//通过客服名称得到客服对应ID
			     //去对话表中查找是否有对应的客服与客户旺旺号
		       $is_true = $sessionMdl->dump(array('ww_s_id'=>$cf_id['account_id'],'ww_c_id'=>$customer_id['customer_id']),'ww_c_id,id');
				   //如果没找到对应对话，则置为”异常订单“
			     if (empty($is_true)){
					    //异常订单
					    $this->save($order_id['order_id'],$cf_id['account_id'],'',0,0,'');
				   }else{
					    //正常订单
					    $this->save($order_id['order_id'],$cf_id['account_id'],$is_true['id'],1,0,'');
				   }

	      }//endif
		else
		  {//通过旺旺聊天记录判断订单归属
        //得到该订单客户旺旺与客服聊天列表
			  $ww_s_id = $sessionMdl->getList('ww_s_id,id',array('ww_c_id'=>$customer_id['customer_id']),0,-1,'end_time desc');
			  //判断该订单对应旺旺是否与客服聊过天
        if (!empty($ww_s_id)){
        	      $this->save($order_id['order_id'],$ww_s_id[0]['ww_s_id'],$ww_s_id[0]['id'],1,0,'');
        	      
        	      
			     }else{
			     	    $max_time = $sessionMdl->getMaxEndtime();//session表中最大结束时间
			     	    
                //如果该客户没有跟客服聊过，则判断订单创建时间是否大于聊天最后时间  
               if ($order['createtime']>$max_time[0]['maxtime']){
                	//如果订单创建时间大约end_time最大值怎讲order_id和createtime插入队列
                	   //先判断队列表中是否有该订单，不能重复插入
                	   if ($orderqueMdl->dump($order_id)){
                		      echo  "cannot repeat insert order_id:".$order_id['order_id'];
                		     }else{
                		     	 $data = array(
                                   'order_id'=>$order_id['order_id'],
                                   'create_time'=>$order['createtime'],
                                   );
                           //插入队列
                	         $orderqueMdl->insert($data);
                		     	}
                	
                }else{
                	//@TODO 加入后续的判断
                   return "unusual order!";
                	}  
                
               
			     }
		  }
    }//endfun 
    
    /**
     *去掉订单备注的左右标识符
     *@return string
     *@param $des 待处理的字符串
     */
    function getName($des){
    	
		  //左右标识符
      $logo=$this->getCfg();
		  if($logo){
            $logo['left'] = preg_quote($logo['left']);
            $logo['right'] = preg_quote($logo['right']);
            $preg = "((?<=".$logo['left'].")([^".$logo['left']."]*)(?=".$logo['right']."))";
            preg_match($preg,$des,$name);
            return isset($name[0])?$name[0]:null;
        }else{
            return null;
        }
		
    }
    /**
     *得到规则配置表里的配置信息
     */
    function getCfg(){
		  $cfgInfoMdl = kernel::single("taobukpi_mdl_rolescfg");//规则配置表
		  $cfginfo = $cfgInfoMdl->getList(' left_mark,right_mark ');
		  $left = $cfginfo[0]['left_mark'];//左标识
		  $right = $cfginfo[0]['right_mark'];//右标识
        if(empty($left) || empty($right)){
			      //TODO 将默认标识写入config文件
            return array('left'=>'[-','right'=>'-]');
        }else{
            return array('left'=>"$left",'right'=>"$right");
        }

    }//endfun
    
    /**
     *保存订单ID、客服ID
     *@return unknown
     *@param $order_id 订单号
     *@param $ww_s_id 旺旺号
     *@param $session_id 会话号
     *@param $o_status 是否异常订单 0异常 1正常
     *@param $percent 百分比
     *@param $o_mark 异常订单描述
     */
    function save($order_id,$ww_s_id,$session_id,$o_status,$percent,$o_mark){
    	    $sessOrderMdl = kernel::single('taobukpi_mdl_session_order');//会话与订单的关系表 
    	    //查看客服订单表是否存在该订单
        	if ($sessOrderMdl->dump(array('order_id'=>$order_id))){
        	      	  echo  "cannot repeat insert order_id:".$order_id;
        	    }else{
        	          //假设矩阵是OK的，则按下单前最近接触的客服
        	      		$data = array(
        	               'id'=>'',
        	               'order_id'=>$order_id,
        	               'ww_s_id'=>$ww_s_id,
        	               'session_id'=>$session_id,
        	               'o_status'=>$o_status,//是否异常订单
        	               'percent'=>$percent,//百分比
        	               'o_mark'=>$o_mark,//异常描述
        	               );
        	             //  print_r($data);exit;
        	          $sessOrderMdl->insert($data);
        	      		}
    	}

}//endcls
?>