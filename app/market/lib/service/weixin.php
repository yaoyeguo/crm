<?php

class market_service_weixin {

    const BAIDU_MAP_API = 'http://api.map.baidu.com';
    const BAIDU_MAP_AK = '59UG35QsCx2xWDX7rGE0It09';
    const BAIDU_MAP_SK = 'kL8916MNwlFhuMzhOAEClLxF13fAVo8T';
    
    //微信接口地址
    public function get_wx_openapi()
    {
        return kernel::openapi_url('openapi.weixin','valid',array('u'=>$this->encrypt(1,"E","qwertyuiop")));
    }

    public function encrypt($string,$operation,$key='')
    {
        if($operation=="D"){
            $string=base64_decode($string);
        }

        $key=md5($key);
        $key_length=strlen($key);
        $string=$operation=='D'?base64_decode($string):substr(md5($string.$key),0,8).$string;
        $string_length=strlen($string);
        $rndkey=$box=array();
        $result='';

        for($i=0;$i<=255;$i++){
            $rndkey[$i]=ord($key[$i%$key_length]);
            $box[$i]=$i;
        }

        for($j=$i=0;$i<256;$i++) {
            $j=($j+$box[$i]+$rndkey[$i])%256;
            $tmp=$box[$i];
            $box[$i]=$box[$j];
            $box[$j]=$tmp;
        }

        for($a=$j=$i=0;$i<$string_length;$i++){
            $a=($a+1)%256;
            $j=($j+$box[$a])%256;
            $tmp=$box[$a];
            $box[$a]=$box[$j];
            $box[$j]=$tmp;
            $result.=chr(ord($string[$i])^($box[($box[$a]+$box[$j])%256]));
        }

        if($operation=='D'){
            if(substr($result,0,8)==substr(md5(substr($result,8).$key),0,8)){
                return substr($result,8);
            }
            else{
                return'';
            }
        }
        else{
            return base64_encode(str_replace('=','',base64_encode($result)));
        }
    }

    public function valid()
    {
        //检查微信令牌
        if(isset($_GET["echostr"])){
            if($this->checkSignature()){
                echo  $_GET["echostr"];
                exit;
            }
        }

        base_kvstore::instance('market')->fetch('wxuser', $wxuser);
        $wxobj = json_decode($wxuser,true);
        
        $this->wxcuruser =$wxobj;
        $this->responseMsg();

        /*
        if($wxobj!=null){
            if(!empty($wxobj["state"])){
                $this->wxcuruser =$wxobj;
                $this->responseMsg();
            }else{
                //检查微信令牌
                if($this->checkSignature()){
                    echo  $_GET["echostr"];
                    exit;
                }
            }
        }
        */
    }

    private function checkSignature()
    {
        base_kvstore::instance('market')->fetch('wxuser', $wxuser);
        $wxobj = json_decode($wxuser,true);

        if($wxobj!=null){
            $signature = $_GET["signature"];
            $timestamp = $_GET["timestamp"];
            $nonce = $_GET["nonce"];
            $token = $wxobj["token"];
            $tmpArr = array($token, $timestamp, $nonce);
            sort($tmpArr);
            $tmpStr = implode( $tmpArr );
            $tmpStr = sha1( $tmpStr );

            if($tmpStr == $signature ){
                $wxobj["state"]=1;
                base_kvstore::instance('market')->store('wxuser', json_encode($wxobj));  //更新状态

                return true;
            }
        }

        return false;
    }
    
    protected function getRequest($param = FALSE) {
        if ($param === FALSE) {
            return $this->request;
        }

        $param = strtolower($param);

        if (isset($this->request[$param])) {
           return $this->request[$param];
        }

        return NULL;
    }
    
    public function getWxUserInfo($params, $wx_member_id)
    {
        $result = kernel::single('ecorder_rpc_request_weixin_user')->user_info($params);
        $result = json_decode($result,true);
        if($result && isset($result['openid'])){
            if($result['subscribe'] == 0){
                $update_result['subscribe']++;
                continue;
            }

            $data = array('wx_member_id'=>$wx_member_id,'wx_nick'=>urlencode($result['nickname']),'sex'=>($result['sex']==1 ? 'male' : 'female'),'city'=>$result['city'],'province'=>$result['province'],'country'=>$result['country'],'update_time'=>time());

            $objWxMember->save($data);
            $update_result['updated']++;
        }else{
            $update_result['failed']++;
        }
    }

    public function getWxUser($page=1,$totalUpdateNums=100,& $msg)
    {
        $update_result = array('total'=>0,'updated'=>0,'subscribe'=>0,'failed'=>0);
        $objWxMember = app::get('market')->model('wx_member');
        $rows = $objWxMember->getNeedUpdateWxUserList($page,$totalUpdateNums);
        if(!$rows){
            return false;
        }
        
        //1.矩阵的微信接口
        $wechat_shops = app::get('ecorder')->model('shop')->get_shops('wechat');
        if($wechat_shops){
            $wechat_shops = array_values($wechat_shops);
            $params['node_id'] = $wechat_shops[0]['node_id'];
            $update_result['total'] = count($rows);
            foreach($rows as $row){
                $params['openid'] = $row['FromUserName'];
                $result = kernel::single('ecorder_rpc_request_weixin_user')->user_info($params);
                $result = json_decode($result,true);
                if($result && isset($result['openid'])){
                    if($result['subscribe'] == 0){
                        $update_result['subscribe']++;
                        continue;
                    }

                    $data = array('wx_member_id'=>$row['wx_member_id'],'wx_nick'=>urlencode($result['nickname']),'sex'=>($result['sex']==1 ? 'male' : 'female'),'city'=>$result['city'],'province'=>$result['province'],'country'=>$result['country'],'update_time'=>time());

                    $objWxMember->save($data);
                    $update_result['updated']++;
                }else{
                    $update_result['failed']++;
                }
            }

            return $update_result;
        }
        
        //2.直连微信接口
        base_kvstore::instance('market')->fetch('wxuser', $wxuser);
        $wxuser = json_decode($wxuser,true);
        $http = new base_httpclient;

        if(!isset($wxuser['appid']) || !isset($wxuser['secret'])){
            $msg = '缺少appid或者secret';
            return false;
        }

        //获取token凭证
        if(!isset($wxuser['access_token']) ||  ($wxuser['get_access_token_time'] + $wxuser['expires_in']) <= time()){
            $request_token_url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$wxuser['appid'].'&secret='.$wxuser['secret'];
            $result = $http->post($request_token_url,array());
            $result = json_decode($result,true);
            if($result && isset($result['access_token'])){
                $wxuser['access_token'] = $result['access_token'];
                $wxuser['expires_in'] = $result['expires_in'];
                $wxuser['get_access_token_time'] = time();
                base_kvstore::instance('market')->store('wxuser', json_encode($wxuser));
            }else{
                $msg = '请求授权接口失败';
                return false;
            }
        }

        if($rows){
            $update_result['total'] = count($rows);
            foreach($rows as $row){
                $request_user_url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$wxuser['access_token'].'&openid='.$row['FromUserName'].'&lang=zh_CN';
                $result = $http->post($request_user_url,array());
                $result = json_decode($result,true);
                if($result && isset($result['openid'])){
                    if($result['subscribe'] == 0){
                        $update_result['subscribe']++;
                        continue;
                    }

                    $data = array('wx_member_id'=>$row['wx_member_id'],'wx_nick'=>urlencode($result['nickname']),'sex'=>($result['sex']==1 ? 'male' : 'female'),'city'=>$result['city'],'province'=>$result['province'],'country'=>$result['country'],'update_time'=>time());

                    $objWxMember->save($data);
                    $update_result['updated']++;
                }else{
                    /*$msg = '请求会员详情接口失败';
                     if($result){
                     $msg .= json_encode($result);
                     }*/
                    $update_result['failed']++;
                }
            }

            return $update_result;
        }
    }

