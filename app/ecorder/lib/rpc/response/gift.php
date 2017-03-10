<?php
class ecorder_rpc_response_gift extends ecorder_rpc_response{

    private $api_url = MATRIX_SYNC_URL_M;
    private static $shopGiftObj = null;
    private static $http = null;
    private static $token = null;

    /**
     * x001		CRM参数不完整
     * x002		CRM对应的客户信息不存在
     * x003		CRM该客户没有设置相应的赠品
     * x004		CRM客户等级未设置，赠品获取失败
     * 4003		sign error
     */
    function get($sdf,&$responseObj)
    {
        //全局API日志
        $log_mdl = app::get('ecorder')->model('api_log');
        $logTitle = 'ERP赠品接口['.$sdf['order_bn'].']';
        $logInfo = '订单赠品接口：<BR>';
        $logInfo .= '请求参数 $sdf 信息：' . var_export($sdf, true) . '<BR>';
        $reason = '';

        $pay_time = floatval($sdf['pay_time']);
        $createtime = floatval($sdf['createtime']);
    
        $shop_name = $sdf['shop_name'];
        $addon = $sdf['addon'];
        $buyer_nick = $sdf['buyer_nick'];
        $receiver_name = $sdf['receiver_name'];
        $mobile = $sdf['mobile'];
        $tel = $sdf['tel'];
        $order_bn = $sdf['order_bn'];
        $payed = floatval($sdf['payed']);
        $province = $sdf['province'];
        $unique_node = $sdf['unique_node'];
        $is_cod = intval($sdf['is_cod']);
        $order_items = json_decode($sdf['items'], true);
        $lv_id = 0;
        $total_paid = 0;
        $is_send_gift = intval($sdf['is_send_gift']);//强制重新发送赠品标志位
        
        if($is_cod==1 or $payed==0){
            $logInfo .= '不处理货到付款和未付款订单';
            $log_mdl->write_log($log_mdl->gen_id(), $logTitle, __CLASS__, __METHOD__, '', '', 'response', 'fail', $logInfo, array('task_id'=>$sdf['order_bn']));            
            $responseObj->send_user_error(app::get('base')->_('x001'), '不处理货到付款和未付款订单');
        }
        
        //记录赠品发送日志,ERP已经废弃该接口
        if($addon){
            $addon = json_decode($addon, true);
            if($addon['func'] == 'log'){
                $res = $this->log($sdf, $responseObj);
                return $res;
            }
        }

        if(!$buyer_nick && !$receiver_name){
            $logInfo .= '买家帐号或收货人不能同时为空';
            $log_mdl->write_log($log_mdl->gen_id(), $logTitle, __CLASS__, __METHOD__, '', '', 'response', 'fail', $logInfo, array('task_id'=>$sdf['order_bn']));
            $responseObj->send_user_error(app::get('base')->_('x001'), '买家帐号或收货人不能同时为空');
        }
        
        //redis防止并发
        $redis = kernel::single('taocrm_service_redis')->redis;
        if($redis){
            //只保存最近一分钟的数据
            $r_key = 'gift_order:';
            
            if($redis->sIsMember($r_key.date('i'), $order_bn)){
                $logInfo .= '同订单号每分钟只能请求一次';
                $log_mdl->write_log($log_mdl->gen_id(), $logTitle, __CLASS__, __METHOD__, '', '', 'response', 'fail', $logInfo, array('task_id'=>$sdf['order_bn']));
                $responseObj->send_user_error(app::get('base')->_('x001'), '同订单号每分钟只能请求一次');
            }
            
            $redis->sAdd($r_key.date('i'), $order_bn);
            $redis->setTimeout($r_key.date('i'), 120);//设置过期时间
        }
        
        //查询赠品日志，已经送过的订单号不送第二次，除非  is_send_gift =1   
        if($is_send_gift == 0){
            $rs = app::get('ecorder')->model('gift_logs')->dump(array('order_bn'=>$order_bn), 'id');
            if($rs){
                $logInfo .= '订单号'.$order_bn.'不能重复赠送';
                $log_mdl->write_log($log_mdl->gen_id(), $logTitle, __CLASS__, __METHOD__, '', '', 'response', 'fail', $logInfo, array('task_id'=>$sdf['order_bn']));
                $responseObj->send_user_error(app::get('base')->_('x001'), '订单号'.$order_bn.'不能重复赠送');
                exit;
            }
        }
        
        $shopObj = app::get('ecorder')->model('shop');
        $shop = $shopObj->dump(array('node_id'=>$sdf['unique_node']),'shop_id,node_type');
        $shop_id = $shop['shop_id'];
        
        $sql = "select a.member_id,b.lv_id from sdb_taocrm_members as a 
                left join sdb_taocrm_member_analysis as b on a.member_id=b.member_id
                where 1=1 ";
        if($buyer_nick && $shop['node_type'] == 'taobao'){
            $sql .= " and a.uname='".$buyer_nick."'  ";
        }else if($receiver_name){
            $sql .= " and a.name='".$receiver_name."' ";
        }
            
        if($mobile){
            $sql .= " and a.mobile='".$mobile."' ";
        }else if($tel){
            $sql .= " and a.tel='".$tel."' ";
        }

        $rs_member = $shopObj->db->selectrow($sql);
        
        //会员等级 和 会员购买金额
        if($rs_member){
            $lv_id = $rs_member['lv_id'];
            
            /*
            $sql = "select sum(payed) as total_paid from sdb_ecorder_orders where member_id=".$rs_member['member_id']." and shop_id='".$shop_id."' and status in ('active','finish') and pay_status='1' ";
            $rs_member_order = $shopObj->db->selectrow($sql);
            $total_paid = $rs_member_order['total_paid'];
            */
            $total_paid = $payed;
        }

        //查询是否存在有效规则
        $time = time();
        $sql = "select * from sdb_ecorder_gift_rule 
                where status = '1' order by priority DESC,id DESC";
        $data = $shopObj->db->select($sql);
        if($data){
        
            //多规则的兼容模式:叠加或者互斥，默认为叠加
            $sql = "select set_type from sdb_ecorder_gift_set_logs order by id DESC";
            $rs_set_type = $shopObj->db->selectRow($sql);
            if($rs_set_type){
                $set_type = $rs_set_type['set_type'];
            }else{
                $set_type = 'include';
            }
        
            $gift_bns = array();//需要发送到erp的赠品列表
            $gift_ids = '0';
            $gift_num = '0';
            $gift_send_log = array();//记录赠品发送日志
           
            //检测是否符合赠送条件
            foreach($data as $rule){

                //互斥排他模式下，退出循环
                if($gift_ids && $gift_ids != '0' && $set_type=='exclude'){
                    break;
                }
            
                //检测时间有效期
                if($rule['time_type']=='createtime'){
                    if($createtime>$rule['end_time'] or $createtime<$rule['start_time']) continue;
                }elseif($rule['time_type']=='pay_time'){
                    if($pay_time>$rule['end_time'] or $pay_time<$rule['start_time']) continue;
                }else{
                    if($time>$rule['end_time'] or $time<$rule['start_time']) continue;
                }
            
                //赠品判断条件
                $rule['filter_arr'] = json_decode($rule['filter_arr'], true);
                
                if(!$rule['gift_ids']){
                    $reason = '没有设定赠品';
                    continue;
                }elseif($rule['shop_id'] && $rule['shop_id']!=$shop_id){
                    $reason = '不符合指定店铺';
                    continue;
                }elseif($rule['lv_id'] && $rule['lv_id']!=$lv_id){
                    $reason = '会员等级不符合';
                    continue;
                }
                
                if($rule['filter_arr']['order_amount']['type']==1){
                    if($rule['filter_arr']['order_amount']['sign']=='bthan'){
                        if($payed<$rule['filter_arr']['order_amount']['max_num']){
                            $reason = '不满足最低付款';
                            continue;
                        }
                    }else{
                        if($payed<$rule['filter_arr']['order_amount']['min_num'] or $payed>$rule['filter_arr']['order_amount']['max_num']){
                            $reason = '不满足付款区间';
                            continue;
                        }
                    }
                }
                if($rule['filter_arr']['order_amount']['type']==2){
                    if($rule['filter_arr']['order_amount']['sign']=='bthan'){
                        if($total_paid<$rule['filter_arr']['order_amount']['max_num']){
                            continue;//累计不满足最低付款
                        }
                    }else{
                        if($total_paid<$rule['filter_arr']['order_amount']['min_num'] or $total_paid>$rule['filter_arr']['order_amount']['max_num']){
                            continue;//累计不满足付款区间
                        }
                    }
                }
                
                //限量赠送
                if($rule['filter_arr']['buy_goods']['limit_type']==1){
                                //判断已经送出的订单数
                                $sql = "select count(distinct order_bn) as total_orders from sdb_ecorder_gift_logs where gift_rule_id=".$rule['id']." ";
                                $rs_temp = $shopObj->db->selectRow($sql);
                                if($rs_temp) {
                        if($rs_temp['total_orders'] >= $rule['filter_arr']['buy_goods']['limit_orders']){
                            $reason = '超过送出数量限制';
                            continue;
                        }
                    }
                }
                                        
                //购买指定商品的数量或金额
                $has_buy = false;
                $item_nums = $this->get_buy_goods_num($rule, $order_items, $has_buy);
                if($rule['filter_arr']['buy_goods']['count_type'] == 'paid'){
                    $item_nums = $payed;
                }
                
                if($has_buy == false){
                    $reason = '不符合指定商品购买条件';
                    continue;
                }
                
                //计算赠品数量
                if($item_nums>0 && $rule['filter_arr']['buy_goods']['num_rule']=='auto'){
                    $ratio = intval($item_nums/$rule['filter_arr']['buy_goods']['per_num']);
                                $suite = $rule['filter_arr']['buy_goods']['send_suite']*$ratio;
                                $suite = min($suite, $rule['filter_arr']['buy_goods']['max_send_suite']);
                                if($suite >= 1){
                                    //数量倍数
                                    $temp_arr = explode(',', $rule['gift_num']);
                                    foreach($temp_arr as $k=>$v){
                                        $temp_arr[$k] = $v * $suite;
                                    }
                                    $temp_arr = implode(',', $temp_arr);
                                }elseif($suite == 0){
                                    //数量不符合要求
                                    $reason = '不符合商品数量购买条件';
                                    continue;
                                }
                                
                            $gift_ids .= ','.$rule['gift_ids'];
                                $gift_num .= ','.$temp_arr;
                                
                                $gift_send_log[] = array(
                                    'gift_rule_id' => $rule['id'],
                                    'gift_ids' => explode(',', $rule['gift_ids']),
                                    'gift_num' => explode(',', $temp_arr)
                                );
                                continue;
                }elseif($item_nums>0){
                                if($rule['filter_arr']['buy_goods']['rules_sign']=='nequal'){
                        if($item_nums!=$rule['filter_arr']['buy_goods']['min_num']){
                                        $reason = '不等于指定数量';
                                        continue;
                        }
                                }elseif($rule['filter_arr']['buy_goods']['rules_sign']=='between'){
                        if($item_nums<$rule['filter_arr']['buy_goods']['min_num'] or $item_nums>=$rule['filter_arr']['buy_goods']['max_num']){
                                        $reason = '不在数量范围内';
                                        continue;
                    }
                }else{
                        if($item_nums<$rule['filter_arr']['buy_goods']['min_num']){
                                        $reason = '小于指定数量';
                                        continue;
                                    }
                                }
                    $gift_ids .= ','.$rule['gift_ids'];
                                $gift_num .= ','.$rule['gift_num'];
                                
                                $gift_send_log[] = array(
                                    'gift_rule_id' => $rule['id'],
                                    'gift_ids' => explode(',', $rule['gift_ids']),
                                    'gift_num' => explode(',', $rule['gift_num'])
                                );
                                continue;
                }               
            }           
            
            //如果符合条件，添加赠送日志
            if($gift_ids && $gift_ids != '0'){
                $gift_bns = array();
                $gifts = array();
            
                //获取每个赠品id对应的数量
                $gift_id_arr = explode(',', $gift_ids);
                $gift_num_arr = explode(',', $gift_num);
                foreach($gift_id_arr as $k=>$v){
                    if(!isset($gift_id_num[$v])) $gift_id_num[$v] = 0;
                
                    if(intval($gift_num_arr[$k])==0){
                        $gift_id_num[$v] += 1;
                    }else{
                        $gift_id_num[$v] += intval($gift_num_arr[$k]);
                    }
                }
            
                $err_msg = '';
                $rs = app::get('ecorder')->model('shop_gift')->getList('id,gift_bn,gift_name,gift_num',array('id'=>$gift_id_arr));
                foreach($rs as $v){
                    if($v['gift_num'] <= 0) {
                        $err_msg .= $v['gift_bn'].'库存不足;';
                        $reason = '赠品库存不足';
                        continue;
                    }
                
                    $rs_gifts[$v['id']] = $v;
                    $gift_num = $gift_id_num[$v['id']];
                
                    $gift_bns[] = $v['gift_bn'];
                    $gifts[$v['gift_bn']] = $gift_num;

                    //扣减库存
                    $sql = "update sdb_ecorder_shop_gift set gift_num=gift_num-".$gift_num.",send_num=send_num+".$gift_num." where id=".$v['id'];
                    $shopObj->db->exec($sql);
                }
                
                //返回erp的发货数据
                $return = array(
                    'm_level'=>$lv_id,
                    'order_bn'=>$order_bn,
                    'gifts'=>$gifts,
                    'gift_bn'=>implode(',', $gift_bns)
                );
                if($err_msg){
                    $return['err_msg'] = $err_msg;
                }

                    //记录赠品发送日志
                $create_time = time();
                $m_gift_logs = app::get('ecorder')->model('gift_logs');
                foreach($gift_send_log as $v){
                    foreach($v['gift_ids'] as $kk=>$vv){
                    
                        //跳过库存为 0 的赠品
                        if(!isset($rs_gifts[$vv])) {
                            $reason = '赠品库存不足';
                            continue;
                        }
                        
                        $md5_key = md5($order_bn.$rs_gifts[$vv]['gift_bn'].$v['gift_rule_id'].$create_time);
                        $log_arr = array(
                            'order_source'=>$shop_name,
                        'order_bn'=>$order_bn,
                        'buyer_account'=>$buyer_nick,
                        'shop_id'=>$shop_id,
                        'paid_amount'=>$payed,
                            'gift_num'=>$v['gift_num'][$kk],
                            'gift_rule_id'=>$v['gift_rule_id'],
                            'gift_bn'=>$rs_gifts[$vv]['gift_bn'],
                            'gift_name'=>$rs_gifts[$vv]['gift_name'],
                            'create_time'=>$create_time,
                            'md5_key'=>$md5_key,
                        'status'=>0,
                    );
                        $q = $m_gift_logs->save($log_arr);
                        if ( ! $q){
                            $sql = "update sdb_ecorder_gift_logs set gift_num=gift_num+".$v['gift_num'][$kk]." where md5_key='$md5_key' ";
                            $m_gift_logs->db->exec($sql);
                        }
                        
                        /*
                        if(!$q){
                            $logInfo .= '同订单号每分钟只能请求一次';
                            $log_mdl->write_log($log_mdl->gen_id(), $logTitle, __CLASS__, __METHOD__, '', '', 'response', 'fail', $logInfo);
                            $responseObj->send_user_error(app::get('base')->_('x001'), '同订单号每分钟只能请求一次');
                            exit;
                        }
                        */
                    }
                }
                
                if(!$gift_send_log){
                    $logInfo .= '赠品为空'.'<br/>'.$reason;
                    $log_mdl->write_log($log_mdl->gen_id(), $logTitle, __CLASS__, __METHOD__, '', '', 'response', 'fail', $logInfo, array('task_id'=>$sdf['order_bn']));
                    return array();
                }
                
                $logInfo .= '返回参数：' . var_export($return, true) . '<BR>';
                $log_mdl->write_log($log_mdl->gen_id(), $logTitle, __CLASS__, __METHOD__, '', '', 'response', 'success', $logInfo, array('task_id'=>$sdf['order_bn']));
                return $return;
            }
            
            $logInfo .= '赠品为空'.'<br/>'.$reason;
            $log_mdl->write_log($log_mdl->gen_id(), $logTitle, __CLASS__, __METHOD__, '', '', 'response', 'fail', $logInfo, array('task_id'=>$sdf['order_bn']));
            return array();	            
        }else{
            $responseObj->send_user_error(app::get('base')->_('x004'), '不存在有效的促销规则，赠品获取失败');
        } 
    }
    
