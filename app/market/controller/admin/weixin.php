<?php

class market_ctl_admin_weixin extends desktop_controller {

    const BAIDU_MAP_API = 'http://api.map.baidu.com';
    const BAIDU_MAP_AK = '59UG35QsCx2xWDX7rGE0It09';
    const BAIDU_MAP_SK = 'kL8916MNwlFhuMzhOAEClLxF13fAVo8T';


    //var $workground = 'market.weixin';
    var $_extra_view = array('market'=>'admin/weixin/guide/step.html');

    public function __construct($app)
    {
        parent::__construct($app);

        base_kvstore::instance('market')->fetch('wxuser', $wxuser);
        $wxuser = json_decode($wxuser,true);
        if(empty($wxuser)){
            $wxuser=array(
                   "token"=>md5(time().$this->genRandomString(5)),
                   "state"=>1,
                   "create_time"=>time()
            );

        }else{
            if(!isset($wxuser['registRule']['replyFinishTxt']))$wxuser['registRule']['replyFinishTxt'] = '签到成功!您此次签到获得积分:<{积分}>,总积分:<{总积分}>';
        }
        base_kvstore::instance('market')->store('wxuser', json_encode($wxuser));

        $wxuser["url"] = kernel::single('market_service_weixin')->get_wx_openapi();
        $this->wxuser=$wxuser;
        $this->wx_bind_status = $this->wx_bind_ok();
        $this->wx_bind_status = true;
        $this->pagedata["wxuser"] = $wxuser;
        $this->pagedata["wx_bind_ok"] = $this->wx_bind_status;

        //判断用户微信版本
        $this->is_enhanced_version();
    }

    public function index()
    {
        //$wxuser=null;
        base_kvstore::instance('market')->fetch('wxuser', $wxuser);
        $wxuser = json_decode($wxuser,true);
        if(empty($wxuser)){
            $wxuser=array(
                   "token"=>md5(time().$this->genRandomString(5)),
                   "state"=>0,
                   "create_time"=>time()
            );

            base_kvstore::instance('market')->store('wxuser', json_encode($wxuser));
        }

        $wxuser["url"] = kernel::single('market_service_weixin')->get_wx_openapi();
        //$wxuser['welcomeword'] = 'test';
        $this->pagedata["wxuser"]=$wxuser;

        if($_GET['tab']){
            $arr = array('index'=>'微信营销站','welcome'=>'欢迎语','keyreply'=>'关键词自动回复');
            $this->pagedata["tab"]=$arr[$_GET['tab']];
        }

        $this->pagedata['optionsShopList'] =  kernel::single('ecorder_service_shop')->getTaobaoShopList();
        $arr = array_keys($this->pagedata['optionsShopList']);
        if(!empty($arr)){
            $this->pagedata['optionsShopListChecked'] = $arr[0];
        }


        $rulelist=$this->app->model("wxautoreply")->getList("*");

        $resplycount=0;

        if($rulelist!=null)
        $resplycount=count($rulelist);

        foreach ($rulelist as $k => &$v) {
            $replycontent = json_decode($v['replycontent'], true);
            $v['keyword'] = $replycontent['keyword'];
        }
        $this->pagedata["rulelist"] = $rulelist;

        $this->pagedata["replaycount"]=$resplycount;

        $this->greet();

        $this->page("admin/weixin/distribution.html");
    }

    private function genRandomString($len)
    {
        $chars = array(
             "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
             "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
             "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G",
             "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R",
             "S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1", "2",
             "3", "4", "5", "6", "7", "8", "9"
             );

             $charsLen = count($chars) - 1;

             shuffle($chars);    // 将数组打乱

             $output = "";

             for ($i=0; $i<$len; $i++)
             {
                 $output .= $chars[mt_rand(0, $charsLen)];
             }

             return $output;
    }

    private function is_enhanced_version(){
        //切换到微信标准版服务 $version 1.切换到标准版 2.切换到增强版
        $domain = $_SERVER['SERVER_NAME'];
        $weibaolai = new taocrm_weibaolai();
        base_kvstore::instance('market')->fetch('wx_info', $wx_info);
        $wx_info = json_decode($wx_info,true);
        if(!empty($wx_info)){
            if($wx_info['version'] != 2){
                if($wx_info['start_time']<=time() && $wx_info['end_date']>=time()){
                    $user_info = $weibaolai->register($domain);
                    if(!empty($user_info) && $user_info['rsp'] == 1){
                        $url = $weibaolai->getNoLoginUrl();
                        if(!empty($url)) {
                            $this->pagedata['url'] = $url;
                            $this->pagedata['is_weibaolai'] =  $wx_info['version'];
                            $this->page('admin/weixin/active.html');exit;
                        }
                        else{
                            die("getNoLoginUrl  fail");
                        }
                    }
                    else{
                        die("register fail");
                    }
                }
                else
                {
                    $wx_info = array(
                        'is_trial'      => '1',
                        'version'       => '2',
                        'start_time'    => '',
                        'end_date'      => ''
                    );
                    base_kvstore::instance('market')->store('wx_info',json_encode($wx_info));
                }
            }
        }
    }
    //新版购买页面
    public function newWeixin(){
        $this->pagedata['url'] = kernel::base_url(1);
        $this->page('admin/weixin/trial.html');exit;
    }

    //处理用户试用
    public function trial(){
        base_kvstore::instance('market')->fetch('wx_info',$date);
        $date = json_decode($date,true);
        $domain = $_SERVER['SERVER_NAME'];
        if(empty($date)){
            $wx = new taocrm_weibaolai();
            $user_info = $wx->register($domain);
            if(!empty($user_info) && $user_info['rsp'] == 1){
                $url = $wx->getNoLoginUrl();
                if(!empty($url)){
                    $time = time()+604800;
                    $wx_info = array(
                        'is_trial'        => '1',
                        'version'         => '1',      //用户当前版本 1 试用版 2 普通版 3 正式购买版
                        'start_time'      => time(),
                        'end_date'        => $time
                    );
                    base_kvstore::instance('market')->store('wx_info',json_encode($wx_info));
                    $return_date = array(
                        'res'  => 0,
                        'msg'  => '试用注册成功,开始体验增强版之旅',
                        'date' => array(
                            'domain' => $domain,
                            'url'    => $url
                        ),
                    );
                    echo json_encode($return_date);exit;
                }
            }
        }
        else
        {
            if($date['is_trial'] != 0){
                $return_date = array(
                    'res' => 1,
                    'msg' =>'你已经试用过啦！继续使用请购买',
                    'date' => ''
                );
                echo json_encode($return_date);exit;
            }
            else{
                 $return_date = array(
                    'res' => 1,
                    'msg' =>'数据错误',
                    'date' => ''
                );
                echo json_encode($return_date);exit;
            }
        }
    }

    public function buy_weixin(){
        $this->page('admin/weixin/buy_weixin.html');exit;
    }

    //上传购买凭证
    public function uploadDocuments(){
        $this->page('admin/weixin/upload.html');
    }

    //处理用户购买请求
    public function postmonitor()
    {
        $domain      =  $_SERVER['SERVER_NAME'];
        $type_val    =  trim($_REQUEST['type_val']);
        $payment_val =  trim($_REQUEST['payment_val']);
        $pic_url     =  trim($_REQUEST['pic_url']);
        $token       =  base_shopnode::get_token();
        $monitor_api_url = 'http://monitor.crmm.taoex.com/index.php/openapi/taocrm.upgrade';
        
        if(empty($type_val) || empty($domain) || empty($payment_val) || empty($pic_url)){
            $arr = array(
                'res' => 1,
                'msg' => '参数错误!'
            );
            echo json_encode($arr);exit;
        }
        
        $payment = array(
            'domain'        => $domain,
            'payment_val'   => $payment_val
        );
        $http = new base_httpclient;
        $resp = $http->post($monitor_api_url.'/check_payment/',$payment);
        $res = json_decode($resp, true);
        if($res){
        if(count($res['res'])){
            $arr = array(
                'res' => 1,
                'msg' => '此支付单号已经提交过了!请不要重复提交!'
            );
            echo json_encode($arr);exit;
        }
        }else{
            $arr = array(
                'res' => 1,
                'msg' => $resp
            );
            echo json_encode($arr);exit;
        }
        $arr = array(
            'type_val'      => $type_val,
            'pic_url'       => $pic_url,
            'domain'        => $domain,
            'payment_val'   => $payment_val,
            'token'         => $token,
        );
        $reslut = $http->post($monitor_api_url.'/upgrade/',$arr);
        echo $reslut;exit;
    }

    public function savewelcome(){
        $this->begin();
        base_kvstore::instance('market')->fetch('wxuser', $wxuser);
        $wxuser = json_decode($wxuser,true);
        if(!empty($wxuser)){
            $wxuser["welcomeword"]=$_POST["welcomeword"];
            //var_dump($wxuser);exit;
            base_kvstore::instance('market')->store('wxuser', json_encode($wxuser));
        }

        $this->end();
        //$this->greet();
    }

    public function refreshwxstate(){
        $this->_check();

        $wxmodel=$this->app->model("wxuser");

        $wxuser=$wxmodel->dump(array("yunmallid"=>$this->user["id"]));

        if($wxuser!=null){
            if(!empty($wxuser["state"])){
                echo "1";
                exit;
            }
        }
        echo "0";
    }


    //weixin market
    public function distribution(){
        $this->index();
    }

    public function greet(){

        base_kvstore::instance('market')->fetch('wxuser', $wxuser);
        $wxuser = json_decode($wxuser,true);

        $word='亲！盼星星盼月亮，终于盼来了你，欢迎成为本站的微信粉丝。想要获得我们的最In新品，最High爆款，最Care客户，最Low特价等活动信息吗？

发送【】内的关键词信息开始与我们互动，就能在微信浏览宝贝，喜欢还能直接下单哟。

【新】：最新上架
【热】：热销商品
【特】：特价折扣
【爆】：爆款推荐
【会】：客户专享';

        if(!empty($wxuser) && !empty($wxuser["welcomeword"])){
            $word=urldecode($wxuser['welcomeword']);
        }

        $this->pagedata["word"]=$word;
        //$this->page("admin/weixin/greet.html");
    }

