<?php
class taocrm_finder_coupon_send_rule{
    var $pagelimit = 10;

    public function __construct($app){
        $this->app = $app;
        $this->controller = app::get('taocrm')->controller('admin_coupon_send_rule');
    }

    var $column_control = '操作';
    var $column_control_width = 50;
    var $column_control_order = COLUMN_IN_HEAD;
    function column_control($row){
        return '<a href="index.php?app=taocrm&ctl=admin_coupon_send_rule&act=edit&p[0]='.$row['rule_id'].'&finder_id='.$_GET['_finder']['finder_id'].'" target="_blank">'.'编辑'.'</a>';
    }

    var $detail_basic = '基本信息';
    public function detail_basic($rule_id){
        $app = app::get('taocrm');
        $render = $app->render();

        $ruleObj = $app->model('coupon_send_rules');
        $rule = $ruleObj->dump($rule_id,'*');

        $shopObj = &app::get(ORDER_APP)->model('shop');
        $shop = $shopObj->dump($rule['shop_id'],'*');
        $rule['shop_id'] = $shop['name'];

        $couponObj = &$app->model('coupons');
        $coupon = $couponObj->dump($rule['coupon_id'],'*');
        $rule['coupon_name'] = $coupon['coupon_name'];

        if($rule['config']['member_lv_ids'][0] == 'all'){
            $memberLvs[] = array(
                'shop_lv_id' => '0',
                'name' => '所有客户',
            );
        }else{
            $shopLvObj = &$app->model('shop_lv');
            $memberLvs = $shopLvObj->getList('shop_lv_id,name',array('shop_lv_id'=>$rule['config']['member_lv_ids']));
        }
        $rule['config']['member_lv_ids'] = $memberLvs;

        
        $ruleMemberObj = $app->model('coupon_rule_member');
        $rule['count'] = $ruleMemberObj->count(array('rule_id' => $rule_id));
        
        $render->pagedata['rule'] = $rule;

        return $render->fetch('admin/coupon/send/detail.html');
    }

    var $detail_member = '发送客户';
    function detail_member($rule_id=null){
        if(!$rule_id) return null;
        $nPage = $_GET['detail_member'] ? $_GET['detail_member'] : 1;

        $app = app::get('taocrm');
        $memberObj = $app->model('members');
        $couponObj = $app->model('coupons');
        $themeObj = $app->model('message_themes');

        $ruleMemberObj = $app->model('coupon_rule_member');
        $members = $ruleMemberObj->getList('*',array('rule_id' => $rule_id),$this->pagelimit*($nPage-1),$this->pagelimit);
        $count = $ruleMemberObj->count(array('rule_id' => $rule_id));
        foreach($members as $key=>$member){
            if($member['member_id'] && $member['member_id']>0){
                $memInfo = $memberObj->getList('uname',array('member_id' => $member['member_id']));
                $member['member_id'] = $memInfo[0]['uname'];
            }
            if($member['coupon_id'] && $member['coupon_id']>0){
                $couponInfo = $couponObj->getList('coupon_name',array('coupon_id' => $member['coupon_id']));
                $member['coupon_id'] = $couponInfo[0]['coupon_name'];
            }
            if($member['theme_id'] && $member['theme_id']>0){
                $themeInfo = $themeObj->getList('theme_title',array('theme_id' => $member['theme_id']));
                $member['theme_id'] = $themeInfo[0]['theme_title'];
            }
            $members[$key] = $member;
        }

        $render = $app->render();
        $render->pagedata['members'] = $members;

        if($_GET['page']) unset($_GET['page']);
        $_GET['page'] = 'detail_member';
        $this->controller->pagination($nPage,$count,$_GET);
        return $render->fetch('admin/coupon/send/members.html');
    }
}