    public function getWxUserList(& $msg)
    {
        $objWxMember = app::get('market')->model('wx_member');
        
        //1.矩阵的微信接口
        $wechat_shops = app::get('ecorder')->model('shop')->get_shops('wechat');
        if($wechat_shops){
            $wechat_shops = array_values($wechat_shops);
            $params['node_id'] = $wechat_shops[0]['node_id'];
            $params['next_openid'] = '';
            
            while(true){
                $res = kernel::single('ecorder_rpc_request_weixin_user')->user_list($params);
                $result = json_decode($res,true);
                if($result && isset($result['total']) && intval($result['total']) > 0){
                    foreach($result['data']['openid'] as $k=>$openid){
                        $row = $objWxMember->db->selectrow('select wx_member_id from sdb_market_wx_member where FromUserName="'.$openid.'"');
                        if(!$row){
                            $data = array('FromUserName'=>$openid,'update_time'=>time());
                            $data['create_time'] = time();
                            $data['weixin_token'] = $wxuser['token'];
                            $objWxMember->save($data);
                        }
                    }

                    if($result['total']>10000 && !empty($result['next_openid'])){
                        $params['next_openid'] = $result['next_openid'];
                    }else{
                        return true;
                    }
                }else{
                    $msg = '请求关注用户接口失败';
                    if($result['errcode'] == '48001'){
                        $msg.= '(api功能未授权)';
                    }else{
                        $msg.= '('.$res.')';
                    }
                    return false;
                }
            }
            exit;
        }
    
        //2.直连微信接口
        base_kvstore::instance('market')->fetch('wxuser', $wxuser);
        $wxuser = json_decode($wxuser,true);
        $http = new base_httpclient;

        //获取token凭证,
        if(!isset($wxuser['appid']) || !isset($wxuser['secret'])){
            $msg = '缺少appid或者secret';
            return false;
        }

        //if(!isset($wxuser['access_token']) ||  ($wxuser['get_access_token_time'] + $wxuser['expires_in']) <= time()){
        $request_token_url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$wxuser['appid'].'&secret='.$wxuser['secret'];
        $result = $http->post($request_token_url,array());
        //var_dump($result);exit;
        $result = json_decode($result,true);
        if($result && isset($result['access_token'])){
            $wxuser['access_token'] = $result['access_token'];
            $wxuser['expires_in'] = $result['expires_in'];
            $wxuser['get_access_token_time'] = time();
            base_kvstore::instance('market')->store('wxuser', json_encode($wxuser));
        }else{
            $msg = '请求授权接口失败';
            return false;
        }
        //}

        while(true){
            if($next_openid){
                $request_user_url = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token='.$wxuser['access_token'].'&openid='.$openId.'&lang=zh_CN&next_openid='.$next_openid;
            }else{
                $request_user_url = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token='.$wxuser['access_token'].'&openid='.$openId.'&lang=zh_CN';
            }

            $jsonResult = $http->post($request_user_url,array());
            //var_dump($result);exit;
            $result = json_decode($jsonResult,true);
            if($result && isset($result['total']) && intval($result['total']) > 0){
                foreach($result['data']['openid'] as $k=>$openid){
                    $row = $objWxMember->db->selectrow('select wx_member_id from sdb_market_wx_member where FromUserName="'.$openid.'"');
                    if(!$row){
                        $data = array('FromUserName'=>$openid,'update_time'=>time());
                        $data['create_time'] = time();
                        $data['weixin_token'] = $wxuser['token'];
                        $objWxMember->save($data);
                    }
                }

                if(!empty($result['next_openid'])){
                    $next_openid = $result['next_openid'];
                }else{
                    return true;
                }
            }else{
                $msg = '请求关注用户接口失败';
                if($result['errcode'] == '48001'){
                    $msg.= '(api功能未授权)';
                }else{
                    $msg.= '('.$jsonResult.')';
                }
                return false;
            }
        }

        return true;
    }

