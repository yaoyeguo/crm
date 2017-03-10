<?php
class taocrm_ctl_admin_sms_log extends desktop_controller{

    var $workground = 'market';

    //短信发送日志
    public function index()
    {
        $actions = array();
        $this->finder(
            'taocrm_mdl_sms_log',
            array(
                'title' => '短信发送记录',
                'actions' => $actions,
                'orderBy' => 'log_id desc',
                'use_buildin_recycle' => false,
                'use_buildin_filter' => true,
                'use_view_tab'=>true,
            )
        );  
    }
    
    function _views()
    {
        $sms_type = array(
            'active_plan' => '营销计划',
            'active_cycle' => '周期营销',
            'market_active' => '营销活动',
            'plugins_plugins' => '自动插件',
            'taocrm_member_import_batch' => '导入客户',
            //'taocrm_member_caselog' => '服务记录',
            //'market_callplan' => '呼叫计划',
            'taocrm_member_group' => '自定义分组',
            //'market_fx_activity' => '分销活动',
            //'sale_model' => '营销模型',
            //'weixin' => '微信服务',
            //'report' => '运营报表',
            'other' => '其他',
        );
    
        $model = $this->app->model('sms_log');
        
        $sub_menu[] = array(
            'label'=>'全部',
            'filter'=>array(),
            'optional'=>false,
            'display'=>true,
        );
        
        foreach($sms_type as $k=>$v){
            $sub_menu[] = array(
                'label'=>$v,
                'filter'=>array('source'=>$k),
                'optional'=>false,
                'display'=>true,
            );
        }

        $i=0;
        foreach($sub_menu as $k=>$v){
            $sub_menu[$k]['filter'] = $v['filter']?$v['filter']:null;
            //$sub_menu[$k]['addon'] = $model->count($v['filter']);
            $sub_menu[$k]['href'] = 'index.php?app='.$_GET['app'].'&ctl='.$_GET['ctl'].'&act='.$_GET['act'].'&view='.$i++;
        }
        return $sub_menu;
    }
    
}