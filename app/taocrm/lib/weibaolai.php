<?php

class taocrm_weibaolai{

	/**
     * 入口，暂时不用
	 */
    public function index(){}
    /**
     * 新用户注册
     */
    public function register($domain){
        if($domain){
            $base_token = base_shopnode::get_token();
            $url = 'http://api.blisssend.com/api/Register?ShopId='.$domain.'&Url='.$base_token;
			$objHttp = kernel::single("base_httpclient");
			$result = $objHttp->get($url,array());
			$result = json_decode($result,true);
			if($result['ResultId'] == 0 && !empty($result['Data'])){
                app::get('taocrm')->setConf('system.bind_weibaolai',$result['Data']);
				$data = array(
				'rsp'=>1,
				'data'=>$result['Data']
				);
			}else{
				$data = array(
				'rsp'=>0
				);
			}
            return $data;
		}
        return false;
	}

	/**
     *  获取免登路径
	 */
	public function getNoLoginUrl(){
		$data = $this->check_install();
		$url = '';
		if($data){
            $url = 'http://www.blisssend.com/index.php?m=Users&a=autologin&appkey='.$data['AppKey'].'&appsec='.$data['AppSec'];
		}

		return $url;
	}
    /**
     * 检测是否登录
     */
	private function check_install(){
		$data = app::get('taocrm')->getConf('system.bind_weibaolai');
		if(!empty($data)){
			return $data;
		}else{
			return false;
		}
	}
    /*
     * 查询用户积分 (微宝来专用)
     * @params
     *  ToUserName
     *  FromUserName
     *  mobile
     */
    public function weibaolai_integral_query(){
        $token = self::$token;
        $url   ='http://'.$_SERVER['SERVER_NAME'].'/index.php/api';
        $post_data = array(
            'method'        => 'taocrm.point.weibaolai_point',
            'ToUserName'    => 'gh_50838fc1943e',
            'FromUserName'  => 'oSnuWjhEcfsteEsy1oREch6Mvjfo',
            'mobile'        => '15225441253'
        );
        $post_data['sign'] = strtoupper(md5(strtoupper(md5($this->assemble($post_data))).$token));
        $objHttp = kernel::single("base_httpclient");
        $result = $objHttp->post($url,$post_data);
        $result = json_decode($result,true);
        print_r($result);exit;
    }
    public function add_wx_user(){
        $url   ='http://'.$_SERVER['SERVER_NAME'].'/index.php/api';
        $token = self::$token;
        $post_data = array(
            'method'        => 'taocrm.point.sync_user',
            'ToUserName'    => 'gh_50838fc1943e',
            'FromUserName'  => 'oSnuWjhEcfsteEsy1oREch6Mvjfo',
            'mobile'        => '15225441253',
            'user_id'       => '0123456789',
            'wx_nick'       => 'hahahahah',
            'create_time'   => '1394932707',
            'update_time'   => '1394932707'
        );
        $post_data['sign'] = strtoupper(md5(strtoupper(md5($this->assemble($post_data))).$token));
        $objHttp = kernel::single("base_httpclient");
        $result = $objHttp->post($url,$post_data);
        $result = json_decode($result,true);
        print_r($result);exit;
    }
    /**
     * 客户单个订单查询 api：taocrm.orders.single
     * $param orderid 订单ID
     */
    public function get_single_order(){
        //$callback = $_REQUEST['callback'];   echo $callback.'('.$result.')';
        $url   ='http://'.$_SERVER['SERVER_NAME'].'/index.php/api';
        $token = self::$token;
        $post_data = array(
            'method'        => 'taocrm.orders.single',
            'order_id'     => '328643484434098'
        );
        $post_data['sign'] = strtoupper(md5(strtoupper(md5($this->assemble($post_data))).$token));
        $objHttp = kernel::single("base_httpclient");
        $result = $objHttp->post($url,$post_data);
        $result = json_decode($result,true);
    }
    /**
     * 物流查询
     * tid        订单号    402284107190621
     */
    public function logistics_query(){
        $url   ='http://'.$_SERVER['SERVER_NAME'].'/index.php/api';
        $token = self::$token;
        $post_data = array(
            'method'        => 'taocrm.orders.trades',
            'tid'           => '404169548946316',
            'ship_mobile'  => '15101091213'
        );
        $post_data['sign'] = strtoupper(md5(strtoupper(md5($this->assemble($post_data))).$token));
        $objHttp = kernel::single("base_httpclient");
        $result = $objHttp->post($url,$post_data);
        $result = json_decode($result,true);
    }
    /*
     * 同步关键字
     * 1.一期先不做
     */
    public function sync_keyworld(){}
    /**
     * 同步数据回CRM，暂时不用
     * 1.会员
     */
    public function sync(){}
    //生成签名
    function assemble($params)
    {
        if(!is_array($params))  return null;
        ksort($params, SORT_STRING);
        $sign = '';
        foreach($params AS $key=>$val){
            if(is_null($val))   continue;
            if(is_bool($val))   $val = ($val) ? 1 : 0;
            $sign .= $key . (is_array($val) ? assemble($val) : $val);
        }
        return $sign;
    }
    /**
     * 客户历史订单查询 api：taocrm.orders.search
     */
    public function historical_order_query(){
        $token = self::$token;
        $url   ='http://'.$_SERVER['SERVER_NAME'].'/index.php/api';
        $post_data = array(
            'member_id'     => '2',
            'method'        => 'taocrm.orders.search',
            'shop_id'       => '4000ba87471c2e4e1a86cc299ccbe864',
            'page_size'     => '10',
            'page'          => '1',
            'start_created_date'    => ''
        );
        $post_data['sign'] = strtoupper(md5(strtoupper(md5($this->assemble($post_data))).$token));
        $objHttp = kernel::single("base_httpclient");
        $result = $objHttp->post($url,$post_data);
        $result = json_decode($result,true);
    }

