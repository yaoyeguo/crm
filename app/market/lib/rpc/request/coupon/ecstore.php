<?php
class market_rpc_request_coupon_ecstore{

    /**
     * 对应店铺信息
     * @var Array
     */
    protected $_shopInfo = array();
    //protected $requestUrl = 'http://rpc.ex-sandbox.com/sync';
    protected $requestUrl = 'http://matrix.ecos.shopex.cn/sync';

    function __construct(){
        $this->app = app::get('market');
    }

     

   /*
    * array(2) {
  [0]=>
  array(9) {
    ["description"]=>
    string(111) "全场购物满300减50。

只要购物车内商品金额满300，输入该优惠券号码，就能立减50！"
    ["coupon_type"]=>
    string(1) "B"
    ["start_time"]=>
    string(10) "1254326400"
    ["user_lv_id"]=>
    string(51) "普通会员,特殊贵宾,白金会员,黄金会员"
    ["coupon_id"]=>
    int(1)
    ["coupon_bn"]=>
    string(10) "Bqcm300j50"
    ["end_time"]=>
    string(10) "1604073600"
    ["coupon_status"]=>
    string(1) "1"
    ["coupon_name"]=>
    string(23) "全场购物满300减50"
  }
}
    * 
    */
    function getCoupon($shopId,& $msg){
        $shopObj = app::get(ORDER_APP)->model('shop');
        $shop = $shopObj->dump($shopId,'node_id');
        $app_exclusion = app::get('base')->getConf('system.main_app');
        $app_id = $app_exclusion['app_id'];
        
        $param = array(
        'method'=>'store.coupon.list.get',
        'from_node_id'=>base_shopnode::node_id($app_id),
        'to_node_id'=> $shop['node_id'],
        'format'=>'json',
        );

        //$param['sign'] = $this->get_sign($param,base_certificate::token());
        //$param['sign'] = $this->get_sign($param, base_shopnode::get_token());

        //$http = kernel::single("base_httpclient");
        //$result  = $http->post($this->requestUrl,$param);
        $api_obj = new ectools_api_prism_request();
        $result = $api_obj->get_api($param,$shopId);
        $result = json_decode($result,true);
        if($result){
            $couponList = json_decode($result['data'],true);
            if(!is_array($couponList)){
                $couponList = array();
            }
        }else{
            $msg = '请求优惠劵接口失败!';
            return false;
        }

        //echo '<pre>'; var_dump(json_decode($result['data'],true));exit;

        return $couponList;
    }

    function getCouponInfo(){
        $http = kernel::single("base_httpclient");
        $param = array(
        'method'=>'store.coupon.send',
        'from_node_id'=>'1030313432',
        'to_node_id'=>'1736323932',
        'format'=>'json',
        'coupon_id'=>'1',
        'num'=>10
        );
        //$param['sign'] = $this->get_sign($param,base_certificate::token());
        $param['sign'] = $this->get_sign($param,base_shopnode::get_token());
        var_dump($param);

        $result  = $http->post('http://rpc.ex-sandbox.com/sync',$param);

        var_dump(json_decode($result,true));exit;
    }

    function sendUserCoupon(){
        $http = kernel::single("base_httpclient");
        $param = array(
        'method'=>'store.coupon.user.send',
        'from_node_id'=>'1030313432',
        'to_node_id'=>'1736323932',
        'format'=>'json',
        'coupon_id'=>'1',
        'num'=>1,
        'user_id'=>'39,40',
        );
        //$param['sign'] = $this->get_sign($param,base_certificate::token());
        $param['sign'] = $this->get_sign($param,base_shopnode::get_token());
        var_dump($param);

        $result  = $http->post('http://rpc.ex-sandbox.com/sync',$param);

        var_dump(json_decode($result,true));exit;
    }

    function getCouponUseLog()
    {
        $http = kernel::single("base_httpclient");
        $param = array(
        'method'=>'store.coupon.use.log.get',
        'from_node_id'=>'1030313432',
        'to_node_id'=>'1736323932',
        'format'=>'json',
        'coupon_id'=>'3',
        );
        //$param['sign'] = $this->get_sign($param,base_certificate::token());
        $param['sign'] = $this->get_sign($param, base_shopnode::get_token());
        var_dump($param);

        $result  = $http->post('http://rpc.ex-sandbox.com/sync',$param);

        var_dump(json_decode($result,true));exit;
    }

    function get_sign($params,$token){
        return strtoupper(md5(strtoupper(md5($this->assemble($params))).$token));
        // return strtolower(md5($this->assemble($params).strtolower(md5($token)) ));
    }

    function assemble($params){
        if(!is_array($params))  return null;
        ksort($params,SORT_STRING);
        $sign = '';
        foreach($params AS $key=>$val){
            $sign .= $key . (is_array($val) ? $this->assemble($val) : $val);
        }
        return $sign;
    }


}


