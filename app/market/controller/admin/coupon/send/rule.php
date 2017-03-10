<?php
class market_ctl_admin_coupon_send_rule extends desktop_controller{
    var $pagelimit = 10;
    var $workground = 'taocrm.sales';

    public function index(){
        $this->finder('market_mdl_coupon_send_rules',array(
            'title'=>'自动发送设置',
            'actions'=>array(
                array('label'=>'新建规则','href'=>'index.php?app=market&ctl=admin_coupon_send_rule&act=add','target'=>'_blank'),
            ),
            'use_buildin_recycle'=>true,
        ));
    }
    
    public function add(){
        $this->_edit();
    }
    
    public function edit($rule_id){
        $this->_edit($rule_id);
    }
    
    private function _edit($rule_id=NULL){
        if($rule_id && $rule_id>0){
            $ruleObj = &$this->app->model('coupon_send_rules');
            $rule = $ruleObj->dump($rule_id);
            $this->pagedata['rule'] = $rule;
            $this->pagedata['select_coupon'] = $rule['coupon_id'];

            if($rule['theme_id'] && $rule['theme_id']>0){
                $themeObj = &$this->app->model('message_themes');
                $theme = $themeObj->dump($rule['theme_id'],'theme_id,group_id');
                $this->pagedata['select_group'] = $theme['group_id'];
                $themes = $themeObj->getList('theme_id,group_id,theme_title',array('group_id'=>$theme['group_id']));
                $this->pagedata['themes'] = $themes;
            }
        }
        $shopObj = &app::get(ORDER_APP)->model('shop');
        $shopList = $shopObj->getList('shop_id,name');
        $this->pagedata['shopList'] = $shopList;
        
        $couponObj = &$this->app->model('coupons');
        $this->pagedata['coupons'] = $couponObj->getList('coupon_id,coupon_name',array('status'=>1,'shop_id'=>$rule['shop_id'],'end_time|than'=>time()));

        $shopLvObj = &app::get('taocrm')->model('shop_lv');
        $this->pagedata['member_level'] = $shopLvObj->getList('shop_lv_id,name',array('shop_id'=>$rule['shop_id']));

        $groupObj = &app::get('taocrm')->model('message_themes_group');
		$this->pagedata['groupList'] = $groupObj->getList('group_id,group_title');

		$this->pagedata['title'] = '优惠券发送规则添加/编辑';
        $this->singlepage('admin/coupon/send/rule.html');
    }
    
    public function toAdd(){
        $this->begin("index.php?app=taocrm&ctl=admin_coupon_send_rule&act=index");
        $data = $_POST;
        $rule = $data['rule'];
        $rule['config'] = $data['config'];
        $rule['coupon_id'] = $data['coupon_id'];
        $rule['theme_id'] = $data['theme'];

        if(!$rule['rule_id']){
            $rule['createtime'] = time();
        }

        if($data['all_members'] && $data['all_members']=='all'){
            $rule['config']['member_lv_ids'][] = 'all';
        }

        if(!$rule['shop_id'] || $rule['shop_id'] == ''){
            $this->end(false,'请选择规则所适用的店铺！');
        }
        if(!$rule['coupon_id'] || $rule['coupon_id'] == ''){
            $this->end(false,'请选择要发送优惠券！');
        }
        if(!$rule['config']['member_lv_ids']){
            $this->end(false,'请选择发送优惠券的客户对象！');
        }
        if(!$rule['condition'] || $rule['condition'] <= 0){
            $this->end(false,'请确认消费金额！');
        }

        $ruleObj = &$this->app->model('coupon_send_rules');
        if($ruleObj->save($rule)){
            $this->end(true,'操作成功');
        }else{
            $this->end(false,'操作失败');
        }
    }

    public function pagination($current,$count,$get){ //本控制器公共分页函数
        $app = app::get('taocrm');
        $render = $app->render();
        $ui = new base_component_ui($this->app);
        $link = 'index.php?app=taocrm&ctl=admin_coupon_send_rule&act=ajax_html&id='.$get['id'].'&finder_act='.$get['page'].'&'.$get['page'].'=%d';
        $this->pagedata['pager'] = $ui->pager(array(
            'current'=>$current,
            'total'=>ceil($count/$this->pagelimit),
            'link' =>$link,
        ));
    }
    
    public function ajax_html(){
        $finder_act = $_GET['finder_act'];
        $html = $this->$finder_act($_GET['id']);
        echo $html;
    }

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
        $this->pagination($nPage,$count,$_GET);
        echo $render->fetch('admin/coupon/send/page_members.html');
    }
}