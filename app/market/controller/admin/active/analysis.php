<?php

class market_ctl_admin_active_analysis extends desktop_controller
{

    public function index()
    {
        $sys_version = kernel::single('taocrm_system')->get_version_code();
        $this->pagedata['sys_version'] = $sys_version;
    
        $view = intval($_GET['view']);
        if($view == 0){
            $this->review();
        }else{
            $this->detail_list();
        }
    }
    
    //任务概览
    public function review()
    { 
        $this->pagedata['cycle'] = $this->get_cycle_task();
        $this->pagedata['plugins'] = $this->get_plugin_task();
        $this->pagedata['ontime'] = $this->get_ontime_task();
        $this->page('admin/active/analysis/review.html');
    }
    
    //任务详情
    public function detail_list()
    {
        $url_prefix = 'index.php?app='.$_GET['app'].'&ctl='.$_GET['ctl'].'&act='.$_GET['act'].'&view='.$_GET['view'].'';
        $group[] = array('label'=>'全部','href'=>$url_prefix);
        $group[] = array('label'=>'等待执行','href'=>$url_prefix.'&status=wait');
        $group[] = array('label'=>'执行中','href'=>$url_prefix.'&status=running');
        $group[] = array('label'=>'完成','href'=>$url_prefix.'&status=finish');
        $group[] = array('label'=>'关闭','href'=>$url_prefix.'&status=dead');
        
        $actions = array(
            array(
                'label' => '执行状态',
                'icon' => 'batch.gif',
                'group' => $group
            ),
        );
        
        if(isset($_GET['status'])){
            $base_filter['status'] = trim($_GET['status']);
        }
        
        $this->finder(
            'market_mdl_active_review',
            array(
                'title'=>'营销任务监控',
                'actions'=>$actions,
                'base_filter'=>$base_filter,
                'use_buildin_set_tag'=>false,
                'use_buildin_import'=>false,
                'use_buildin_export'=>false,
                'use_buildin_recycle'=>false,
                'use_buildin_filter'=>true,
                'use_buildin_tagedit'=>true,
                'use_view_tab'=>true,
                'orderBy'=>'last_run_time DESC',
            )
        );
    }
    
    public function _views()
    {
        $sub_menu[] = array(
            'label'=> '任务概览',
            'display'=>true,
            'view'=>'index.php?app=market&ctl=admin_active_analysis&act=index',
        );

        $sub_menu[] = array(
            'label'=> '任务详情',
            'display'=>true,
            'view'=>'index.php?app=market&ctl=admin_active_analysis&act=index&view=1',
        );
        return $sub_menu;
    }
    
    //插件运行状态
    private function get_plugin_task()
    {
        $plugins = array(
            'opened' => 0,
            'closed' => 0,
            'run_times' => 0,
            'succ_num' => 0,
            'fail_num' => 0,
            'last_run_time' => 0,
        );
        $mdl_plugins = app::get('plugins')->model('plugins');
        $rs = $mdl_plugins->getList('*');
        if($rs){
            foreach($rs as $v){
                if($v['end_time']<time()){
                    $plugins['closed']++;
                }else{
                    $plugins['opened']++;
                }
                $plugins['last_run_time'] = max($plugins['last_run_time'], $v['last_run_time']);
                
                //初始化插件执行数据
                $this->init_plugin_review($v);
            }
        }
        
        $mdl_plugins_log = app::get('plugins')->model('log');
        $plugins['run_times'] = $mdl_plugins_log->count();
        
        $rs = $mdl_plugins_log->dump(array('status'=>'成功'), 'sum(sms_count) as succ_num');
        $plugins['succ_num'] = $rs['succ_num'];
        
        $rs = $mdl_plugins_log->dump(array('status'=>'失败'), 'sum(sms_count) as fail_num');
        $plugins['fail_num'] = $rs['fail_num'];
        
        if(!$plugins['last_run_time']) 
            $plugins['last_run_time'] = '-';
        else
            $plugins['last_run_time'] = date('Y-m-d H:i:s', $plugins['last_run_time']);
            
        return $plugins;
    }
    
    //定时任务运行状态
    private function get_ontime_task()
    {
        $ontime = array(
            'wait' => 0,
            'finish' => 0,
        );
        $mdl_plugins_log = $this->app->model('active');
        $ontime['wait'] = $mdl_plugins_log->count(array('is_timing'=>1, 'is_active'=>'execute'));
        
        $mdl_plugins_log = $this->app->model('active');
        $ontime['finish'] = $mdl_plugins_log->count(array('is_timing'=>1, 'is_active'=>'finish'));
        
        return $ontime;
    }
    
    //周期任务运行状态
    private function get_cycle_task()
    {
        $cycle = array(
            'opened' => 0,
            'closed' => 0,
            'run_times' => 0,
            'succ_num' => 0,
            'fail_num' => 0,
            'last_run_time' => 0,
        );
        $mdl_active_cycle = $this->app->model('active_cycle');
        $cycle['opened'] = $mdl_active_cycle->count(array('status'=>1));
        $cycle['closed'] = $mdl_active_cycle->count() - $cycle['opened'];
        
        $rs = $mdl_active_cycle->dump(array(), 'sum(run_times) as run_times');
        $cycle['run_times'] = $rs['run_times'];
        
        $rs = $mdl_active_cycle->dump(array(), 'max(exec_time) as last_run_time');
        $cycle['last_run_time'] = $rs['last_run_time'];
        
        $mdl_active_cycle_log = $this->app->model('active_cycle_log');
        $rs = $mdl_active_cycle_log->dump(array(), 'sum(succ_num) as succ_num');
        $cycle['succ_num'] = $rs['succ_num'];
        
        $rs = $mdl_active_cycle_log->dump(array(), 'sum(send_num) as total_num');
        $cycle['fail_num'] = $rs['total_num'] - $cycle['succ_num'];
     
        if(!$cycle['last_run_time']) 
            $cycle['last_run_time'] = '-';
        else
            $cycle['last_run_time'] = date('Y-m-d H:i:s', $cycle['last_run_time']);
            
        return $cycle;
    }
    
    //更新插件运行监控数据
    public function init_plugin_review($v)
    {
        if(!$v) return false;        
        $data = array(
            'active_id' => $v['plugin_id'],
            'title' => $v['plugin_name'],
            'active_type' => 'plugins',
            'begin_time' => $v['buy_time'],
            'end_time' => $v['end_time'],
            'last_run_time' => $v['last_run_time'],
            'status' => ($v['end_time']>time()) ? 'wait' : 'dead',
            'exec_times' => 0,
            'send_members' => 0,
            'update_time' => time(),
        );
        
        $mdl_plugins_log = app::get('plugins')->model('log');
        $data['exec_times'] = $mdl_plugins_log->count(array('plugin_id'=>$v['plugin_id']));
        
        $rs = $mdl_plugins_log->dump(array('plugin_id'=>$v['plugin_id'], 'status'=>'成功'), 'sum(sms_count) as succ_num');
        $data['send_members'] = floatval($rs['succ_num']);
        
        $filter = array(
            'active_id' => $v['plugin_id'],
            'active_type' => 'plugins',
        );
        
        $mdl_review = $this->app->model('active_review');
        $rs = $mdl_review->dump($filter);
        if($rs){
            $q = $mdl_review->update($data, array('id'=>$rs['id']));
        }else{
            $q = $mdl_review->save($data);
        }
        return true;
    }
}
