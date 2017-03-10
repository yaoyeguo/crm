<?php
class ecorder_ctl_admin_sms_sign extends desktop_controller
{
	var $name = "短信签名";
	var $workground = "ecorder.shop";

	function index()
    {
        $this->sync_old_sign();
    
        $title = '短信签名管理';
        $actions = array(
            array(
                'label' => '添加签名',
                'href' => 'index.php?app=ecorder&ctl=admin_sms_sign&act=edit&finder_id=' . $_GET['finder_id'],
                'target' => 'dialog::{width:600,height:300,title:\'添加签名\'}'
            ),
        );

        $this->finder(
            'ecorder_mdl_sms_sign',
            array(
                'title'=>$title,
                'actions'=>$actions,
                'use_buildin_new_dialog' => false,
                'use_buildin_set_tag'=>false,
                'use_buildin_recycle'=>true,
                'use_buildin_export'=>false,
                'use_buildin_import'=>false,
                'orderBy'=>'sign_id DESC',
            )
        );
	}
    
    function edit()
    {
        if($_POST){
            $this->save();
            exit;
        }
        $model = &app::get('ecorder')->model('shop');
        $rs = $model->getList('shop_id,name');
        foreach((array)$rs as $v){
            $shops[$v['shop_id']] = $v['name'];
        }
        
        $sign_id = intval($_GET['sign_id']);
        if($sign_id>0){
            $model = &app::get('ecorder')->model('sms_sign');
            $rs = $model->dump($sign_id);
        }
         
        //var_dump($shops);
        $this->pagedata['shops'] = $shops;
        $this->pagedata['rs'] = $rs;
        $this->display('admin/sms/sign_edit.html');
	}
    
    //保存短信签名
    function save()
    {
        $url = 'index.php?app=ecorder&ctl=admin_sms_sign&act=index';
        $this->begin($url);
        
        $sign_id = intval($_POST['sign_id']);
        $sms_sign = trim($_POST['sms_sign']);
        $sms_sign = str_replace(array('【','】'), '', $sms_sign);
        $sms_sign_old = trim($_POST['sms_sign_old']);
        $shop_ids = $_POST['shop_ids'];
        
        //中文，中英文字母,2-10位
        if(preg_match("/[`~!@#$%^&*()【】\-.+?]/", $sms_sign) > 0) {
            //$this->end(false, '签名必须是中文,字母的组合，2-10位长度');
        }
        
        if(preg_match("/^[0-9]+$/", $sms_sign) > 0) {
            $this->end(false, '签名不能全为数字，请修改');
        }
        
        //调用注册签名接口
        if(1 or $sms_sign != $sms_sign_old){
            $res = $this->reg_sms_sign($sms_sign);
            if($res['res'] == 'fail'){
                $this->end(false, $res['msg']);
            }
        }
        
        $model = $this->app->model('sms_sign');
        if($shop_ids){
            foreach($shop_ids as $shop_id){
                $sql = "update sdb_ecorder_sms_sign set shop_ids=REPLACE(shop_ids,',{$shop_id}','') where shop_ids like '%{$shop_id}%' ";
                $model->db->exec($sql);
            }
        }

        $save_arr = array(
            'sms_sign' => $sms_sign,
            'extend_no' => $res['data']['extend_no'],
            'review' => $res['data']['review'],
            'is_code_sign' => $_POST['is_code_sign'] == '1' ? 'true' : 'false',
            'modified_time' => time(),
            'shop_ids' => $shop_ids ? ','.implode(',',$shop_ids) : '',
        );

        //把旧的验证码改为否
        if($save_arr['is_code_sign'] == 'true'){
            $model->update(array('is_code_sign'=>'false'), array('is_code_sign'=>'true'));
        }

        if($sign_id == 0){
            $save_arr['create_time'] = time();
            $model->insert($save_arr);
        }else{
            $model->update($save_arr, array('sign_id'=>$sign_id));
        }
        
        //将签名回写到店铺表
        $this->update_shop_signs();
        
        $this->end(true,'签名保存成功');
    }
    