    public function keyreply(){
        $this->begin();
        //var_dump($_POST);exit;
        if ($_POST) {

            $this->saveKeyreply($_POST);
        }

        // header('Location: index.php?app=market&ctl=admin_weixin&act=products');
        $this->end();
        exit;
    }

    function processKeyWords(& $postKeyWords,$selfKeyWords=array(),$source){
        //检查关键字是否在总表
        $objWxKeyWords = app::get('market')->model('wx_keywords');
        $keyword = array();
        foreach($postKeyWords as $v){
            if(!empty($v) && !in_array($v, $keyword)){
                $keyword[] = $v;
            }
        }
        $postKeyWords = $keyword;
        $msg = '';

        //当前类型是否存在关键词,如果存在，就拿新提交的和存在对比，取出增加的，校验是否存在于总表。
        if(!empty($selfKeyWords)){
            $newKeyWords = array();
            foreach($postKeyWords as $v){
                if(!in_array($v, $selfKeyWords)){
                    $newKeyWords[] = $v;
                }
            }
            $checkKeyWords = $newKeyWords;
        }else{
            $checkKeyWords = $postKeyWords;
        }

        if(!empty($checkKeyWords) && $objWxKeyWords->check($checkKeyWords,$msg)){
            $this->end(false,$msg);
        }else{
            //已存在关键词，先删后加,不存在的话直接添加
            if($selfKeyWords){
                $objWxKeyWords->delete($selfKeyWords);
            }

            $objWxKeyWords->add($postKeyWords,$source);
        }
    }

    private function saveKeyreply( $data) {
        $saveData = array();
        $filter = array();
        $saveData['rulename'] = $rulename = $data['regex'];
        $ruleArr = array();
        $model = $this->getWxautoreplyModel();
        $pdata = json_decode($data['pdata'], true);

        if(empty($data['regex'])){
            $this->end(false,'规则名必填');
        }

        if(empty($data['key1']) && empty($data['key2']) && empty($data['key3'])){
            $this->end(false,'关键名必填一个');
        }

        if(!isset($pdata['item_top']) || empty($pdata['item_top'])){
            $this->end(false,'商品必须选择一个');
        }

        $key = array();
        if ($data['key1']) {
            array_push($key, $data['key1']);
        }
        if ($data['key2']) {
            array_push($key, $data['key2']);
        }
        if ($data['key3']) {
            array_push($key, $data['key3']);
        }

        //对总表关键词进行处理
        $postKeyWords = $key;
        if($pdata['rid']){
            $result = $model->dump(array('id' => intval($pdata['rid'])));
            $replycontent = json_decode($result['replycontent'],true);
            $selfKeyWords = $replycontent['keyword'];

        }else{
            $selfKeyWords = array();
        }
        $this->processKeyWords($postKeyWords,$selfKeyWords,'products');
        $key = $postKeyWords;

        //更新
        if (isset($pdata['rid']) && $pdata['rid'] > 0) {
            $ruleData = array();
            $ruleData['name'] = $rulename;
            $ruleData['shop_id'] = $data['shop_id'];

            $ruleData['keyword'] = $key;
            $ruleData['item_top'] = $pdata['item_top'];
            if (isset($pdata['items']) && $pdata['items']) {
                $ruleData['items'] = $pdata['items'];
            }
            $saveData['replycontent'] = json_encode($ruleData);

            $model->update($saveData, array('id' => $pdata['rid']));
        }
        else {
            //添加数据
            $ruleData = array();
            $ruleData['name'] = $saveData['rulename'];
            $ruleData['shop_id'] = $data['shop_id'];
            $ruleData['keyword'] = $key;
            $ruleData['item_top'] = $pdata['item_top'];
            $ruleData['items'] = $pdata['items'];
            $saveData['replycontent'] = json_encode($ruleData);
            $model->insert($saveData);
        }
    }

    public function getkeyreplydata()
    {
        if ($_POST['rid'] && intval($_POST['rid']) > 0) {
            $model = $this->getWxautoreplyModel();
            $result = $model->dump(array('id' => intval($_POST['rid'])));
            echo $result['replycontent'];
        }
    }

    public function iskeyreplydata()
    {
        $this->_check();
        $wxuser=$this->app->model("wxuser")->dump(array("yunmallid"=>$this->user["id"]));
        $model = $this->getWxautoreplyModel();
    }

    public function delkeyreply()
    {
        if ($_POST['rid'] && intval($_POST['rid']) > 0) {
            // $objWxKeyWords = app::get('market')->model('wx_keywords');
            //$objWxKeyWords->delete();
            $model = $this->getWxautoreplyModel();

            //删除关键词总表
            $result = $model->dump(array('id' => intval($_POST['rid'])));
            $replycontent = json_decode($result['replycontent'],true);
            $selfKeyWords = $replycontent['keyword'];
            $objWxKeyWords = app::get('market')->model('wx_keywords');
            $objWxKeyWords->delete($selfKeyWords);

            $result = $model->delete(array('id' => intval($_POST['rid'][0])));
            if ($result) {
                echo 1;
            }
            else {
                echo 0;
            }
        }
    }

    private function getWxautoreplyModel()
    {
        if ($this->wxautoreplyModel == null) {
            $this->wxautoreplyModel = $this->app->model('wxautoreply');
        }
        return $this->wxautoreplyModel;
    }

    public function queryproduct(){
        $productList = kernel::single('ecgoods_service_goods')->getProductsByName($_POST['shop_id'],$_POST['key']);

        if($productList){

            $arritem=array();

            foreach ($productList as $key => $value) {
                $arritem[]=array(
                "id"=>$value['outer_id'],
                "title"=>$value['name'],
                "link"=>'http://item.taobao.com/item.htm?id='.$value['outer_id'],
                "media"=>array(
                  "s"=>$value['pic_url'],
                  "l"=>$value['pic_url']
                )
                );
            }
            echo json_encode(array(
            // "recordcount"=>$data->total_results,
            "items"=>$arritem
            ));
        }
    }

    //是否已经绑定微信帐号
    public function wx_bind_ok()
    {
        $return = false;
        base_kvstore::instance('market')->fetch('wxuser', $wxuser);
        $wxuser = json_decode($wxuser,true);

        $auto_replys = $this->app->model('wx_keywords_autoreply')->dump(array(1=>1));

        //微信帐号已经绑定。自动回复内容不为空，关键词回复不为空
        if($wxuser['state']==1 && ($wxuser['welcomeword']!='' or $wxuser['autoreplyword']!='') && $auto_replys){
            $return = true;
        }

        return $return;
    }

    //微信互动
    public function dashboard()
    {
        $objMember = app::get('market')->model('wx_member');
        $this->pagedata["wx_info"] =  $objMember->getWxInfo();

        $objWxJoinLog = app::get('market')->model('wx_join_log');
        $this->pagedata["wx_join_info"] = $objWxJoinLog->getTodayInfo();

        $tab2_status = 1;
        if($this->wxuser['state'] == 1) $tab2_status = 0;

        base_kvstore::instance('market')->fetch('ucenter', $ucenter);
        $this->pagedata['ucenter'] = json_decode($ucenter, true);

        $this->pagedata['tab2_status'] = $tab2_status;
        $this->page("admin/weixin/dashboard.html");
    }

    function saveAutoReplyWord(){
        $this->begin();
        base_kvstore::instance('market')->fetch('wxuser', $wxuser);
        $wxuser = json_decode($wxuser,true);
        if(!empty($wxuser)){
            $wxuser["autoreplyword"]=$_POST["autoreplyword"];
            //var_dump($wxuser);exit;
            base_kvstore::instance('market')->store('wxuser', json_encode($wxuser));
        }

        $this->end();
    }

    public function saveBindInfo(){
        $this->begin();
        base_kvstore::instance('market')->fetch('wxuser', $wxuser);
        $wxuser = json_decode($wxuser,true);
        if(!empty($wxuser)){
            $wxuser['wx_account'] = $_POST['wx_account'];
            $wxuser['appid'] = $_POST['appid'];
            $wxuser['secret'] = $_POST['secret'];
            unset($wxuser['access_token']);
            base_kvstore::instance('market')->store('wxuser', json_encode($wxuser));
            $this->end(true,'保存成功');
        }else{
            $this->end(true,'保存成功');
        }
    }

    //前台微信会员中心设置
    public function save_ucenter_info()
    {
        $this->begin();
        base_kvstore::instance('market')->fetch('ucenter', $ucenter);
        $ucenter = json_decode($ucenter,true);

        $ucenter['shop_name'] = trim($_POST['shop_name']);
        $ucenter['logo'] = $_POST['logo'];

        base_kvstore::instance('market')->store('ucenter', json_encode($ucenter));
        $this->end(true,'保存成功');
    }

    public function autoReply(){
        $tab = intval($_GET['tab']);
        base_kvstore::instance('market')->fetch('wxuser', $wxuser);
        $wxuser = json_decode($wxuser,true);

        if($tab == 2){
            $tab2_status = 1;
        }else{
            $tab2_status = 0;
        }

        $this->pagedata["wxuser"]=$this->wxuser;
        $this->pagedata["tab2_status"]=$tab2_status;
        $this->pagedata["tab"]=$tab;
        $this->page("admin/weixin/auto_reply.html");
    }

