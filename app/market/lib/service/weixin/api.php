<?php
class market_service_weixin_api {

    private $wx_obj;
    private $api_data;
    private $api_name;
    private $api_get_prama = '';

    private $api_list = array(
        'get_token' => 'https://api.weixin.qq.com/cgi-bin/token',
        'message_mass_send' => 'https://api.weixin.qq.com/cgi-bin/message/mass/send',
        'media_uploadnews' => 'https://api.weixin.qq.com/cgi-bin/media/uploadnews',
        //'media_upload' => 'http://file.api.weixin.qq.com/cgi-bin/media/upload',
        'media_upload' => 'https://api.weixin.qq.com/cgi-bin/media/upload',
    );

    function __construct()
    {
        base_kvstore::instance('market')->fetch('wxuser', $this->wxuser);
        $this->wxuser = json_decode($this->wxuser,true);
        $this->get_token();
        $this->wx_obj = json_decode($wxuser,true);
    }

    function set_test()
    {
        $this->test_api = true;
    }

    private function get_token($update = false)
    {
        $this->wx_token = kernel::single('taocrm_service_redis')->redis->GET('wx_token');
        $wx_token_time = kernel::single('taocrm_service_redis')->redis->TTL('wx_token');
        $update = true;
        if($wx_token_time <= 20 || $update = true) 
        {
            $this->method = 'get';
            if($this->wxuser == null) 
            {
                return $this->err('data do not emtpy');
            }
            $param = array(
                'grant_type' => 'client_credential',
                'appid' => trim($this->wxuser['appid']),
                'secret' => trim($this->wxuser['secret']),
            );
            $api_url = $this->api_list['get_token'] .'?'. http_build_query($param);
            $res = $this->curl($api_url);
            $rs = json_decode($res,true);
            kernel::single('taocrm_service_redis')->redis->SETEX('wx_token',$rs['expires_in'],$rs['access_token']);
            $this->wx_token = $rs['access_token'];
        }
    }
    
    //上传多媒体文件接口
    //http://mp.weixin.qq.com/wiki/index.php?title=%E4%B8%8A%E4%BC%A0%E4%B8%8B%E8%BD%BD%E5%A4%9A%E5%AA%92%E4%BD%93%E6%96%87%E4%BB%B6
    private function media_upload()
    {
        $this->method = 'post';
        if($this->api_data == null) 
        {
            return $this->err('data do not emtpy');
        }
        //$this->api_data = json_encode($this->api_data);
        $this->test_rs = '{"type":"thumb","media_id":"MEDIA_ID'.rand(0,time()).'","created_at":123456789}';
    }

