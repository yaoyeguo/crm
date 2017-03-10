<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class taocrm_mdl_member_points extends dbeav_model{

    //积分调整：增加或减少
    public function save_points($params)
    {
        $this->_chk_params($params);
        
        if($params['points']<0){
            return $this->points_minus_new($params);
        }else{
            return $this->points_add($params);
        }
        } 
        
    public function points_add($params)
    {
        $filter = array(
            'member_id'=>$params['member_id'],
            'shop_id'=>$params['shop_id'],
            'points_type'=>$params['points_type'],
            'invalid_time'=>$params['invalid_time'],
        );
        $rs = $this->dump($filter);
        //if(!$rs){
            $save = array(
                'member_id' => $params['member_id'],
                'points' => $params['points'],
                'points_type' => $params['points_type'],
                'shop_id' => $params['shop_id'],
                'create_time' => time(),
                'modified_time' => time(),
                'invalid_time' => $params['invalid_time'],
            );
            $this->insert($save);
        /*}else{
            $save = array(
                'points' => $rs['points'] + $params['points'],
                'modified_time' => time(),
            );
            $this->update($save, array('id' => $rs['id']));
        }*/
        
        $this->count_global_points($params['points'], $params['member_id']);
    }
    
    /**
     * 积分消费
     * 扣除积分，从即将过期的积分开始扣除
     */
    public function points_minus($params)
    {
        $this->_chk_params($params);
        
        $sql = 'SELECT id,points,invalid_time FROM sdb_taocrm_member_points';
        $sql .= ' WHERE member_id='.$params['member_id'].' ';
        $sql .= ' AND points>0 ';
        //$sql .= ' AND invalid_time>0 ';
        if($params['shop_id']) $sql .= ' AND shop_id="'.$params['shop_id'].'" ';
        //if($params['points_type']) $sql .= ' AND points_type="'.$params['points_type'].'" ';
        //$sql .= ' ORDER BY invalid_time ASC ';
        $rs = $this->db->select($sql);
        if(!$rs){
            //没有可用积分，写入负分
            $save = array(
                'member_id' => $params['member_id'],
                'points' => $params['points'],
                'points_type' => $params['points_type'] ? $params['points_type'] : 'other',
                'shop_id' => $params['shop_id'],
                'create_time' => time(),
                'modified_time' => time(),
                'invalid_time' => 0,
            );
            $this->insert($save);
        }else{
            $remain_points = $params['points'];
            //按过期时间先后依次扣除
            foreach($rs as $v){
                /*
                if($v['invalid_time']==0){
                    $fixed_points = $v;
                    continue;
                }
                */
            
                if($v['points'] + $remain_points >0){
                    $points = abs($remain_points);
                }else{
                    $points = $v['points']; 
                }
                $remain_points += $v['points'];
                
                $sql = 'UPDATE sdb_taocrm_member_points SET points=points-'.$points.' where id='.$v['id'].' ';
                $this->db->exec($sql);
                
                if($remain_points>=0) break;
            }
            
            //扣除永久积分
            //todo : 启用积分有效期的时候，$fixed_points应该是多维数组，下面的代码有bug
            if(0 && $remain_points<0 && $fixed_points) {
                if($fixed_points['points'] + $remain_points >0){
                    $points = abs($remain_points);
                }else{
                    $points = $fixed_points['points']; 
                }
                $remain_points += $v['points'];
                
                $sql = 'UPDATE sdb_taocrm_member_points SET points=points-'.$points.' where id='.$v['id'].' ';
                $this->db->exec($sql);
            }
            
            //积分余额不足，写入负分
            if($remain_points<0) {
                $save = array(
                    'member_id' => $params['member_id'],
                    'points' => $remain_points,
                    'points_type' => $params['points_type'] ? $params['points_type'] : 'other',
                    'shop_id' => $params['shop_id'],
                    'create_time' => time(),
                    'modified_time' => time(),
                    'invalid_time' => 0,
                );
                $this->insert($save);
            }
        }
        
        $this->count_global_points($params['points'], $params['member_id']);
    }
    //新减积分方法
    public function points_minus_new($params)
    {
        $this->_chk_params($params);

        $points_data_new = array();
        $point_abs = abs($params['points']);
        foreach($params['point_data'] as $kp => $vp){
            if($point_abs > 0){
                $cha = $point_abs - $vp['points'];
                if($cha >= 0){
                    $points_data_new[$kp] = 0;
                    //当积分扣除完之后，delete该记录
                    $this->db->exec('DELETE FROM `sdb_taocrm_member_points` WHERE `id` = '.$vp['id'].' LIMIT 1;');
                }else{
                    $points_data_new[$kp] = abs($cha);
                    //当积分未扣除完，update该记录
                    $this->db->exec('update sdb_taocrm_member_points set points='.abs($cha) .' where id='.$vp['id']);
                }
                $point_abs = $point_abs - $vp['points'];
            }
        }
        //更新全局会员表
        $this->count_global_points($params['points'],$params['member_id']);
    }

    //积分汇总到全局会员
    public function count_global_points($points, $member_id)
    {
        $operator = '+';
        if($points < 0){
            $operator = '-';
            $points = abs($points);
        }
        $sump = $this->get_points(array('member_id'=>$member_id));
        $sum_point = 0;
        foreach($sump as $vdata){
            $sum_point += $vdata['points'];
        }
       // $sql = 'UPDATE sdb_taocrm_members SET points=points'.$operator.$points.',point_update_time='.time().' where member_id='.$member_id.' ';
        $sql = 'UPDATE sdb_taocrm_members SET points='.$sum_point.',point_update_time='.time().' where member_id='.$member_id.' ';
        $this->db->exec($sql);
        //若有负积分，直接清零
        $sql = 'UPDATE sdb_taocrm_members SET points=0 where points < 0 ';
        $this->db->exec($sql);
    }
    
    /**
     *  全局积分复核
     *  注意：全局积分只汇总交易积分
     */
    public function review_global_points($member_id)
    {
        $sql = 'SELECT SUM(points) AS total_points FROM sdb_taocrm_member_points WHERE member_id='.$member_id.' ';
        $rs = $this->db->selectRow($sql);
        if($rs) {
            $total_points = floatval($rs['total_points']);
        }else{
            $total_points = 0;
        }
        
        $sql = 'UPDATE sdb_taocrm_members SET points='.$total_points.' where member_id='.$member_id.' ';
        $this->db->exec($sql);
    }
    
    /**
     *  获取用户积分
     *  按积分类型分开统计
     */
    public function get_points($params)
    {
        $points = 0;
        
        if(is_array($params['member_id'])){
            $params['member_id'] = implode(',', $params['member_id']);
        }
        
        $sql = 'SELECT SUM(points) AS points,MAX(modified_time) AS modified_time,shop_id FROM sdb_taocrm_member_points';
        $sql .= ' WHERE member_id in ('.$params['member_id'].') ';
        $sql .= ' AND (invalid_time > '.time().' OR ISNULL(invalid_time)) ';
        if($params['shop_id']) $sql .= ' AND shop_id="'.$params['shop_id'].'" ';
        $sql .= ' GROUP BY shop_id ';
        $rs = $this->db->select($sql);
        return $rs;
    }
    
    /**
     *  不区分积分类型
     *  按店铺返回积分合计
     */
    public function get_member_points($params)
    {
        $points = 0;
        
        if(is_array($params['member_id'])){
            $params['member_id'] = implode(',', $params['member_id']);
        }
        
        $sql = 'SELECT  SUM(a.points) as points,b.name,MAX(a.modified_time) AS modified_time FROM sdb_taocrm_member_points as a ,sdb_ecorder_shop as b';
        $sql .= ' WHERE a.member_id in ('.$params['member_id'].') ';
        $sql .= ' AND  a.shop_id = b.shop_id  ';
        //$sql .= ' AND (invalid_time > '.time().' OR invalid_time=0) ';
        if($params['shop_id']) $sql .= ' AND a.shop_id="'.$params['shop_id'].'" ';
        $sql .= ' GROUP BY a.shop_id ';
        //echo($sql);
        $rs = $this->db->select($sql);
        return $rs;
    }


    //获取即将过期的积分
    public function get_invalid_points($params)
    {
        $this->_chk_params($params);
        
        $invalid_points = 0;
        $days = $params['days'] ? intval($params['days']) : 30;
        
        $sql = 'SELECT SUM(points) AS total FROM sdb_taocrm_member_points';
        $sql .= ' WHERE member_id='.$params['member_id'].' ';
        $sql .= ' AND (invalid_time BETWEEN '.strtotime("-{$days} days").' AND '.time().') ';
        if($params['shop_id']) $sql .= ' AND shop_id="'.$params['shop_id'].'" ';
       // if($params['points_type']) $sql .= ' AND points_type="'.$params['points_type'].'" ';
        $rs = $this->db->selectRow($sql);
        if($rs){
            $invalid_points  = $rs['total'];
        }
        
        return $invalid_points;
    }
    
    //参数检测，如果存在points_type 必须和db一致
    private function _chk_params(&$params)
    {
        /*if(isset($params['points_type'])){
            $schema = $this->app->model('all_points_log')->get_schema();
            $points_type_conf = $schema['columns']['points_type']['type'];
            if(!isset($points_type_conf[$params['points_type']])){
                $params['points_type'] = 'other';
            }
        }*/
        
        //$params['invalid_time'] = is_numeric($params['invalid_time']) ? intval($params['invalid_time']) : strtotime($params['invalid_time']);
        $params['points'] = intval($params['points']);
    }
    
    /**
     *  积分校对
     */
    public function review($member_id)
    {
        $mdl_members = $this->app->model('members');
        $mdl_member_analysis = $this->app->model('member_analysis');
        $mdl_member_points = $this->app->model('member_points');
        $mdl_points_log = $this->app->model('all_points_log');
        
        //检查是否有合并的会员数据
        $member_ids[] = $member_id;
        $rs = $mdl_members->getList('member_id', array('parent_member_id'=>$member_id));
        if($rs){
            foreach($rs as $v){
                $member_ids[] = $v['member_id'];
            }
        }
        
        //初始化积分数据
        $mdl_member_analysis->update(array('points'=>0), array('member_id'=>$member_id));
        $mdl_members->update(array('points'=>0), array('member_id'=>$member_ids));
        $mdl_member_points->delete(array('member_id'=>$member_ids));

        $shop_points = array();
       // $type_points = array();
        $log_points = $mdl_points_log->dump(array('member_id'=>$member_ids), 'sum(points) as points');
        
        $rs = $mdl_points_log->getList('points,shop_id', array('member_id'=>$member_ids));
        foreach($rs as $v){
            $shop_points[$v['shop_id']] = floatval($shop_points[$v['shop_id']]) + $v['points'];
           // $type_points[$v['points_type']][$v['shop_id']] = floatval($type_points[$v['points_type']][$v['shop_id']]) + $v['points'];
        }
        
        //更新全局积分
        $mdl_members->update(array('points'=>floatval($log_points['points'])), array('member_id'=>$member_id));
        
        //更新店铺会员积分
        foreach($shop_points as $k=>$v){
            $mdl_member_analysis->update(array('points'=>floatval($v)), array('member_id'=>$member_id,'shop_id'=>$k));
        }
        
      /*  //更新类型积分
        foreach($type_points as $k=>$v){
            foreach($v as $kk=>$vv){
                $arr = array(
                    'member_id' => $member_id,
                    'points' => floatval($vv),
                    'points_type' => $k,
                    'shop_id' => $kk,
                    'create_time' => time(),
                    'modified_time' => time(),
                    //'invalid_time' => $params['invalid_time'],
                );
                $mdl_member_points->insert($arr);
            }
        }*/
    }
}