    public function keywordAutoReply()
    {
        $param = array(
            'title'=>'关键词列表',
            'use_buildin_recycle'=>false,
            'use_buildin_filter'=>false,
            'base_filter'=>array(),
            'actions'=>array(
        array(
                    'label'=>'添加',
                    'href'=>'index.php?app=market&ctl=admin_weixin&act=addAutoReply',
                    'target'=>'dialog::{onClose:function(){window.location.reload();},width:800,height:400,title:\'添加规则\'}'
                    ),
                    array(
                    'label'=>'删除',
                    'submit'=>'index.php?app=market&ctl=admin_weixin&act=deleteAutoReply',
                    ),
                    ),
                    );

                    if(!isset($_GET['tab']) && $this->wx_bind_status == false){
                        $param['top_extra_view'] = $this->_extra_view;
                    }

                    $this->finder('market_mdl_wx_keywords_autoreply',$param);
    }

    public function addAutoReply($id=null){

        if($id!=null){
            $objReply = app::get('market')->model('wx_keywords_autoreply');
            $rs = $objReply->dump(array('id'=>$id),'*');
            $rs['keyword'] = json_decode($rs['keyword'],true);
            $this->pagedata['replyRule'] = $rs;
        }

        $objWxNews = app::get('market')->model('wx_news');
        $this->pagedata['news_items'] = $objWxNews->getList('wx_news_id,title');

        $this->display('admin/weixin/add_auto_reply.html');
    }

    function saveAutoReply(){
        $this->begin();
        $objReply = app::get('market')->model('wx_keywords_autoreply');
        $arr = $_POST;

        if(empty($arr['keyword'][0])){
            $this->end(false,'关键字必填');
            exit;
        }

        //对总表关键词进行处理
        $postKeyWords = $arr['keyword'];
        if($arr['id']){
            $selfKeyWords = $objReply->getKeywordsById($arr['id']);

        }else{
            $selfKeyWords = array();
        }
        $this->processKeyWords($postKeyWords,$selfKeyWords,'reply');
        $arr['keyword'] = json_encode($postKeyWords);


        if($objReply->save($arr)){

            $this->end(true,'保存成功');
        }else{
            $this->end(false,'保存失败');
        }
    }

    function deleteAutoReply(){
        $objReply= $this->app->model('wx_keywords_autoreply');
        $data = $_POST;

        $this->begin();
        if(!$data['id']){
            $this->end(false,app::get('taocrm')->_('无数据提交'));
        }

        //删除关键字总表
        $keywords = $objReply->getKeywordsById($data['id']);
        $objWxKeyWords = app::get('market')->model('wx_keywords');
        $objWxKeyWords->delete($keywords);

        if($objReply->delete($data['id'])){
            $this->end(true,app::get('taocrm')->_('操作成功'),'index.php?app=market&ctl=admin_weixin&act=keywordAutoReply');
        }else{
            $this->end(false,app::get('taocrm')->_('操作失败'));
        }
    }

    /***************微信群发消息开始****************/
    public function msg_send_all()
    {
        $param = array(
            'title'=>'群发消息列表',
            'use_buildin_recycle'=>false,
            'use_buildin_filter'=>false,
            'base_filter'=>array(),
            'orderBy'=>'id DESC',
            'actions'=>array(
                array(
                    'label'=>'新增群发内容',
                    'href'=>'index.php?app=market&ctl=admin_weixin&act=add_send_all_msg',
                    'target'=>'dialog::{width:800,height:400,title:\'新增群发内容\'}'
                ),
                array(
                    'label'=>'作废',
                    'submit'=>'index.php?app=market&ctl=admin_weixin&act=delete_send_msg&l=1',
                    'target'=>'dialog::{width:200,height:100,title:\'作废\'}'
                ),
            ),
        );

        if(!isset($_GET['tab']) && $this->wx_bind_status == false){
            $param['top_extra_view'] = $this->_extra_view;
        }

        $this->finder('market_mdl_wx_msg_send_all',$param);
    }

    //发送数据调用微信接口函数
    public function push_msg_to_wx($fun_name = null,$data=null,$api_get_param = null,$test_api = false)
    {
        $wx_obj = kernel::single('market_service_weixin_api');
        $test_api && $wx_obj->set_test();
        return $wx_obj->push_api($fun_name,$data,$api_get_param);
    }

    //新增和编辑群发内容
    public function add_send_all_msg($id = null)
    {
        if($id!=null){
            $msg_mod = app::get('market')->model('wx_msg_send_all');
            $rs = $msg_mod->dump(array('id'=>$id),'*');
            $rs['content'] = json_decode($rs['msg_content'],true);
            $this->pagedata['msg_data'] = $rs;
        }

        $objWxNews = app::get('market')->model('wx_news');
        $this->pagedata['news_items'] = $objWxNews->getList('wx_news_id,title');

        $this->display('admin/weixin/add_send_all_msg.html');
    }

    //保存群发内容
    function save_send_msg()
    {
        $this->begin('index.php?app=market&ctl=admin_weixin&act=msg_send_all');
        $msg_mod = app::get('market')->model('wx_msg_send_all');
        $arr = $_POST;
        switch($arr['send_type'])
        {
            case 'msg':
                $arr['msg_content'] = json_encode($arr['content']);
                break;
            case 'news':
                $news_mod = app::get('market')->model('wx_news');
                $news = $news_mod->dump($arr['news_id']);
                $news_info = json_decode($news['news_info'],true);
                if($news['type'] == '1')
                {
                    $news_info = array($news_info);    
                }
                foreach($news_info as $k => $v)
                {
                    if($v['link_type'] == 'url')
                    {
                        $link_data[] = $k+1;
                        continue;
                    }
                    $news_info[$k]['picurl'] = base_storager::image_path($v['picurl'],'s' );
                }
                if(count($link_data)>0)
                {
                    $err = implode(',',$link_data);
                    $this->end(false,'您选择的图文素材第'.$err.'个素材是链接，请修改');
                }
                $arr['msg_content'] = json_encode($news_info);
                break;
            default:
                $this->end(false,'保存类型未定义');
                break;
        }

        empty($arr['id']) && $arr['create_time'] = time();
        empty($arr['id']) && $arr['create_man'] = kernel::single('desktop_user')->get_name();
        $arr['update_time'] = time();

        if($msg_mod->save($arr)){
            $this->end(true,'保存成功');
        }else{
            $this->end(false,'保存失败');
        }
    }

    function delete_send_msg($id = null)
    {
        if(!$_POST || $_GET['l'])
        {
            $this->pagedata['id'] = $_GET['l'] ? implode(',',$_POST['id']) : $id;
            $this->pagedata['msg'] = '是否确定作废？';
            $this->pagedata['app'] = 'market';
            $this->pagedata['ctl'] = 'admin_weixin';
            $this->pagedata['act'] = 'delete_send_msg';
            $this->display('admin/weixin/check_form.html');
        }else{
            $this->begin('index.php?app=market&ctl=admin_weixin&act=msg_send_all');
            $msg_mod = app::get('market')->model('wx_msg_send_all');
            $ids = explode(',',$_POST['id']);

            foreach($ids as $id)
            {
                $data['id'] = $id;
                $data['del_flag'] = 1;
                $data['update_time'] = time();
                $msg_mod->save($data);
            }

            $this->end(true,'操作成功');
        }
    }
    /***************微信群发消息结束****************/

    function autoPlugin(){
        $param=array(
            'title'=>'微信插件列表',
            'use_buildin_recycle'=>false,
            'use_buildin_filter'=>false,
            'base_filter'=>array(),
            'actions'=>array(
        ),
        );
        $this->finder('market_mdl_wx_plugins',$param);
    }

    function products(){
        $rulelist=$this->app->model("wxautoreply")->getList("*");

        $resplycount=0;

        if($rulelist!=null)
        $resplycount=count($rulelist);

        foreach ($rulelist as $k => &$v) {
            $replycontent = json_decode($v['replycontent'], true);
            $v['keyword'] = $replycontent['keyword'];
        }
        $this->pagedata["rulelist"] = $rulelist;

        $this->pagedata["replaycount"]=$resplycount;

        $this->pagedata['optionsShopList'] =  kernel::single('ecorder_service_shop')->getTaobaoShopList();
        $arr = array_keys($this->pagedata['optionsShopList']);
        if(!empty($arr)){
            $this->pagedata['optionsShopListChecked'] = $arr[0];
        }


        $this->page("admin/weixin/products.html");
    }

    public function survey()
    {
        $param = array(
            'title'=>'互动活动(问答)',
            'use_buildin_recycle'=>false,
            'use_buildin_filter'=>true,
        	'use_buildin_selectrow' => true,
            'orderBy' => "modified DESC",
            'base_filter'=>array(),
            'actions'=>array(
        array(
                'label'=>'添加活动',
                'href'=>'index.php?app=market&ctl=admin_weixin&act=survey_edit',
                'target'=>'dialog::{width:650,height:355,title:\'添加活动\'}'
                ),
                array(
                'label'=>'删除',
                'submit'=>'index.php?app=market&ctl=admin_weixin&act=delete_survey',
                )
                ),
                );

                if($this->wx_bind_status == false){
                    $param['top_extra_view'] = $this->_extra_view;
                }

                $this->finder('market_mdl_wx_survey',$param);
    }

    function delete_survey(){
        $oSurvey= &$this->app->model('wx_survey');
        $data = $_POST;

        $this->begin();
        if(!$data['survey_id']){
            $this->end(false,app::get('taocrm')->_('无数据提交'));
        }

        //删除关键字总表
        $rs = $oSurvey->getList('keywords',array('survey_id'=>$data['survey_id']));
        foreach($rs as $v){
            $keywords[] = $v['keywords'];
        }
        $objWxKeyWords = app::get('market')->model('wx_keywords');
        $objWxKeyWords->delete($keywords);

        if($oSurvey->delete($data['survey_id'])){
            $this->end(true,app::get('taocrm')->_('操作成功'),'index.php?app=market&ctl=admin_weixin&act=survey');
        }else{
            $this->end(false,app::get('taocrm')->_('操作失败'));
        }
    }

