<?php
class taocrm_finder_active{
    var $pagelimit = 10;

    public function __construct($app){
        $this->app = $app;
        $this->controller = app::get('taocrm')->controller('admin_active');
    }

    var $addon_cols = "status,coupon_id";
    var $column_control = '操作';
    var $column_control_width = 70;
    var $column_control_order = COLUMN_IN_HEAD;
    function column_control($row){
        if($row[$this->col_prefix.'status'] && $row[$this->col_prefix.'status']=='1'){
            $button = '';
        }else{
            $button = '<a href="index.php?app=taocrm&ctl=admin_active&act=execute&p[0]='.$row['active_id'].'">'.'启动'.'</a>';
        }
        return $button;
    }

    var $column_endtime = '结束时间';
    var $column_endtime_width = 130;
    var $column_endtime_order = COLUMN_IN_TAIL;
    function column_endtime($row){
        $couponObj = app::get('taocrm')->model('coupons');
        $coupon = $couponObj->dump($row[$this->col_prefix.'coupon_id'],'*');
        if($row[$this->col_prefix.'coupon_id'] && $coupon['end_time']){
            return date('Y-m-d H:i:s',$coupon['end_time']);
        }else{
            return '-';
        }
    }

    var $column_status = '活动状态';
    var $column_status_width = 70;
    var $column_status_order = COLUMN_IN_TAIL;
    function column_status($row){
        if($row[$this->col_prefix.'status']=='1'){
            return '已启动';
        }else{
            return '未启动';
        }
    }

    var $detail_basic = '基本信息';
    public function detail_basic($active_id){
        $app = app::get('taocrm');
        $activeObj = $app->model('active');
        $active = $activeObj->dump($active_id,'*');

        if($active['coupon_id'] && $active['coupon_id']>0){
            $couponObj = $app->model('coupons');
            $coupon = $couponObj->dump($active['coupon_id'],'coupon_name,end_time');
            $active['coupon_name'] = $coupon['coupon_name'];
            $active['end_time'] = $coupon['end_time'];
        }

        $themeObj = $app->model('message_themes');
        $theme = $themeObj->dump($active['theme_id'],'theme_title');
        $active['theme_title'] = $theme['theme_title'];

        $acMemObj = $app->model('active_member');
        $active['count'] = $acMemObj->count(array('active_id' => $active_id));

        $render = $app->render();
        $render->pagedata['active'] = $active;

        return $render->fetch('admin/active/detail.html');
    }

    var $detail_order = '活动客户';
    function detail_order($active_id=null){
        if(!$active_id) return null;
        $nPage = $_GET['detail_order'] ? $_GET['detail_order'] : 1;

        $app = app::get('taocrm');
        
        $activeObj = $app->model('active');
        $active = $activeObj->dump($active_id,'*');

        $acMemObj = $app->model('active_member');
        $members = $activeObj->getMemberList('*',array('active_id' => $active_id),$this->pagelimit*($nPage-1),$this->pagelimit);
        $count = $acMemObj->count(array('active_id' => $active_id));

        $render = $app->render();
        $render->pagedata['members'] = $members;
        $render->pagedata['active'] = $active;

        if($_GET['page']) unset($_GET['page']);
        $_GET['page'] = 'detail_order';
        $this->controller->pagination($nPage,$count,$_GET);
        return $render->fetch('admin/active/members.html');
    }
}