<?php
class market_ctl_admin_exchange_order extends desktop_controller{
    var $workground = 'market.sales';

    public function index(){
        $this->finder('market_mdl_exchange_order',
            array(
                'title' => '兑换日志',
                'use_buildin_export' => false,
                'use_buildin_recycle' => false,
                'use_buildin_set_tag' => false,
                'use_buildin_tagedit' => false,
                'orderBy' => 'order_id desc',
            )
        );
    }
    
    public function edit($log_id) {
        $logs = $this->app->model('points_log')->dump($log_id);
        
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
        $this->display('admin/points/log_edit.html');
    }
    
	public function save(){
        $this->begin();
        $logs = $_POST;
        $logs['create_time'] = time();
        $logs['is_active'] = 1;
        $logs['op_user'] = kernel::single('desktop_user')->get_name();
        $logs['user_ip'] = $_SERVER['REMOTE_ADDR'];
        $this->app->model('points_log')->insert($logs);
        
        kernel::database()->exec('UPDATE sdb_taocrm_member_analysis SET points=points+'.$logs['points'].' where member_id='.$logs['member_id'].' and shop_id="'.$logs['shop_id'].'" ');
        
        kernel::database()->exec('UPDATE sdb_taocrm_points_log SET is_active="0" where log_id='.$logs['pid'].' ');
        
		$this->end(true,'保存成功');
	}
	
}