    public function survey_edit()
    {
        $oSurvey = $this->app->model('wx_survey');
        if($_POST){
            $this->begin('index.php?app=market&ctl=admin_weixin&act=survey');
            $data = $_POST;
            $survey_id = intval($_POST['survey_id']);

            //关键词检验
            if($survey_id>0){
                $result = $oSurvey->dump(array('survey_id' => $survey_id));
                $selfKeyWords = array($result['keywords']);
            }else{
                $selfKeyWords = array();
            }
            $postKeyWords = array($data['keywords']);
            $this->processKeyWords($postKeyWords,$selfKeyWords,'survey');

            $data['start_date'] = strtotime($data['start_date']);
            $data['end_date'] = strtotime($data['end_date']);

            $data['item_ids'] = json_encode(explode(',',$data['item_ids']));
            //echo('<pre>');var_dump($data);die();
            if($survey_id>0){
                //unset($data['_DTYPE_TIME'],$data['_DTIME_']);
                $data['modified'] = date('Y-m-d H:i:s');
                $q = $oSurvey->update($data, array('survey_id'=>$survey_id));
            }else{
                unset($data['survey_id']);
                $data['modified'] = date('Y-m-d H:i:s');
                $data['created'] = date('Y-m-d H:i:s');
                $q = $oSurvey->insert($data);
            }
            //var_dump($data);die();
            $this->end(true,'保存成功');
            exit;
        }

        //获取默认的问答题库
        $items = array();
        $oSurveyItems = $this->app->model('wx_survey_items');
        $items = $oSurveyItems->getList('item_id,title', '', 0, 20);
        $this->pagedata['items'] = $items;

        $survey_id = intval($_GET['survey_id']);
        $sel_items = array();
        $rs['is_active'] = 1;

        if($survey_id>0){
            $rs = $oSurvey->dump($survey_id);
            $rs['item_ids'] = json_decode($rs['item_ids'], true);
            if($rs['item_ids']){
                $rs_items = $oSurveyItems->getList('item_id,title', array('item_id'=>$rs['item_ids']), 0, 20);
                if($rs_items){
                    foreach($rs_items as $v){
                        $items[$v['item_id']] = $v;
                    }
                    foreach($rs['item_ids'] as $v){
                        $sel_items[] = $items[$v];
                    }
                }
                $this->pagedata['select_items'] = $sel_items;
            }
            $rs['item_ids'] = implode(',', $rs['item_ids']);
        }
        if(!$rs['start_date']) $rs['start_date'] = date('Y-m-d');
        if(!$rs['end_date']) $rs['end_date'] = date('Y-m-d', strtotime('+30 days'));
        $this->pagedata['rs'] = $rs;

        $this->display("admin/weixin/survey_edit.html");
    }

    //互动问答库
    public function survey_items()
    {
        $param = array(
            'title'=>'互动问答库',
            'use_buildin_recycle'=>true,
            'use_buildin_filter'=>true,
        	'use_buildin_selectrow' => true,
        //'orderBy' => "modified DESC",
            'base_filter'=>array(),
            'actions'=>array(
        array(
                    'label'=>'添加问题',
                    'href'=>'index.php?app=market&ctl=admin_weixin&act=survey_items_edit',
                    'target'=>'dialog::{width:650,height:355,title:\'添加问题\'}'
                    ),
                    ),
                    );

                    if($this->wx_bind_status == false){
                        $param['top_extra_view'] = $this->_extra_view;
                    }

                    $this->finder('market_mdl_wx_survey_items',$param);
    }

    public function survey_items_edit()
    {
        $oSurveyItems = $this->app->model('wx_survey_items');
        if($_POST){
            $this->begin('index.php?app=market&ctl=admin_weixin&act=survey_items');
            $data = $_POST;
            $item_id = intval($_POST['item_id']);

            $data['options'] = json_encode($data['options']);
            $data['option_tags'] = json_encode($data['option_tags']);
            if($item_id>0){
                $data['modified'] = date('Y-m-d H:i:s');
                $q = $oSurveyItems->update($data, array('item_id'=>$item_id));
            }else{
                unset($data['item_id']);
                $data['modified'] = date('Y-m-d H:i:s');
                $data['created'] = date('Y-m-d H:i:s');
                $q = $oSurveyItems->insert($data);
            }
            //var_dump($data);die();
            $this->end(true,'保存成功');
            exit;
        }

        $item_id = intval($_GET['item_id']);
        $rs['item_id'] = $item_id;
        $rs['item_type'] = 1;
        $rs['is_active'] = 1;

        if($item_id>0){
            $rs = $oSurveyItems->dump($item_id);
            $rs['options'] = json_decode($rs['options'], true);
            $rs['option_tags'] = json_decode($rs['option_tags'], true);
        }

        $this->pagedata['rs'] = $rs;
        $this->display("admin/weixin/survey_items_edit.html");
    }

    public function plugin(){
        $objPlugins = app::get('market')->model('wx_plugins');
        $objPlugins->init();
        $this->pagedata['plugins'] = $objPlugins->getPlugins();
        $this->page("admin/weixin/plugin.html");
    }

    public function changePluginStatus(){
        $this->begin();
        $objPlugins = app::get('market')->model('wx_plugins');
        $data = array('status'=>$_POST['status']);
        $objPlugins->update($data,array('id'=>$_POST['id']));
        $this->end();
    }

    public function ajax_get_items()
    {
        $q = trim($_POST['q']);
        if($q){
            $where = "where title like '%$q%'";
        }
        $oSurveyItems = $this->app->model('wx_survey_items');
        $sql = "select item_id,title from sdb_market_wx_survey_items $where limit 20";
        $rs = $oSurveyItems->db->select($sql);
        echo(json_encode($rs));
    }

    function registRuleSet(){
        base_kvstore::instance('market')->fetch('wxuser', $wxuser);
        $wxuser = json_decode($wxuser,true);
        $this->pagedata['registRule'] = $wxuser['registRule'];
        $this->page("admin/weixin/regist_rule_set.html");
    }

    function saveRegistRule(){
        $this->begin();
        $post = $_POST;
        $registRule = array();
        base_kvstore::instance('market')->fetch('wxuser', $wxuser);
        $wxuser = json_decode($wxuser,true);

        if(isset($post['regist_rule'])){
            $registRule['regist_rule'] = $post['regist_rule'];
            if($post['regist_rule'] == 1){
                if(isset($wxuser['registRule']['regist_rule_keywords'])){
                    $selfKeyWords = explode(',', $wxuser['registRule']['regist_rule_keywords']);
                    $objWxKeyWords = app::get('market')->model('wx_keywords');
                    $objWxKeyWords->delete($selfKeyWords);
                }
            }else if($post['regist_rule'] == 2){
                $post['regist_rule_keywords'] = trim($post['regist_rule_keywords']);
                if(!empty($post['regist_rule_keywords'])){
                    $postKeyWords = explode(',', $post['regist_rule_keywords']);

                    $selfKeyWords = array();
                    if(!empty($wxuser['registRule']['regist_rule_keywords'])){
                        $selfKeyWords = explode(',', $wxuser['registRule']['regist_rule_keywords']);
                    }

                    $this->processKeyWords($postKeyWords,$selfKeyWords,'regist');
                    $registRule['regist_rule_keywords'] = $post['regist_rule_keywords'];
                }else{
                    $this->end(false,'签到规则,回复关键字为空');
                }
            }
        }else{
            $this->end(false,'请选择签到规则');
        }

        if(isset($post['regist_point_rule'])){
            $registRule['regist_point_rule'] = $post['regist_point_rule'];
            if($post['regist_point_rule'] == 1){
                $registRule['regist_point_rule_1_point'] = $post['regist_point_rule_1_point'];
            }else if($post['regist_point_rule'] == 2){
                $registRule['regist_point_rule_2_point'] = $post['regist_point_rule_2_point'];
                $registRule['regist_point_rule_2_times'] = $post['regist_point_rule_2_times'];
                $registRule['regist_point_rule_2_times_point'] = $post['regist_point_rule_2_times_point'];
            }else{
                $registRule['regist_point_rule_3_point'] = $post['regist_point_rule_3_point'];
                $registRule['regist_point_rule_3_go_point'] = $post['regist_point_rule_3_go_point'];
                $registRule['regist_point_rule_3_times'] = $post['regist_point_rule_3_times'];
                $registRule['regist_point_rule_3_times_point'] = $post['regist_point_rule_3_times_point'];
            }
        }else{
            $this->end(false,'请选择签到积分规则');
        }


        $registRule['replyFinishTxt'] = $post['replyFinishTxt'];
        $wxuser['registRule'] = $registRule;
        base_kvstore::instance('market')->store('wxuser', json_encode($wxuser));
        $this->end(true,'保存成功','index.php?app=market&ctl=admin_weixin&act=registRuleSet');
    }

    function pointLog(){
        $param=array(
            'title'=>'微信积分日志列表',
            'use_buildin_recycle'=>false,
            'use_buildin_filter'=>false,
            'base_filter'=>array(),
            'actions'=>array(
        ),
        );
        $this->finder('market_mdl_wx_point_log',$param);
    }

    function openId()
    {
        $param=array(
            'title'=>'微信客户账号列表',
            'use_buildin_recycle'=>false,
            'use_buildin_filter'=>false,
            'base_filter'=>array(),
            'actions'=>array(
        array(
                    'label'=>'更新微信客户',
                    'href'=>'index.php?app=market&ctl=admin_weixin&act=updateWxUser',
                    'target'=>'dialog::{width:650,height:355,title:\'更新微信客户\'}'
                    ),
                    ),
                    );
                    $this->finder('market_mdl_wx_member',$param);
    }

    function updateWxUser(){
        $this->display("admin/weixin/update_wx_user.html");
    }