    //到短信平台注册短信签名
    public function reg_sms_sign($sms_sign)
    {
        $sms_sign = trim($sms_sign);
        $sms_sign = str_replace(array('【','】'), '', $sms_sign);
        
		#code:接口调用
		base_kvstore::instance('market')->fetch('account', $account);
		$account = unserialize($account);
		$shopex_id = $account['entid'];

		//对密码进行解密
		$market_edm_des = kernel::single('market_edm_des');
		if(strlen($account['password']) > 64){
			$password = $market_edm_des->decrypt($account['password']);
		}else{//兼容旧的原始密码
			$password = md5($account['password'].'ShopEXUser');
		}

		$api = SMS_SIGN_API.'/new';
		$pai_params = array(
            'client_id' => SMS_SIGN_KEY,
            'client_secret' => SMS_SIGN_SECRET,
            'shopexid' => $shopex_id,
            'passwd' => $password,
            'content' =>  '【'.$sms_sign.'】'
        );

        $res = $this->_get_url_content($api,$pai_params,1);
        $result = json_decode($res,true);
        if(!$result || $result['code'] != 0){
            //注册失败
            $err_msg = $result['data'] ? $result['data'] : $res;
            return array('res'=>'fail','msg'=>$err_msg);
        }else{
            //注册成功，返回编号和审核结果
            $data = array(
                'review'=>$result['data']['review'],
                'extend_no'=>$result['data']['extend_no'],
            );
            return array('res'=>'succ','data'=>$data);
        }
    }
    
    //更新店铺的签名
    public function update_shop_signs()
    {
        $sms_sign = array();
        $model = app::get('ecorder')->model('sms_sign');
        $rs = $model->getList('sms_sign,review,extend_no,shop_ids');
        foreach((array)$rs as $v){
            if($v['shop_ids']){
                $shop_ids = explode(',', substr($v['shop_ids'],1));
                foreach($shop_ids as $shop_id){
                    $sms_sign[$shop_id] = $v;
                }
            }
        }
        
        $model = app::get('ecorder')->model('shop');
        $rs = $model->getList('config,shop_id');
        foreach((array)$rs as $v){
            $config = unserialize($v['config']);
            $config['review'] = (string)$sms_sign[$v['shop_id']]['review'];
            $config['extend_no'] = (string)$sms_sign[$v['shop_id']]['extend_no'];
            $config['sms_sign'] = (string)$sms_sign[$v['shop_id']]['sms_sign'];
            
            $data = array(
                'shop_id'=>$v['shop_id'],
                'config'=>serialize($config),
            );
            $model->save($data);
        }
    }
    
    //从店铺表同步老的签名
    public function sync_old_sign()
    {
        $sms_sign_model = $this->app->model('sms_sign');
        if($sms_sign_model->count()>0){
            return true;
        }
    
        $sms_signs = array();
        $model = app::get('ecorder')->model('shop');
        $rs = $model->getList('config,shop_id');
        foreach((array)$rs as $v){
            $config = unserialize($v['config']);
            if($config['extend_no']){
                if(!isset($sms_signs[$config['extend_no']])){
                    $sms_signs[$config['extend_no']] = array(
                        'review'=>$config['review'],
                        'extend_no'=>$config['extend_no'],
                        'sms_sign'=>$config['sms_sign'],
                    );
                }
                $sms_signs[$config['extend_no']]['shop_ids'][] = $v['shop_id'];
            }            
        }
        
        //不存在老的有效签名
        if(!$sms_signs) return false;
        
        foreach($sms_signs as $v){
            $save_arr = array(
                'sms_sign' => $v['sms_sign'],
                'extend_no' => $v['extend_no'],
                'review' => $v['review'],
                'modified_time' => time(),
                'shop_ids' => $v['shop_ids'] ? ','.implode(',',$v['shop_ids']) : '',
            );
            
            $save_arr['create_time'] = time();
            $sms_sign_model->insert($save_arr);
        }
    }
    
    /**
     * 从给定的url获取内容
     *
     * @param string $url
     * @return string
     */
    private function _get_url_content($url,$params = '',$is_post = false)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        if($is_post){
            curl_setopt($ch, CURLOPT_POST, 1); // 发送一个常规的Post请求
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        }
        $content = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpcode >= 400){
            return $httpcode;
        }
        return $content;
    }
}
