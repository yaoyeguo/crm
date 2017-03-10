<?php

class market_ctl_admin_active_cronlist extends market_ctl_admin_active_abstract
{

	public function index()
	{
        //sdb_plugins_plugins 插件表    sdb_market_active 营销活动表   sdb_market_active_cycle 周期购表    sdb_market_active_plan 营销计划表 sdb_plugins_log插件执行log sdb_market_active_cycle_log 周期购表log

        $plugins = &app::get('plugins')->model('plugins');
        $plugins_log = &app::get('plugins')->model('log');
        //查询开启的插件
        $plugins_sql = 'select worker,last_run_time from '.$plugins->table_name(1).' where end_time > '.time();
        $plugins_list = $plugins->db->select($plugins_sql);
        foreach($plugins_list as $plugins)
        {
            $p_log_sql = 'select count(*) as exec_count,sum(sms_count) as exec_num from '.$plugins_log->table_name(1).' where worker = \''.$plugins['worker']."'";
            $plugins_log_count = current($plugins_log->db->select($p_log_sql));
            if(empty($plugins_log_count['exec_count']))
                continue;
            $p_log_sql2 = 'select plugin_name,start_time,end_time,`status` from '.$plugins_log->table_name(1).' where worker = \''.$plugins['worker']."' order by start_time desc limit 1";
            $plugins_log_last = current($plugins_log->db->select($p_log_sql2));
            $data_arr[] = array(
                        'active_name'   => $plugins_log_last['plugin_name'],
                        'start_time'    => $plugins_log_last['start_time'],
                        'end_time'      => $plugins_log_last['end_time'],
                        'exec_cycle'    => '每小时执行',
                        'exec_time'     => '8:00~20:00',
                        'exec_count'    => $plugins_log_count['exec_count'],
                        'exec_num'      => $plugins_log_count['exec_num'],
                        'status'        => $plugins_log_last['status'],
                        'type'          => '插件运行',
                        'type_a'        => '跳转到插件',
                        'type_href'     => 'index.php?app=plugins&ctl=admin_manage&act=index',
                );
        }


        //营销活动表
        $active = &app::get('market')->model('active');
        $active_list = $active->getList('active_name,start_time,end_time,exec_time,valid_num,is_active');
        foreach($active_list as $active)
        {
            switch($active['is_active']){
                case 'sel_coupon':
                    $active['is_active'] = '选择优惠劵';
                break;
                case 'sel_member':
                    $active['is_active'] = '等待选择客户';
                break;
                case 'sel_template':
                    $active['is_active'] = '等待选择模板';
                break;
                case 'wait_exec':
                    $active['is_active'] = '等待执行';
                break;
                case 'finish':
                    $active['is_active'] = '执行完成';
                break;
                case 'dead':
                    $active['is_active'] = '已作废';
                break;
                case 'execute':
                    $active['is_active'] = '正在执行';
                break;
                default:
                    $active['is_active'] = '不明';
                break;
            }
            $data_arr[] = array(
                        'active_name'   => $active['active_name'],
                        'start_time'    => $active['start_time'],
                        'end_time'      => $active['end_time'],
                        'exec_cycle'    => '指定时间执行',
                        'exec_time'     => $active['exec_time'],
                        'exec_count'    => $active['exec_time'] > time() ? 1 : 0,
                        'exec_num'      => $active['valid_num'],
                        'status'        => $active['is_active'],
                        'type'          => '营销活动',
                        'type_a'          => '跳转到营销活动',
                        'type_href'     => 'index.php?app=market&ctl=admin_active_sms&act=index',
                );
        }
        //周期购表
        $active_cycle = &app::get('market')->model('active_cycle');
        //$active_cycle_log = &app::get('market')->model('active_cycle_log');
        $active_cycle_list = $active_cycle->getList('active_name,start_time,end_time,exec_time,run_times,total_send_num,run_status,fixed_cycle_days,auto_cycle_days,cycle_type,auto_cycle_type');
        foreach($active_cycle_list as $active)
        {
            switch($active['run_status']){
                case 'wait':
                    $active['run_status'] = '等待执行';
                break;
                case 'running':
                    $active['run_status'] = '执行中';
                break;
                case 'finish':
                    $active['run_status'] = '完成';
                break;
                case 'closed':
                    $active['run_status'] = '已关闭';
                break;
                default:
                    $active['run_status'] = '不明';
                break;
            }

            switch($active['auto_cycle_type'])
            {
                case 'order_finish':
                       $active['auto_cycle_type'] = '订单完成时间';
                break;
                case 'order_paid':
                       $active['auto_cycle_type'] = '订单付款时间';
                break;
                case 'order_create':
                       $active['auto_cycle_type'] = '订单创建时间';
                break;
            }

            $data_arr[] = array(
                        'active_name'   => $active['active_name'],
                        'start_time'    => $active['start_time'],
                        'end_time'      => $active['end_time'],
                        'exec_cycle'    => $active['cycle_type'] == 'auto' ? $active['auto_cycle_type'].'后 '.$active['auto_cycle_days'].' 天' : '已完成订单，每 '.$active['fixed_cycle_days'].' 天一次',
                        'exec_time'     => $active['exec_time'],
                        'exec_count'    => $active['run_times'],
                        'exec_num'      => $active['total_send_num'],
                        'status'        => $active['run_status'],
                        'type'          => '周期营销',
                        'type_a'          => '跳转到周期营销',
                        'type_href'     => 'index.php?app=market&ctl=admin_active_cycle&act=index',
                );
        }
        //营销计划表
        $active_plan = &app::get('market')->model('active_plan');
        $active_plan_list = $active_plan->getList('active_name,start_time,end_time,run_times,total_send_num,run_status,exec_time');
        foreach($active_plan_list as $active)
        {
            switch($active['run_status'])
            {
                case 'wait':
                       $active['run_status'] = '等待执行';
                break;
                case 'running':
                       $active['run_status'] = '执行中';
                break;
                case 'finish':
                       $active['run_status'] = '完成';
                break;
                case 'closed':
                       $active['run_status'] = '已关闭';
                break;
            }

            $data_arr[] = array(
                        'active_name'   => $active['active_name'],
                        'start_time'    => $active['start_time'],
                        'end_time'      => $active['end_time'],
                        'exec_cycle'    => '指定时间执行',
                        'exec_time'     => $active['exec_time'],
                        'exec_count'    => $active['run_times'],
                        'exec_num'      => $active['total_send_num'],
                        'status'        => $active['run_status'],
                        'type'          => '营销计划',
                        'type_a'          => '跳转到营销计划',
                        'type_href'     => 'index.php?app=market&ctl=admin_active_plan&act=edit',
                );
        }

        $this->pagedata['data_list'] = $data_arr;
        $this->page('admin/active/cronlist.html');
	}
}