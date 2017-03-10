<?php
class marketcenter_ctl_admin_cards extends desktop_controller {
    var $workground = 'marketcenter.workground.setting';
    public function __construct($app){
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
    }
    function index() {
        $group[] = array('label'=>app::get('marketcenter')->_('折扣劵'),'href'=>'index.php?app=marketcenter&ctl=admin_cards&act=add&type=DISCOUNT','target'=>'_blank');
        $group[] = array('label'=>app::get('marketcenter')->_('代金劵'),'href'=>'index.php?app=marketcenter&ctl=admin_cards&act=add&type=CASH','target'=>'_blank');
        $group[] = array('label'=>app::get('marketcenter')->_('礼品劵'),'href'=>'index.php?app=marketcenter&ctl=admin_cards&act=add&type=GIFT','target'=>'_blank');
        $group[] = array('label'=>app::get('marketcenter')->_('团购劵'),'href'=>'index.php?app=marketcenter&ctl=admin_cards&act=add&type=GROUPON','target'=>'_blank');
        $group[] = array('label'=>app::get('marketcenter')->_('优惠券'),'href'=>'index.php?app=marketcenter&ctl=admin_cards&act=add&type=GENERAL_COUPON','target'=>'_blank');
        $this->finder('marketcenter_mdl_cards',array(
            'title' => app::get('marketcenter')->_('卡劵列表'),
            'actions' => array(array('label' => app::get('marketcenter')->_('添加卡券'),
                'group'=>$group,
                ),),
            ));
        $this->page('cards/index.html');
    }
    function add($type = null){
        $shopObj = app::get('ecorder')->model('shop');
        $shopList=$shopObj->getList("name,node_id",array('shop_type'=>'wechat'));
        $this->pagedata['shop'] = $shopList;
        $this->pagedata['card']['card_type'] = $_GET['type'];
        $this->pagedata['sections'] = $this->_sections($_GET['type']);
        header("Cache-Control:no-store");
        $this->singlepage('cards/promotion/frame.html');
    }

