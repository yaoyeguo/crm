<?php
class market_service_coupon{

    /**
     * 对应店铺信息
     * @var Array
     */
    protected $_shopInfo = array();

    function __construct(){
        $this->app = app::get('market');
    }

     

    /**
     * 活动添加与更新
     *
     * @access private
     * @param array $couponInfo 客户信息
     * @param string $shopId 店铺ID
     * @return int 客户ID
     */
    public function saveCoupon($couponInfo) {

        if (empty($couponInfo)) {
            return null;
        }

        $couponDetail = array();
        $couponId = null;
        /*$structs = array(
         'coupon_id' => 'coupon_id',
         'outer_coupon_id' => 'outer_coupon_id',
         'active_id' => 'active_id',
         'outer_activity_id' => 'outer_activity_id',
         'outer_activity_url' => 'outer_activity_url',
         'coupon_name' => 'coupon_name',
         'shop_id' => 'shop_id',
         'status' => 'status',
         'created' => 'created',
         'updated' => 'updated',
         'end_time' => 'end_time',
         'denominations' => 'denominations',
         'conditions' => 'conditions',
         'coupon_count' => 'coupon_count',
         'person_limit_count' => 'person_limit_count',
         'used_num' => 'used_num',
         'f_sync_coupon' => 'f_sync_coupon',
         'f_sync_coupon_msg' => 'f_sync_coupon_msg',
         'f_sync_activity_msg' => 'f_sync_activity_msg',
         'f_sync_activity' => 'f_sync_activity',
         'remark' => 'remark',
         'source' => 'source',
         );*/
        $structs = app::get('market')->model('coupons')->get_structs();
        $couponsData = utils::structToArray($structs,$couponInfo);

        if(!$couponsData['coupon_id']){
            $couponsData['created'] = time();
        }

        $couponsData['updated'] = time();

        if($this->app->model('coupons')->save($couponsData)){
            $couponId = $couponsData['coupon_id'];
        }
        return $couponId;
    }

    public function requestCoupon($couponId,& $msg){
        $coupon = $this->app->model('coupons')->dump($couponId,'coupon_id,status,shop_id,conditions,start_time,end_time,denominations');
        $rpcobj = kernel::single('market_rpc_request_coupon');
        $msg = $rpcobj->add($coupon);
        if($msg == 'success'){
            return true;
        }else{
            return false;
        }
    }

    public function saveCouponDetail($couponInfo) {

        if (empty($couponInfo)) {
            return null;
        }

        //$couponDetail = array();
        $couponId = null;
        $structs = app::get('market')->model('coupon_used')->get_structs();
        $couponsData = utils::structToArray($structs,$couponInfo);

        if($this->app->model('coupon_used')->save($couponsData)){
            $couponId = $couponsData['coupon_id'];
        }
        return $couponId;
    }

    public function saveCouponSent($sentInfo) {

        if (empty($sentInfo)) {
            return null;
        }

        $sendId = null;
        $structs = app::get('market')->model('coupon_sent')->get_structs();
        $sentData = utils::structToArray($structs,$sentInfo);

        if($this->app->model('coupon_sent')->save($sentData)){
            $sendId = $sentData['sent_id'];
        }
        return $sendId;
    }

    //调用ecstore的优惠券接口
    public function getCouponFromEcstore($data){
        //$api_url = 'http://rpc.ex-sandbox.com/sync';//内网
        $api_url = MATRIX_SYNC_URL_M;
        $headers = array('Connection' => 20);
        $core_http = kernel::single('base_httpclient');

        $app_exclusion = app::get('base')->getConf('system.main_app');
        $params['app_id'] = 'ecos.taocrm';#写死app_id
        $params['from_node_id'] = base_shopnode::node_id($app_exclusion['app_id']);//'1531333636';
        $params['to_node_id'] = $data['node_id'];//'1437333036';
        $params['method'] = 'store.member.getCoupon';
        $params['member_id'] = $data['member_id']; #会员id
        $token = base_shopnode::get('token',$app_exclusion['app_id']);//'7da23d54b6e2f1a099197e3e83a00ba527ce8fcfd210a705d3418ebca75c87e5';
        $params['sign'] = $this->gen_matrix_sign($params,$token);
        $response = $core_http->post($api_url, $params,$headers);
        $response_arr = json_decode($response,true);
        return $response_arr;
    }

    //调用ecstore的推荐设置更新接口
    public function store_referrals_update($data){
        //$api_url = 'http://rpc.ex-sandbox.com/sync';//内网
        $api_url = MATRIX_SYNC_URL_M;
        $headers = array('Connection' => 20);
        $core_http = kernel::single('base_httpclient');

        $app_exclusion = app::get('base')->getConf('system.main_app');
        $params['app_id'] = 'ecos.taocrm';#写死app_id
        $params['from_node_id'] = base_shopnode::node_id($app_exclusion['app_id']);
        //$params['from_node_id'] = '1531333636';
        $params['to_node_id'] = $data['node_id'];//'1437333036';
        $params['method'] = 'store.referrals.update';
        $params['status'] = $data['recommend_status']; #开启状态
        $params['points'] = $data['recommend_point']; #推荐获取积分
        $token = base_shopnode::get('token',$app_exclusion['app_id']);//'7da23d54b6e2f1a099197e3e83a00ba527ce8fcfd210a705d3418ebca75c87e5';
        //$token = '7da23d54b6e2f1a099197e3e83a00ba527ce8fcfd210a705d3418ebca75c87e5';
        $params['sign'] = $this->gen_matrix_sign($params,$token);
        $response = $core_http->post($api_url, $params,$headers);
        $response_arr = json_decode($response,true);
        return $response_arr;
    }

    //调用ecstore的签到更新接口
    public function store_member_signin($data){
        //$api_url = 'http://rpc.ex-sandbox.com/sync';//内网
        $api_url = MATRIX_SYNC_URL_M;
        $headers = array('Connection' => 20);
        $core_http = kernel::single('base_httpclient');

        $app_exclusion = app::get('base')->getConf('system.main_app');
        $params['app_id'] = 'ecos.taocrm';#写死app_id
        $params['from_node_id'] = base_shopnode::node_id($app_exclusion['app_id']);//'1531333636';
        $params['to_node_id'] = $data['node_id'];//'1437333036';
        $params['method'] = 'store.member.signin';
        $params['member_id'] = $data['member_id']; #会员id
        $params['signin_time'] = $data['signin_time']; #签到时间戳
        $token = base_shopnode::get('token',$app_exclusion['app_id']);//'7da23d54b6e2f1a099197e3e83a00ba527ce8fcfd210a705d3418ebca75c87e5';
        $params['sign'] = $this->gen_matrix_sign($params,$token);
        $response = $core_http->post($api_url, $params,$headers);
        $response_arr = json_decode($response,true);
        return $response_arr;
    }

    function gen_matrix_sign($params,$token){
        return strtoupper(md5(strtoupper(md5($this->assemble($params))).$token));
    }
    function assemble($params)
    {
        if(!is_array($params)){
            return null;
        }

        ksort($params,SORT_STRING);
        $sign = '';
        foreach($params AS $key=>$val){
            $sign .= $key . (is_array($val) ? assemble($val) : $val);
        }
        return $sign;
    }

}


