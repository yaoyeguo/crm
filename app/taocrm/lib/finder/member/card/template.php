<?php
class taocrm_finder_member_card_template{
	var $addon_cols = "";

	var $column_edit = "操作";
	var $column_edit_width = 100;
	var $column_edit_order = COLUMN_IN_HEAD;
	function column_edit($row)
	{
		$id = $row['id'];
		$button1  = '<a href="index.php?app=taocrm&ctl=admin_member_card&act=edit&id='.$id.'" target="dialog::{width:650,height:320,title:\'编辑会员卡规则\'}">编辑</a>';

        if($row['card_type'] == '0'){
            $button1  .= ' | <a href="index.php?app=taocrm&ctl=admin_member_card&act=make_card&id='.$id.'" target="dialog::{width:650,height:320,title:\'自动生成会员卡\'}">生成会员卡</a>';
        }

		return  $button1;
	}

	var $detail_make_card = '生成记录';
	function detail_make_card($templateId=null){
		if(!$templateId) return null;
		$memberCardMakeLogObj = &app::get('taocrm')->model('member_card_make_log');
		$memberCardObj = &app::get('taocrm')->model('member_card');
		$logs = $memberCardMakeLogObj->getList('*',array('member_card_template_id'=>$templateId),0,-1,'create_time desc');
		foreach ($logs as $k=>$log){
			if($Log['is_type_code'] == '0'){
				$log['is_type_code'] = '否';
			}else{
				$log['is_type_code'] = '是';
			}

			if($log['card_pwd_rule'] == '0'){
				$log['card_pwd_rule'] = '纯字母';
			}else if($log['card_pwd_rule'] == '1'){
				$log['card_pwd_rule'] = '纯数字';
			}else if($log['card_pwd_rule'] == '2'){
				$log['card_pwd_rule'] = '字母数字混合';
			}
			
			$log['bind_count'] = $memberCardObj->count(array('member_card_make_log_id'=>$log['id'],'card_status'=>'active'));
			
			$logs[$k] = $log;
		}

		$app = app::get('taocrm');
		$render = $app->render();
		$render->pagedata['logs'] = $logs;
		return $render->fetch('admin/member/card/make_loglist.html');
	}

}