    /**
     * 初始化
     */
    public function init(){
        $this->init_keywords();
        $this->init_menus();
        $this->init_news();
    }
    /**
     * 积分更新
     */
    public function integral_edit(){
        $token = self::$token;
        $url   ='http://'.$_SERVER['SERVER_NAME'].'/index.php/api';
        $post_data = array(
            'member_id'     => '2',
            'method'        => 'taocrm.point.update',
            'shop_id'       => '4000ba87471c2e4e1a86cc299ccbe864',
            'type'          => '1',
            'point'         => '100',
            'point_type'    => '1',
            'point_desc'    => '1'
        );
        $post_data['sign'] = strtoupper(md5(strtoupper(md5($this->assemble($post_data))).$token));
        $objHttp = kernel::single("base_httpclient");
        $result = $objHttp->post($url,$post_data);
        $result = json_decode($result,true);
    }

    /**
     * 初始化关键字
     * type:1表示模糊匹配 2表示完全匹配
     */
	private function init_keywords(){
        $appkey = 'ee6f62e6b0eb4179816025acc107d3df';
        $appsec = 'd5f30004d28e48fab543c5f86b6dfa4e';
        $KeyWord = '查询';
        $Text    = '您的余额为1000元';
        $Type    = '1';
        $falg    = true;
        $user_data = $this->check_install();
        if(empty($user_data)){
            return fasle;
        }
        $url = 'http://api.blisssend.com/api/UpLoadNewInfoText/?Appkey='.$appkey.'&Appsec='.$appsec.'&KeyWord='.urlencode($KeyWord).'&Text='.urlencode($Text).'&Type='.$Type;
        $objHttp = kernel::single("base_httpclient");
        $result = $objHttp->get($url,array());
        $result = json_decode($result,true);
        if($result['ResultId'] != 0 )
            $falg = false;

        return $falg;
	}

    /**
     * 初始化菜单
     */
	private function init_menus(){

	}

    /**
     * 初始化图文素材
     * http://api.blisssend.com/api/UpLoadNewInfoPic/?Appkey=xx&Appsec=xx&KeyWord=xx&Text=xx&Pid=xx&Type=1&PicUrl=xx&ShowPic=xx&Info=xx&Url=xx&Title=xx
     * Type:2完全匹配 用户输入的和此关键词一样才会触发
     * KeyWord:关键词
     * Title：标题
     * Text:简介
     * Pid：文章所属类别
     * PicUrl：封面图片地址
     * ShowPic：详细页是否显示封面 1显示 0不显示
     * Info：图文详细页内容
     * Url：图文外链网址 如果Info：图文详细页内容不为空，这里请留空
     */
	private function init_news(){
        $AppKey        = 'ee6f62e6b0eb4179816025acc107d3df';
        $AppSec        = 'd5f30004d28e48fab543c5f86b6dfa4e';
        $KeyWord       = '动漫 dongman dm';
        $Text          = '文本信息';
        $Type          = '1';
        $Title         = '进击的巨人 死神 海贼王';
        $Pid           = '1';
        $PicUrl        = 'http://www.blisssend.com/uploads/k/knjudp1412833796/b/f/a/0/thumb_5438eb5449901.jpg';
        $ShowPic       = '1';
        $Info          = '哈哈呵呵嘿嘿咳咳恩恩额额';
        $Url           = '';
        //$params_data = urlencode($params_data);
        $uri = 'http://api.blisssend.com/api/UpLoadNewInfoPic/?Appkey='.$AppKey.'&Appsec='.$AppSec.'&';
        $uri.= 'KeyWord='.urlencode($KeyWord).'&Text='.urlencode($Text).'&Pid='.$Pid.'&Type='.$Type.'&PicUrl='.$PicUrl.'&ShowPic='.$ShowPic.'&Info='.urlencode($Info).'&Url='.$Url.'&Title='.urlencode($Title);
        $objHttp = kernel::single("base_httpclient");
        $result = $objHttp->get($uri,array());
        $result = json_decode($result,true);
        if($result['ResultId'] != 0 )
            $falg = false;
        return $falg;
	}

	/**
     * 积分查询
	 */
    public function integral_query(){
        $token = self::$token;
        $url   ='http://'.$_SERVER['SERVER_NAME'].'/index.php/api';
        $post_data = array(
            'member_id' => '2',
            'method'    => 'taocrm.point.get'
        );
        $post_data['sign'] = strtoupper(md5(strtoupper(md5($this->assemble($post_data))).$token));
        $objHttp = kernel::single("base_httpclient");
        $result = $objHttp->post($url,$post_data);
        $result = json_decode($result,true);
	}
}
