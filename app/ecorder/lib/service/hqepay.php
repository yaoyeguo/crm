<?php
class ecorder_service_hqepay {

    function __construct(){
        $this->app = app::get('ecorder');
    }

    function detail_delivery($data){
        #获取物流数据
        //$data = $this->getDeliveryInfo($logi_no);
        $delivery_html = null;
        $rpc_data['order_bn'] = $data['order_bn'];
        $rpc_data['company_name'] = $data['company_name'];
        $rpc_data['logi_no'] = $data['logi_no'];
        $corp_default = $this->corp_default();
        foreach($corp_default as $k => $v){
            if($v['name'] == $data['company_name']){
                $rpc_data['logi_code'] = $v['type'];
                break;
            }
        }


        $rpc_result = $this->get_dly_info($rpc_data);
        /*if($rpc_result['rsp'] == 'succ'){
            $count = count( $rpc_result['data']);
            $max = $count - 1;#最新那条物流记录
            $html = "<ul style='margin-top:10px;'>";
            foreach($rpc_result['data'] as $key=>$val){
                #这时间是最新的
                if($max == $key ){
                    $html .= "<li style='line-height:15px;border-bottom:1px dotted  #ddd;'><font  style='font-size:13px;COLOR: red'>".$val['AcceptTime']."".$val['AcceptStation']."</font><li/>";
                }else{
                    $html .= "<li style='line-height:15px;border-bottom:1px dotted  #ddd;'>"."<em style='font-size:13px;COLOR: black'>".$val['AcceptTime']."</em>&nbsp;&nbsp;".$val['AcceptStation']." <li/>";
                }
            }
            $html .='</ul>';
        }else{
            $html = "<ul>";
            if($rpc_result['err_msg'] == "'HTTP Error 500: Internal Server Error'"){
                $html .= "<li style='line-height:15px;margin-top:10px;border-bottom:1px dotted  #ddd;'><font color='red'>此订单可能缺少物流公司或运单号</font><li/>";
            }else{
                $html .= "<li style='line-height:15px;margin-top:10px;border-bottom:1px dotted  #ddd;'><font color='red'>".$rpc_result['err_msg']."</font><li/>";
            }
        }
        $html .='</ul>';
        $html .= "<div  style='font-weight:700;font-color:#000000;margin-bottom:10px;'>华强宝提供数据支持(<font>以上信息由物流公司提供，如无跟踪信息或有疑问，请咨询对应物流公司</font>)<div>";
        return $html;*/
        return $rpc_result;
    }