    public function responseMsg() {
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

        //extract post data
        if (!empty($postStr)){

            //error_log(var_export($postStr,true)."\n",3,DATA_DIR.'/sy.txt');

            $postObj = (array)simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);

            /*$logmodel=$this->app->model("log");

            $arr=array(
            "logcontent"=>json_encode($postObj)
            );

            $logmodel->save($arr);*/

            $this->request = array_change_key_case($postObj, CASE_LOWER);

            if($this->wxcuruser!=null){

                //保存微信客户
                $db = kernel::database();
                $data = array('FromUserName'=>$this->getRequest('fromusername'),'ToUserName'=>$this->getRequest('tousername'),'update_time'=>time());
                $row = $db->selectrow('select wx_member_id from sdb_market_wx_member where FromUserName="'.$this->getRequest('fromusername').'"');
                if($row){
                    $data['wx_member_id'] = $row['wx_member_id'];
                }else{
                    $data['create_time'] = time();
                }
                $objWxMember = app::get('market')->model('wx_member');
                $objWxMember->save($data);
                $this->wx_member_id = $data['wx_member_id'];
            }

            switch ($this->getRequest('msgtype')) {
                case 'event':
                    switch ($this->getRequest('event')) {
                        case 'subscribe':
                            $this->onSubscribe();
                            break;
                        case 'unsubscribe':
                            $this->onUnsubscribe();
                            break;
                        case 'CLICK':
                            $this->onClick();
                            break;
                        case 'LOCATION':
                            $this->storeLOCATION();
                            break;
                    }
                    break;
                case 'text':
                    $this->onText();
                    break;
                case 'image':
                    $this->onImage();
                    break;
                case 'location':
                    $this->onLocation();
                    break;
                case 'link':
                    $this->onLink();
                    break;
                default:
                    $this->onUnknown();
                    break;
            }
        }
    }
    
    //保存用户的地理位置信息到KV
    public function storeLOCATION()
    {
        $fromusername = $this->getRequest('fromusername');
        $location_x = $this->getRequest('latitude');
        $location_y = $this->getRequest('longitude');
        
        if($location_x && $location_y){
            base_kvstore::instance('weixin')->store($fromusername.'_location', "$location_x,$location_y");
        }
    }
    
    //获取用户的地理位置信息
    public function getLOCATION()
    {
        $location = '';
        $fromusername = $this->getRequest('fromusername');
        if($fromusername){
            base_kvstore::instance('weixin')->fetch($fromusername.'_location', $location);
        }
        return $location;
    }

    /**
     * 用户关注时触发
     *
     * @return void
     */
    protected function onSubscribe() {
        // $this->responseText('欢迎关注');
        $objWxEvent=app::get('market')->model("wx_event");
        $data = array('FromUserName'=>$this->getRequest('fromusername'),'ToUserName'=>$this->getRequest('tousername'),'event_type'=>'subscribe','create_time'=>time());
        $objWxEvent->save($data);

        if($this->wxcuruser!=null){
            $this->responseText(urldecode($this->wxcuruser["welcomeword"]));
        }
    }

    /**
     * 用户取消关注时触发
     *
     * @return void
     */
    protected function onUnsubscribe() {
        $objWxEvent=app::get('market')->model("wx_event");
        $data = array('FromUserName'=>$this->getRequest('fromusername'),'ToUserName'=>$this->getRequest('tousername'),'event_type'=>'unsubscribe','create_time'=>time());
        $objWxEvent->save($data);
        // $this->responseText('取消关注');
    }

    /**
     * 添加微信用户参与日志
     *
     * @return void
     */
    function addJoinLog($data){
        return true;
        $stime = strtotime(date('Y-m-d 00:00:00'));
        $etime = strtotime('+1 day',$stime);
        $db = kernel::database();
        $row = $db->selectrow('select log_id from sdb_market_wx_join_log where created >='.$stime .' and created<='.$etime .' and wx_member_id='.$this->wx_member_id);
        if($row){
            $data['log_id'] = $row['log_id'];
            $data['wx_member_id'] = $this->wx_member_id;
        }else{
            $data['wx_member_id'] = $this->wx_member_id;
            $data['FromUserName'] = $this->getRequest('fromusername');
            $data['ToUserName'] = $this->getRequest('tousername');
            $data['created'] = time();
        }
        $objWxJoinLog = app::get('market')->model('wx_join_log');
        $objWxJoinLog->save($data);
    }

    //先去总表查，然后根据类型进行响应
    function processKeyWord($searchkey){
       //签到积分 1 任意回复内容都增加积分  2 必须回复关键词才有积分，如果前面有问答，则问答优先。
        base_kvstore::instance('market')->fetch('wxuser', $wxuser);
        $wxuser = json_decode($wxuser,true);
        /* if(isset($wxuser['registRule'])){
            if($wxuser['registRule']['regist_rule'] == 1){
                $wxuser['wx_member_id'] = $this->wx_member_id;
                $point = $this->processRegistPoint($wxuser);
            }
        }*/

        //检查当前微信用户有没有问答
        $objSurveyLog = app::get('market')->model('wx_survey_log');
        $survey_log_id = $objSurveyLog->checkSurvey($this->getRequest('fromusername'));
        if($survey_log_id){
            $txtSurvey = $objSurveyLog->continueSurvey($survey_log_id,$searchkey);
            if($txtSurvey){
                $this->responseText($txtSurvey);
            }else{
                $objSurveyLog->closeSurveyBySys($survey_id);
                $this->responseText('没有找到问答活动题目');
            }
        }

        $prefixKey = substr($searchkey, 0,3);
        $objWxKeyWords = app::get('market')->model('wx_keywords');
        $enumPrefixKey = $objWxKeyWords->getPluginsKey();
        if(in_array($prefixKey, $enumPrefixKey)){
            $this->addJoinLog(array('is_keyword'=>1));
            $this->processKeyWordByPlugin($searchkey,$prefixKey);
        }else{

            //系统内置关键字
            $systemKeyWord = array('会员中心');
            if(in_array($searchkey, $systemKeyWord)){
                $objWxMember=app::get('market')->model("wx_member");
                $wxMember_data = $objWxMember->dump(array('FromUserName'=>$this->getRequest('fromusername')));
                if(!empty($wxMember_data['member_id'])){
                    //$msg = kernel::base_url(1).'/index.php/market/site_weixin_member/member_center?fromusername='.$this->getRequest('fromusername');
                    $link = kernel::base_url(1).'/index.php/market/site_weixin_member/member_center?fromusername='.$this->getRequest('fromusername').'&my_xy='.$this->getLOCATION();
                    $picurl = kernel::base_url(1).'/app/market/statics/img/center.jpg';
                }else{
                    //$msg = kernel::base_url(1).'/index.php/market/site_weixin_member/index?fromusername='.$this->getRequest('fromusername');
                    $link = kernel::base_url(1).'/index.php/market/site_weixin_member/index?fromusername='.$this->getRequest('fromusername');
                    $picurl = kernel::base_url(1).'/app/market/statics/img/bind.jpg';

                }
                //$this->responseText($msg);
                $items[] = new NewsResponseItem('','',$picurl,$link);
                $this->responseNews($items);
            }

            $item = $objWxKeyWords->getRowByKeyWord($searchkey);
            if($item){
                switch ($item['source']){
                    case 'products':
                        $this->addJoinLog(array('is_keyword'=>1));
                        $this->processKeyWordByProduct($searchkey);
                        break;
                    case 'reply':
                        $this->addJoinLog(array('is_keyword'=>1));
                        $this->processKeyWordByReply($searchkey);
                        break;

                    case 'survey':
                        $this->addJoinLog(array('is_survey'=>1));
                        $this->processKeyWordBySurvey($searchkey);
                        break;
                        
                    case 'regist':
                        $wxuser['wx_member_id'] = $this->wx_member_id;
                        $point = $this->processRegistPoint($wxuser);//返回值为0表示今天已签到过，其他今天没有签到过
                        if($point > 0){
                            $db = kernel::database();
                            $row = $db->selectrow('select tb_nick,mobile,points,member_id from sdb_market_wx_member where FromUserName="'.$this->getRequest('fromusername').'"');
                            //微信会员表中member_id不为空，取全局会员积分；为空去微信会员表积分
                            if(empty($row['member_id'])){
                                //$wxPoint = intval($row['points']);
                                //没有绑定，直接跳到绑定页面
                                $link = kernel::base_url(1).'/index.php/market/site_weixin_member/index?fromusername='.$this->getRequest('fromusername');
                                $picurl = kernel::base_url(1).'/app/market/statics/img/bind.jpg';
                                $items[] = new NewsResponseItem('','',$picurl,$link);
                                $this->responseNews($items);
                                break;
                            }else{
                                //写入签到日志
                                $wxSignInLogObj = app::get('market')->model('wx_sign_in_log');
                                $wxSignInLogObj->saveSignInLog(array('fromusername'=>$this->getRequest('fromusername'),'member_id'=>$row['member_id'],'create_time'=>time()));

                                $pointObj=kernel::single("taocrm_member_point");
                                $msg = '';
                                $sum_points = $pointObj->get($row['member_id'],$msg,'',time());
                                $wxPoint = $sum_points['total_point'];
                            }
                            if(!isset($wxuser['registRule']['replyFinishTxt'])){
                                $msg = '签到成功!您此次签到获得积分:<{积分}>,总积分:<{总积分}>';
                            }else{
                                $msg = $wxuser['registRule']['replyFinishTxt'];
                            }

                            $msg = str_replace(array('<{积分}>','<{总积分}>'), array($point,$wxPoint), $msg);
                        }else{
                            $msg = '您今天已经签到过了！';
                        }


                        $this->responseText($msg);

                        //$this->responseText(sprintf('签到成功!您此次签到获得积分:%s,总积分:%s',$point,$wxPoint));
                        break;
                        
                    case 'vote':
                        $this->addJoinLog(array('is_vote'=>1));
                        $this->processKeyWordByVote($searchkey);
                        break;

                    case 'due':
                        $this->addJoinLog(array('is_due'=>1));
                        $this->processKeyWordByDue($searchkey);
                        break;
                        /* case 'system':

                        break;
                        case 'plugin':
                        $this->processKeyWordByPlugin();
                        break;*/
                }
            }
        }

        $this->addJoinLog(array('is_chat'=>1));

        //更新本次聊天记录为未响应
        $objWxChat = app::get('market')->model("wx_chat");
        $chat = array('chat_id'=>$this->chat_id,'is_response'=>0);
        $objWxChat->save($chat);
        $responseText = trim($wxuser["autoreplyword"]);
        if(empty($responseText)){
            //$this->responseText('');exit;
            echo('');
            exit;
            //参考文档：http://mp.weixin.qq.com/wiki/index.php?title=%E5%8F%91%E9%80%81%E8%A2%AB%E5%8A%A8%E5%93%8D%E5%BA%94%E6%B6%88%E6%81%AF
        }
        $this->responseText(urldecode($responseText));
    }

    //处理投票信息
    function processKeyWordByVote($searchkey){

        $fromusername = $this->getRequest('fromusername');

        $oVoteResult = app::get('market')->model("wx_vote_result");
        $result = $oVoteResult->dump(array('wx_id'=>$fromusername));
        if($result){
            $this->responseText('您已经参与了投票~');
        }else{
            $oVote = app::get('market')->model("wx_vote");

            $rs = $oVote->dump(array('keywords'=>$searchkey,'is_active'=>1));

            if($rs){
                $link = kernel::base_url(1).'/index.php/market/site_weixin_vote/index/?id='.$rs['vote_id'].'&wx_id='.$fromusername;
                $items[] = new NewsResponseItem($rs['title'],$rs['desc'],$rs['picurl'],$link);

                $this->responseNews($items);
            }
        }
    }

    //处理预约信息
    function processKeyWordByDue($searchkey){

        $fromusername = $this->getRequest('fromusername');

        $oVote = app::get('market')->model("wx_due");

        $rs = $oVote->dump(array('keywords'=>$searchkey,'is_active'=>1));

        if($rs){
            $link = kernel::base_url(1).'/index.php/market/site_weixin_due/index/?id='.$rs['due_id'].'&wx_id='.$fromusername;
            $rs['picurl'] = base_storager::image_path($rs['picurl'],'s' );
            $items[] = new NewsResponseItem($rs['title'],$rs['desc'],$rs['picurl'],$link);

            $this->responseNews($items);
        }
    }

    function processRegistPoint($wxuser){
        $objWxMember = app::get('market')->model('wx_member');
        $registRule = $wxuser['registRule'];
        $point = 0;
        $wx_member_id = $wxuser['wx_member_id'];
        $wxMember = $objWxMember->get($wx_member_id);
        if(isset($registRule['regist_point_rule']) && $objWxMember->checkRegistPoint_new($wxMember['FromUserName'],date('Y-m-d'))){
           // $objWxMember->toRegist($wx_member_id);//更新签到次数
            $wxSignInLogObj = app::get('market')->model('wx_sign_in_log');
            $wxSignInLogData = $wxSignInLogObj->dump(array('FromUserName'=>$wxMember['FromUserName']));
            $registCount = $wxSignInLogData['sign_in_times'];

            $msg = '';

            //签到积分规则
            if($registRule['regist_point_rule'] == 1){
                $point = $registRule['regist_point_rule_1_point'];
            }else if($registRule['regist_point_rule'] == 2){
                //$registCount = $registCount - 1;
                if(  $registCount > 1 && $registCount % $registRule['regist_point_rule_2_times'] == 0){
                    $point = $registRule['regist_point_rule_2_times_point'];
                }else{
                    $point = $registRule['regist_point_rule_2_point'];
                }
            }else{
                //$registCount = $registCount - 1;
                if( $registCount > 1 && $registCount >= $registRule['regist_point_rule_3_times']){
                    $point = $registRule['regist_point_rule_3_times_point'];
                }else{
                    $point = $registRule['regist_point_rule_3_point'];
                    if( $registCount > 1){
                        $point += ($registCount-1) * $registRule['regist_point_rule_3_go_point'];
                    }
                }
            }

            if($point > 0){
                //微信会员表中，有member_id（即手机绑定过）,此时送积分更新全局积分明细表；member_id为空（即未手机未绑定）,此时更新微信会员表
                if($wxMember['member_id']){
                    $id = kernel::single('taocrm_member_point')->update('',$wxMember['member_id'],2,$point,'微信签到送积分',$msg,null,'wechat');
                }else{
                    $id = $objWxMember->updatePoint($wx_member_id,2,$point,'签到积分',$msg);
                }

                if(!$id){
                    $this->responseText($msg);
                }

                //全渠道积分
                if($wxMember['mobile']){
                    //固化微信帐号和CRM客户的关联
                    if( ! $wxMember['member_id']){
                        $member = app::get('taocrm')->model('members')->get_member_info(array('mobile'=>$wxMember['mobile'], 'parent_member_id'=>0), 'member_id');
                        if($member['member_id']){
                            $wxMember['member_id'] = $member['member_id'];
                            $objWxMember->update(array('member_id'=>$wxMember['member_id']), array('wx_member_id'=>$wx_member_id));
                        }
                    }
                }
            }
        }else{
            
        }

        $this->addJoinLog(array('is_regist'=>1));

        return $point;
    }

    function processKeyWordBySurvey($searchkey){
        $objSurveyLog = app::get('market')->model('wx_survey_log');
        $survey = $objSurveyLog->getSurveyByKey($searchkey);
        if($survey){
            $survey_id = $objSurveyLog->createSurvey($this->getRequest('fromusername'),$survey);
            $txtSurvey = $objSurveyLog->getStartSurvey();
            if($txtSurvey){
                $this->responseText($txtSurvey);
            }else{
                $objSurveyLog->closeSurvey($survey_id);
                $this->responseText('没有找到问答活动题目');
            }
        }else{
            $this->responseText('没有找到问答活动');
        }
    }

    function processKeyWordByPlugin($searchkey,$prefixKey){
        $objPlugins = app::get('market')->model('wx_plugins');
        if($objPlugins->isOpen($prefixKey)){
            $db = kernel::database();
            switch ($prefixKey){
                /*case 'tb#':
                 $objWxMember=app::get('market')->model("wx_member");
                 $nick = substr($searchkey, 3);
                 if(!empty($nick)){
                 $data = array('ToUserName'=>$this->getRequest('tousername'),'tb_nick'=>$nick,'update_time'=>time());
                 $row = $db->selectrow('select wx_member_id from sdb_market_wx_member where ToUserName="'.$this->getRequest('tousername').'"');
                 if($row){
                 $data['wx_member_id'] = $row['wx_member_id'];
                 }else{
                 $data['create_time'] = time();
                 }
                 $objWxMember->save($data);
                 $this->responseText('绑定淘宝账号成功');
                 }else{
                 $this->responseText('淘宝账号为空');
                 }
                 break;*/
                case 'sj#':
                    $objWxMember=app::get('market')->model("wx_member");
                    $mobile = trim(substr($searchkey, 3));
                    $mobile = str_replace(array("\r","\n"), '', $mobile);
                    if(strlen($mobile) != 11){
                        $this->responseText('手机号不是11位');
                    }

                    if(!empty($mobile)){
                        $data = array('wx_member_id'=>$this->wx_member_id,'mobile'=>$mobile);
                        //会员识别
                        $members = app::get('taocrm')->model("members");
                        $members_data = $members->dump(array('mobile'=>$mobile));
                        if($members_data){
                            $data['member_id'] = $members_data['member_id'];
                        }else{
                            //如果此手机号在全局会员表中不存在记录，用此手机号新创建一条记录
                            $membersObj = kernel::single("taocrm_members");
                            $msg = '';
                            $sdf = array('mobile'=>$mobile);
                            $memberId = $membersObj->add($sdf,$msg);
                            $data['member_id'] = $memberId['member_id'];
                        }
                        $objWxMember->save($data);
                        //自动发给微信会员一张会员卡
                        $this->autoMemberCard($data['member_id']);
                        $this->responseText('绑定手机号成功');
                    }else{
                        $this->responseText('手机号为空');
                    }
                    break;
                    
                case 'jf#':
                    $row = $db->selectrow('select tb_nick,mobile,points from sdb_market_wx_member where FromUserName="'.$this->getRequest('fromusername').'"');
                    if($row){
                        $memberId = 0;
                        if($row['tb_nick']){
                            $member = $db->selectrow('select member_id from sdb_taocrm_members where uname="'.$row['tb_nick'].'"');
                            if($member){
                                $memberId = $member['member_id'];
                            }
                        }

                        if($row['mobile']){
                            $member = $db->selectrow('select member_id from sdb_taocrm_members where mobile="'.$row['mobile'].'"');
                            if($member){
                                $memberId = $member['member_id'];
                            }
                        }

                        if($row['mobile']){
                            $point = $db->selectrow('select sum(points) as points from sdb_taocrm_members force index (ind_mobile) where mobile="'.$row['mobile'].'" and parent_member_id=0 ');
                            $points = $row['points'] + floatval($point['points']);
                            $txt = sprintf('尊贵的会员您好，您的积分为：[%s]'."\n", $points);
                            $this->responseText($txt);
                            /*
                            $pointObj=kernel::single("taocrm_member_point");
                            $msg = '';
                            $memberPointList = $pointObj->get($memberId,$msg);
                            if($memberPointList){
                                $txt = '';
                                foreach($memberPointList as $point){
                                    $shop = $db->selectrow('select name from sdb_ecorder_shop where shop_id="'.$point['shop_id'].'"');
                                    $txt .= sprintf('尊贵的会员您好，您在【%s】的积分有：[%s]'."\n",$shop['name'],$point['points']);
                                }

                                $this->responseText($txt);
                            }else{
                                $this->responseText($msg);
                            }
                            */
                        }else{
                            $this->responseText('非常抱歉，没有找到您的信息，请重新绑定。发送【sj#】绑定手机号，例如：sj#139XXXXXXX');
                        }
                    }else{
                        $this->responseText('非常抱歉，没有找到您的信息，请重新绑定。发送【sj#】绑定手机号，例如：sj#139XXXXXXX');
                    }
                    break;

                case 'wl#':
                    $row = $db->selectrow('select tb_nick,mobile from sdb_market_wx_member where FromUserName="'.$this->getRequest('fromusername').'"');
                    if($row && !empty($row['mobile'])){

                        $orders = $db->select('select logi_no,transit_step_info from sdb_plugins_trades where ship_mobile='.$row['mobile'].' and order_status="active"');
                        if($orders){
                            $msg = '';
                            foreach($orders as $order){
                                $msg .= sprintf('物流单号:[%s]',$order['logi_no']);
                                if(!empty($order['transit_step_info'])){
                                    $transit_step_info = json_decode($order['transit_step_info'],true);
                                    $txt = '';
                                    if(isset($transit_step_info['status_desc'])){
                                        $txt = $transit_step_info['status_desc'];
                                    }else{
                                        foreach($transit_step_info as $info){
                                            $txt .= $info['status_desc']."\n";
                                        }
                                    }
                                    $msg .= sprintf('中转信息:[%s]',$txt);
                                }else{
                                    $msg .= sprintf('中转信息:[%s]','该订单无流转信息');
                                }
                            }
                            $this->responseText($msg);
                        }else{
                            $this->responseText('您最近没有发货订单哦');
                        }
                    }else{
                        $this->responseText('非常抱歉，没有找到您的信息，请重新绑定。发送【sj#】绑定手机号，例如：sj#139XXXXXXX');
                    }

                    break;
            }
        }
    }

    function processKeyWordByProduct($searchkey){
        $rulemodel=app::get('market')->model("wxautoreply");

        $rulelist=$rulemodel->getList("*");

        if($rulelist!=null){

            foreach ($rulelist as $key => $value) {

                $reply=json_decode($value["replycontent"]);

                if(in_array($searchkey, $reply->keyword)){

                    $items = array();

                    array_push($items,new NewsResponseItem($reply->item_top->title,$reply->item_top->title,$reply->item_top->media->s,$reply->item_top->link));

                    foreach ($reply->items as $itemkey => $itemvalue) {
                        array_push($items,new NewsResponseItem($itemvalue->title,$itemvalue->title,$itemvalue->media->s,$itemvalue->link));
                    }

                    $this->responseNews($items);

                    exit;
                }
            }
        }
    }

    function processKeyWordByReply($searchkey){
        $rulemodel=app::get('market')->model("wx_keywords_autoreply");

        $rulelist=$rulemodel->getList("*");

        if($rulelist!=null){

            foreach ($rulelist as $key => $value) {

                $keyword = json_decode($value['keyword'],true);
                if(in_array($searchkey, $keyword)){
                    if($value['reply_type'] == 'msg'){
                        $this->responseText($value['replycontent']);
                    }else if($value['reply_type'] == 'news'){
                        $this->replyNews($value['wx_news_id']);
                    }
                    exit;
                }
            }
        }
    }

    //在url里增加微信openid
    public function addOpenID($url)
    {
        !stristr($url,'wx_id') && (!stristr($url,'?') ? $url.='?wx_id='.$this->getRequest('fromusername') : $url.='&wx_id='.$this->getRequest('fromusername'));
        return $url;
    }

    private function replyNews($wx_news_id){
        $modelWxNews=app::get('market')->model("wx_news");
        $news = $modelWxNews->dump($wx_news_id);
        $news_info = json_decode($news['news_info'],true);
        if($news_info){
            $items = array();
            if($news['type'] == 1){
                $item = $news_info;
                $link = '';
                if($item['link_type'] == 'url'){
                    $link = $this->addOpenID($item['link_type_url']);
                }else{
                    $link = kernel::base_url(1).'/index.php/market/site_weixin_news/index/?id='.$wx_news_id;
                }
                $item['picurl'] = base_storager::image_path($item['picurl'],'s' );
                $items[] = new NewsResponseItem($item['title'],$item['digest'],$item['picurl'],$link);
            }else{
                foreach($news_info as $k=>$item){

                    $link = '';
                    if($item['link_type'] == 'url'){
                        $link = $this->addOpenID($item['link_type_url']);
                    }else{
                        $link = kernel::base_url(1).'/index.php/market/site_weixin_news/index/?id='.$wx_news_id.'&i='.$k;
                    }

                    $item['picurl'] = base_storager::image_path($item['picurl'],'s' );
                    $items[] = new NewsResponseItem($item['title'],$item['digest'],$item['picurl'],$link);
                }
            }

            $this->responseNews($items);
        }
    }

    public function onClick()
    {
        $this->processKeyWord($this->getRequest('eventkey'));
    }

    public function onText() {
        // $this->responseText('收到了文字消息：' . $this->getRequest('content'));
        $searchkey= str_replace(" ", "", $this->getRequest('content'));

        if($this->wxcuruser!=null){

            //保存聊天记录
            $objWxChat = app::get('market')->model("wx_chat");
            $chat = array('wx_member_id'=>$this->wx_member_id,'FromUserName'=>$this->getRequest('fromusername'),'ToUserName'=>$this->getRequest('tousername'),'chat_content'=>$searchkey,'created'=>date('Y-m-d H:i:s'));
            $objWxChat->save($chat);
            $this->chat_id = $chat['chat_id'];

            $this->processKeyWord($searchkey);
        }
    }

    /**
     * 收到图片消息时触发
     *
     * @return void
     */
    protected function onImage() {
        /* $items = array(
         new NewsResponseItem('标题一', '描述一', $this->getRequest('picurl'), $this->getRequest('picurl')),
         new NewsResponseItem('标题二', '描述二', $this->getRequest('picurl'), $this->getRequest('picurl')),
         );

         $this->responseNews($items);*/
    }

    /**
     * 收到地理位置消息时触发，用于子类重写
     *
     * @return void
     */
    protected function onLocation() {
        $this->get_store_map();
        // $this->responseText('收到了位置消息：' . $this->getRequest('location_x') . ',' . $this->getRequest('location_y'));
    }

    /**
     * 收到链接消息时触发，用于子类重写
     *
     * @return void
     */
    protected function onLink() {
        // $this->responseText('收到了链接：' . $this->getRequest('url'));
    }

    /**
     * 收到未知类型消息时触发，用于子类重写
     *
     * @return void
     */
    protected function onUnknown() {
        // $this->responseText('收到了未知类型消息：' . $this->getRequest('msgtype'));
    }

    /**
     * 回复文本消息
     *
     * @param  string  $content  消息内容
     * @param  integer $funcFlag 默认为0，设为1时星标刚才收到的消息
     * @return void
     */
    protected function responseText($content, $funcFlag = 0) {
        exit(new TextResponse($this->getRequest('fromusername'), $this->getRequest('tousername'), $content, $funcFlag));
    }

    /**
     * 回复音乐消息
     *
     * @param  string  $title       音乐标题
     * @param  string  $description 音乐描述
     * @param  string  $musicUrl    音乐链接
     * @param  string  $hqMusicUrl  高质量音乐链接，Wi-Fi 环境下优先使用
     * @param  integer $funcFlag    默认为0，设为1时星标刚才收到的消息
     * @return void
     */
    protected function responseMusic($title, $description, $musicUrl, $hqMusicUrl, $funcFlag = 0) {
        exit(new MusicResponse($this->getRequest('fromusername'), $this->getRequest('tousername'), $title, $description, $musicUrl, $hqMusicUrl, $funcFlag));
    }

    /**
     * 回复图文消息
     * @param  array   $items    由单条图文消息类型 NewsResponseItem() 组成的数组
     * @param  integer $funcFlag 默认为0，设为1时星标刚才收到的消息
     * @return void
     */
    protected function responseNews($items, $funcFlag = 0) {
        exit(new NewsResponse($this->getRequest('fromusername'), $this->getRequest('tousername'), $items, $funcFlag));
    }

    /**
     * 给用户发送消息
     * @return void
     */
    public function response_chat($fromusername,$tousername,$msg)
    {
        base_kvstore::instance('market')->fetch('wxuser', $wxuser);
        $wxuser = json_decode($wxuser,true);
        $http = new base_httpclient;

        //获取token凭证,
        if(!isset($wxuser['token'])){
            $msg = '缺少token';
            return false;
        }
        $request_token_url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$wxuser['token'];
        $params = array(
                    'touser' => $fromusername,
                    'msgtype' => 'text',
                    'text' => array(
                        'content' => $msg,
        ),
        );
        $result = $http->post($request_token_url,$params);

        $result = json_decode($result,true);
        if($result && isset($result['access_token']))
        {
            $wxuser['access_token'] = $result['access_token'];
            $wxuser['expires_in'] = $result['expires_in'];
            $wxuser['get_access_token_time'] = time();
            base_kvstore::instance('market')->store('wxuser', json_encode($wxuser));
        }else{
            $msg = '请求授权接口失败';
            return false;
        }
    }
    /**
     *求两个已知经纬度之间的距离,单位为米
     *@param lng1,lng2 经度
     *@param lat1,lat2 纬度
     *@return float 距离，单位米
     *@author www.Alixixi.com
     **/
    function getdistance($lng1,$lat1,$lng2,$lat2){
        //将角度转为狐度
        $radLat1=deg2rad($lat1);//deg2rad()函数将角度转换为弧度
        $radLat2=deg2rad($lat2);
        $radLng1=deg2rad($lng1);
        $radLng2=deg2rad($lng2);
        $a=$radLat1-$radLat2;
        $b=$radLng1-$radLng2;
        $s=2*asin(sqrt(pow(sin($a/2),2)+cos($radLat1)*cos($radLat2)*pow(sin($b/2),2)))*6378.137*1000;
        return $s;
    }
    /**
     * 回复离用户近的店铺坐标
     * @return void
     */
    public function get_store_map()
    {
        $mod_obj = app::get('market')->model('wx_store_subbranch');
        //取数据
        $store_list = $mod_obj->getList('store_id,picurl,store_name,map_x,map_y,store_area');
        $map_arr = array();
        $return_num = array();
        foreach($store_list as $store)
        {
            $all_store[$store['store_id']] = $store;
            $dist = $this->getdistance($store['map_y'],$store['map_x'],$this->getRequest('location_x'),$this->getRequest('location_y'));
            $return_num[] = array(
                'dist'=>intval($dist),
                'store_area'=>$store['store_area'],
                'store_name'=>$store['store_name'],
                'store_id'=>$store['store_id'],
            );

        }
        sort($return_num);
        $store_5 = array_slice($return_num,0,3,1);

        $data = array();
        //拼装店铺数据及图文对象
        foreach($store_5 as $map)
        {
            $k = $map['store_id'];
            $picurl = base_storager::image_path($all_store[$k]['picurl'],'s');
            $link = kernel::base_url(1).'/index.php/market/site_weixin_news/store_map/?id='.$all_store[$k]['store_id'].'&my_xy='.$this->getRequest('location_x').','.$this->getRequest('location_y');
            $picurl || $picurl = 'http://api.map.baidu.com/staticimage?width=280&height=190&zoom=15&center='.$all_store[$k]['map_x'].','.$all_store[$k]['map_y'];
            $data[] = new NewsResponseItem($all_store[$k]['store_name'].':'.$map['dist'].'米','',$picurl,$link);
        }
        $this->responseNews($data);
    }

    /**
     * 自动生成会员卡
     * 1、添加会员卡类型；2、添加微信默认模板；3、生成会员卡（按年月为一个批次，如：201506）
     */
    function autoMemberCard($member_id){
        //1、添加会员卡类型
        $memberCardTypeObj = app::get('taocrm')->model('member_card_type');
        $re_type_data = $memberCardTypeObj->dump(array('type_code'=>'wx'));
        if(empty($re_type_data)){
            $typeData = array('type_name'=>'微信','type_code'=>'wx','create_time'=>time(),'update_time'=>time());
            $memberCardTypeObj->save($typeData);

            $re_type_data = $memberCardTypeObj->dump(array('type_code'=>'wx'));
        }

        //2、添加微信默认模板
        $memberCardTypeId = $re_type_data['id'];
        $memberCardTemplateObj = app::get('taocrm')->model('member_card_template');
        $re_template_data = $memberCardTemplateObj->dump(array('card_name'=>'微信会员卡'));
        if(empty($re_template_data)){
            $templateData = array('is_type_code'=>'1','card_name'=>'微信会员卡','member_card_type_id'=>$memberCardTypeId,'card_len'=>6,'card_pwd_len'=>4,'card_pwd_rule'=>'0','card_type'=>'1');
            $memberCardTemplateObj->save($templateData);

            $re_template_data = $memberCardTemplateObj->dump(array('card_name'=>'微信会员卡'));
        }

        //3、生成会员卡（按年月为一个批次，如：201506）
        $memberCardTemplateId = $re_template_data['id'];
        $memberCardObj = app::get('taocrm')->model('member_card');
        $msg = '';
        $re = $memberCardObj->doMakeCard_wx($memberCardTemplateId,1,$member_id,$msg);
        return $re;
    }

    /**
     * baiduAPI sn生成器
     * @return void
     */
    protected function caculateAKSN($ak, $sk, $url, $querystring_arrays, $method = 'GET'){
        if ($method === 'POST'){
            ksort($querystring_arrays);
        }
        $querystring = http_build_query($querystring_arrays);
        return md5(urlencode($url.'?'.$querystring.$sk));
    }
}
/**
 * 用于回复的基本消息类型
 */