    //上传图文消息素材接口
    //API文档：http://mp.weixin.qq.com/wiki/
    private function media_uploadnews()
    {
        if($this->api_data == null) {
            return $this->err('data do not emtpy');
        }
        $this->method = 'post';
        $media_data = array();
        foreach($this->api_data['articles'] as $v){
            $media_data[] = '{
                "thumb_media_id":"'.$v['thumb_media_id'].'",
                "title":"'.$this->clear_special_char($v['title']).'",
                "digest":"'.$this->clear_special_char($v['digest']).'",
                "content":"'.$this->clear_special_char($v['content']).'",
                "show_cover_pic":"'.$v['show_cover_pic'].'"
            }';
        }
        //$this->api_data = json_encode($this->api_data);
        $this->api_data = '{"articles": ['.implode(',', $media_data).']}';
        $this->test_rs = '{"type":"news", "media_id":"CsEf3ldqkAYJAU6EJeIkStVDSvffUJ54vqbThMgplD-VJXXof6ctX5fI6-aYyUiQ'.rand(0,time()).'", "created_at":'.time().'}';
    }
    
    //转换可能出错的字符
    function clear_special_char($str)
    {
        $str = str_replace('"', '\"',$str);
        $str = str_replace("\n", '',$str);
        return $str;
    }

    //群发消息接口
    //API文档：http://mp.weixin.qq.com/wiki/
    private function message_mass_send()
    {
        if($this->api_data == null) 
        {
            return $this->err('data do not emtpy');
        }
        $this->method = 'post';
        switch($this->api_data['msgtype'])
        {
            case 'text':
                $msg_data = array(
                    'touser' => $this->api_data['touser'],
                    'text' => $this->api_data['text'],
                    'msgtype' => 'text',
                );
                $this->api_data['text'] = $this->clear_special_char($this->api_data['text']);
                $touser = implode('","', $this->api_data['touser']);
                $msg_data = '{"touser":["'.$touser.'"],"msgtype":"text","text":{"content":"'.$this->api_data['text']['content'].'"}}';
                break;
            case 'mpnews':
                $msg_data = array(
                    'touser' => $this->api_data['touser'],
                    'mpnews' => $this->api_data['mpnews'],
                    'msgtype' => 'text',
                );
                $touser = implode('","', $this->api_data['touser']);
                $media_id = $this->api_data['mpnews']['media_id'];
                $msg_data = '{"touser":["'.$touser.'"],"mpnews":{"media_id":"'.$media_id.'"},"msgtype":"mpnews"}';
                break;
            default:
                return $this->err('other msgtype not open');
                break;
        }
        //$this->api_data = json_encode($msg_data);
        $this->api_data = $msg_data;
        $this->test_rs = ' { "errcode":0, "errmsg":"send job submission success", "msg_id":34182 }';
    }
    
    //分配接口
    public function push_api($fun,$data = null,$api_get_param = null)
    {
        if(!method_exists($this,$fun))
        {
            return $this->err('function not exists');
        }
        $this->api_data = $data;
        $this->api_name = $fun;
        $this->api_get_param = $api_get_param;
        
        $this->$fun();
        $rs = json_decode($this->send_api(),true);
        $this->test_api = false;
        if($rs['errcode'] > 0)
        {
            $rs['token'] = $this->wx_token;
        }
        return $rs;
    }

    //获取接口地址
    private function get_api()
    {
        $api_url = $this->api_list[$this->api_name];
        return !empty($api_url) ? $api_url : '';
    }
    
    //发送数据到接口
    private function send_api()
    {
        $api_url = $this->get_api(); 
        $token = $this->wx_obj['token'];
        $token = $this->wx_token;
        $api_url .= '?access_token='.$token;  
        !empty($this->api_get_param) && $api_url .= '&'.http_build_query($this->api_get_param);
        
        //err_log($api_url, 'api');err_log($this->api_data, 'api');exit;
        
        if(!$this->test_api){
            $http_obj = new base_httpclient;
            if($this->method == 'get')
                $res = $this->curl($api_url);
            else
                $res = $this->curl($api_url,array('post'=>$this->api_data));
        }else{
            $res = $this->test_rs;
        }
        return $res;
    }

    //报错
    private function err($str='The unknown err')
    {
        return array('status'=>false,'msg'=>$str);
    }
    //CURL   
    /**  
     * 使用：  
     * curl('http://www.baidu.com');  
     *  
     * POST数据  
     * $post = array('aa'=>'ddd','ee'=>'d')  
     * 或  
     * $post = 'aa=ddd&ee=d';  
     * curl('http://www.baidu.com',array('post'=>$post));  
     */  
    function curl($url, $conf = array())   
    {   
        if(!function_exists('curl_init') or !is_array($conf))  return FALSE;   

        $post = '';   
        $purl = parse_url($url);   

        $arr = array(   
            'post' => FALSE,   
            'return' => TRUE,   
            //'cookie' => 'C:/cookie.txt',
        );   
        $arr = array_merge($arr, $conf);   
        $ch = curl_init();   

        if($purl['scheme'] == 'https')   
        {   
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);   
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);   
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);   
        }   

        curl_setopt($ch, CURLOPT_URL, $url);   
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, $arr['return']);   
        curl_setopt($ch, CURLOPT_COOKIEJAR, $arr['cookie']);   
        curl_setopt($ch, CURLOPT_COOKIEFILE, $arr['cookie']);   

        if($arr['post'] != FALSE)   
        {   
            curl_setopt($ch, CURLOPT_POST, TRUE);   
            if(is_array($arr['post']) && !isset($arr['post']['media']))
            {   
                $post = http_build_query($arr['post']);   
            } else {   
                $post = $arr['post'];   
            }   
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);   
        }   

        $result = curl_exec($ch);   
        curl_close($ch);   

        return $result;   
    }  
}
