<?php
include_once(dirname(__FILE__)."/lib/oauth2.php");

class market_backstage_publish{

    private $api_url = 'https://openapi.ishopex.cn/apis#api:prism-notify/publish';
    private $prism_site = 'https://openapi.ishopex.cn';
    private $key = 'oywnjsux';
    private $secret = 'h6ay6rx6e23caugon3md';
    private $routing_key = 'bnow.stat.crm';
    private $doc_url = 'http://wiki.dev.shopex.cn/index.php?title=Prodata';

    /**
     * 大屏幕数据采集推送
     * data[day] : Y-m-d
     */
    function push($data, $type='order')
    {
        if(!$data['day']){
            $data['day'] = strtotime('-1 day');
        }else{
            $data['day'] = strtotime($data['day']);
        }

        $this->push_member($data['day'], $data['day']+86400);
    }

    /**
     * 订单数据
     * 只处理已付款订单
     */
    function push_order($order_sdf)
    {
        if($order_sdf['pay_status'] == '0'){
            return false;
        }

        if(rand(1,100) <=5){
            return false;
        }

        $order_info = $this->get_order_info($order_sdf);

        if(!$order_info) return false;
        $this->http_to_bnow($order_info);
    }

    public function get_order_info($order_sdf)
    {
        //非当天数据不传
        if(date('Y-m-d', $order_sdf['createtime']) != date('Y-m-d')){
            return false;
        }

        $order_info = array(
            '@class' => 'prodata-order',
            'tid' => $order_sdf['order_bn'],
            'from_type' => strval($order_sdf['shop_type']),
            'from_nodeid' => base_rpc_service::$node_id,
            'amount' => $order_sdf['payed'],
            'prod_nums' => $order_sdf['item_num'],
            'time' => $order_sdf['createtime'],
            'province' => $this->get_area_id($order_sdf),
        );
        return $order_info;
    }

    //获取订单收货区域对应的area_id
    public function get_area_id($order_sdf)
    {
        //err_log($order_sdf,'prism');
        //mainland:四川/资阳市/安岳县:2782
        preg_match("/mainland:(.+?)\//",$order_sdf['consignee']['area'],$area);
        //删除区域里的附加词
        $state = str_replace(array('省','自治区','市','壮族自治区','回族自治区','维吾尔自治区','特别行政区'),'',$area[1]);

        $area_conf = array(
            '北京' => 110000,
            '天津' => 120000,
            '河北' => 130000,
            '山西' => 140000,
            '内蒙古' => 150000,
            '辽宁' => 210000,
            '吉林' => 220000,
            '黑龙江' => 230000,
            '上海' => 310000,
            '江苏' => 320000,
            '浙江' => 330000,
            '安徽' => 340000,
            '福建' => 350000,
            '江西' => 360000,
            '山东' => 370000,
            '河南' => 410000,
            '湖北' => 420000,
            '湖南' => 430000,
            '广东' => 440000,
            '广西' => 450000,
            '海南' => 460000,
            '重庆' => 500000,
            '四川' => 510000,
            '贵州' => 520000,
            '云南' => 530000,
            '西藏' => 540000,
            '陕西' => 610000,
            '甘肃' => 620000,
            '青海' => 630000,
            '宁夏' => 640000,
            '新疆' => 650000,
            '台湾' => 710000,
            '香港' => 810000,
            '澳门' => 820000,
            '海外' => 990000,
        );

        if(isset($area_conf[$state])){
            return $area_conf[$state];
        }else{
            return '990000';
        }
    }

    /**
     * 会员数据
     * start_time : 时间戳
     */
    function push_member($start_time, $end_time)
    {
        //$sql = "select count(*) as member_nums from sdb_taocrm_members where create_time >={$start_time} and create_time<{$end_time} ";
        $sql = "select count(*) as member_nums from sdb_taocrm_members ";
        $member = kernel::database()->selectRow($sql);
        $param['member_nums'] = ceil($member['member_nums']*0.95);
        $param['time'] = date('Y-m-d', $start_time);
        $param['@class'] = 'prodata-member';

        if($param['member_nums'] == 0){
            return false;
        }
        $this->http_to_bnow($param);
    }

    function http_to_bnow($param)
    {
        $app_exclusion = app::get('base')->getConf('system.main_app');
        $node_id = base_shopnode::node_id($app_exclusion['app_id']);

        $param['nodeid'] = $node_id;
        $param['shopexid'] = '';
        $param['product'] = $this->getProlineCode();
        $param['code'] = $this->getProductCode();
        $paramss['data'] = json_encode($param);
        $this->sendToBnow($paramss, $param['@class']);
    }

    function sendToBnow($paramss,$date_type='')
    {
        $config['key'] = $this->key;
        $config['secret']= $this->secret;
        $config['site'] = $this->prism_site;
        $config['oauth'] = PRISM_OAUTH;
        $paramss['content-type'] = 'application/json';
        $paramss['routing_key'] = $this->routing_key;
        $prism = new oauth2($config);
        $type = 'api/platform/notify/publish';
        $r = $prism->request()->get('api/platform/timestamp');
        $time = $r->parsed();
        $prism->request()->timeout = 1;
        $rall = $prism->request()->post($type, $paramss, $time);
        $results = $rall->parsed();
        //err_log($paramss, 'prism');
        //err_log($results, 'prism');
        if($results['result']){
            //成功
        }else{
            //失败
        }

        //远程日志
        if($date_type == 'prodata-member'){
            $log = array(
                'domain' => $_SERVER['SERVER_NAME'],
                'op_user' => 'crontab',
                'result' => $results['result'] ? '成功':'失败',
                'params' => json_encode(
                    array(
                        'paramss'=>$paramss,
                        'results'=>$results,
                    )
                ),
                'ip' => $_SERVER['REMOTE_ADDR'],
            );
            $http = new base_httpclient;
            $http->post('http://monitor.crmm.taoex.com/index.php/openapi/taocrm.log/add/',$log);
        }
    }

    //产品线代码
    function getProlineCode()
    {
        $codes = array(
                0=>'C-0006',
            );
        return $codes[0];
    }

    //产品代码
    function getProductCode()
    {
        $codes = array(
                1=>'product_0042',  //标准
                2=>'product_0043',  //企业
                3=>'product_0300',  //旗舰
                4=>'product_0043',  //默认:企业
            );
        $version_code = app::get('taocrm')->getConf('system.version_code');
        switch($version_code){
            case 'Base_Ver':
                $site_ver=1;
                break;

            case 'High_Ver':
            case 'HighAll_Ver':
                $site_ver=2;
                break;

            case 'Pro_Ver':
                $site_ver=3;
                break;

            default:
                $site_ver=4;
                break;
        }
        return $codes[$site_ver];
    }
}
