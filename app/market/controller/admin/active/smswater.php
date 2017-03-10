<?php 
class market_ctl_admin_active_smswater extends desktop_controller {
    
	public function index(){
		$param=array(
            'title'=>'短信流水',
            'orderBy' => "create_time desc", 
            'use_buildin_recycle'=>false,
            'use_buildin_filter'=>true,
            'use_view_tab'=>true,
            'use_buildin_import'=>false,
            'use_buildin_export'=>false,
        );
		$this->finder('market_mdl_sms_op_record',$param);
	}
    
    function _views(){
        $oRecord = $this->app->model('sms_op_record');

        $sub_menu[] = array(
            'label'=> '全部',
            'filter'=> '',
            'optional'=>false,	
        );
        
        $sub_menu[] = array(
            'label'=> '购买插件',
            'filter'=> array('remark'=>'购买插件'),
            'optional'=>false,	
        );
        
        $sub_menu[] = array(
            'label'=> '短信冻结',
            'filter'=> array('remark'=>'短信冻结'),
            'optional'=>false,	
        );
        
        $sub_menu[] = array(
            'label'=> '扣除佣金',
            'filter'=> array('remark'=>'扣除佣金'),
            'optional'=>false,	
        );
        
        $sub_menu[] = array(
            'label'=> '	短信解冻',
            'filter'=> array('remark'=>'短信解冻'),
            'optional'=>false,	
        );

        $i=0;
        foreach($sub_menu as $k=>$v){
            $count =$oRecord->count($v['filter']);
            $sub_menu[$k]['filter'] = $v['filter']?$v['filter']:null;
            $sub_menu[$k]['addon'] = $count;
            $sub_menu[$k]['href'] = 'index.php?app=market&ctl=admin_active_smswater&act=index&view='.$i++;
        }
        return $sub_menu;
    } 

}