    //有效的商品数量
    private function get_buy_goods_num($rule, $order_items, &$has_buy)
    {
        $item_nums = 0;
        $item_num_arr = array();
        $item_bns = array();
        $buy_goods_bns = $rule['filter_arr']['buy_goods']['goods_bn'];
        $count_type = $rule['filter_arr']['buy_goods']['count_type'];//num or sku
        if( ! is_array($buy_goods_bns)){
            $buy_goods_bns = array(strtoupper($buy_goods_bns));
            }
            
        //清理空数据
        $buy_goods_bns = kernel::single('ecorder_func')->clear_value($buy_goods_bns);
            
        foreach($order_items as $item){
        
            $item['bn'] = strtoupper($item['bn']);
            
            if($rule['filter_arr']['buy_goods']['type']==1){
                if($rule['filter_arr']['buy_goods']['buy_type'] == 'all'){
                    //购买了全部指定商品
                    if( ! in_array($item['bn'], $item_bns)) $item_bns[] = $item['bn'];
                    if(in_array($item['bn'], $buy_goods_bns)){
                        $item_num_arr[$item['bn']] = intval($item_num_arr[$item['bn']]) + $item['nums'];
                        unset($buy_goods_bns[array_search($item['bn'], $buy_goods_bns)]);
                    }
                }elseif($rule['filter_arr']['buy_goods']['buy_type'] == 'none'){
                    //排除购买的指定商品
                    if( ! in_array($item['bn'], $buy_goods_bns)){
                        $item_nums += $item['nums'];
                        if( ! in_array($item['bn'], $item_bns)) $item_bns[] = $item['bn'];
                        $has_buy = true;
                    }
                }else{
                //}else($rule['filter_arr']['buy_goods']['buy_type'] == 'any'){
                    //购买了任意一个指定商品
                    if(in_array($item['bn'], $buy_goods_bns)){
                        $item_nums += $item['nums'];     
                        if( ! in_array($item['bn'], $item_bns)) $item_bns[] = $item['bn'];
                        $has_buy = true;
                    }
                }
        }else{
                $item_nums += $item['nums'];
                $item_num_arr[$item['bn']] = intval($item_num_arr[$item['bn']]) + $item['nums'];
                $has_buy = true;
            }
        }
        
        //购买了全部指定商品，数量以最少的为准
        if($rule['filter_arr']['buy_goods']['type']==1){
            if($rule['filter_arr']['buy_goods']['buy_type'] == 'all'){
                if( ! $buy_goods_bns){
                    $item_nums = min($item_num_arr);
                    $has_buy = true;
                }
            }
        }
        
        if($count_type == 'sku' && $has_buy === true){
            $item_nums = count($item_bns);
        }
        
        return $item_nums;
    }