    //获取微信用户列表
    function getWxUserlist(){
        $msg = '';
        kernel::single('market_service_weixin')->getWxUserList($msg);
        $result = array();
        if(!empty($msg)){
            $result['status'] = false;
            $result['msg'] = $msg;
        }else{
            $result['status'] = true;
            $objWxMember = app::get('market')->model('wx_member');
            $result['total'] =  $objWxMember->getWxUserCount();
            $result['updateTotal'] =  $objWxMember->getNeedUpdateWxUserCount();
        }

        echo json_encode($result);
        exit;
    }

    //获取微信用户的详细信息
    function toUpdateWxUser()
    {
        $msg = '';
        $nums = 100;
        $page = isset($_POST['page']) ? $_POST['page'] : 1;
        $objWxMember = app::get('market')->model('wx_member');
        $getResult = kernel::single('market_service_weixin')->getWxUser($page,$nums,$msg);
        $result = array();
        if(!empty($msg)){
            $result['status'] = false;
            $result['msg'] = $msg;
        }else{
            $result['status'] = true;
            $result['updateResult'] = $getResult;
        }

        echo json_encode($result);
        exit;
    }

    public function opPoint($wx_member_id)
    {
        $this->pagedata['wx_member_id'] = $wx_member_id;
        $this->display('admin/weixin/point/edit.html');
    }

    public function savePoint(){
        $this->begin();
        $wx_member_id = $_POST['wx_member_id'];
        $point = $_POST['point'];
        $op_type = $_POST['op_type'];
        $objWxMember = app::get('market')->model('wx_member');
        if($op_type == 1){
            $id = $objWxMember->updatePoint($wx_member_id,2,$point,'手工增加积分',$msg);
        }else{
            $id = $objWxMember->updatePoint($wx_member_id,2,-$point,'手工扣减积分',$msg);
        }

        if($id){
            $this->end(true,'操作成功');
        }else{
            $this->end(false,$msg);
        }
    }
    
    public function customMenu()
    {
        kernel::single('market_ctl_admin_weixin_menu')->customMenu();
    }

    public function news()
    {
        $actions = array(
        array(
                    'label'=>'添加单条图文',
                    'href'=>'index.php?app=market&ctl=admin_weixin&act=addNews&type=1',
                    'target'=>'dialog::{width:800,height:400,title:\'添加单条图文\'}'
                    ),
                    array(
                    'label'=>'添加多条图文',
                    'href'=>'index.php?app=market&ctl=admin_weixin&act=addNews&type=2',
                    'target'=>'dialog::{width:800,height:400,title:\'添加多条图文\'}'
                    ),
        );
        $param=array(
            'title'=>'图文素材',
            'use_buildin_recycle'=>false,
            'use_buildin_filter'=>false,
            'base_filter'=>array(),
            'actions'=>$actions,
            'orderBy'=>'wx_news_id DESC',
                    );
                    $this->finder('market_mdl_wx_news',$param);
    }

    public function addNews($wx_news_id){
        $modelWxNews = $this->app->model('wx_news');
        if($wx_news_id){
            $news = $modelWxNews->dump($wx_news_id);
            $news['news_info'] = json_decode($news['news_info'],true);
            $addCount = 5 - count($news['news_info']);
            if($addCount > 0){
                for($i=0;$i<$addCount;$i++){
                    $news['news_info'][] = array('title'=>'');
                }
            }
        }else{
            $news_info = array();
            for($i=0;$i<5;$i++){
                $news_info[] = array('title'=>'');
            }
            $news = array('type'=>$_GET['type'],'news_info'=>$news_info);
        }

        $this->pagedata['wx_ucenter_url'] = kernel::base_url(1).'/index.php/market/site_weixin_ucenter/points';
        $this->pagedata['news'] = $news;

        if($news['type'] == 1){
            $this->display('admin/weixin/news.html');
        }else{
            $this->display('admin/weixin/more_news.html');
        }

    }

    public function saveNews(){
        $this->begin();
        $post = $_POST;
        //echo '<pre>';var_dump($post);
        //exit;
        $modelWxNews = $this->app->model('wx_news');

        if($modelWxNews->saveNews($post)){
            $this->end(true,'保存成功');
        }else{
            $this->end(false,'保存失败');
        }

    }

    public function ajaxNewsItem(){
        $this->pagedata['index_item'] = $_POST['index_item'];
        $this->pagedata['item'] = $_POST['news_item'];
        $this->display('admin/weixin/more_news_item.html');
    }

    public function ajaxGetNewsItem(){
        $modelWxNews = $this->app->model('wx_news');
        $news_items = $modelWxNews->getList('wx_news_id,title',array('title|has'=>$_POST['q']));
        echo json_encode($news_items);
    }

    public function noResponseChat()
    {
        $param=array(
            'title'=>'人工处理聊天记录列表',
            'use_buildin_recycle'=>false,
            'use_buildin_filter'=>false,
            'base_filter'=>array('is_response'=>0,'chat_pid'=>0),
            'actions'=>array(),
            'orderBy' => "created DESC",
        );
        $this->finder('market_mdl_wx_chat',$param);
    }

    public function response_chat(){
        if($_POST)
        {
            $url = "index.php?app=market&ctl=admin_weixin&act=noResponseChat";
            $this->begin($url);
            $not_empty = array(
                    'chat_re_content' => '回复内容',
                    );

            $info = array(
                        'chat_pid'   => !empty($_POST['info']['chat_id'])   ? intval($_POST['info']['chat_id'])   : false,
                        'chat_content' => !empty($_POST['info']['chat_content'])   ? trim($_POST['info']['chat_content'])   : false,
                        'created'   => date('Y-m-d H:i:s',time()),
                        'chat_type'   => 'send',
                    );
            $info['chat_pid'] || $this->end(false,'参数异常');

            foreach($not_empty as $k => $v)
            {
                if($info[$k] === false)
                {
                    $this->end(false,$not_empty[$k].'不能为空');
                }
            }

            //新增回复数据
            $mod_obj = app::get('market')->model("wx_chat");
            $up_info = $data = $mod_obj->dump($info['chat_pid']);
            foreach($info as $k => $v)
            {
                $up_info[$k] = $v;
            }
            unset($up_info['chat_id']);
            $rt = $mod_obj->save($up_info);
            $rt = $rt ? true : false;

            //修改状态
            $data['response_type'] = $rt ? 1 : 0;
            $rt = $mod_obj->save($data);

            //回复
            kernel::single('market_service_weixin')->response_chat($data['FromUserName'],$data['ToUserName'],$up_info['chat_content']);

            $this->end($rt,app::get('base')->_($rt?'回复成功':'回复失败'));
        }else
        {
            $id = !empty($_GET['id']) ? intval($_GET['id']) : false;
            $render = app::get('market')->render();

            if(!$id)
            {
                $render->pagedata['info'] = false;
            }else
            {
                $mod_obj = app::get('market')->model('wx_chat');
                $info = $mod_obj->dump($id);
                $render->pagedata['info'] = $info;
            }
            $render->display('admin/weixin/chat/response.html');
        }

    }

    /*门店管理*/
    public function store_manage()
    {
        $title = '店铺设置';

        $this->finder('market_mdl_wx_store_subbranch',array(
            'title'=> $title,
            'actions'=>array(
            	array(
                	'label'=>'新增店铺',
                    'href'=>'index.php?app=market&ctl=admin_weixin&act=store_manage_add',
                    'target'=>'dialog::{width:822,height:500,title:\'新增店铺\'}'
             	),
             ),
            'orderBy' => '',//去掉默认排序
            'use_buildin_recycle'=>true,
            'use_view_tab'=>true,
            //'finder_cols'=>'member_id,column_tag,name,uname,mobile,tel,email,addr,last_contact_time',
        ));
    }

    /*门店管理编辑*/
    public function store_manage_add()
    {
        if($_POST)
        {
            $url = "index.php?app=market&ctl=admin_weixin&act=store_manage";
            $this->begin($url);
            $not_empty = array(
                    'store_name' => '店铺名称',
                    'phone'      => '店铺电话',
                    'store_area' => '店铺地区',
                    'address'    => '店铺地址'
                    );

            $info = array(
                        'store_name' => !empty($_POST['info']['store_name']) ? trim($_POST['info']['store_name'])   : false,
                        'phone'      => !empty($_POST['info']['phone'])      ? trim($_POST['info']['phone'])        : false,
                        'store_area' => !empty($_POST['info']['store_area']) ? trim($_POST['info']['store_area'])   : false,
                        'address'    => !empty($_POST['info']['address'])    ? trim($_POST['info']['address'])      : false,
                        'open_time'  => !empty($_POST['info']['open_time'])  ? trim($_POST['info']['open_time'])    : '',
                        'business'   => !empty($_POST['info']['business'])   ? trim($_POST['info']['business'])     : '',
                        'create_time'=> time(),
                    );
            list($info['map_x'],$info['map_y']) = explode(',',trim($_POST['info']['map']));
            foreach($not_empty as $k => $v)
            {
                if($info[$k] === false)
                {
                    $this->end(false,$not_empty[$k].'不能为空');
                }
            }

            //保存到本地数据库
            $mod_obj = app::get('market')->model("wx_store_subbranch");
            $rt = $mod_obj->save($info);
            $rt = $rt ? true : false;

            $this->end($rt,app::get('base')->_($rt?'新增成功':'新增失败'));

        }else
        {
            $render = app::get('market')->render();

            $render->display('admin/weixin/store/edit.html');
        }
    }

    public function store_manage_ajax()
    {
        $adr = !empty($_GET['adr']) ? trim($_GET['adr']) : false;
        $city = !empty($_GET['city']) ? trim($_GET['city']) : false;
        if(!$adr || !$city)
        {
            echo 'false';
            exit;
        }

        $uri = '/place/v2/search';
        $querystring_arrays = array(
                    'q' => $adr,
                    'region' => $city,
                    'output' => 'json',
                    'ak' => self::BAIDU_MAP_AK,
                    'timestamp' => time(),
            );
        $api_url = self::BAIDU_MAP_API.$uri.'?'.http_build_query($querystring_arrays).'&sn='.$this->caculateAKSN(self::BAIDU_MAP_AK,self::BAIDU_MAP_SK,$uri,$querystring_arrays);

        $http = new base_httpclient;
        echo $http->get($api_url);
        exit;
    }

