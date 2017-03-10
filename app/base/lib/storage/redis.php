<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class base_storage_redis implements base_interface_storager{

    function __construct(){
        $this->redis=new Redis;
        list($host, $port) = explode(":", constant('STORAGE_MEMCACHED'));
        if( ! $this->redis->connect($host, $port, 3)){
            echo('Error:Connect redis server failed.');
        }
    }

    function save($file,&$url,$type,$addons,$ext_name=""){
        $id = $this->_get_ident($file,$type,$addons,$url,$path,$ext_name);
        if($path && $this->redis->set($path,file_get_contents($file))){
            return $id;
        }else{
            return false;
        }
    }

    function replace($file,$id){
        if($this->redis->set($id,file_get_contents($file))){
            return $id;
        }else{
            return false;
        }
    }

    function _get_ident($file,$type,$addons,&$url,&$path,$ext_name){    
        $path = $this->_ident($id).$ext_name;
        $url = STORAGE_HOST.$path;
        return $path;
    }


    function remove($id){
        if($id){
            return $this->redis->del($id);
        }else{
            return true;
        }
    }

    function _ident($id){
        return '/'.md5(microtime().base_certificate::get()).$id;
    }

    function getFile($id,$type){
        if($type=='public'){
            $f_dir = DATA_DIR.'/public'; 
        }else{
            $f_dir = DATA_DIR.'/private'; 
        }
        $tmpfile = tempnam($f_dir);
        if($id && file_put_contents($tmpfile,$this->redis->get($id))){
            return $tmpfile;
        }else{
            return true;
        }
    }
}
