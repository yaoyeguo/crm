<?php

class taocrm_ctl_admin_member_card extends desktop_controller{

	var $workground = 'taocrm.member';

	public function __construct($app)
	{
		parent::__construct($app);
			
	}

	public function index(){
		$title = '会员卡管理';
		$actions = array(

		);

		$actions[] = array(
            'label'=>'添加会员卡',
            'href'=>'index.php?app=taocrm&ctl=admin_member_card&act=add',
			'target'=>'dialog::{width:650,height:320,title:\'添加会员卡规则\'}'
			);

			$baseFilter = array();

			//var_dump($baseFilter);
			$this->finder('taocrm_mdl_member_card_template',array(
            'title'=> $title,
            'base_filter'=>$baseFilter,
            'actions'=>$actions,
        	'orderBy' => 'update_time DESC',
            'use_buildin_import'=>false,
            'use_buildin_export'=>false,
            'use_buildin_recycle'=>false,
            'use_buildin_filter'=>false,
            'use_buildin_tagedit'=>false,
            'use_view_tab'=>false,
            'use_buildin_setcol'=>false,//列配置
            'use_buildin_refresh'=>false,//刷新
			));
	}

	public function add(){

		$memberCardTemplate = array('type_id_selected'=>0,'is_type_code'=>'1','card_len'=>6,'card_pwd_len'=>4,'card_pwd_rule'=>'0');
		$this->pagedata['formdata'] = $memberCardTemplate;
		$this->initFrom();
	}

	public function edit(){

		$memberCardTemplateObj = &$this->app->model('member_card_template');
		$memberCardTemplate = $memberCardTemplateObj->dump($_GET['id']);
		//var_dump($memberCardTemplate);exit;
		$this->pagedata['formdata'] = $memberCardTemplate;
		$this->initFrom();
	}

	public function initFrom(){

		$memberCardTypeObj = &$this->app->model('member_card_type');
		$memberCardTypeList = $memberCardTypeObj->getList('id,type_name,type_code',array(),0,-1,'update_time desc');
		$rows = array();
		/*if($memberCardTypeList){
			foreach($memberCardTypeList as $row){
			$rows[$row['id']] = $row['type_name'];
			}
			$this->pagedata['memberCardTypeList'] = $rows;
			if(empty($this->pagedata['formdata']['member_card_type_id'])){
			$this->pagedata['formdata']['member_card_type_id'] = $memberCardTypeList[0]['id'];
			}
			}*/
		if(empty($this->pagedata['formdata']['member_card_type_id'])){
			$this->pagedata['formdata']['member_card_type_id'] = $memberCardTypeList[0]['id'];
		}
		$this->pagedata['jsonMemberCardTypeList'] = json_encode($memberCardTypeList);

		$this->pagedata['memberCardIsType'] = array('1'=>'是','0'=>'否');
		$this->pagedata['memberCardLen'] = array(6=>6,7=>7,8=>8,9=>9,10=>10);
		$this->pagedata['memberCardPwd'] = array(4=>4,5=>5,6=>6,7=>7,8=>8);
		$this->pagedata['memberCardPwdRule'] = array('0'=>'纯字母','1'=>'纯数字','2'=>'字母数字混合');
		$this->display('admin/member/card.html');
	}

	public function save(){
		$this->begin('index.php?app=taocrm&ctl=admin_member_card&act=index');
		$memberCardTemplateObj = &$this->app->model('member_card_template');
		$this->end($memberCardTemplateObj->save($_POST));
	}

	public function make_card(){

		$this->pagedata['id'] = $_GET['id'];
		$this->display('admin/member/make_card.html');
	}

	public function do_make_card(){
		$this->begin('index.php?app=taocrm&ctl=admin_member_card&act=index');
		if($_POST['make_count'] > 5000){
			$this->end(false,'每批次最多生成5000张');
		}
		$memberCardObj = &$this->app->model('member_card');
		$msg = '';
		$this->end($memberCardObj->doMakeCard($_POST['id'],$_POST['make_count'],$msg),$msg);
	}

	public function get_log_info($logId,$page){
		$pagelimit = 20;
		$page = $page ? $page : 1;
		$memberCardItems = app::get('taocrm')->model('member_card')->getPager(array('member_card_make_log_id'=>$logId),'*',$pagelimit * ($page - 1), $pagelimit);
		$card_status_arr = array ('unactive' => '未激活','active' => '激活','loss' => '挂失','logout'=>'注销');
		$membersObj = &app::get('taocrm')->model('members');
		foreach($memberCardItems['data'] as $k=>$memberCard){
			$memberCard['card_status'] = $card_status_arr[$memberCard['card_status']];
			if($memberCard['member_id']){
				$memberCard['member'] = $membersObj->dump($memberCard['member_id'],'uname,mobile');
			}
			$memberCardItems['data'][$k] = $memberCard;
		}
			
		$count = $memberCardItems ['count'];
		$total_page = ceil ( $count / $pagelimit );
		$pager = $this->ui ()->pager ( array ('current' => $page, 'total' => $total_page, 'link' => 'index.php?app=taocrm&ctl=admin_member_card&act=get_log_info&p[0]='.$logId.'&p[1]=%d' ) );
		$this->pagedata['pager'] = $pager;

		$this->pagedata['memberCardList'] = $memberCardItems['data'];
		$this->display('admin/member/card/make_log_info.html');
	}

	public function export_member_card($logId){
		$this->begin();

		$memberCardObj = &app::get('taocrm')->model('member_card');
		$member_card_list = $memberCardObj->getList('card_no,card_pwd,send_card_channel,card_status',array('member_card_make_log_id'=>$logId),0,-1);
		if(empty($member_card_list)){
			echo '没数据';
			exit;
		}
		
		$memberCardMakeLogObj = &app::get('taocrm')->model('member_card_make_log');
		$log = $memberCardMakeLogObj->dump($logId,'batch_no');

		$title = array('序号','会员卡号','会员卡密','发卡渠道','激活状态');
		$content = array();
		$content[] = implode(",", $title);
		$card_status_arr = array ('unactive' => '未激活','active' => '激活','loss' => '挂失','logout'=>'注销');
		foreach($member_card_list as  $k=>$card){
			$row = array($k+1,$card['card_no'],$card['card_pwd'],$card['send_card_channel'],$card_status_arr[$card['card_status']]);
			$content[] = implode(",", $row);
		}
		
		header("Content-Type: text/csv");
        $filename = '导出会员卡-'.$log['batch_no'].'('.date('Y-m-d').').csv';
        $encoded_filename = urlencode($filename);
        $encoded_filename = str_replace("+", "%20", $encoded_filename);
        
        $ua = $_SERVER["HTTP_USER_AGENT"];
        if (preg_match("/MSIE/", $ua)) {
            header('Content-Disposition: attachment; filename="' . $encoded_filename . '"');
        } else if (preg_match("/Firefox/", $ua)) {
            header('Content-Disposition: attachment; filename*="utf8\'\'' . $filename . '"');
        } else {
            header('Content-Disposition: attachment; filename="' . $filename . '"');
        }
        //header("Content-Disposition: attachment; filename=".$data['name'].'.csv');  
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');  
        header('Expires:0');
        header('Pragma:public');
        
		echo iconv('utf-8','gb2312',implode("\n", $content));

		/*while(!feof($url))
		 {
		 echo fgets($url). "\n";
		 }
		 fclose($url);echo 21;exit;*/
		exit;
	}

}
