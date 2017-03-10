<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class taocrm_mdl_member_analysis extends dbeav_model{

    var $defaultOrder = array('id', ' DESC');
    
    public function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null, $forceIndex='')
    {
        $rs = parent::getList($cols, $filter, $offset, $limit, $orderType, $forceIndex);
        if($rs){
            foreach($rs as $k=>$v){
                //如果不是finder调用，_0_member_id不存在
                if($v['_0_member_id']){
                    $member_ids[] = $v['_0_member_id'];
                    $rs[$k]['id'] = $v['_0_member_id'];
                }
            }
            
            if($member_ids){
                //获取每个会员的标签
                $oTag = app::get('taocrm')->model('member_tag');
                $tagInfo = $oTag->getMemberTagInfo($member_ids);
                if($tagInfo){
                    foreach($rs as $k=>$v){
                        if(isset($tagInfo[$v['_0_member_id']]))
                            $rs[$k]['tagInfo'] = implode('；', $tagInfo[$v['_0_member_id']]);
                    }
                }
                
                //获取每个会员的地区和姓名
                $oMembers = app::get('taocrm')->model('members');
                $areaInfo = $oMembers->getAllAreasInfo($member_ids);
                if($areaInfo){
                    foreach($rs as $k=>$v){
                        if(isset($areaInfo[$v['_0_member_id']]))
                            $rs[$k]['area'] = $areaInfo[$v['_0_member_id']]['area'];
                            $rs[$k]['name'] = $areaInfo[$v['_0_member_id']]['name'];
                    }
                }
            }
        }
        return $rs;
    }
    
    public function getUniqueKey($id) {
        $id = intval($id);
        if($id==0) return false;
        $rs = $this->dump($id,'member_id,shop_id');
        return $rs;
    }
    
    public function _filter($filter,$tableAlias=null,$baseWhere=null)
    {
        if (is_array($filter)) {
            if (isset($filter['member_uname'])
                or isset($filter['name'])
                or isset($filter['mobile'])
            ){
                $memberObj = $this->app->model("members");
                $_filter = array();
                if($filter['member_uname']) $_filter['uname|head'] = $filter['member_uname'];
                if($filter['name']) $_filter['name|head'] = $filter['name'];
                if($filter['mobile']) $_filter['mobile|head'] = $filter['mobile'];
                $rows = $memberObj->getList('member_id', $_filter);
                $memberId[] = 0;
                foreach($rows as $row){
                    $memberId[] = $row['member_id'];
                }
                $where .= '  AND member_id IN ('.implode(',', $memberId).')';
                unset($filter['member_uname'],$filter['name'],$filter['mobile']);
            }
            return parent::_filter($filter,$tableAlias,$baseWhere).$where;
        }
        else {
            return $filter;
        }

    }
    
    public function searchOptions()
    {
    	 $parentOptions = parent::searchOptions();
        $childOptions = array(
            'member_uname'=>app::get('base')->_('客户名'),
            'name'=>app::get('base')->_('姓名'),
            'mobile'=>app::get('base')->_('手机号'),
        );
        return $Options = array_merge($parentOptions,$childOptions);
    }
    
    //将报表条件转换为对应的member_id，带分页和页码（改造中）
    function get_filter_member(&$params){;
        $filter = $params;
        if(!is_numeric($filter['date_from'])){
            $filter['date_from'] = strtotime($filter['date_from']);
            $filter['date_to'] = strtotime($filter['date_to']);
        }
        if(!$filter['date_from'] && !$filter['date_to']) {
            $filter['date_from'] = strtotime($filter['date']);
            $filter['date_to'] = strtotime('+1 days',$filter['date_from']);
        }
        
        if($filter['member_status']) {//新老客户
            $final_filter = $this->filter_by_member_status($filter);
            
        }elseif($filter['area']){//省份区域
            $final_filter = $this->filter_by_area($filter);  
            
        }elseif($filter['hours']){//购买时段
            $final_filter = $this->filter_by_hours($filter);  
            
        }elseif($filter['relation']){//AB商品关联度
            $final_filter = $this->filter_by_relation($filter);  
            
        }elseif($filter['buy_times']){//下单次数
            $final_filter = $this->filter_by_buy_times($filter);
        }else{
            $final_filter = $this->filter_by_order($filter);
            
        }
        return $final_filter;
    }
    
    function filter_by_buy_times($filter){
        $filter['date_from'] = date('Y-m-d',$filter['date_from']);
        $filter['date_to'] = date('Y-m-d',$filter['date_to']);
        $oAnalysisDay = kernel::single('taocrm_analysis_day');
        $rs = $oAnalysisDay->get_member_buy_times($filter);
        $final_filter['filter_sql'] = $rs['filter_sql'];
        $final_filter['member_id'] = $rs['members'];
        $final_filter['total'] = $rs['total'];//var_dump($final_filter);
        //创建营销活动参数
        $final_filter['params'] = array(
            'buy_times'=>$filter['buy_times'],
            'date_from'=>$filter['date_from'],
            'date_to'=>$filter['date_to']
        );
        
        return $final_filter;
    }
    
    function get_freq_member($filter){

    	$oAnalysisDay = kernel::single('taocrm_analysis_day');
        $rs = $oAnalysisDay->get_member_freq($filter);
        $final_filter['filter_sql'] = $rs['filter_sql'];
        $final_filter['member_id'] = $rs['members'];
        $final_filter['total'] = $rs['total'];
        //创建营销活动参数
        $final_filter['params'] = array(
            'buy_times'=>$filter['buy_freq'],
        );
        return $final_filter;
    }
    
    //新老客户筛选
    function filter_by_member_status($filter){
        $filter['date_from'] = date('Y-m-d',$filter['date_from']);
        $filter['date_to'] = date('Y-m-d',$filter['date_to']);
        
        $oAnalysisDay = kernel::single('taocrm_analysis_day');
        $final_filter = $oAnalysisDay->get_member_old_new($filter);
        
        //创建营销活动参数
        $final_filter['params'] = array(
            'member_status'=>$filter['member_status'],
            'date_from'=>$filter['date_from'],
            'date_to'=>$filter['date_to'],
            'date'=>$filter['date'],
            'count_by'=>$filter['count_by']
        );

        return $final_filter;
    }
    
    //关联商品筛选
    function filter_by_relation($filter){
        
        if($filter['in_order'] == 1){
            return $this->filter_by_basket($filter);            
        }
        
        $oShopGoods = kernel::single('ecgoods_ctl_admin_shop_goods');
        $rs = $oShopGoods->get_goods_relate($filter['goods_a'],$filter);
        $member_a = $rs['members'];//购买A商品的客户
        
        $rs = $oShopGoods->get_goods_relate($filter['goods_b'],$filter);
        $member_b = $rs['members'];//购买B商品的客户
        
        foreach($member_a as $vv){
            if(in_array($vv,$member_b)) {
                $member_ab[] = $vv;
            }else{
                $a_members[] = $vv;
            }
        }
        if($filter['relation'] == 'ab'):
            $final_filter['member_id'] = array_unique($member_ab);
        elseif($filter['relation'] == 'only_a'):
            $final_filter['member_id'] = array_unique($a_members);
        endif;
        
        $user_id = kernel::single('desktop_user')->get_id();
        base_kvstore::instance('analysis')->store('filter_member_'.$user_id,implode(',',$final_filter['member_id']));
        
        $final_filter['total'] = sizeof($final_filter['member_id']);
        $final_filter['params'] = array(
            'goods_a' => $filter['goods_a'],
            'goods_b' => $filter['goods_b'],
            'date_from' => $filter['date_from'],
            'date_to' => $filter['date_to'],
            'in_order' => $filter['in_order'],
            'relation' => $filter['relation'],
        );
        
        return $final_filter;
    }
    
    
    //购物篮分析
    public function filter_by_basket(&$filter){
        
        $goods_a = $filter['goods_a'];//A商品
        $goods_b = $filter['goods_b'];
        $date_from = $filter['date_from'];
        $date_to = $filter['date_to'];
        $shop_id = $filter['shop_id'];

        $db = kernel::database();
        
        // 符合条件的order_id
        $where = '';
        if ($shop_id) $where .= " and a.shop_id='$shop_id' ";
        $where .= " and (a.create_time between ".$date_from." and ".$date_to.") ";
        $where .= " and a.goods_id in ($goods_a,$goods_b) ";
        $sql = "SELECT distinct(b.member_id) FROM `sdb_ecorder_order_items` as a
            inner join sdb_ecorder_orders as b on a.order_id=b.order_id
            where a.goods_id>0 $where
            group by a.order_id HAVING count(distinct a.goods_id)>1";//die($sql);
        $final_filter['filter_sql'] = $sql;
        $rs = $db->select($sql);
        foreach($rs as $v){
            $final_filter['member_id'][] = $v['member_id']; 
        }
        
        $final_filter['total'] = sizeof($final_filter['member_id']);
        $final_filter['params'] = array(
            'goods_a' => $filter['goods_a'],
            'goods_b' => $filter['goods_b'],
            'date_from' => $filter['date_from'],
            'date_to' => $filter['date_to'],
            'in_order' => $filter['in_order'],
            'relation' => $filter['relation'],
        );
        
        return $final_filter;  
    }
    
    function filter_by_hours($filter){
        if($filter['order_status'] == 'paid') $where .= 'and pay_status="1" ';
        if($filter['order_status'] == 'finish') $where .= 'and status="finish" ';

        $sql = "select distinct(member_id) from sdb_ecorder_orders 
        where shop_id='".$filter['shop_id']."' $where 
        and createtime between ".$filter['date_from']." and ".$filter['date_to']."
        and FROM_UNIXTIME(createtime,'%H')='".$filter['hours']."' ";
        $final_filter['filter_sql'] = $sql;
        //分页代码
        if($filter['page']>=0) $sql .= ' limit '.($filter['page']*$filter['plimit']).','.$filter['plimit'];
        $rs = $this->db->select($sql);
        foreach($rs as $v){
            $member_ids[] = $v['member_id'];
        }
        $final_filter['member_id'] = $member_ids;
        
        //返回合计
        $sql = "select count(distinct(member_id)) as total from sdb_ecorder_orders 
        where shop_id='".$filter['shop_id']."' $where 
        and createtime between ".$filter['date_from']." and ".$filter['date_to']."
        and FROM_UNIXTIME(createtime,'%H')='".$filter['hours']."' ";
        $rs = $this->db->selectRow($sql);
        $final_filter['total'] = $rs['total'];
        
        //创建营销活动参数
        $final_filter['params'] = array(
            'order_status'=>$filter['order_status'],
            'date_from'=>$filter['date_from'],
            'date_to'=>$filter['date_to'],
            'hours'=>$filter['hours'],
        );
        
        return $final_filter;
    }
    
    function filter_by_area($filter){
        //获取id
        $sql = "select region_id,region_grade from sdb_ectools_regions where region_id='".$filter['area']."'";
        $rs = $this->db->selectRow($sql);
        if(!$rs) return false;
        
        if($rs['region_grade']=='1'):
            $where .= 'and state_id="'.$rs['region_id'].'" ';
        elseif($rs['region_grade']=='2'):
            $where .= 'and city_id="'.$rs['region_id'].'" ';
        elseif($rs['region_grade']=='3'):
            $where .= 'and district_id="'.$rs['region_id'].'" ';
        endif;
            
        if($filter['order_status'] == 'paid') $where .= 'and pay_status="1" ';
        if($filter['order_status'] == 'finish') $where .= 'and status="finish" ';

        $sql = "select distinct(member_id) from sdb_ecorder_orders 
        where shop_id='".$filter['shop_id']."' $where 
        and createtime between ".$filter['date_from']." and ".$filter['date_to']." ";
        $final_filter['filter_sql'] = $sql;
        //分页代码
        if($filter['page']>=0) $sql .= ' limit '.($filter['page']*$filter['plimit']).','.$filter['plimit'];
        $rs = $this->db->select($sql);
        foreach($rs as $v){
            $member_ids[] = $v['member_id'];
        }
        $final_filter['member_id'] = $member_ids;
        
        //返回合计
        $sql = "select count(distinct(member_id)) as total from sdb_ecorder_orders 
        where shop_id='".$filter['shop_id']."' $where 
        and createtime between ".$filter['date_from']." and ".$filter['date_to']." ";
        $rs = $this->db->selectRow($sql);
        $final_filter['total'] = $rs['total'];
        
        //创建营销活动参数
        $final_filter['params'] = array(
            'order_status'=>$filter['order_status'],
            'date_from'=>$filter['date_from'],
            'date_to'=>$filter['date_to'],
            'area'=>$filter['area'],
        );
        
        return $final_filter;
    }
    
    //获得客户列表
    protected function getMemberInfoByOrder($filter)
    {
        //订单参数
        $params = array();
        //订单状态
        $params['order_status'] = $filter['order_status'];
        //订单起始时间
        $params['order_start_time'] = $filter['date_from'];
        //订单结束时间
        $params['order_end_time'] = $filter['date_to'];
        //订单统计单位
        $params['order_count_by'] = $filter['count_by']; 
    }
    
    function filter_by_order($filter){
        if($filter['order_status'] == 'unship') $where .= 'and pay_status="1" and ship_status="0" ';
        if($filter['order_status'] == 'ship') $where .= 'and pay_status="1" and ship_status="1" ';
        if($filter['order_status'] == 'paid') $where .= 'and pay_status="1" ';
        if($filter['order_status'] == 'finish') $where .= ' and pay_status="1" and status="finish" ';
        if($filter['order_status'] == 'unpaid') $where .= 'and pay_status="0" ';
        if($filter['order_status'] == 'dead') $where .= ' and pay_status="1" and status in ("dead","active") ';

        if($filter['count_by']=='date'){
            $where .= " AND (createtime between ".$filter['date_from']." and ".$filter['date_to'].")";
        }
        if($filter['count_by']=='week'){
            $where .= " AND (FROM_UNIXTIME(createtime,'%Y.%U') = '".$filter['date']."')";
        }
        if($filter['count_by']=='month'){
            $where .= " AND (FROM_UNIXTIME(createtime,'%Y-%m') = '".$filter['date']."')";
        }
        if($filter['count_by']=='year'){
            $where .= " AND (FROM_UNIXTIME(createtime,'%Y') = '".$filter['date']."')";
        }
        
        $sql = "select distinct(member_id) from sdb_ecorder_orders 
        where shop_id='".$filter['shop_id']."' $where ";
        $final_filter['filter_sql'] = $sql;
        //分页代码
        if($filter['page']>=0) $sql .= ' limit '.($filter['page']*$filter['plimit']).','.$filter['plimit'];
        $rs = $this->db->select($sql);
        foreach($rs as $v){
            $member_ids[] = $v['member_id'];
        }
        $final_filter['member_id'] = $member_ids;
        
        //返回合计
        $sql = "select count(distinct(member_id)) as total from sdb_ecorder_orders 
        where shop_id='".$filter['shop_id']."' $where ";
        $rs = $this->db->selectRow($sql);//var_dump($sql);
        $final_filter['total'] = $rs['total'];
        
        //创建营销活动参数
        $final_filter['params'] = array(
            'order_status'=>$filter['order_status'],
            'date_from'=>$filter['date_from'],
            'date_to'=>$filter['date_to'],
            'date'=>$filter['date'],
            'count_by'=>$filter['count_by'],
        );
        
        return $final_filter;
    }

    //获取店铺的客户数
    public function get_members($shop_id){
    	$sql='select count(member_id) as totalme from sdb_taocrm_member_analysis where shop_id="'.$shop_id.'"';
    	$count=$this->db->select($sql);
    	return $count[0]['totalme'];
    }
    
    //获取固定数量的订单金额
    public function get_all_mon($shop_id,$lim){
    	$sql='SELECT sum( `total_amount` ) as tota_money
			FROM (
				SELECT total_amount
				FROM sdb_taocrm_member_analysis
				WHERE `shop_id` = "'.$shop_id.'"
				ORDER BY `id` ASC
				LIMIT 0 , '.$lim.'
			) AS aa';
    	$tatal_money=$this->db->select($sql);
    	return $tatal_money[0]['tota_money'];
    }
    
    function modifier_total_orders($row){
        if ($row){
            return $row;
        }else{
            return '-';
        }
    }

    function modifier_total_amount($row){
        if ($row != '0.00'){
            return $row;
        }else{
            return '-';
        }
    } 
    
}