abstract class WechatResponse {

    protected $toUserName;
    protected $fromUserName;
    protected $funcFlag;

    public function __construct($toUserName, $fromUserName, $funcFlag) {
        $this->toUserName = $toUserName;
        $this->fromUserName = $fromUserName;
        $this->funcFlag = $funcFlag;
    }

    abstract public function __toString();

}

/**
 * 用于回复的文本消息类型
 */
class TextResponse extends WechatResponse {

    protected $content;

    protected $template = '<xml>
  <ToUserName><![CDATA[%s]]></ToUserName>
  <FromUserName><![CDATA[%s]]></FromUserName>
  <CreateTime>%s</CreateTime>
  <MsgType><![CDATA[text]]></MsgType>
  <Content><![CDATA[%s]]></Content>
  <FuncFlag>%s<FuncFlag>
</xml>';

    public function __construct($toUserName, $fromUserName, $content, $funcFlag = 0) {
        parent::__construct($toUserName, $fromUserName, $funcFlag);
        $this->content = str_replace('<br/>', "\n", $content);
    }

    public function __toString() {
        return sprintf($this->template,
        $this->toUserName,
        $this->fromUserName,
        time(),
        $this->content,
        $this->funcFlag
        );
    }

}

/**
 * 用于回复的音乐消息类型
 */
