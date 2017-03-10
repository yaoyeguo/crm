<?php
class taocrm_ctl_admin_points_log extends desktop_controller{

    //var $workground = 'taocrm.member';

    public function index()
    {
        $actions = array(
            /*
            array(
                'label'=>'积分调整',
                'href'=>'index.php?app=taocrm&ctl=admin_points_log&act=edit_manual',
                'target'=>'dialog::{width:650,height:355,title:\'积分调整\'}'
            ),
            */
            array(
                'label'=>'积分初始化',
                'href'=>'index.php?app=taocrm&ctl=admin_points_log&act=init_points'
            ),
        );
        $this->finder('taocrm_mdl_all_points_log',
            array(
                'title' => '积分日志',
                'actions' => $actions,
                'use_buildin_export' => false,
                'use_buildin_recycle' => false,
                'use_buildin_set_tag' => false,
                'use_buildin_tagedit' => false,
                'use_buildin_selectrow' => false,
                'orderBy' => 'id desc',
            )
        );
    }
    
    function _views()
    {
        $oRecord = $this->app->model('all_points_log');

        $sub_menu[] = array(
            'label'=> '全部',
            'filter'=> '',
            'optional'=>false,
        );

        $schema = $this->app->model('all_points_log')->get_schema();
        /*$points_type_conf = $schema['columns']['points_type']['type'];
        foreach($points_type_conf as $k=>$v){
        $sub_menu[] = array(
                'label'=> $v,
                'filter'=> array('points_type'=>$k),
            'optional'=>false,
        );
        }*/

        $i=0;
        foreach($sub_menu as $k=>$v){
            $count =$oRecord->count($v['filter']);
            $sub_menu[$k]['filter'] = $v['filter']?$v['filter']:null;
            $sub_menu[$k]['addon'] = $count;
            $sub_menu[$k]['href'] = 'index.php?app=taocrm&ctl=admin_points_log&act=index&view='.$i++;
        }
        return $sub_menu;
    }
    
    public function edit_manual($log_id)
    {
        $schema = $this->app->model('all_points_log')->get_schema();
       // $points_type_conf = $schema['columns']['points_type']['type'];
    
        if($log_id)
        {
            $logs = $this->app->model('all_points_log')->dump($log_id);
            
            $rs = $this->app->model('members')->dump($logs['member_id'],'uname,name');
            $logs['uname'] = $rs['account']['uname'];
            $logs['name'] = $rs['contact']['name'];
            $logs['points'] = $logs['points']*(-1);
            
            $filter = array(
                'member_id' => $logs['member_id'],
                'shop_id' => $logs['shop_id'],
            );
            $rs = $this->app->model('member_analysis')->dump($filter,'points');
            $logs['total_points'] = $rs['points'];
            $this->pagedata['logs'] = $logs;
        }
        if($_GET['member_id'])
        {
            $member_ids[] = $_GET['member_id'];
            $objMembers = $this->app->model('members');
            /*
            $subMembers = $objMembers->getList('member_id',array('parent_member_id'=>$member_id));
            $member_ids[] = $member_id;
            if($subMembers){
                foreach($subMembers as $row){
                    $member_ids[] = $row['member_id'];
                }   
            }*/ 
            $member_info = $objMembers->getList('member_id,uname,shop_id',array('member_id'=>$member_ids));
            $this->pagedata['member_info']=$member_info;
            if( ! $member_info[0]['shop_id']){
                $rs = $this->app->model('member_analysis')->dump(array('member_id'=>$member_ids), 'shop_id');
                $this->pagedata['shop_id']=$rs['shop_id'];
                $shop_id = $rs['shop_id'];
            }else{
                $this->pagedata['shop_id']=$member_info[0]['shop_id'];
                $shop_id = $member_info[0]['shop_id'];
            }
            if($_GET['source_page']){
                $objMemberAn = $this->app->model('member_analysis')->dump(array('member_id'=>$member_ids,'shop_id'=>$shop_id),'points');
                $cur_points = $objMemberAn['points'];
            }else{
                $data_points = $this->app->model('member_points')->get_points(array('member_id'=>$member_ids));
                $cur_points = 0;
                foreach($data_points as $kp => $kv){
                    $cur_points += $kv['points'];
                }
            }
            $this->pagedata['points'] = $cur_points;
        }
        $shopObj = app::get('ecorder')->model('shop');
        $shopList=$shopObj->get_shops('all', 'select');
        
       // $this->pagedata['points_type_conf']=$points_type_conf;
        $this->pagedata['shopList']=$shopList;//店铺信息
        $this->pagedata['finder_id']=$_GET['finder_id'];//店铺信息
        $this->pagedata['source_page']=$_GET['source_page'];
        $this->display('admin/points/log_edit_manual.html');
    }
    
