<?php 

class ecgoods_mdl_group extends dbeav_model{

    public function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null)
    {
        //var_dump($_POST['s']);
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
        $sql = 'SELECT '.$cols.' FROM `'.$this->table_name(true).'` WHERE '.$this->_filter($filter);
        if($orderType)$sql.=' ORDER BY '.(is_array($orderType)?implode($orderType,' '):$orderType);

        $data = $this->db->selectLimit($sql,$limit,$offset);
        $this->tidy_data($data, $cols);
        if($this->use_meta && count($meta_info['metacols']) && $data){
            foreach($meta_info['metacols'] as $col){
                $obj_meta = new dbeav_meta($this->table_name(true),$col,$meta_info['has_pk']);
                $obj_meta->select($data);
            }
        }
        
        foreach($data as $v){
            $group_ids[] = $v['group_id'];
        }
        
        $res = array();
        if($group_ids){
        $rp_data = array(
            'date_from'=>strtotime($_POST['s']['time_from']),
            'date_to'=>strtotime($_POST['s']['time_to']),
                'group_ids'=>implode(',',$group_ids),
        );
        $taocrm_middleware_connect = kernel::single('taocrm_middleware_connect');
        $res = $taocrm_middleware_connect->GoodsGroupCount($rp_data);
        }
        
        //array(3) { [3]=> array(2) { ["buyPersons"]=> string(1) "0" ["totalAmount"]=> string(4) "0.00" } }
        foreach($data as $k=>$v){
            $data[$k]['total_num'] = $res[$v['group_id']]['goodsNum'];
            $data[$k]['total_amount'] = $res[$v['group_id']]['totalAmount'];
            if($res[$v['group_id']]['goodsNum']>0)
                $data[$k]['avg_price'] = round($data[$k]['total_amount']/$data[$k]['total_num'],2);
            $data[$k]['buy_person'] = $res[$v['group_id']]['buyPersons'];
        }
        //echo('<pre>');var_dump($res);
        return $data;
    }

}