    /**
     * baiduAPI sn生成器
     * @return void
     */
    protected function caculateAKSN($ak, $sk, $url, $querystring_arrays, $method = 'GET')
    {
        if ($method === 'POST'){
            ksort($querystring_arrays);
        }
        $querystring = http_build_query($querystring_arrays);
        return md5(urlencode($url.'?'.$querystring.$sk));
    }

    /*门店管理编辑*/
    public function store_manage_edit()
    {
        if($_POST)
        {
            $url = "index.php?app=market&ctl=admin_weixin&act=store_manage";
            $this->begin($url);
            $not_empty = array(
                    'store_name' => '店铺名称',
                    'phone'      => '店铺电话',
                    'store_area' => '店铺地区',
                    'address'    => '店铺地址'
                    );


            $info = array(
                        'store_id'   => !empty($_POST['info']['store_id'])   ? intval($_POST['info']['store_id'])   : false,
                        'store_name' => !empty($_POST['info']['store_name']) ? trim($_POST['info']['store_name'])   : false,
                        'phone'      => !empty($_POST['info']['phone'])      ? trim($_POST['info']['phone'])        : false,
                        'store_area' => !empty($_POST['info']['store_area']) ? trim($_POST['info']['store_area'])   : false,
                        'address'    => !empty($_POST['info']['address'])    ? trim($_POST['info']['address'])      : false,
                        'open_time'  => !empty($_POST['info']['open_time'])  ? trim($_POST['info']['open_time'])    : '',
                        'business'   => !empty($_POST['info']['business'])   ? trim($_POST['info']['business'])     : '',
                        'picurl'     => !empty($_POST['info']['picurl'])     ? trim($_POST['info']['picurl'])       : '',
                    );
            $info['store_id'] || $this->end(false,'参数异常');

            list($info['map_x'],$info['map_y']) = explode(',',trim($_POST['info']['map']));
            foreach($not_empty as $k => $v)
            {
                if($info[$k] === false)
                {
                    $this->end(false,$not_empty[$k].'不能为空');
                }
            }

            //保存到本地数据库
            $mod_obj = app::get('market')->model("wx_store_subbranch");
            $rt = $mod_obj->save($info);
            $rt = $rt ? true : false;

            $this->end($rt,app::get('base')->_($rt?'保存成功':'保存失败'));
        }else
        {
            $id = !empty($_GET['item_id']) ? intval($_GET['item_id']) : false;
            $render = app::get('market')->render();

            if(!$id)
            {
                $render->pagedata['info'] = false;
            }else
            {
                $mod_obj = app::get('market')->model('wx_store_subbranch');
                $info = $mod_obj->dump($id);
                $info['map'] = !empty($info['map_x']) ? $info['map_x'].','. $info['map_y'] : '';
                $render->pagedata['info'] = $info;
            }
            $render->display('admin/weixin/store/edit.html');
        }
    }

    /*微信活动管理-积分抽奖*/
    public function lottery_manage(){
        $title = '积分抽奖';

        $this->finder('market_mdl_wx_integral_lottery',array(
            'title'=> $title,
            'actions'=>array(
            	array(
                	'label'=>'新增活动',
                    'href'=>'index.php?app=market&ctl=admin_weixin&act=lottery_manage_add',
                    'target'=>'dialog::{width:822,height:500,title:\'新增活动\'}'
             	),
             ),
            'orderBy' => 'lottery_status ASC,create_time DESC',//去掉默认排序
            'use_buildin_recycle'=>false,
            'use_view_tab'=>true,
        ));
    }

    /*微信活动管理-积分抽奖编辑*/
    public function lottery_manage_edit(){
         if($_POST)
        {
            $url = "index.php?app=market&ctl=admin_weixin&act=lottery_manage";
            $this->begin($url);
            $not_empty = array(
                    'lottery_name'  => '活动名称',
                    'minus_score'   => '抽奖扣除积分',
                    'start_time'    => '开始时间',
                    'end_time'      => '结束时间'
                    );

            $info = array(
                        'lottery_id'    => !empty($_POST['info']['lottery_id']) ? intval($_POST['info']['lottery_id'])  : false,
                        'lottery_name'  => !empty($_POST['info']['lottery_name'])? trim($_POST['info']['lottery_name']) : false,
                        'minus_score'   => !empty($_POST['info']['minus_score'])? trim($_POST['info']['minus_score'])   : false,
                        'start_time'    => !empty($_POST['info']['start_time']) ? strtotime($_POST['info']['start_time']): false,
                        'end_time'      => !empty($_POST['info']['end_time'])   ? strtotime($_POST['info']['end_time']) : false,
                        'win_msg'       => !empty($_POST['info']['win_msg'])    ? trim($_POST['info']['win_msg'])       : '',
                        'lose_msg'      => !empty($_POST['info']['lose_msg'])   ? trim($_POST['info']['lose_msg'])      : '',
                        'start_msg'     => !empty($_POST['info']['start_msg'])  ? trim($_POST['info']['start_msg'])     : '',
                        'end_msg'       => !empty($_POST['info']['end_msg'])    ? trim($_POST['info']['end_msg'])       : '',
                        'update_time'   => time(),
                    );

            $info['lottery_id'] || $this->end(false,'参数异常');

            if($info['start_time'] >= $info['end_time'])
            {
                $this->end(false,'开始时间不能小于结束时间');
            }

            foreach($not_empty as $k => $v)
            {
                if($info[$k] === false)
                {
                    $this->end(false,$not_empty[$k].'不能为空');
                }
            }

            //保存到本地数据库
            $mod_obj = app::get('market')->model("wx_integral_lottery");
            $rt = $mod_obj->save($info);
            $rt = $rt ? true : false;

            //奖品处理
            unset($_POST['awards']['key']);
            $count_awards = count($_POST['awards']);
            foreach($_POST['awards'] as $award)
            {
                $award = array(
                            'lottery_id'  => $info['lottery_id'],
                            'awards_name' => !empty($award['awards_name'])  ? trim($award['awards_name'])   : false,
                            'awards_info' => !empty($award['awards_info'])  ? trim($award['awards_info'])   : false,
                            'awards_stock' => !empty($award['awards_stock']) ? intval($award['awards_stock']) : 0,
                            'win_rate'    => !empty($award['win_rate'])     ? intval($award['win_rate'])    : false,
                            'update_time' => time(),
                            'create_time' => time(),
                        );
                if(empty($award['awards_name']) || empty($award['win_rate']))
                {
                    continue;
                }
                $awards[] = $award;
            }
            if(count($awards) < $count_awards)
            {
                $this->end(false,'有效奖品不能少于'.$count_awards.'个,保存奖品失败');
            }
            if($awards)
            {
                $mod_info = app::get('market')->model("wx_integral_lotteryinfo");
                $re_de = $mod_info->delete(array('lottery_id' => $info['lottery_id']));
                foreach($awards as $award)
                {
                    $award_rt = $mod_info->save($award);
                }
            }
            $this->end($rt,app::get('base')->_($rt?'保存成功':'保存失败'));

        }else
        {
            $id = !empty($_GET['item_id']) ? intval($_GET['item_id']) : false;
            $render = app::get('market')->render();

            if(!$id)
            {
                $render->pagedata['info'] = false;
            }else
            {
                $mod_obj = app::get('market')->model('wx_integral_lottery');
                $info = $mod_obj->dump($id);
                $render->pagedata['info'] = $info;

                $mod_info = app::get('market')->model("wx_integral_lotteryinfo");
                $lotteryinfo = $mod_info->getList('*',array('lottery_id' => $id));
                $render->pagedata['lotteryinfo'] = $lotteryinfo;
                $render->pagedata['lotteryinfo_count'] = count($lotteryinfo);
            }
            if($_GET['type']){
                $render->pagedata['oper_type'] = $_GET['type'];
            }
            $render->display('admin/weixin/lottery/edit.html');
        }
    }