    public function edit($log_id)
    {
        $logs = $this->app->model('all_points_log')->dump($log_id);
        
        $rs = $this->app->model('members')->dump($logs['member_id'],'uname,name,parent_member_id');
        $logs['uname'] = $rs['account']['uname'];
        $logs['name'] = $rs['contact']['name'];
        $logs['points'] = $logs['points']*(-1);
        
        //处理合并后的客户数据
        if($rs['parent_member_id']>0){
            $logs['member_id'] = $rs['parent_member_id'];
        }
        
        $filter = array(
            'member_id' => $logs['member_id'],
            'shop_id' => $logs['shop_id'],
        );
        $rs = $this->app->model('member_analysis')->dump($filter, 'points');
        $logs['total_points'] = $rs['points'];
        
        $this->pagedata['logs'] = $logs;
        $this->display('admin/points/log_edit.html');
    }
    
	public function save()
    {
        $mdl_member_points = $this->app->model('member_points');
        
        $this->begin();
        
        $logs = $_POST;
        $logs['member_id'] = floatval($logs['member_id']);
        $logs['pid'] = intval($logs['pid']);
        $logs['op_time'] = time();
        $logs['is_active'] = 1;
        $logs['op_user'] = kernel::single('desktop_user')->get_name();
        $logs['user_ip'] = $_SERVER['REMOTE_ADDR'];
        $logs['point_desc'] = '手工调整:'.$logs['remark'];

        $oPointsLog = $this->app->model('all_points_log');

        if($logs['member_id']>0){
            $sql = 'SELECT member_id,points FROM sdb_taocrm_members WHERE member_id='.$logs['member_id'];
            if($logs['shop_id']){
                $sql = 'SELECT id,member_id,points FROM sdb_taocrm_member_analysis WHERE member_id='.$logs['member_id'];
                $sql .= ' AND shop_id="'.$logs['shop_id'].'" ';
            }
        }elseif($logs['uname'] != ''){
            $sql = 'SELECT a.id,a.member_id,a.points FROM sdb_taocrm_member_analysis as a
                left join sdb_taocrm_members as b on a.member_id=b.member_id
                WHERE b.uname="'.$logs['uname'];
            if($logs['shop_id']){
                $sql .= '" AND a.shop_id="'.$logs['shop_id'].'" ';
            }
        }else{
            $this->end(false,'参数错误');
        }
        $dbPoints = $oPointsLog->db->selectRow($sql);
        if(!$dbPoints){
            $this->end(false,'客户信息不存在');
        }else{
            $logs['member_id'] = $dbPoints['member_id'];
        }

        $sql = 'select points,id from sdb_taocrm_member_points where member_id='.$dbPoints['member_id'];
        if($logs['shop_id']){
            $sql .= ' and shop_id="'.$logs['shop_id'].'"';
        }
        $sql .= ' and (invalid_time >= '.time().' or ISNULL(invalid_time)) order by invalid_time';
        $points_data = $mdl_member_points->db->select($sql);
        if(!empty($points_data)){
            $sum_point = 0;
            foreach($points_data as $vdata){
                $sum_point += $vdata['points'];
            }
        }
        if($logs['points'] < 0 && $sum_point < abs($logs['points'])){
            $this->end(false,'当前积分不足以扣减积分！');
        }
        $msg = '';
        $id = kernel::single('taocrm_member_point')->update($logs['shop_id'],$logs['member_id'],2,$logs['points'],'管理员手动修改',$msg,null,'manual');
        /*if($logs['shop_id']){
            $operator = ($logs['points']>0) ? '+' : '-';
            $sql = 'UPDATE sdb_taocrm_member_analysis SET points='.$sum_point.$operator.abs($logs['points']).' where id='.$dbPoints['id'].' ';
            $oPointsLog->db->exec($sql);//更新店铺积分
        }

        $logs['op_before_point'] = $sum_point;
        $logs['op_after_point'] = $sum_point + $logs['points'];
        $oPointsLog->insert($logs);//写入积分日志
        if($logs['pid']>0){
            $oPointsLog->db->exec('UPDATE sdb_taocrm_all_points_log SET is_active="0" where id='.$logs['pid'].' ');
        }

        $logs['points_type'] = 'manual';
        if($logs['points'] < 0){
            $logs['point_data'] = $points_data;
        }
        $mdl_member_points->save_points($logs);//全渠道积分*/
        
		$this->end(true,'保存成功');
	}
    
