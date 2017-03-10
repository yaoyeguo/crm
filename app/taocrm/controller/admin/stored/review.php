<?php

/**
 *  积分校对
 */
class taocrm_ctl_admin_points_review extends desktop_controller{

    public function index()
    {
        $member_id = floatval($_GET['member_id']);
        $member_ids[] = $member_id;
        
        $mdl_members = $this->app->model('members');
        $mdl_member_analysis = $this->app->model('member_analysis');
        $mdl_member_points = $this->app->model('member_points');
        $mdl_points_log = $this->app->model('all_points_log');
        
        //检查是否有合并的会员数据
        $rs = $mdl_members->getList('member_id', array('parent_member_id'=>$member_id));
        if($rs){
            foreach($rs as $v){
                $member_ids[] = $v['member_id'];
            }
        }
        
        $rs_members = $mdl_members->dump(array('member_id'=>$member_id), 'points,uname,name,mobile');
        $global_points = $mdl_members->dump(array('member_id'=>$member_ids), 'sum(points) as points');
        $shop_points = $mdl_member_analysis->dump(array('member_id'=>$member_ids), 'sum(points) as points');
        $member_points = $mdl_member_points->dump(array('member_id'=>$member_ids), 'sum(points) as points');
        $log_points = $mdl_points_log->dump(array('member_id'=>$member_ids), 'sum(points) as points');
        
        $has_error = 'Y';
        if($global_points['points']==$log_points['points']
            && $shop_points['points']==$log_points['points']
            && $member_points['points']==$log_points['points']
        ){
            $has_error = 'N';
        }
        
        //vdump($global_points);
        $this->pagedata['member_id'] = $member_id;
        $this->pagedata['finder_id'] = $_GET['finder_id'];
        $this->pagedata['member'] = $rs_members;
        $this->pagedata['global_points'] = floatval($global_points['points']);
        $this->pagedata['shop_points'] = floatval($shop_points['points']);
        $this->pagedata['member_points'] = floatval($member_points['points']);
        $this->pagedata['log_points'] = floatval($log_points['points']);
        $this->pagedata['has_error'] = $has_error;
        
        $this->display('admin/points/review.html');
    }
    
    //根据积分日志校对积分
    public function save()
    {
        $this->begin();
        
        $member_id = floatval($_POST['member_id']);
        if($member_id>0){
            $mdl_member_points = $this->app->model('member_points');
            $res = $mdl_member_points->review($member_id);
        }
        
        $this->end(true, '校对完成');
    }
    
}