    /*微信活动管理-积分抽奖编辑*/
    public function lottery_manage_add(){
         if($_POST)
        {
            $url = "index.php?app=market&ctl=admin_weixin&act=lottery_manage";
            $this->begin($url);
            $not_empty = array(
                    'lottery_name'  => '活动名称',
                    'minus_score'   => '抽奖扣除积分',
                    'start_time'    => '开始时间',
                    'end_time'      => '结束时间'
                    );

            $info = array(
                        'lottery_name'  => !empty($_POST['info']['lottery_name'])? trim($_POST['info']['lottery_name']) : false,
                        'lottery_status'=> 'create',
                        'start_time'    => !empty($_POST['info']['start_time']) ? strtotime($_POST['info']['start_time']): false,
                        'end_time'      => !empty($_POST['info']['end_time'])   ? strtotime($_POST['info']['end_time']) : false,
                        'create_time'   => time(),
                        'win_msg'       => !empty($_POST['info']['win_msg'])    ? trim($_POST['info']['win_msg'])       : '恭喜你中奖！',
                        'lose_msg'      => !empty($_POST['info']['lose_msg'])   ? trim($_POST['info']['lose_msg'])      : '很遗憾，未能中奖，再试一次！',
                        'start_msg'     => !empty($_POST['info']['start_msg'])  ? trim($_POST['info']['start_msg'])     : '活动礼品正在筹备中，精确期待！',
                        'end_msg'       => !empty($_POST['info']['end_msg'])    ? trim($_POST['info']['end_msg'])       : '本次活动已经结束！',
                        'minus_score'   => !empty($_POST['info']['minus_score'])? trim($_POST['info']['minus_score'])   : false,
                        'update_time'   => time(),
                    );
            if($info['start_time'] >= $info['end_time'])
            {
                $this->end(false,'开始时间不能小于结束时间');
            }

            foreach($not_empty as $k => $v)
            {
                if($info[$k] === false)
                {
                    $this->end(false,$not_empty[$k].'不能为空');
                }
            }

            //保存到本地数据库
            $mod_obj = app::get('market')->model("wx_integral_lottery");
            $rt = $mod_obj->save($info);
            $rt_bool = $rt ? true : false;

            //奖品处理
            unset($_POST['awards']['key']);
            $count_awards = count($_POST['awards']);
            foreach($_POST['awards'] as $award)
            {
                $award = array(
                            'lottery_id'  => $info['lottery_id'],
                            'awards_name' => !empty($award['awards_name'])  ? trim($award['awards_name'])   : false,
                            'awards_info' => !empty($award['awards_info'])  ? trim($award['awards_info'])   : false,
                            'awards_stock' => !empty($award['awards_stock']) ? intval($award['awards_stock']) : 0,
                            'win_rate'    => !empty($award['win_rate'])     ? intval($award['win_rate'])    : false,
                            'update_time' => time(),
                            'create_time' => time(),
                        );
                if(empty($award['awards_name']) || empty($award['win_rate']))
                {
                    continue;
                }
                $awards[] = $award;
            }
            if(count($awards) < $count_awards)
            {
                $this->end(false,'有效奖品不能少于'.$count_awards.'个,保存奖品失败');
            }
            if($awards)
            {
                $mod_info = app::get('market')->model("wx_integral_lotteryinfo");
                foreach($awards as $award)
                {
                    $award_rt = $mod_info->save($award);
                }
            }
            $this->end($rt,app::get('base')->_($rt_bool?'保存成功':'保存失败'));

        }else
        {
            $render = app::get('market')->render();
            $render->display('admin/weixin/lottery/edit.html');
        }
    }

    public function lottery_manage_viewp()
    {
        $id = !empty($_GET['item_id']) ? intval($_GET['item_id']) : false;

        $url = "index.php?app=market&ctl=admin_weixin&act=lottery_manage";
        $this->begin($url);

        $mod_log = app::get('market')->model("wx_integral_lotterylog");
        $lotterylog = $mod_log->getList('*',array('lottery_id' => $id),0,-1,'log_id DESC');
        $render = app::get('market')->render();
        $render->pagedata['lotterylog'] = $lotterylog;
        $render->pagedata['item_id'] = $id;
        $render->display('admin/weixin/lottery/view.html');
    }

    //预览链接
    public function lottery_manage_href()
    {
        $id = !empty($_GET['item_id']) ? intval($_GET['item_id']) : false;

        $url = "index.php?app=market&ctl=admin_weixin&act=lottery_manage";
        $this->begin($url);

        $url = kernel::base_url(1).'/index.php/market/site_weixin_ucenter/lottery?lottery_id='.$id;

        $render = app::get('market')->render();
        $render->pagedata['url'] = $url;
        $render->pagedata['item_id'] = $id;
        $render->pagedata['img_url'] = 'index.php?app=market&ctl=admin_weixin&act=lottery_manage_img&item_id='.$id;
        $render->display('admin/weixin/lottery/href.html');
    }

    //二维码
    public function lottery_manage_img()
    {
        $id = !empty($_GET['item_id']) ? intval($_GET['item_id']) : false;

        if($_GET['m'] == 'd')
        {
            header('Content-Disposition: attachment; filename="two_dimension_code_'.$id.'.jpg"');
        }

        include ROOT_DIR."/script/phpqrcode/phpqrcode.php";//引入PHP QR库文件
        $errorCorrectionLevel = "L";
        $matrixPointSize = "4";
        $url = kernel::base_url(1).'/index.php/market/site_weixin_ucenter/lottery?lottery_id='.$id;

        QRcode::png($url, false, $errorCorrectionLevel, $matrixPointSize);
    }

    public function lottery_manage_close()
    {
        $id = !empty($_POST['item_id']) ? intval($_POST['item_id']) : false;
        $invalid_name = !empty($_POST['invalid_name']) ? trim($_POST['invalid_name']) : false;

        $url = "index.php?app=market&ctl=admin_weixin&act=lottery_manage";
        $this->begin($url);

        if($invalid_name == 'on')
        {
            $mod_obj = app::get('market')->model("wx_integral_lottery");
            $info['lottery_id'] = $id;
            $info['close_time'] = time();
            $info['lottery_status'] = 'close';
            $rs = $mod_obj->save($info);
            $rs = $rs ? true : false;
        }else
        {
            $rs = true;
        }
        $this->end($rs,app::get('base')->_($rs?'保存成功':'保存失败'));
    }

    public function lottery_manage_close_select()
    {
        $id = !empty($_GET['item_id']) ? intval($_GET['item_id']) : false;

        $url = "index.php?app=market&ctl=admin_weixin&act=lottery_manage";
        $this->begin($url);

        $render = app::get('market')->render();
        $render->pagedata['item_id'] = $id;
        $render->display('admin/weixin/lottery/close_select.html');
    }

    public function lottery_manage_export()
    {
        $id = !empty($_GET['item_id']) ? intval($_GET['item_id']) : false;

        $url = "index.php?app=market&ctl=admin_weixin&act=lottery_manage";
        $this->begin($url);

        $mod_log = app::get('market')->model("wx_integral_lotterylog");
        $lotterylog = $mod_log->getList('*',array('lottery_id' => $id));
        $list = array();

        $table_column = array(
                    'create_time'   => '参与时间',
                    'people_name'   => '参与人',
                    'phone'         => '手机号',
                    'minus_score'   => '扣除积分',
                    'lottery_info_name' => '抽奖结果',
                    'lottery_name'  => '活动名称'
                    );
        foreach($lotterylog as $log)
        {
            $list[] = array(
                        'create_time'   => date('Y-m-d',$log['create_time']),
                        'people_name'   => $log['people_name'],
                        'phone'         => $log['phone'],
                        'minus_score'   => $log['minus_score'],
                        'lottery_info_name' => $log['lottery_info_name'],
                        'lottery_name'  => $log['lottery_name']
                    );
        }

        $this->_export_data($table_column,$list);
    }

    /*导出数据*/
    private function _export_data($title_line = array(),$params = array(),$has_title = true,$iconv_set = array('UTF-8','GB18030'))
    {
        if($has_title == true)
        {
            header("Content-type:application/vnd.ms-excel");
            $select_time = $this->pagedata['select_date']['start_date'] ? $this->pagedata['select_date']['start_date'].'-'.$this->pagedata['select_date']['end_date'] : 'all';
            $file_name = "导出数据_{$this->_view_type}_{$select_time}.xls";
            header("Content-Disposition:attachment;filename={$file_name}");

            foreach($title_line as $title)
            {
                echo iconv($iconv_set[0],$iconv_set[1],"{$title}\t");
            }
            echo "\n";
        }

        foreach($params as $par_arr)
        {
            foreach($title_line as $title_key => $title_name)
            {
                echo iconv($iconv_set[0],$iconv_set[1],"{$par_arr[$title_key]}\t");
            }
            echo "\n";
        }
    }
    /*微信活动管理-积分换购*/
    public function redemption_manage(){
        $title = '积分换购';
        $this->finder('market_mdl_wx_points_buy',array(
            'title'=> $title,
            'actions'=>array(
            	array(
                	'label'=>'新增活动',
                    'href'=>'index.php?app=market&ctl=admin_weixin&act=buy_manage_add',
                    'target'=>'dialog::{width:822,height:500,title:\'新增活动\'}'
             	),
             ),
            'orderBy' => 'buy_id desc',//去掉默认排序
            'use_buildin_recycle'=>false,
            'use_view_tab'=>true,
            //'finder_cols'=>'member_id,column_tag,name,uname,mobile,tel,email,addr,last_contact_time',
        ));
    }