class MusicResponse extends WechatResponse {

    protected $title;
    protected $description;
    protected $musicUrl;
    protected $hqMusicUrl;

    protected $template = '<xml>
  <ToUserName><![CDATA[%s]]></ToUserName>
  <FromUserName><![CDATA[%s]]></FromUserName>
  <CreateTime>%s</CreateTime>
  <MsgType><![CDATA[music]]></MsgType>
  <Music>
    <Title><![CDATA[%s]]></Title>
    <Description><![CDATA[%s]]></Description>
    <MusicUrl><![CDATA[%s]]></MusicUrl>
    <HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
  </Music>
  <FuncFlag>%s<FuncFlag>
</xml>';

    public function __construct($toUserName, $fromUserName, $title, $description, $musicUrl, $hqMusicUrl, $funcFlag) {
        parent::__construct($toUserName, $fromUserName, $funcFlag);
        $this->title = $title;
        $this->description = $description;
        $this->musicUrl = $musicUrl;
        $this->hqMusicUrl = $hqMusicUrl;
    }

    public function __toString() {
        return sprintf($this->template,
        $this->toUserName,
        $this->fromUserName,
        time(),
        $this->title,
        $this->description,
        $this->musicUrl,
        $this->hqMusicUrl,
        $this->funcFlag
        );
    }

}

