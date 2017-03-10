<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class taocrm_mdl_member_report extends dbeav_model{
    
    public function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderby=null)
    {
        if(is_array($filter['member_id'])){
            $filter['member_id'] = implode(',',$filter['member_id']);
        }
        $sql = "select a.id,a.member_id,a.total_amount,b.uname,b.name,a.total_orders,
        a.first_buy_time,a.last_buy_time,b.area 
        from sdb_taocrm_member_analysis as a
        left join sdb_taocrm_members as b on a.member_id=b.member_id
        where a.member_id in (".$filter['member_id'].") ";
        $sql = "select b.member_id as id,b.member_id,sum(c.total_amount) as total_amount,b.uname,b.name,count(c.order_id) as total_orders,
            min(c.createtime) as first_buy_time,b.order_last_time as last_buy_time,b.area 
            from sdb_taocrm_members as b
            left join sdb_ecorder_orders as c on c.member_id=b.member_id
            left join sdb_taocrm_member_analysis as d on c.member_id=d.member_id
            where b.member_id in (".$filter['member_id'].") ";
        if($filter['shop_id']){
            $sql .= " and a.shop_id='".$filter['shop_id']."' ";
        }
        $sql .= " group by b.member_id order by b.order_last_time desc ";
        //$sql .= " group by b.member_id order by last_buy_time desc ";
        //$sql .= " limit $offset,$limit ";
        $rs = $this->db->select($sql);
        //echo($sql);
        return $rs;
    }
    
    public function count($filter=null){
        return $filter['total'];
    }
    
    public function get_schema(){
        $schema = array (
            'columns' => array (
                'member_id' => array (
                    'type' => 'varchar(32)',
                    'required' => true,
                    'label' => '客户ID',
                    'editable' => false,
                    'width' =>60,
                    'is_title' => true,
                    'orderby' => false,
                    'order'=>10
                ),
                'uname' => array (
                    'type' => 'varchar(100)',
                    'label' => '客户名',
                    'editable' => false,
                    'orderby' => false,
                    'width' =>120,
                    'order'=>20
                ),
                'name' => array (
                    'type' => 'varchar(100)',
                    'label' => '真实姓名',
                    'editable' => false,
                    'orderby' => false,
                    'width' =>100,
                    'order'=>30
                ),
                'total_amount' => array (
                    'type' => 'varchar(100)',
                    'label' => '成功订单总金额',
                    'editable' => false,
                    'orderby' => false,
                    'width' =>100,
                    'order'=>40
                ),
                'total_orders' => array (
                    'type' => 'varchar(100)',
                    'label' => '订单总数',
                    'editable' => false,
                    'orderby' => false,
                    'width' =>100,
                    'order'=>50
                ),
                'area' => array (
                    'type' => 'region',
                    //'sdfpath' => 'contact/area',
                    'label' => '区域',
                    'editable' => false,
                    'orderby' => false,
                    'width' =>180,
                    'order'=>55
                ),
                'first_buy_time' => array (
                    'type' => 'time',
                    'label' => '初次购买',
                    'editable' => false,
                    'orderby' => false,
                    'width' =>130,
                    'order'=>60
                ),
                'last_buy_time' => array (
                    'type' => 'time',
                    'label' => '最后购买',
                    'editable' => false,
                    'orderby' => false,
                    'width' =>130,
                    'order'=>70
                ),
            ),
            'idColumn' => 'id',
            'in_list' => array (
                0 => 'member_id',
                1 => 'uname',
                2 => 'name',
                3 => 'total_amount',
                4 => 'total_orders',
                5 => 'first_buy_time',
                6 => 'last_buy_time',
                7 => 'area',
            ),
            'default_in_list' => array (
                0 => 'member_id',
                1 => 'uname',
                2 => 'name',
                3 => 'total_amount',
                4 => 'total_orders',
                5 => 'first_buy_time',
                6 => 'last_buy_time',
                7 => 'area',
            ),
        );
        return $schema;
    }
    
    public function _filter($filter,$tableAlias=null,$baseWhere=null){
        if (isset($filter['member_uname'])){
            $memberObj = &$this->app->model("members");
            $rows = $memberObj->getList('member_id',array('uname|has'=>$filter['member_uname']));
            $memberId[] = 0;
            foreach($rows as $row){
                $memberId[] = $row['member_id'];
            }
            $where .= '  AND member_id IN ('.implode(',', $memberId).')';
            unset($filter['member_uname']);
        }
        return parent::_filter($filter,$tableAlias,$baseWhere).$where;
    }

}

