<?php 
class market_mdl_active_assess extends dbeav_model {

    /*
    public function modifier_create_time($row)
    {
        $date = date("Y-m-d",$row);
        return $date ;
    }*/
    
    public function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderby=null)
    {
        $data = parent::getList($cols, $filter, $offset, $limit, $orderby);
        $img_url = '<img src="'.kernel::base_url(1).'/app/market/statics/smile.gif" /> ';
        $img_url_black = '<img src="'.kernel::base_url(1).'/app/market/statics/smile_black.gif" /> ';
        
        //$taocrm_middleware_connect = kernel::single('taocrm_middleware_connect');
        $active_model = app::get('market')->model('active');
        //echo('<pre>');var_dump($data);
        
        //获取参加活动的总人数        
        foreach($data as $k=>$v){
            $active_ids[] = $v['_0_active_id'];
        }
        if($active_ids){
            $rs_active = $active_model->getList('active_id,valid_num', array('active_id'=>$active_ids));
            if($rs_active){
                foreach($rs_active as $v){
                    $valid_num[$v['active_id']] = $v['valid_num'];
                }
            }
        }
        
        // 处理内存计算数据
        $time_diff = time() - 60*60*6; // 6小时内只显示缓存数据
        foreach($data as $k=>$v){
        
            //如果不是从finder过来，不执行操作
            if(!isset($v['_0_active_id'])) break;
        
            $data[$k]['total_members'] = intval($v['_0_total_members']);
            if(!$data[$k]['total_members']){
                $data[$k]['total_members'] = intval($valid_num[$v['_0_active_id']]);
            }
            //$v['data_update_time'] = intval($v['_0_data_update_time']);
        
            /*
            $rs_active = $active_model->dump($v['_0_active_id'], 'shop_id,exec_time');
        
            //活动开始时间
            $rs_active['start_time'] = intval($rs_active['start_time']);
            if($rs_active['start_time']==0)
                $rs_active['start_time'] = $rs_active['exec_time'];
            
            //活动结束时间
            $rs_active['end_time'] = intval($rs_active['end_time']);
            if($rs_active['end_time']==0)
                $rs_active['end_time'] = $rs_active['exec_time'] + 86400*15;
                
            
            if(($v['data_update_time']<$time_diff && $v['data_update_time']<$rs_active['end_time'])){
            
                $params = array(
                    'shopId'=>$rs_active['shop_id'],
                    'taskId'=>$v['_0_active_id'],
                    'execTime'=>$rs_active['exec_time'],
                    'beginTime'=>$rs_active['start_time'],
                    'endTime'=>$rs_active['end_time'],
                );
                $res = $taocrm_middleware_connect->ActiveTotalInfo($params);
                
                $data[$k]['paid_members'] = 0;
                $data[$k]['order_members'] = 0;
                $data[$k]['paid_amount'] = 0;
                $data[$k]['total_amount'] = 0;
                $data[$k]['total_members'] = 0;
                foreach($res as $temp_k => $temp_v){
                    if($temp_k == 'C') continue;
                    $data[$k]['paid_members'] += ceil($temp_v['PayMember']);
                    $data[$k]['order_members'] += ceil($temp_v['BuyMember']);
                    $data[$k]['paid_amount'] += ceil($temp_v['AmountCount']);
                    $data[$k]['total_amount'] = 0;
                    $data[$k]['total_members'] += ceil($temp_v['MemberCount']);
                }
                
                $save_arr = array(
                    'total_members' => $data[$k]['total_members'],
                    'paid_members' => $data[$k]['paid_members'],
                    'order_members' => $data[$k]['order_members'],
                    'paid_amount' => $data[$k]['paid_amount'],
                    'total_amount' => $data[$k]['total_amount'],
                    'data_update_time' => time(),
                );
                //$this->update($save_arr, array('id'=>$v['id']));
            }
            */
            $data[$k]['order_members'] = floatval($data[$k]['order_members']);
            $data[$k]['paid_members'] = floatval($data[$k]['paid_members']);
            
            $data[$k]['lost_members'] = $data[$k]['total_members'] - $data[$k]['order_members'];
            $data[$k]['io_ratio'] = round($data[$k]['paid_amount'] / ($data[$k]['total_members']*0.05));
            $data[$k]['order_ratio'] = round($data[$k]['order_members']*100 / ($data[$k]['total_members']), 2);
            
            if($data[$k]['order_ratio']<1){
                $data[$k]['effect_img'] = $img_url_black;
            }elseif($data[$k]['order_ratio']>=1 && $data[$k]['order_ratio']<5){
                $data[$k]['effect_img'] = $img_url;
            }elseif($data[$k]['order_ratio']>=5 && $data[$k]['order_ratio']<10){
                $data[$k]['effect_img'] = $img_url.$img_url;
            }elseif($data[$k]['order_ratio']>=10 && $data[$k]['order_ratio']<15){
                $data[$k]['effect_img'] = $img_url.$img_url.$img_url;
            }else{
                $data[$k]['effect_img'] = $img_url.$img_url.$img_url.$img_url;
            }

            $data[$k]['io_ratio'] = '1：'.$data[$k]['io_ratio'];
            $data[$k]['order_ratio'] .= '%';
        }
        return $data;
    }
    
    public function _filter($filter,$tableAlias=null,$baseWhere=null)
    {
        if (isset($filter['active_name'])){
            $activeobj=&app::get('market')->model('active');
            $rows = $activeobj->getList('active_id',array('active_name|has'=>$filter['active_name']));
            $memberId[] = 0;
            foreach($rows as $row){
                $memberId[] = $row['active_id'];
            }
            $where .= '  AND active_id IN ('.implode(',', $memberId).')';
            unset($filter['active_name']);
        }
        return parent::_filter($filter,$tableAlias,$baseWhere).$where;
    }
    
    public function searchOptions()
    {
    	 $parentOptions = parent::searchOptions();
        $childOptions = array(
            'active_name'=>app::get('base')->_('活动名称'),
        );
        return $Options = array_merge($parentOptions,$childOptions);
    }
	
    
}