/**
 * 用于回复的图文消息类型
 */
class NewsResponse extends WechatResponse {

    protected $items = array();

    protected $template = '<xml>
  <ToUserName><![CDATA[%s]]></ToUserName>
  <FromUserName><![CDATA[%s]]></FromUserName>
  <CreateTime>%s</CreateTime>
  <MsgType><![CDATA[news]]></MsgType>
  <ArticleCount>%s</ArticleCount>
  <Articles>
    %s
  </Articles>
  <FuncFlag>%s<FuncFlag>
</xml>';

    public function __construct($toUserName, $fromUserName, $items, $funcFlag) {
        parent::__construct($toUserName, $fromUserName, $funcFlag);
        $this->items = $items;
    }

    public function __toString() {
        return sprintf($this->template,
        $this->toUserName,
        $this->fromUserName,
        time(),
        count($this->items),
        implode($this->items),
        $this->funcFlag
        );
    }

}

/**
 * 单条图文消息类型
 */
class NewsResponseItem {

    protected $title;
    protected $description;
    protected $picUrl;
    protected $url;

    protected $template = '<item>
  <Title><![CDATA[%s]]></Title>
  <Description><![CDATA[%s]]></Description>
  <PicUrl><![CDATA[%s]]></PicUrl>
  <Url><![CDATA[%s]]></Url>
</item>';

    public function __construct($title, $description, $picUrl, $url) {
        $this->title = $title;
        $this->description = $description;
        $this->picUrl = $picUrl;
        $this->url = $url;
    }

    public function __toString() {
        return sprintf($this->template,
        $this->title,
        $this->description,
        $this->picUrl,
        $this->url
        );
    }
}