    private function _sections($type) {
        return  array(
                'basic'=> array(
                    'label'=>app::get('marketcenter')->_('基本信息'),
                    'options'=>'',
                    'file'=>'cards/promotion/basic.html',
                ),
                'receive'=> array(
                    'label'=>app::get('marketcenter')->_('领劵设置'),
                    'options'=>'',
                    'file'=>'cards/promotion/receive.html',
                ),
                'delivery'=> array(
                    'label'=>app::get('marketcenter')->_('销劵设置'),
                    'options'=>'',
                    'file'=>'cards/promotion/delivery.html',
                ),
                'content'=> array(
                    'label'=>app::get('marketcenter')->_('优惠券详情'),
                    'options'=>'',
                    'file'=>'cards/promotion/content.html',
                ),
                'shop'=> array(
                    'label'=>app::get('marketcenter')->_('服务信息'),
                     'options'=>'',
                    //'file'=>'cards/promotion/shop.html',
                ),
            );
    }
    public function toAdd() {

        $this->begin();
        $aData = $this->_prepareRuleData($_POST);
        $mSRO = $this->app->model("cards");
        $bResult = $mSRO->save($aData);
        $this->end($bResult,app::get('marketcenter')->_('操作成功'));
    }
    private function _prepareRuleData($aData) {

        $aResult = $aData['card'];
        if(!$aResult['node_id']) $this->end( false,'请选择节点！' );
        if(!$aResult['logo']) $this->end( false,'卡劵商家LOGO不能为空！' );
        $pic = explode('?', base_storager::image_path($aResult['logo']));
        $pic_url = explode('public',$pic[0]);
        $url = ROOT_DIR.'/public'.$pic_url[1];
        $data = file_get_contents($url);
        $data = base64_encode($data);
        $weixin_card = kernel::single('marketcenter_service_card');
        $aResult['logo_url'] = $weixin_card->uploadlogo($data,$aResult['node_id']);
        if(!$aResult['logo_url']) $this->end( false,'卡劵商家LOGO为空,微信卡劵LOGO接口出错！' );
        if(!$aResult['title']) $this->end( false,'优惠券标题不能为空！' );
        if(!$aResult['color']) $this->end( false,'优惠券颜色不能为空！' );
        if($aResult['type'] == 'DATE_TYPE_FIX_TIME_RANGE'){
            $aResult['begin_timestamp'] = strtotime($aResult['begin_timestamp']);
            $aResult['end_timestamp'] = strtotime($aResult['end_timestamp']);
            if($aResult['begin_timestamp']<time()){
                $this->end( false,'起用日期不能早于现在日期！');
            }
            if($aResult['end_timestamp']<time()){
                $this->end( false,'结束日期不能早于现在日期！');
            }
            if($aResult['end_timestamp']<$aResult['begin_timestamp']){
                $this->end( false,'结束日期不能早于起用日期！');
            }
        }
        if(!$aResult['quantity']) $this->end( false,'优惠券库存不能为空！' );
        if($aResult['get_limit'] > $aResult['quantity']) $this->end( false,'个人领劵数量不能超过库存数量！' );
        if(empty($aResult['get_limit'])){
            $aResult['get_limit'] = 1;
        }
        if(empty($aResult['can_share'])){
            $aResult['can_share'] = 'false';
        }
        if(empty($aResult['can_give_friend'])){
            $aResult['can_give_friend'] = 'false';
        }

        if(!$aResult['code_type']) $this->end( false,'必须选择销劵方式！' );
        if(isset($aResult['default_detail'])&&!$aResult['default_detail']) $this->end( false,'必须填写优惠详情！' );
        if(isset($aResult['discount'])&&!$aResult['discount']) $this->end( false,'必须填写打折额度！' );
        if(isset($aResult['discount'])&& ($aResult['discount']>=100 || $aResult['discount']<=0)) $this->end( false,'打折额度格式错误！' );
        if(isset($aResult['least_cost'])&&!$aResult['least_cost']) $this->end( false,'必须填写起用金额！' );
        if(isset($aResult['reduce_cost'])&&!$aResult['reduce_cost']) $this->end( false,'必须填写减免金额！' );
        if(isset($aResult['reduce_cost'])&&(!is_numeric($aResult['least_cost'])||!is_numeric($aResult['reduce_cost']))) $this->end( false,'金额格式错误！' );
        if(isset($aResult['gift'])&&!$aResult['gift']) $this->end( false,'必须填写优惠详情！' );
        if(isset($aResult['deal_detail'])&&!$aResult['deal_detail']) $this->end( false,'必须填写团购详情！' );
        if(!$aResult['description']) $this->end( false,'必须填写使用须知！' );
        
        $aResult['card_id'] = $this->create_card($aResult);
        $user = kernel::single('desktop_user');
        $aResult['creater'] =$user->get_login_name();
        $aResult['create_time'] = time();

        if(!empty($aResult['card_id'])){
            $aResult['status'] ='update';
        }else{
            $aResult['status'] ='unupdate';
        }
        return $aResult;
    }
    function create_card($bData){
        $bResult = array();
        if(!empty($bData['card_type']))$bResult['card_type'] = $bData['card_type'];
        if(!empty($bData['logo_url']))$bResult['logo_url'] = $bData['logo_url'];
        if(!empty($bData['brand_name']))$bResult['brand_name'] = $bData['brand_name'];
        if(!empty($bData['code_type']))$bResult['code_type'] = $bData['code_type'];
        if(!empty($bData['title']))$bResult['title'] = $bData['title'];
        if(!empty($bData['sub_title']))$bResult['sub_title'] = $bData['sub_title'];
        if(!empty($bData['color']))$bResult['color'] = $bData['color'];
        if(!empty($bData['notice']))$bResult['notice'] = $bData['notice'];
        if(!empty($bData['service_phone']))$bResult['service_phone'] = $bData['service_phone'];
        if(!empty($bData['description']))$bResult['description'] = $bData['description'];
        if(!empty($bData['type']))$bResult['date_info']['type'] = $bData['type'];
        if($bData['type'] == 'DATE_TYPE_FIX_TIME_RANGE'){
            $bResult['date_info']['begin_timestamp'] = $bData['begin_timestamp'];
            $bResult['date_info']['end_timestamp'] = $bData['end_timestamp'];
        }
        if($bData['type'] == 'DATE_TYPE_FIX_TERM'){
            $bResult['date_info']['fixed_term'] = $bData['fixed_term'];
            $bResult['date_info']['fixed_begin_term'] = $bData['fixed_begin_term'];
        }
        $bResult['date_info'] = json_encode($bResult['date_info']);
        if(!empty($bData['quantity']))$bResult['sku']['quantity'] = $bData['quantity'];
        $bResult['sku'] = json_encode($bResult['sku']);
        if(!empty($bData['get_limit']))$bResult['get_limit'] = $bData['get_limit'];
        if(!empty($bData['use_custom_code']))$bResult['use_custom_code'] = $bData['use_custom_code'];
        if(!empty($bData['bind_openid']))$bResult['bind_openid'] = $bData['bind_openid'];

        if(!empty($bData['can_share']))$bResult['can_share'] = $bData['can_share'];
        if(!empty($bData['can_give_friend']))$bResult['can_give_friend'] = $bData['can_give_friend'];

        if(!empty($bData['location_id_list']))$bResult['location_id_list'] = $bData['location_id_list'];
        if(!empty($bData['custom_url_name']))$bResult['custom_url_name'] = $bData['custom_url_name'];
        if(!empty($bData['custom_url']))$bResult['custom_url'] = $bData['custom_url'];
        if(!empty($bData['custom_url_sub_title']))$bResult['custom_url_sub_title'] = $bData['custom_url_sub_title'];
        if(!empty($bData['promotion_url_name']))$bResult['promotion_url_name'] = $bData['promotion_url_name'];
        if(!empty($bData['promotion_url']))$bResult['promotion_url'] = $bData['promotion_url'];
        if(!empty($bData['source']))$bResult['source'] = $bData['source'];
        if(!empty($bData['default_detail']))$bResult['default_detail'] = $bData['default_detail'];
        $weixin_card = kernel::single('marketcenter_service_card');
        $card_id = $weixin_card->create($bResult,$bData['node_id']);
        return $card_id;
    }
}