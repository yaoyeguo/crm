<?php
class ecorder_mdl_shop extends dbeav_model{

    static $restore = false;
    protected static $hardeWareConnect = null;

    function gen_id($shop_bn){
        if(empty($shop_bn)){
            return false;
        }else{
            $shop_id = md5($shop_bn);
            if($this->db->selectrow("SELECT shop_id FROM sdb_ecorder_shop WHERE shop_id='".$shop_id."'")){
                return false;
            }else{
                return $shop_id;
            }
        }
    }

    function save(&$data)
    {
        if(isset($data['config']) && is_array($data['config'])){
            $config = $data['config'];
            if($config['password']){
                $config['password'] = $this->aes_encode($config['password']);
            }
            unset($data['config']);
            $data['config'] = serialize($config);
        }
        $data['active'] = 'true';

        if(self::$restore){
            return parent::save($data);
        }else{
            if(!$data['shop_id']){
                $shop_id = $this->gen_id($data['shop_bn']);
                if($shop_id){
                    $data['shop_id'] = $shop_id;
                }else{
                    return false;
                }
                parent::save($data);
                //暂时不通知内存从数据库加载新店铺三个月内的订单
                //$connect = $this->getConnect();
                //$connect->addShop(array('shopId' => $shop_id));
                return true;
            }else{
                return parent::save($data);
            }
        }
    }

    protected function getConnect()
    {
        if (self::$hardeWareConnect == null) {
            self::$hardeWareConnect = new taocrm_middleware_connect;
        }
        return self::$hardeWareConnect;
    }

    public function insert(&$data){
        if(parent::insert($data)){
            foreach(kernel::servicelist('ecorder_shop_ex') as $name=>$object){
                if(method_exists($object,'insert')){
                    $object->insert($data);
                }
            }
            return true;
        }else{
            return false;
        }
    }

    public function update($data,$filter,$mustUpdate = null){
        if(parent::update($data,$filter,$mustUpdate)){
            foreach(kernel::servicelist('ecorder_shop_ex') as $name=>$object){
                if(method_exists($object,'update')){
                    $object->update($data);
                }
            }
            return true;
        }else{
            return false;
        }
    }

    public function delete($filter,$subSdf = 'delete'){
        if(parent::delete($filter)){
            foreach(kernel::servicelist('ecorder_shop_ex') as $name=>$object){
                if(method_exists($object,'delete')){
                    $object->delete($filter);
                }
            }
            return true;
        }else{
            return false;
        }
    }
    
    //店铺类型
    function modifier_shop_type($row){
        $tmp = ecorder_shop_type::get_shop_type();
        return isset($tmp[$row]) ? $tmp[$row] : $row;
    }

    function modifier_subbiztype($row){
        $tmp = array('zx'=>'直销','fx'=>'分销','fxq'=>'意向分销商申请');
        return $tmp[$row];
    }

    function pre_recycle($data){
        $filter = $data;
        unset($filter['_finder']);
        if($data['isSelectedAll'] == '_ALL_'){
            $shop = $this->getList('shop_id',$filter);
            foreach($shop as $v){
                $shop_id[] = $v['shop_id'];
            }
        }else{
            $shop_id = $data['shop_id'];
        }
        if ($data)
        foreach ($data as $key=>$val){
            //判断是否已绑定，否则无法删除
            if ($val['node_id'] && $val['node_type']!='offlinepos'){
                $this->recycle_msg = '店铺:'.$val['name'].'已绑定，无法删除!';
                return false;
            }
        }
        //$syndata['shop_id'] = implode(",",$shopid);
        return true;
    }

    function pre_delete($shop_id){
        return true;
    }

    function pre_restore($shop_id){
        self::$restore = true;
        return true;
    }

    /*需要删除的代码
     function get_format_post($certi_app,$post=array(),$format='json'){
     $post_basic = array(
     'certi_app' => $certi_app,
     'certificate_id' => kernel::single("base_certificate")->get('certificate_id'),
     'app_id' => 'ecos.ome', //默认就用ecos，以后有新app再和申请到的license进行绑定
     'app_instance_id' => '',
     'version' => '1.0',
     'certi_url' => kernel::base_url(1),
     'certi_session' => kernel::single('base_session')->sess_id(),
     //'certi_validate_url' => kernel::api_url('api.shop_callback','certi_validate'),
     'format' => $format,
     'shop_version'=>''
     );
     $post = array_merge($post,$post_basic);
     $post['certi_ac'] = $this->make_shopex_ac($post,kernel::single("base_certificate")->get('token'));
     return $post;
     }

     function make_shopex_ac($temp_arr,$token){
     ksort($temp_arr);
     $str = '';
     foreach($temp_arr as $key=>$value){
     if($key!='certi_ac') {
     $str.=$value;
     }
     }
     return md5($str.$token);
     }

     function to_shopex_certificate($format_post){
     $url = LICENSE_CENTER;
     $res = kernel::single('base_httpclient')->post($url,$format_post);
     return $res;
     }*/