    public function get_points()
    {
        $return = array(
            'points' => array(),
            'msg' => 'succ',
        );
        $member_id = floatval($_POST['member_id']);
        $shop_id = $_POST['shop_id'];
        $mdl_member_points = $this->app->model('member_points');
        if($member_id && $shop_id){
            $filter = array('member_id'=>$member_id, 'shop_id'=>$shop_id);
            $rs = $this->app->model('member_analysis')->dump($filter, 'points');
            if($rs){
                $return['points'] = $rs['points'];
            }else{
                $return['msg'] = "<font color=red>客户ID <b>$member_id</b> 不存在</font>";
            }
        }
        echo(json_encode($return));
    }
    
    //初始化全部客户的积分
    public function init_points()
    {
        if($_POST){
            $init_type = $_POST['init_type'];
            $shop_id = $_POST['shop_id'];
            $page = intval($_POST['page']);
            $where = '';
            
            if(!$shop_id){
                die('fail请选择店铺');
            }
            
            //判断积分规则是否存在
            if($page == 0){
                $rs = app::get('ecorder')->model('shop_credit')->count(array('shop_id'=>$shop_id));
                if($rs == 0) die('fail店铺没有设置积分规则，请先设置规则后再初始化积分');
            }
            
            $page_size = 10;            
            $offset = $page*$page_size;
            
            $sql = "select count(*) as total from sdb_taocrm_member_analysis where shop_id='$shop_id' $where ";
            $rs = kernel::database()->selectRow($sql);
            $total_members = $rs['total'];
            
            if($init_type == 'blank'){
                set_time_limit(3600);
                $where .= ' and points<=0 ';
                $sql = "select member_id from sdb_taocrm_member_analysis where shop_id='$shop_id' $where ";
            }else{
                $sql = "select member_id from sdb_taocrm_member_analysis where shop_id='$shop_id' $where limit $offset,$page_size ";
            }          
            
            $rs = kernel::database()->select($sql);
            if(!$rs) die('finish');
            
            $taocrm_member_point = kernel::single('taocrm_member_point');
            foreach($rs as $v){
                $taocrm_member_point->init_member_point($shop_id, $v['member_id']);
            }
            $percent = round($offset*100/$total_members,1);
            
            if($init_type == 'blank'){
                die('finish');
            }            
            die("succ$percent%");
        }
    
        $sql = 'select shop_id,name from sdb_ecorder_shop';
        $shoplist = kernel::database()->select($sql);
        $this->pagedata['shoplist'] = $shoplist;
        $this->display('admin/points/init.html');
    }
    
}
