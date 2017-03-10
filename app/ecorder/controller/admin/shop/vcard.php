<?php
class ecorder_ctl_admin_shop_vcard extends desktop_controller{
    var $name = "店铺管理";
    var $workground = "setting_tools";
    var $pa ;
    var $app_key = 'tgcrm';
    var $openapi_url = 'http://api.wwgenius.taoex.com/openapi/';

    /*
     * 添加前端店铺
     */
    function addterminal(){
        $this->_editterminal();
    }

    /*
     * 编辑前端店铺
     */
    function editterminal($shop_id){
        $this->_editterminal($shop_id);
    }

    function _editterminal($shop_id=NULL,$para=""){
        $oShop = &app::get('ecorder')->model("shop_vcard");
        $shoptype = ecorder_shop_type::get_shop_type();
        $shop_type = array();
        $i = 0;
        if ($shoptype)
        foreach ($shoptype as $k=>$v){
            $shop_type[$i]['type_value'] = $k;
            $shop_type[$i]['type_label'] = $v;
            $i++;
        }

        if($shop_id){
            $shop = $oShop->dump($shop_id);
            $shop_config = unserialize($shop['config']);
            $shop_tel = explode('-',strval($shop['tel']));
            $shop['tel_code'] = $shop_tel[0];
            $shop['tel_phone'] = strval($shop_tel[1]);
            $shop['tel_extension'] = strval($shop_tel[2]);

            $this->pagedata['shop']=$shop;
            $this->pagedata['shop_config'] = $shop_config;
        }

        $this->pagedata['shop_type'] = $shop_type;
        $this->display("admin/shop/vcard_edit.html");
    }

    public function saveterminal()
    {
        $oShop = &app::get('ecorder')->model("shop_vcard");
        $url = 'index.php?app=plugins&ctl=admin_vcard&act=index';
        $this->begin($url);
        $svae_data = $_POST['shop'];

        //表单验证
        if (strlen($svae_data['zip']) <> '6'){
            $this->end(false,app::get('base')->_('请输入正确的邮编'));
        }
        //固定电话与手机必填一项
        $gd_tel = str_replace(" ","",$svae_data['tel']);
        $mobile = str_replace(" ","",$svae_data['mobile']);
        if (!$gd_tel && !$mobile){
            $this->end(false,app::get('base')->_('固定电话与手机号码必需填写一项'));
        }
        $pattern1 = "/^\d{1,4}-\d{7,8}(-\d{1,6})?$/i";
        if ($gd_tel){
            if (!preg_match($pattern1, $gd_tel)){
                $this->end(false,app::get('base')->_('请填写正确的固定电话号码'));
            }
        }
        $pattern2 = "/^\d{8,15}$/i";
        if ($mobile){
            if (!preg_match($pattern2, $mobile)){
                $this->end(false,app::get('base')->_('请输入正确的手机号码'));
            }
            if ($mobile[0] == '0'){
                $this->end(false,app::get('base')->_('手机号码前请不要加0'));
            }
        }
        
        //调用旺旺精灵接口
        $svae_data['vcard_id'] = intval($svae_data['vcard_id']);
        $vcard = $this->get_vcard_id($svae_data);
        if(isset($vcard['error_response'])){
            $this->end(false, $vcard['error_response']['msg']);
        }else{
            $vcard = $vcard['vcard.save']['data'];
        }
        
        //生成短地址
        if($svae_data['vcard_id'] != $vcard['vcard_id']){
            //$vcard_url = $vcard['openapi_url'].'?id='.$vcard['vcard_id'];
            //$SinaObj = kernel::single('market_shorturl');
            //$vcard_url = $SinaObj->shortenSinaUrl($vcard_url);
            $svae_data['vcard_url'] = $vcard['vcard_url'];
            $svae_data['vcard_id'] = $vcard['vcard_id'];
            $svae_data['passcode'] = $vcard['passcode'];
        }
        
        //保存到本地数据库
        $rt = $oShop->save($svae_data);
        $rt = $rt ? true : false;                
        
        $this->end($rt,app::get('base')->_($rt?'保存成功':'保存失败'));
    }
    
    private function get_vcard_id($data)
    {    
        //[area] => mainland:上海/上海市/徐汇区:25
        $area = explode(':', $data['area']);
        $data['address'] = str_replace('/','',$area[1]).$data['address'];
        $data['source'] = $this->app_key;
        $data['app_key'] = $this->app_key;
        $data['method'] = 'vcard.save';
        $data['sign'] = $this->gen_sign($data);
        
        $resp = $this->curl($this->openapi_url, $data);
        //err_log($resp);
        // 返回示例
        //{"vcard.save":{"success":"true","data":{"res":"succ","msg":"","vcard_id":"3","openapi_url":"http:\\/\\/192.168.51.77\\/wwgenius_csapi\\/openapi\\/vcard.php"}}}
        $resp_arr = json_decode($resp, true);
        return $resp_arr;
    }
    
    /**
     * @desc 获取签名
     */
    private function gen_sign($data)
    {
        $data['app_secret'] = strtoupper(md5($data['app_key'].'^_^'.'iloveshopex'));
        $str = $this->assemble($data) . $data['app_secret'];
        return strtoupper(md5($str));
    }
    
    private function assemble($params)
    {
        if(!is_array($params))  return null;

        ksort($params, SORT_STRING);
        $sign = '';
        foreach($params AS $key=>$val){
            $val = trim($val);
            if(is_null($val))   continue;
            if(is_bool($val))   $val = ($val) ? 1 : 0;
            $sign .= $key . (is_array($val) ? $this->assemble($val) : $val);
        }
        return $sign;
    }
    
    private function curl($url, $postFields = null)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FAILONERROR, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		if (is_array($postFields) && 0 < count($postFields))
		{
			$postBodyString = "";
			$postMultipart = false;
			foreach ($postFields as $k => $v)
			{
				if("@" != substr($v, 0, 1))//判断是不是文件上传
				{
					$postBodyString .= "$k=" . urlencode($v) . "&"; 
				}
				else//文件上传用multipart/form-data，否则用www-form-urlencoded
				{
					$postMultipart = true;
				}
			}
			unset($k, $v);
			curl_setopt($ch, CURLOPT_POST, true);
			if ($postMultipart)
			{
				curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
			}
			else
			{
				curl_setopt($ch, CURLOPT_POSTFIELDS, substr($postBodyString,0,-1));
			}
		}
		$reponse = curl_exec($ch);
		
		if (curl_errno($ch))
		{
			throw new Exception(curl_error($ch),0);
		}
		else
		{
			$httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			if (200 !== $httpStatusCode)
			{
				throw new Exception($reponse,$httpStatusCode);
			}
		}
		curl_close($ch);
		return $reponse;
	}
}