    #绑定华强宝物流
    public function bind() {
        $app_exclusion = app::get('base')->getConf('system.main_app');
         $token = base_shopnode::get('token',$app_exclusion['app_id']);
        //$token = '865a9a837e80b898dfab07c7ddbd64fe1534fba7dc6630d707bfe49460c9614b';
        $node_id = base_shopnode::node_id($app_exclusion['app_id']);
        //$node_id='1307735731';
         $certi_id = base_certificate::get('certificate_id');
        //$certi_id = '1187959433';
        $api_url = kernel::base_url(true)."/index.php/api";
        //$api_url = "http://demo.crm.taoex.com/index.php/api";
        $params = array(
            'app' => 'app.applyNodeBind',
            'node_id' => $node_id,
            'from_certi_id' => $certi_id,
            'callback' => '',
            'sess_callback' => '',
            'api_url' => $api_url,
            'node_type' => 'hqepay',
            'to_node' => '1227722633',#写死的
            'shop_name' => '物流跟踪',
            "api_key"=> "1236217",#写死的
            "api_secret"=> "cf98e49d-9ebe-43cb-a690-ad96295b3457",#写死的
            // "api_url"=>"http://port.hqepay.com/Ebusiness/EbusinessOrderHandle.aspx", #写死的
        );

        $params['certi_ac']=$this->genSign($params,$token);
        //$api_url2 = 'http://sws.ex-sandbox.com/api.php';
        $api_url2 = MATRIX_RELATION_URL.'api.php';
        $headers = array('Connection' => 5);
        $core_http = kernel::single('base_httpclient');
        $response = $core_http->post($api_url2, $params,$headers);
        $response = json_decode($response,true);
        if($response['res'] == 'succ' || $response['msg']['errorDescription'] == '绑定关系已存在,不需要重复绑定') {
            base_kvstore::instance('ome/bind/hqepay')->store('ome_bind_hqepay', true);
            return true;
        }
        return false;
    }
    public function genSign($params, $token) {
        ksort($params);
        $str = '';
        foreach ($params as $key =>$value) {
            if ($key != 'certi_ac') {
                $str .= $value;
            }
        }
        $signString = md5($str.$token);
        return $signString;
    }
    #查询物流信息
    public function rpc_logistics_hqepay($rpc_data){
        #检测是否已经绑定华强宝物流
        base_kvstore::instance('ome/bind/hqepay')->fetch('ome_bind_hqepay', $is_ome_bind_hqepay);
        if(!$is_ome_bind_hqepay){
            $rs = $this->bind();
            if(!$rs){
                $return_data['rsp'] = 'fail';
                $return_data['err_msg'] = '没有绑定!';
                return  $return_data;
            }
        }

        $app_exclusion = app::get('base')->getConf('system.main_app');
        $params['app_id'] = 'ecos.taocrm';
        //$params['from_node_id'] = '1307735731';
        $params['from_node_id'] = base_shopnode::node_id($app_exclusion['app_id']);
        $params['to_node_id'] = '1227722633';#写死node_id
        $params['method'] = 'logistics.trace.detail.get';
        $params['tid'] = $rpc_data['order_bn']; #订单号
        $params['company_code'] = $rpc_data['logi_code'];
        $params['company_name'] = $rpc_data['company_name'];
        $params['logistic_code'] = $rpc_data['logi_no'];
        //$token = '865a9a837e80b898dfab07c7ddbd64fe1534fba7dc6630d707bfe49460c9614b';
        $token = base_shopnode::get('token',$app_exclusion['app_id']);
        $params['sign'] = $this->gen_matrix_sign($params,$token);

        //$api_url = 'http://matrix.ecos.shopex.cn/sync';
        $api_url = MATRIX_SYNC_URL_M;
        $time_out = 5;
        $headers = array(
            'Connection'=>$time_out,
        );
        $core_http = kernel::single('base_httpclient');
        $res = $core_http->post($api_url, $params,$headers);
        $res = json_decode($res,true);
        $return_data = null;
        if($res['rsp'] == 'fail'){
            $return_data['rsp'] = 'fail';
            $return_data['err_msg'] = $res->err_msg;
        }else{
            $return_data['rsp'] = 'succ';
            $_data = json_decode($res['data'],true);
            $return_data['data'] =  $_data['Traces'];
        }
        return $return_data;
    }
    #crm与华强宝快递对接，查看物流状态
    function get_dly_info($rpc_data = false){
        $rs = array();
        $data = $this->rpc_logistics_hqepay($rpc_data);
        if($data['rsp'] == 'succ'){
            #倒叙排序
            krsort($data['data']);
        }
        return $data;
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

    /*
     *快递公司名称和公司代码的清单
     */
    function corp_default(){
        $dly_corp = array(
            'EMS' => array('name' => 'EMS','type' => 'EMS','website' => 'http://www.ems.com.cn/','request_url' => 'http://www.ems.com.cn/','kdapi_code'=>'ems',),
            'STO' => array('name' => '申通E物流','type' => 'STO','website' => 'http://www.sto.cn/','request_url' => 'http://www.sto.cn/','kdapi_code'=>'shentong',),
            'TTKDEX' => array('name' => '天天快递','type' => 'TTKDEX','website' => 'http://www.ttkdex.com/','request_url' => 'http://www.ttkdex.com/','kdapi_code'=>'tiantian',),
            'YTO' => array('name' => '圆通速递','type' => 'YTO','website' => 'http://www.yto.net.cn/','request_url' => 'http://www.yto.net.cn/','kdapi_code'=>'yuantong',),
            'SF' => array('name' => '顺丰速运','type' => 'SF','website' => 'http://www.sf-express.com/','request_url' => 'http://www.sf-express.com/','kdapi_code'=>'shunfeng',),
            'YUNDA' => array('name' => '韵达快递','type' => 'YUNDA','website' => 'http://www.yundaex.com/','request_url' => 'http://www.yundaex.com/','kdapi_code'=>'yunda',),
            'ZTO' => array('name' => '中通速递','type' => 'ZTO','website' => 'http://www.zto.cn/','request_url' => 'http://www.zto.cn/','kdapi_code'=>'zhongtong',),
            'LB' => array('name' => '龙邦物流','type' => 'LB','website' => 'http://www.lbex.com.cn/','request_url' => 'http://www.lbex.com.cn/','kdapi_code'=>'longbanwuliu',),
            'ZJS' => array('name' => '宅急送','type' => 'ZJS','website' => 'http://www.zjs.com.cn/','request_url' => 'http://www.zjs.com.cn/','kdapi_code'=>'zhaijisong',),
            'UAPEX' => array('name' => '全一快递','type' => 'UAPEX','website' => 'http://www.apex100.com/','request_url' => 'http://www.apex100.com/','kdapi_code'=>'UAPEX',),
            'HTKY' => array('name' => '汇通速递','type' => 'HTKY','website' => 'http://www.htky365.com/','request_url' => 'http://www.htky365.com/','kdapi_code'=>'huitongkuaidi',),
            'CNMH' => array('name' => '民航快递','type' => 'CNMH','website' => 'http://www.caesz.com/','request_url' => 'http://www.caesz.com/','kdapi_code'=>'minghangkuaidi',),
            'AIRFEX' => array('name' => '亚风速递','type' => 'AIRFEX','website' => 'http://www.airfex.net/','request_url' => 'http://www.airfex.net/','kdapi_code'=>'yafengsudi',),
            'FAST' => array('name' => '快捷速递','type' => 'FAST','website' => 'http://www.fastexpress.com.cn/','request_url' => 'http://www.fastexpress.com.cn/','kdapi_code'=>'FAST',),
            'DDS' => array('name' => 'DDS快递','type' => 'DDS','website' => 'http://www.qc-dds.net/','request_url' => 'http://www.qc-dds.net/','kdapi_code'=>'dds',),
            'CYEXP' => array('name' => '长宇','type' => 'CYEXP','website' => 'http://www.cyexp.com/','request_url' => 'http://www.cyexp.com/','kdapi_code'=>'changyuwuliu',),
            'CRE' => array('name' => '中铁快运','type' => 'CRE','website' => 'http://www.cre.cn/','request_url' => 'http://www.cre.cn/','kdapi_code'=>'zhongtiewuliu',),
            'FEDEX' => array('name' => 'FedEx','type' => 'FEDEX','website' => 'http://www.dhl68.cn/fedex/','request_url' => 'http://www.dhl68.cn/fedex/','kdapi_code'=>'fedex',),
            'UPS' => array('name' => 'UPS','type' => 'UPS','website' => 'http://www.ups.com/cn','request_url' => 'http://www.ups.com/cn','kdapi_code'=>'ups',),
            'DHL' => array('name' => 'DHL','type' => 'DHL','website' => 'http://www.cn.dhl.com/zh.html','request_url' => 'http://www.cn.dhl.com/zh.html','kdapi_code'=>'dhl',),
            'DBL' => array('name' => '德邦物流','type' => 'DBL','website' => 'http://www.deppon.com/','request_url' => 'http://www.deppon.com/','kdapi_code'=>'debangwuliu',),
            'POST' => array('name' => '邮政平邮','type' => 'POST','website' => 'http://www.183yf.cn/','request_url' => 'http://www.183yf.cn/','kdapi_code'=>'youzhengpingyou',),
            'DTW' => array('name' => '大田','type' => 'DTW','website' => 'http://www.dtw.com.cn/','request_url' => 'http://www.dtw.com.cn/','kdapi_code'=>'datianwuliu',),
            'YUD' => array('name' => '长发','type' => 'YUD','website' => 'http://www.yud.com.cn/','request_url' => 'http://www.yud.com.cn/','kdapi_code'=>'changfawuliu',),
            'AAE' => array('name' => 'AAE全球专递','type' => 'AAE','website' => 'http://www.aae-lxwj.com/','request_url' => 'http://www.aae-lxwj.com/','kdapi_code'=>'andewuliu',),
            'ANJELEX' => array('name' => '安捷快递','type' => 'ANJELEX','website' => 'http://www.anjelex.com/','request_url' => 'http://www.anjelex.com/','kdapi_code'=>'anjiekuaidi',),
            'AND' => array('name' => '安信达快递','type' => 'AND','website' => 'http://www.anxinda.com/','request_url' => 'http://www.anxinda.com/','kdapi_code'=>'anxindakuaixi',),
            'EES' => array('name' => '百福东方','type' => 'EES','website' => 'http://www.ees.com.cn/','request_url' => 'http://www.ees.com.cn/','kdapi_code'=>'baifudongfang',),
            'BJKD' => array('name' => '彪记快递','type' => 'BJKD','website' => 'http://www.820618.56885.net/','request_url' => 'http://www.820618.56885.net/','kdapi_code'=>'biaojikuaidi',),
            //'BTH' => array('name' => 'BHT','type' => 'BHT','website' => 'http://www.annto.com/','request_url' => 'http://www.annto.com/','kdapi_code'=>'bth',),
            'CCES' => array('name' => '希伊艾斯快递','type' => 'CCES','website' => 'http://www.cces.com.cn/','request_url' => 'http://www.cces.com.cn/','kdapi_code'=>'cces',),
            'COE' => array('name' => '中国东方','type' => 'COE','website' => 'http://www.coe.com.hk/','request_url' => 'http://www.coe.com.hk/','kdapi_code'=>'coe',),
            'DPEX' => array('name' => 'DPEX','type' => 'DPEX','website' => 'http://www.dpex.com.cn/','request_url' => 'http://www.dpex.com.cn/','kdapi_code'=>'dpex',),
            'DEXP' => array('name' => 'D速快递','type' => 'DEXP','website' => 'http://www.d-exp.cn/','request_url' => 'http://www.d-exp.cn/','kdapi_code'=>'dsukuaidi',),
            'FKD' => array('name' => '飞康达物流','type' => 'FKD','website' => 'http://www.fkd.com.cn/','request_url' => 'http://www.fkd.com.cn/','kdapi_code'=>'feikangda',),
            'FHKD' => array('name' => '凤凰快递','type' => 'FHKD','website' => 'http://www.phoenixexp.com/','request_url' => 'http://www.phoenixexp.com/','kdapi_code'=>'fenghuangkuaidi',),
            'NEDA' => array('name' => '港中能达物流','type' => 'NEDA','website' => 'http://www.nd56.com/','request_url' => 'http://www.nd56.com/','kdapi_code'=>'ganzhongnengda',),
            'EP183' => array('name' => '广东邮政物流','type' => 'EP183','website' => 'http://www.ep183.cn/','request_url' => 'http://www.ep183.cn/','kdapi_code'=>'guangdongyouzhengwuliu',),
            'GLS' => array('name' => 'GLS快递','type' => 'GLS','website' => 'http://www.gls-group.net/','request_url' => 'http://www.gls-group.net/','kdapi_code'=>'gls',),
            'HLWL' => array('name' => '恒路物流','type' => 'HLWL','website' => 'http://www.e-henglu.com/','request_url' => 'http://www.e-henglu.com/','kdapi_code'=>'hengluwuliu',),
            'HXL' => array('name' => '华夏龙物流','type' => 'HXL','website' => 'http://www.chinadragon56.com/','request_url' => 'http://www.chinadragon56.com/','kdapi_code'=>'huaxialongwuliu',),
            'SKZZE' => array('name' => '京广速递','type' => 'SKZZE','website' => 'http://www.szkke.com/','request_url' => 'http://www.szkke.com/','kdapi_code'=>'jinguangsudikuaijian',),
            'JOUST' => array('name' => '急先达','type' => 'JOUST','website' => 'http://www.joust.net.cn/','request_url' => 'http://www.joust.net.cn/','kdapi_code'=>'jixianda',),
            'JJWL' => array('name' => '佳吉物流','type' => 'JJWL','website' => 'http://www.jiaji.com/','request_url' => 'http://www.jiaji.com/','kdapi_code'=>'jiajiwuliu',),
            'JYWL' => array('name' => '佳怡物流','type' => 'JYWL','website' => 'http://www.jiayi56.com/','request_url' => 'http://www.jiayi56.com/','kdapi_code'=>'jiayiwuliu',),
            'JYM' => array('name' => '加运美','type' => 'JYM','website' => 'http://jymkd.fs.365ditu.com/','request_url' => 'http://jymkd.fs.365ditu.com/','kdapi_code'=>'jiayunmeiwuliu',),
            'LTS' => array('name' => '联昊通物流','type' => 'LTS','website' => 'http://lts.com.cn/','request_url' => 'http://lts.com.cn/','kdapi_code'=>'lianhaowuliu',),
            'LBKD' => array('name' => '蓝镖快递','type' => 'LBKD','website' => 'http://www.bluedart.cn/','request_url' => 'http://www.bluedart.cn/','kdapi_code'=>'lanbiaokuaidi',),
            'PSHY' => array('name' => '配思货运','type' => 'PSHY','website' => 'http://www.peisi.cn/','request_url' => 'http://www.peisi.cn/','kdapi_code'=>'peisihuoyunkuaidi',),
            'QCKD' => array('name' => '全晨快递','type' => 'QCKD','website' => 'http://www.qckd.net/','request_url' => 'http://www.qckd.net/','kdapi_code'=>'quanchenkuaidi',),
            'QJT' => array('name' => '全际通物流','type' => 'QJT','website' => 'http://www.quanjt.com/','request_url' => 'http://www.quanjt.com/','kdapi_code'=>'quanjitong',),
            'QRT' => array('name' => '增益速递','type' => 'QRT','website' => 'http://www.at-express.com/','request_url' => 'http://www.at-express.com/','kdapi_code'=>'quanritongkuaidi',),
            'SHWL' => array('name' => '盛辉物流','type' => 'SHWL','website' => 'http://www.shenghui56.com/','request_url' => 'http://www.shenghui56.com/','kdapi_code'=>'shenghuiwuliu',),
            'SFWL' => array('name' => '盛丰物流','type' => 'SFWL','website' => 'http://www.sfwl.com.cn/','request_url' => 'http://www.sfwl.com.cn/','kdapi_code'=>'shengfengwuliu',),
            'SDWL' => array('name' => '上大物流','type' => 'SDWL','website' => 'http://www.sundapost.net/','request_url' => 'http://www.sundapost.net/','kdapi_code'=>'shangda',),
            'HOAU' => array('name' => '天地华宇','type' => 'HOAU','website' => 'http://www.hoau.net/','request_url' => 'http://www.hoau.net/','kdapi_code'=>'HOAU',),
            'TNT' => array('name' => 'TNT','type' => 'TNT','website' => 'http://www.tnt.com.cn/','request_url' => 'http://www.tnt.com.cn/','kdapi_code'=>'tnt',),
            'WJWL' => array('name' => '万家物流','type' => 'WJWL','website' => 'http://manco2009.id666.com/','request_url' => 'http://manco2009.id666.com/','kdapi_code'=>'wanjiawuliu',),
            'WJHKSD' => array('name' => '文捷航空速递','type' => 'WJHKSD','website' => 'http://www.wjexpress.com/','request_url' => 'http://www.wjexpress.com/','kdapi_code'=>'wenjiesudi',),
            'WYSD' => array('name' => '伍圆速递','type' => 'WYSD','website' => 'http://www.5ysd.56885.net/','request_url' => 'http://www.5ysd.56885.net/','kdapi_code'=>'wuyuansudi',),
            'XB' => array('name' => '新邦物流','type' => 'XB','website' => 'http://www.xbwl.cn/','request_url' => 'http://www.xbwl.cn/','kdapi_code'=>'xinbangwuliu',),
            'XFWL' => array('name' => '信丰物流','type' => 'XFWL','website' => 'http://www.xf-express.com.cn/','request_url' => 'http://www.xf-express.com.cn/','kdapi_code'=>'xinfengwuliu',),
            'STARS' => array('name' => '星晨急便','type' => 'STARS','website' => 'http://www.4006688400.com/','request_url' => 'http://www.4006688400.com/','kdapi_code'=>'xingchengjibian',),
            'XFHONG' => array('name' => '鑫飞鸿物流快递','type' => 'XFHONG','website' => 'http://www.xfhex.cn/','request_url' => 'http://www.xfhex.cn/','kdapi_code'=>'xinhongyukuaidi',),
            'EBON' => array('name' => '一邦速递','type' => 'EBON','website' => 'http://www.ebon-express.com/','request_url' => 'http://www.ebon-express.com/','kdapi_code'=>'yibangwuliu',),
            'UC' => array('name' => '优速物流','type' => 'UC','website' => 'http://www.uc56.com/','request_url' => 'http://www.uc56.com/','kdapi_code'=>'youshuwuliu',),
            'YCWL' => array('name' => '远成物流','type' => 'YCWL','website' => 'http://www.ycgwl.com/','request_url' => 'http://www.ycgwl.com/','kdapi_code'=>'yuanchengwuliu',),
            'YWFKD' => array('name' => '源伟丰快递','type' => 'YWHKD','website' => 'http://www.ywfex.com/','request_url' => 'http://www.ywfex.com/','kdapi_code'=>'yuanweifeng',),
            'YZJC' => array('name' => '元智捷诚快递','type' => 'YZJC','website' => 'http://www.yjkd.com/','request_url' => 'http://www.yjkd.com/','kdapi_code'=>'yuanzhijiecheng',),
            'YFWL' => array('name' => '越丰物流','type' => 'YFWL','website' => 'http://www.yfexpress.com.hk/','request_url' => 'http://www.yfexpress.com.hk/','kdapi_code'=>'yuefengwuliu',),
            'YADWL' => array('name' => '源安达','type' => 'YADWL','website' => 'http://www.yadex.com.cn/','request_url' => 'http://www.yadex.com.cn/','kdapi_code'=>'yuananda',),
            'YFHWL' => array('name' => '原飞航物流','type' => 'YFHWL','website' => 'http://www.yfhex.com/','request_url' => 'http://www.yfhex.com/','kdapi_code'=>'yuanfeihangwuliu',),
            'YUNTONG' => array('name' => '运通快递','type' => 'YUNTONG','website' => 'http://www.ytkd168.com/','request_url' => 'http://www.ytkd168.com/','kdapi_code'=>'yuntongkuaidi',),
            'ZHONGY' => array('name' => '中邮物流','type' => 'ZHONGY','website' => 'http://www.cnpl.com.cn/','request_url' => 'http://www.cnpl.com.cn/','kdapi_code'=>'zhongyouwuliu',),
            'POSTB' => array('name' => '邮政国内小包','type' => 'POSTB','website' => 'http://www.183yf.cn/','request_url' => 'http://www.183yf.cn/','kdapi_code'=>'youzhengguoneixiaobao'),
            'EYB' => array('name' => 'EMS经济快递','type' => 'EYB','website' => 'http://www.ems.com.cn/','request_url' => 'http://www.ems.com.cn/','kdapi_code'=>'ems'),
            'Dangdang'=>array('name'=>'当当物流','type'=>'DANGDANG','website'=>'http://www.dangdang.com','request_url'=>'http://www.dangdang.com','kdapi_code'=>'dangdang'),
            'AMAZON'=>array('name'=>'亚马逊物流','type'=>'AMAZON','website'=>'http://www.amazon.com','request_url'=>'http://www.amazon.com','kdapi_code'=>'amazon'),

            'ZHQKD' =>array('name' => '汇强快递','type' => 'ZHQKD','website' => 'http://www.hq-ex.com','request_url' => 'http://www.hq-ex.com','kdapi_code' => 'ZHQKD'),
            'AIR' =>array('name' => '亚风','type' => 'AIR','website' => '','request_url' => '','kdapi_code' => 'AIR'),
            'DFH' =>array('name' => '东方汇','type' => 'DFH','website' => '','request_url' => '','kdapi_code' => 'DFH'),
            'SY' =>array('name' => '首业','type' => 'SY','website' => '','request_url' => '','kdapi_code' => 'SY'),
            'YC' =>array('name' => '远长','type' => 'YC','website' => '','request_url' => '','kdapi_code' => 'YC'),
            'UNIPS' =>array('name' => '发网','type' => 'UNIPS','website' => '','request_url' => '','kdapi_code' => 'UNIPS'),
            'GZLT' =>array('name' => '飞远配送 ','type' => 'GZLT','website' => '','request_url' => '','kdapi_code' => 'GZLT'),
            'QFKD' =>array('name' => '全峰快递','type' => 'QFKD','website' => '','request_url' => '','kdapi_code' => 'QFKD'),
            'SCKJ' =>array('name' => '成都东骏快捷','type' => 'SCKJ','website' => '','request_url' => '','kdapi_code' => 'SCKJ'),
            'GDEMS' =>array('name' => '广东EMS','type' => 'GDEMS','website' => '','request_url' => '','kdapi_code' => 'GDEMS'),
            'HZABC' =>array('name' => '杭州爱彼西','type' => 'HZABC','website' => '','request_url' => '','kdapi_code' => 'HZABC'),
            'YCT' =>array('name' => '黑猫宅急便','type' => 'YCT','website' => '','request_url' => '','kdapi_code' => 'YCT'),
            'GZFY' =>array('name' => '凡宇速递','type' => 'GZFY','website' => '','request_url' => '','kdapi_code' => 'GZFY'),
            'BJCS' =>array('name' => '城市100','type' => 'BJCS','website' => '','request_url' => '','kdapi_code' => 'BJCS'),
            'SURE' =>array('name' => '速尔','type' => 'SURE','website' => '','request_url' => '','kdapi_code' => 'SURE'),
            'CNEX' =>array('name' => '佳吉快运','type' => 'CNEX','website' => '','request_url' => '','kdapi_code' => 'CNEX'),
            'BEST' =>array('name' => '百世物流','type' => 'BEST','website' => '','request_url' => '','kdapi_code' => 'BEST'),
            'SHQ' =>array('name' => '华强物流','type' => 'SHQ','website' => '','request_url' => '','kdapi_code' => 'SHQ'),
            'GTO' =>array('name' => '国通快递','type' => 'GTO','website' => 'http://gto365.com','request_url' => 'http://gto365.com','kdapi_code' => 'GTO'),
            'ESB' =>array('name' => 'E速宝','type' => 'ESB','website' => '','request_url' => '','kdapi_code' => 'ESB'),
            'vp088rufengda' =>array('name' => '如风达','type' => 'vp088rufengda','website' => 'http://www.rufengda.com/','request_url' => 'http://www.rufengda.com/','kdapi_code' => 'vp088rufengda'),
            'OTHER' =>array('name' => '其他','type' => 'OTHER','website' => 'null','request_url' => 'null','kdapi_code' => 'OTHER'),
            'AOYOUZGKY' =>array('name' => '澳邮中国快运','type' => 'AOYOUZGKY','website' => '','request_url' => ''),
            'AOSHISD' =>array('name' => '傲世速递','type' => 'AOSHISD','website' => '','request_url' => ''),
            'HQKY' =>array('name' => '华企快运','type' => 'HQKY','website' => '','request_url' => ''),
            '016feikangda' =>array('name' => '飞康达','type' => '016feikangda','website' => '','request_url' => ''),
            'GSD' =>array('name' => '共速达','type' => 'GSD','website' => '','request_url' => ''),
            '027jixianda' =>array('name' => '急先达','type' => '027jixianda','website' => '','request_url' => ''),
            'wu011xiaohongmao' =>array('name' => '小红帽物流','type' => 'wu011xiaohongmao','website' => '','request_url' => ''),

            'wu074quanfeng' =>array('name' => '国美代运（全枫）','type' => 'wu074quanfeng','website' => '','request_url' => ''),
            'GOME_ZJS' =>array('name' => '国美代运（宅急送）','type' => 'GOME_ZJS','website' => '','request_url' => ''),
            'wu008tonghetianxia' =>array('name' => '通和天下）','type' => 'wu008tonghetianxia','website' => '','request_url' => 'http://www.cod56.com/'),
            'vtp' =>array('name' => '微特派','type' => 'vtp','website' => 'http://www.vtepai.com/','request_url' => 'http://www.vtepai.com/'),
            'CDTKKD' =>array('name' => '成都同康','type' => 'CDTKKD','website' => '','request_url' => ''),
            'vp076xingcheng' =>array('name' => '四川星程','type' => 'vp076xingcheng','website' => 'http://www.sccod.com','request_url' => 'http://www.sccod.com'),
            'DSSD' =>array('name' => '都市速代','type' => 'DSSD','website' => '','request_url' => ''),
            'GZONS' =>array('name' => '广州欧妮斯','type' => 'GZONS','website' => '','request_url' => ''),
            'wu055haochuan' =>array('name' => '上海浩川','type' => 'wu055haochuan','website' => 'http://haochuansh.com','request_url' => 'http://haochuansh.com'),
            'STWL' =>array('name' => '速通物流','type' => 'STWL','website' => '','request_url' => ''),
            'ANWL' =>array('name' => '安能物流','type' => 'ANWL','website' => '','request_url' => ''),
            'DDWL' =>array('name' => '大达物流','type' => 'DDWL','website' => '','request_url' => ''),
            'IHAIER' =>array('name' => '海尔物流','type' => 'IHAIER','website' => '','request_url' => ''),
            'aucma56' =>array('name' => '澳柯玛物流','type' => 'aucma56','website' => '','request_url' => ''),
            'cod36524' =>array('name' => '河北城通物流有限公司','type' => 'cod36524','website' => '','request_url' => ''),
            'GOMEKD' =>array('name' => '国美快递','type' => 'GOMEKD','website' => '','request_url' => ''),


            'ShunTongExpress' =>array('name' => '顺通快递','type' => 'ShunTongExpress','website' => '','request_url' => ''),
            'paier' =>array('name' => '派尔快递','type' => 'paier','website' => '','request_url' => ''),
            'kudisong' =>array('name' => '快递送','type' => 'kudisong','website' => '','request_url' => ''),
            'xiaoyuan' =>array('name' => '校园快递','type' =>'xiaoyuan','website' => '','request_url' => ''),
            'wuyou' =>array('name' => '无忧快递','type' => 'wuyou','website' => '','request_url' => ''),

        );

        return $dly_corp;
    }

}
