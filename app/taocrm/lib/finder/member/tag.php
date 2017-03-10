<?php
class taocrm_finder_member_tag{

    var $pagelimit = 10;
    var $addon_cols = 'mobile_valid_nums';

    public function __construct($app)
    {
        $this->app = $app;
        $this->controller = app::get('taocrm')->controller('admin_member_tag');
    }

    var $column_edit = '操作';
    var $column_edit_order = 2;
    function column_edit($row)
    {
        $href = '';
        !in_array($row['tag_type'],array('system_a','system_b','system_c','system_d')) && $href .= '<a href="index.php?app=taocrm&ctl=admin_member_tag&act=edit&p[0]='.$row['tag_id'].'&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '" target="dialog::{title:\''.app::get('taocrm')->_('编辑标签').'\', width:650, height:355}">编辑</a> | ';
        $href .= '<a href="index.php?app=taocrm&ctl=admin_member_tag&act=sendSms&p[0]='.$row['tag_id'].'&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '" target="dialog::{title:\''.app::get('taocrm')->_('发送短信').'\', width:650, height:355}">发送</a>';
        return $href;
    }

    var $column_mobiles = '手机有效人数';
    var $column_mobiles_order = 30;
    function column_mobiles($row)
    {
        $url = 'index.php?app=taocrm&ctl=admin_member_report&act=index&filter_type=member_tag&tag_id='.$row['tag_id'];
        $mobile_valid_nums = $row[$this->col_prefix.'mobile_valid_nums'];
        $href = '';
        $href .= ' <a target="_blank" href="index.php?app=desktop&act=alertpages&goto='.urlencode($url).'" style="background:url(\''.kernel::base_url(0).'/app/desktop/statics/bundle/zoom_btn.gif\') no-repeat right;padding:0 20px 0 0;" />';
        $href .= $mobile_valid_nums . '</a>';
        return $href;
    }

}