    function log($sdf,&$responseObj)
    {        
        $addon = json_decode($sdf['addon'], true);
        $params = $addon['params'];

		$order_bn = $sdf['order_bn'];
		if(!$order_bn){
			$responseObj->send_user_error(app::get('base')->_('x001'), 'CRM参数不完整');
		}
        
        //更新赠品发送日志
        $m_gift_logs = app::get('ecorder')->model('gift_logs');
        foreach($params as $item){
            $data = array(
                'status' => 1,
                'send_num' => ceil($item['nums']),
                'update_time' => time(),                
            );
            $filter = array(
                'order_bn' => $order_bn,
                'gift_bn' => $item['bn'],                
            );
            $m_gift_logs->update($data, $filter);
        }
        return array('res'=>'succ');
	}
	
	private function sign($params,$token='BS-CRM'){
        //return $this->make_sign_matrix($params);
		return strtoupper(md5(strtoupper(md5($this->assemble($params))).$token));
	}
	
	private function assemble($params){
		if(!is_array($params)){
			return null;
		}
	
		ksort($params,SORT_STRING);
		$sign = '';
		foreach($params AS $key=>$val){
			$sign .= $key . (is_array($val) ? $this->assemble($val) : $val);
		}
		return $sign;
	}
    
    function make_sign_matrix($params){
        ksort($params);
        $query = '';
        foreach($params as $k=>$v){
            $query .= $k.'='.$v.'&';
        }
         
        return md5(substr($query,0,strlen($query)-1).base_certificate::get('token'));
    }
	
}