      /*微信活动管理-积分换购编辑*/
    public function buy_manage_edit(){
         if($_POST)
        {
            $url = "index.php?app=market&ctl=admin_weixin&act=redemption_manage";
            $this->begin($url);
            $not_empty = array(
                    'buy_name'  => '活动名称',
                    'minus_score'   => '抽奖扣除积分',
                    'start_time'    => '开始时间',
                    'end_time'      => '结束时间'
                    );

            $info = array(
                        'buy_id'        => !empty($_POST['info']['buy_id'])     ? intval($_POST['info']['buy_id'])      : false,
                        'buy_name'      => !empty($_POST['info']['buy_name'])   ? trim($_POST['info']['buy_name'])      : false,
                        'start_time'    => !empty($_POST['info']['start_time']) ? strtotime($_POST['info']['start_time']): false,
                        'end_time'      => !empty($_POST['info']['end_time'])   ? strtotime($_POST['info']['end_time']) : false,
                        'minus_score'   => !empty($_POST['info']['minus_score'])? trim($_POST['info']['minus_score'])   : false,
                        'limit_times'   => $_POST['info']['limit_times'] != 0 ?trim($_POST['info']['limit_times']) : 'Unlimited',
                        'msg'           => !empty($_POST['info']['msg'])        ? trim($_POST['info']['msg'])           : '',
                        'goods_name'    => !empty($_POST['info']['goods_name']) ? trim($_POST['info']['goods_name'])    : '',
                        'goods_code'    => !empty($_POST['info']['goods_code']) ? trim($_POST['info']['goods_code'])    : '',
                        'goods_img'     => !empty($_POST['info']['goods_img'])  ? trim($_POST['info']['goods_img'])     : '',
                        'goods_msg'     => !empty($_POST['info']['goods_msg'])  ? trim($_POST['info']['goods_msg'])     : '',
                        'goods_all_stock'=> !empty($_POST['info']['goods_all_stock'])? intval($_POST['info']['goods_all_stock']): 0,
                        'goods_stock'   => !empty($_POST['info']['goods_all_stock'])? 'goods_all_stock - join_num' : 0,
                        'update_time'   => time(),
                        'shop_gift_id'=>!empty($_POST['info']['shop_gift_id']) ? trim($_POST['info']['shop_gift_id'])  : 0
                    );

            $info['buy_id'] || $this->end(false,'参数异常');

            if($info['start_time'] >= $info['end_time'])
            {
                $this->end(false,'开始时间不能小于结束时间');
            }

            foreach($not_empty as $k => $v)
            {
                if($info[$k] === false)
                {
                    $this->end(false,$not_empty[$k].'不能为空');
                }
            }

            //保存到本地数据库
            $mod_obj = app::get('market')->model("wx_points_buy");
            $rt = $mod_obj->save($info);
            $rt = $rt ? true : false;

            $this->end($rt,app::get('base')->_($rt?'保存成功':'保存失败'));

        }else
        {
            $id = !empty($_GET['item_id']) ? intval($_GET['item_id']) : false;
            $render = app::get('market')->render();

            if(!$id)
            {
                $render->pagedata['info'] = false;
            }else
            {
                $mod_obj = app::get('market')->model('wx_points_buy');
                $info = $mod_obj->dump($id);
                if($info['limit_times'] == null){
                    $info['limit_times'] = 0;
                }
               // var_dump($info);
                if($info['limit_times']){
                    $info['limit_times'] = $info['limit_times'] == 'Unlimited' ? 0 : $info['limit_times'];
                }else{
                    $info['limit_times'] = 0;
                }

                $render->pagedata['info'] = $info;
                //erp赠品
                $objShopGift = app::get('ecorder')->model('shop_gift');
                $erp_gifts = $objShopGift->getList('id,gift_name');
                $erp_gifts_arr =array();
                foreach($erp_gifts as $k => $v){
                    $erp_gifts_arr[$v['id']] = $v['gift_name'];
                }
                $render->pagedata['erp_gifts_arr'] = $erp_gifts_arr;
                $limit_times_arr = array(0=>'不限',1=>'1次',2=>'2次',3=>'3次',4=>'4次',5=>'5次');
                $render->pagedata['limit_times_arr'] = $limit_times_arr;
            }
            if($_GET['type']){
                $render->pagedata['oper_type'] = $_GET['type'];
            }
            $render->display('admin/weixin/buy/edit.html');
        }
    }

       /*微信活动管理-积分换购编辑*/
    public function buy_manage_add(){
         if($_POST)
        {
            $url = "index.php?app=market&ctl=admin_weixin&act=redemption_manage";
            $this->begin($url);
            $not_empty = array(
                    'buy_name'  => '活动名称',
                    'minus_score'   => '抽奖扣除积分',
                    'start_time'    => '开始时间',
                    'end_time'      => '结束时间'
                    );

            $info = array(
                        'buy_name'      => !empty($_POST['info']['buy_name'])   ? trim($_POST['info']['buy_name'])      : false,
                        'start_time'    => !empty($_POST['info']['start_time']) ? strtotime($_POST['info']['start_time']): false,
                        'end_time'      => !empty($_POST['info']['end_time'])   ? strtotime($_POST['info']['end_time']) : false,
                        'minus_score'   => !empty($_POST['info']['minus_score'])? trim($_POST['info']['minus_score'])   : false,
                        'limit_times'   => $_POST['info']['limit_times'] != 0 ?trim($_POST['info']['limit_times']) : 'Unlimited',
                        'msg'           => !empty($_POST['info']['msg'])        ? trim($_POST['info']['msg'])           : '',
                        'goods_name'    => !empty($_POST['info']['goods_name']) ? trim($_POST['info']['goods_name'])    : '',
                        'goods_code'    => !empty($_POST['info']['goods_code']) ? trim($_POST['info']['goods_code'])    : '',
                        'goods_img'     => !empty($_POST['info']['goods_img'])  ? trim($_POST['info']['goods_img'])     : '',
                        'goods_msg'     => !empty($_POST['info']['goods_msg'])  ? trim($_POST['info']['goods_msg'])     : '',
                        'goods_all_stock'=> !empty($_POST['info']['goods_all_stock'])? intval($_POST['info']['goods_all_stock']): 0,
                        'goods_stock'=> !empty($_POST['info']['goods_all_stock'])? intval($_POST['info']['goods_all_stock']): 0,
                        'create_time'   => time(),
                        'update_time'   => time(),
                        'buy_status'   => 'create',
                        'shop_gift_id'=>!empty($_POST['info']['shop_gift_id']) ? trim($_POST['info']['shop_gift_id'])  : 0
                    );
            if($info['start_time'] >= $info['end_time'])
            {
                $this->end(false,'开始时间不能小于结束时间');
            }

            foreach($not_empty as $k => $v)
            {
                if($info[$k] === false)
                {
                    $this->end(false,$not_empty[$k].'不能为空');
                }
            }

            //保存到本地数据库
            $mod_obj = app::get('market')->model("wx_points_buy");
            $rt = $mod_obj->save($info);
            $rt = $rt ? true : false;

            $this->end($rt,app::get('base')->_($rt?'保存成功':'保存失败'));

        }else
        {
            $render = app::get('market')->render();
            //erp赠品
            $objShopGift = app::get('ecorder')->model('shop_gift');
            $erp_gifts = $objShopGift->getList('id,gift_name');
            $erp_gifts_arr =array();
            foreach($erp_gifts as $k => $v){
                $erp_gifts_arr[$v['id']] = $v['gift_name'];
            }
            $render->pagedata['erp_gifts_arr'] = $erp_gifts_arr;
            $limit_times_arr = array(0=>'不限',1=>'1次',2=>'2次',3=>'3次',4=>'4次',5=>'5次');
            $render->pagedata['limit_times_arr'] = $limit_times_arr;
            $render->pagedata['info'] = array('limit_times'=>0);
            $render->pagedata['page_type'] = 'add';
            $render->display('admin/weixin/buy/edit.html');
        }
    }

    public function buy_manage_close()
    {
        $id = !empty($_POST['item_id']) ? intval($_POST['item_id']) : false;
        $invalid_name = !empty($_POST['invalid_name']) ? trim($_POST['invalid_name']) : false;

        $url = "index.php?app=market&ctl=admin_weixin&act=redemption_manage";
        $this->begin($url);

        if($invalid_name == 'on')
        {
            $mod_obj = app::get('market')->model("wx_points_buy");
            $info['buy_id'] = $id;
            $info['close_time'] = time();
            $info['buy_status'] = 'close';
            $rs = $mod_obj->save($info);
            $rs = $rs ? true : false;
        }else
        {
            $rs = true;
        }
        $this->end($rs,app::get('base')->_($rs?'保存成功':'保存失败'));
    }

    public function buy_manage_close_select()
    {
        $id = !empty($_GET['item_id']) ? intval($_GET['item_id']) : false;

        $url = "index.php?app=market&ctl=admin_weixin&act=redemption_manage";
        $this->begin($url);

        $render = app::get('market')->render();
        $render->pagedata['item_id'] = $id;
        $render->display('admin/weixin/buy/close_select.html');
    }

    public function buy_manage_viewp()
    {
        $id = !empty($_GET['item_id']) ? intval($_GET['item_id']) : false;

        $url = "index.php?app=market&ctl=admin_weixin&act=redemption_manage";
        $this->begin($url);

        /*$mod_log = app::get('market')->model("wx_points_buylog");
        $lotterylog = $mod_log->getList('*',array('buy_id' => $id));*/
        $exchange_orders = app::get('ecorder')->model("exchange_orders");
        $lotterylog = $exchange_orders->getList('*',array('buy_id' => $id));
        $wx_points_buy = app::get('market')->model("wx_points_buy");
        $buy_list = $wx_points_buy->getList('*',array('buy_id' => $id));
        foreach($lotterylog as $key => $value){
            $lotterylog[$key]['minus_score'] = $buy_list[0]['minus_score'];
        }
        
        $render = app::get('market')->render();
        $render->pagedata['buylog'] = $lotterylog;
        $render->pagedata['item_id'] = $id;
        $render->display('admin/weixin/buy/view.html');
    }

    public function buy_manage_export()
    {
        $id = !empty($_GET['item_id']) ? intval($_GET['item_id']) : false;

        $url = "index.php?app=market&ctl=admin_weixin&act=redemption_manage";
        $this->begin($url);

        /*$mod_log = app::get('market')->model("wx_points_buylog");
        $lotterylog = $mod_log->getList('*',array('buy_id' => $id));*/
        $exchange_orders = app::get('ecorder')->model("exchange_orders");
        $lotterylog = $exchange_orders->getList('*',array('buy_id' => $id));
        $list = array();

        $table_column = array(
                    'create_time'   => '参与时间',
                    'people_name'   => '参与人',
                    'phone'         => '手机号',
                    'people_adr'         => '收货地址',
                    'minus_score'   => '扣除积分',
                    'goods_code' => '商品编码',
                    'goods_name'  => '商品名称',
                    'buy_num'  => '兑换数量'
                    );

        foreach($lotterylog as $log)
        {
            $list[] = array(
                        'create_time'   => date('Y-m-d',$log['create_time']),
                        'people_name'   => $log['receiver'],
                        'phone'         => $log['mobile'],
                        'minus_score'   => $log['minus_score'],
                        'people_adr'   => $log['addr'],
                        'goods_code' => $log['goods_code'],
                        'goods_name'  => $log['goods_name'],
                        'buy_num'  => $log['buy_num']
                    );
        }

        $this->_export_data($table_column,$list);
    }
    public function get_shop_gift_id(){
        if($_POST){
            $objShopGift = app::get('ecorder')->model('shop_gift');
            $giftData = $objShopGift->dump(array('id'=>intval($_POST['gift_id'])));
            //可兑换总量
            $giftData['change_num'] = $giftData['gift_num'] - $giftData['send_num'];
        }
        echo json_encode($giftData);exit;
    }
}