    function aes_encode($str){
        $aes = kernel::single('ecorder_aes',true);// 把加密后的字符串按十六进制进行存储
        $key = kernel::single("base_certificate")->get('token');// 密钥
        $keys = $aes->makeKey($key);

        $ct = $aes->encryptString($str, $keys);
        return $ct;
    }

    function aes_decode($str){
        $aes = kernel::single('ecorder_aes',true);// 把加密后的字符串按十六进制进行存储
        $key = kernel::single("base_certificate")->get('token');// 密钥
        $keys = $aes->makeKey($key);

        $dt = $aes->decryptString($str, $keys);

        return $dt;
    }

    function searchOptions(){
        return array();
    }

    public function get_shop_orders($shop_id){
        $rs = $this->app->model('shop_analysis')->dump($shop_id);
        return $rs['orders'];
    }

    //获取店铺的短信签名
    public function get_sms_sign($shop_id=''){
        $sql = 'select config,name from sdb_ecorder_shop';
        if($shop_id){
            $sql .= " where shop_id='$shop_id' ";
        }
        $rs = $this->db->selectrow($sql);
        $config = unserialize($rs['config']);
        if(!$config['sms_sign']) {
            return $rs['name'];
        }else{
            return $config['sms_sign'];
        }
    }

    //检测店铺的短信签名
    public function chk_sms_sign($shop_id=false){
        $sql = 'select config from sdb_ecorder_shop';
        if($shop_id){
            $sql .= " where shop_id='$shop_id' ";
        }
        $rs = $this->db->select($sql);
        foreach($rs as $v){
            $config = unserialize($v['config']);
            if(!$config['sms_sign']) return false;
        }
        return true;
    }

    public function set_last_market_time($shop_id='', $last_market_time=false)
    {
        if(!$last_market_time)
            $last_market_time = time();
        $sql = "update sdb_ecorder_shop set last_market_time={$last_market_time} where shop_id='{$shop_id}' ";
        $this->db->exec($sql);
    }
    
    //可用的短信签名
    public function get_sms_sign_list()
    { 
        $sign_list = array();
        $mdl = $this->app->model('sms_sign');
        $rs = $mdl->getList('sms_sign,extend_no', array('review'=>'true'));
        foreach($rs as $v){
            $k = $v['extend_no'];
            $sign_list[$k]['sign'] = $v['sms_sign'];
            $sign_list[$k]['extend_no'] = $v['extend_no'];
        }
        return $sign_list;
    }
    
    public function get_shops($type='all', $style='data')
    {
        $shops = array();
        $rs = $this->getList('shop_id,name,subbiztype,shop_type,node_id,node_type',array(),0,-1,'orders DESC,last_download_time DESC');
        foreach($rs as $v){
            //跳过分销平台
            if($type == 'no_fx'){
                if($v['subbiztype']=='fx' or $v['shop_type']=='shopex_b2b') continue;
            }
            
            //跳过非分销平台
            if($type == 'fenxiao'){
                if($v['subbiztype']!='fx' && $v['shop_type']!='shopex_b2b') continue;
            }
            
            //跳过非淘宝
            if($type == 'taobao'){
                if($v['shop_type']!='taobao') continue;
            }
            
            //只取微信
            if($type == 'wechat'){
                if($v['node_type']!='wechat' or !$v['node_id']) continue;
            }
            
            if($style=='select'){
                $shops[$v['shop_id']] = $v['name'];
            }else{
                $shops[$v['shop_id']] = $v;
            }
        }
        return $shops;
    }
    
    public function get_unnormal_shops($last_download_time='', $min_create_time='')
    {
        $unnormal_shops = array();
        
        if( ! $last_download_time) $last_download_time = time() - 86400; //如果24小时无订单，报警提示
        if( ! $min_create_time) $min_create_time = time() - 86400; //24小时内添加的新店铺不提示
        
        $rs = $this->getList('name,node_id,node_type,last_download_time', array('last_download_time|sthan'=>$last_download_time,'create_time|sthan'=>$min_create_time));
        if($rs){
            foreach($rs as $v){
                if($v['node_id'] && $v['node_type']!='offlinepos' && $v['node_type']!='ecos.ome'){
                    $unnormal_shops[] = $v;
                }
            }
        }
        
        return $unnormal_shops;
    }
}
