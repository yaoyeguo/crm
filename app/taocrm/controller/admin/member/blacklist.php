<?php 
class taocrm_ctl_admin_member_blacklist extends desktop_controller {

	function index(){
		$memberObj = &app::get(taocrm)->model('members');
		$this->base_filter=array('sms_blacklist'=>'true');
		$this->finder('taocrm_mdl_members',array(
            'title'=>'黑名单客户',
            'actions'=>array(
                array(
                    'label'=>'删除',
                    'submit'=>'index.php?app=taocrm&ctl=admin_member_blacklist&act=sms_black_del',
                ),
                array(
                    'label'=>'批量添加',
                    'href'=>'index.php?app=taocrm&ctl=admin_member_blacklist&act=batch_add&type=sms',
                    'target'=>'dialog::{width:500,height:350,title:\'批量添加黑名单\'}'
                ),
            ),
            'use_buildin_export'=>false,//导出
            'use_buildin_import'=>false,//导入
            'use_buildin_recycle'=>false,
			'base_filter' =>$this->base_filter,
        ));
	}
	
    public function _views()
    { 
        $baseFilter = $this->base_filter;
        $memberObj = &app::get('taocrm')->model('members');
//        $tmp = array('sms_blacklist' => 'true', 'uname' => '落叶冰心77');
//        $tmpCount = $memberObj->count($tmp);
        $sum = $memberObj->count($baseFilter);
        $sub_menu = array();
        $sub_menu[0]['filter'] = null;
        $sub_menu[0]['label'] = '全部';
        $sub_menu[0]['addon'] = $sum;
        $sub_menu[0]['href'] = 'index.php?app=taocrm&ctl='.$_GET['ctl'].'&act='.$_GET['act'].'&view=0';
//        $items = $this->getViewsItems();
//        $i = 1;
//        foreach ($items as $k => $v) {
//            $sub_menu[$i]['label'] = trim($v['filter_name']);
//            $queryFilter = $baseFilter;
//            foreach ($v['filter_query'] as $km => $vm) {
//                $queryFilter[trim($km)] = trim($vm);
//            }
//            $sub_menu[$i]['filter'] = null;
//            $sub_menu[$i]['addon'] = $memberObj->count($queryFilter);
//            $sub_menu[$i]['href'] = 'index.php?app=taocrm&ctl='.$_GET['ctl'].'&act='.$_GET['act']."&view={$i}";
//            $i++;
//        }
        return $sub_menu;
    }
    
    protected function getViewsItems()
    {
        $app = $_GET['app'];
        $act = $_GET['act'];
        $ctl = $_GET['ctl'];
        $user = kernel::single('desktop_user');
        $userId = $user->get_id();
        $filterObj = app::get('desktop')->model('filter');
        $filter = array("user_id" => $userId, 'app' => $app, 'act' => $act, 'ctl' => $ctl);
        $data = $filterObj->getList('*', $filter);
        
        $filterQuerys = array();
        $i = 0;
        foreach ($data as $v) {
            $filterQuery = explode("&", $v['filter_query']);
            $userQuery = array();
            foreach ($filterQuery as $v1) {
                $userQuery = explode("=", $v1);
                $userKey = rawurldecode($userQuery[0]);
                $userValue = rawurldecode($userQuery[1]);
                $filterQuerys[$i]['filter_query'][$userKey] = $userValue;
            }
            $filterQuerys[$i]['filter_name'] = $v['filter_name'];
            $i++;
        }
        return $filterQuerys;
    }
	
	function edm_blaclist()
    {
		$this->base_filter=array('edm_blacklist'=>'true');
		$this->finder('taocrm_mdl_members',array(
            'title'=>'邮件黑名单',
			'actions'=>array(
                array(
                'label'=>'删除',
                'submit'=>'index.php?app=taocrm&ctl=admin_member_blacklist&act=edm_black_del',
                ),
            ),
            'use_buildin_export'=>false,//导出
            'use_buildin_import'=>false,//导入
            'use_buildin_recycle'=>false,
			'base_filter' =>$this->base_filter,
            ));
	
	}
    
	//删除短信黑名单
	function sms_black_del()
    {
		$this->begin('index.php?app=taocrm&ctl=admin_member_blacklist&act=index');
		$memberObj = &app::get(taocrm)->model('members');
		$members = $_POST['member_id'];
		if(count($members) > 1){
			foreach($members as $v){
				$rs=$memberObj->update(array('sms_blacklist'=>'false'),array('member_id'=>$v));
			}
		}else{
			$rs=$memberObj->update(array('sms_blacklist'=>'false'),array('member_id'=>$_POST['member_id'][0]));
		}
		if ($rs){
			 $this->end(true,'黑名单删除成功');
		}
	}
    
	//删除邮件黑名单
	function edm_black_del()
    {
		$this->begin('index.php?app=taocrm&ctl=admin_member_blacklist&act=edm_blaclist');
		$memberObj = &app::get(taocrm)->model('members');
		$members = $_POST['member_id'];
		if(count($members) > 1){
			foreach($members as $v){
				$rs=$memberObj->update(array('edm_blacklist'=>'false'),array('member_id'=>$v));
			}
		}else{
			$rs=$memberObj->update(array('edm_blacklist'=>'false'),array('member_id'=>$_POST['member_id'][0]));
		}
		if ($rs){
			 $this->end(true,'邮件黑名单删除成功');
		}
	}
	
    //批量加黑
	function batch_add()
    {
        $type = $_GET['type'];
        $this->pagedata['type'] = $type;
        $this->display('admin/member/blacklist/batch_add.html');
    }
    
    //批量加黑
    function batch_save()
    {
        $page_size = 50;
        $blacklist = explode("\n", $_POST['blacklist']);
        $type = $_POST['type'];
        $filter_fields = $_POST['filter_fields'];
        $type == 'edm' ? $act='edm_blaclist' : $act='index';
        $block_count = 0;
        
        $db = kernel::database();
        
        // 分批次执行
        for($i=0;$i<=(sizeof($blacklist)/$page_size);$i++){
            $content = array_slice($blacklist,($i*$page_size),$page_size);
            if(!$content) break;
            if($type == 'edm'){
                $type = 'edm_blacklist';
            }else{
                $type = 'sms_blacklist';
            }
            $sql = "update sdb_taocrm_members set $type='true' where $filter_fields in ('".implode("','", $content)."') ";
            $db->exec($sql);
            $block_count += $db->affect_row();
        }       
    
        $this->begin('index.php?app=taocrm&ctl=admin_member_blacklist&act='.$act);
        $this->end(true, "$block_count");
    }
}
