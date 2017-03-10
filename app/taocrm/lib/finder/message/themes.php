<?php
class taocrm_finder_message_themes{
    var $column_control = '操作';
    public $column_control_order = 4;
    function column_control($row){
        if($row['status'] && $row['status']==1){
            $button = '';
        }else{
            $button = '<a href="index.php?app=taocrm&ctl=admin_sms&act=edit_theme&p[0]='.$row['theme_id'].'&_finder[finder_id]='.$_GET['_finder']['finder_id'].'" target="dialog::{title:\''.app::get('taocrm')->_('编辑短信模板').'\', width:680, height:260}">'.'编辑'.'</a>';
        }
        return $button;
    }
    
    
	public $addon_cols = "theme_content";    
	public $column_smsTemplateView = '预览';
	public $column_smsTemplateView_order = 50;
	public function column_smsTemplateView($row) {
		$params = array(
			'uname' => '张三',
			'coupon' => '满100送5元',
			'name' => '李四的店铺',
		);
				
		$button = '<a href="javascript:void(0);" title="'
				. taocrm_messenger_smsTemplate::convertTemplateToContent($params, $row[$this->col_prefix . 'theme_content'])
				. '">' . '预览' . '</a>';

		return $button;
	}
}