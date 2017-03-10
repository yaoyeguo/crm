<?php 
class market_mdl_active_monitor extends dbeav_model {

	// public function modifier_create_time($row)
    // {
        // $date = date("Y-m-d",$row);
        // return $date ;
    // }
    public function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null)
    {
        //var_dump($filter);
        if(!$cols){
            $cols = $this->defaultCols;
        }
        if(!empty($this->appendCols)){
            $cols.=','.$this->appendCols;
        }
        if($this->use_meta){
             $meta_info = $this->prepare_select($cols);
        }
        $orderType = $orderType?$orderType:$this->defaultOrder;
        $sql = 'SELECT * FROM sdb_market_active WHERE '.$this->_filter($filter);
        if($orderType)$sql.=' ORDER BY '.(is_array($orderType)?implode($orderType,' '):$orderType);
        //var_dump($sql);

        $data = $this->db->selectLimit($sql,$limit,$offset);
        $this->tidy_data($data, $cols);
        if($this->use_meta && count($meta_info['metacols']) && $data){
            foreach($meta_info['metacols'] as $col){
                $obj_meta = new dbeav_meta($this->table_name(true),$col,$meta_info['has_pk']);
                $obj_meta->select($data);
            }
        }
        
        foreach($data as $v){
            $goods_ids[] = $v['goods_id'];
        }
        
        $rp_data = array(
            'date_from'=>strtotime($filter['s']['time_from']),
            'date_to'=>strtotime($filter['s']['time_to']),
            'goods_ids'=>implode(',',$goods_ids),
        );
        $taocrm_middleware_connect = kernel::single('taocrm_middleware_connect');
        $res = $taocrm_middleware_connect->GoodsBuyTimes($rp_data);
        
        //array(3) { [3]=> array(2) { ["buyPersons"]=> string(1) "0" ["totalAmount"]=> string(4) "0.00" } }
        foreach($data as $k=>$v){
            $data[$k]['one_times_person'] = $res[$v['goods_id']]['oneTimesPerson'];
            $data[$k]['two_times_person'] = $res[$v['goods_id']]['twoTimesPerson'];
            $data[$k]['two_times_days'] = $res[$v['goods_id']]['twoTimesDays'];
            $data[$k]['thr_times_person'] = $res[$v['goods_id']]['thrTimesPerson'];
            $data[$k]['thr_times_days'] = $res[$v['goods_id']]['thrTimesDays'];
            $data[$k]['for_times_person'] = $res[$v['goods_id']]['fourTimesPerson'];
            $data[$k]['for_times_days'] = $res[$v['goods_id']]['fourTimesDays'];
        }
        //echo('<pre>');var_dump($res);
        return $data;
    }
    
    function count($filter=null)
    {
        $sql = 'SELECT count(*) as _count FROM sdb_ecgoods_shop_goods WHERE '.$this->_filter($filter);
        $row = $this->db->select($sql);
        return intval($row[0]['_count']);
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