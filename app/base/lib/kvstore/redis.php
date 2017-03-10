<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

/*
 * @package base
 * @copyright Copyright (c) 2010, shopex. inc
 * @author edwin.lzh@gmail.com
 * @license 
 */
class base_kvstore_redis extends base_kvstore_abstract implements base_interface_kvstore_base 
{

    static private $_cacheObj;

    function __construct($prefix) 
    {
        $this->connect();
        $this->prefix = $prefix;
    }//End Function

    public function connect() 
    {
        if(!isset(self::$_cacheObj)){
            if(defined('KVSTORE_MEMCACHE_CONFIG') && constant('KVSTORE_MEMCACHE_CONFIG')){
                self::$_cacheObj = new Redis();
                $config = explode(':', KVSTORE_MEMCACHE_CONFIG);
                if( ! $config[1]) $config[1] = '6379';
                self::$_cacheObj->connect($config[0], $config[1], 3);
            }else{
                trigger_error('can\'t load KVSTORE_MEMCACHE_CONFIG, please check it', E_USER_ERROR);
            }
        }
    }//End Function

    public function fetch($key, &$value, $timeout_version=null) 
    {
        $data = self::$_cacheObj->get($this->create_key($key));
        if($data !== false){
            //$data = $this->uncompress($data);
            $store = unserialize($data);    //todo：反序列化
            if($timeout_version < $store['dateline']){
                if($store['ttl'] > 0 && ($store['dateline']+$store['ttl']) < time()){
                    return false;
                }
                $value = $store['value'];
                return true;
            }
        }
        return false;
    }//End Function

    public function store($key, $value, $ttl=0) 
    {
        $store['value'] = $value;
        $store['dateline'] = time();
        $store['ttl'] = $ttl;
        
        $key = $this->create_key($key);
        $value = serialize($store);
        //$value = $this->compress($value);
        
        $res = self::$_cacheObj->set($key, $value);
        if($store['ttl']>0) self::$_cacheObj->setTimeout($key, $store['ttl']);//设置过期时间
        return $res;
    }//End Function

    public function delete($key) 
    {
        return self::$_cacheObj->delete($this->create_key($key));
    }//End Function

    public function recovery($record) 
    {
        $key = $record['key'];
        $store['value'] = $record['value'];
        $store['dateline'] = $record['dateline'];
        $store['ttl'] = $record['ttl'];
        
        $key = $this->create_key($key);
        $value = serialize($store);
        //$value = $this->compress($value);
        
        $res = self::$_cacheObj->set($key, $value);
        if($store['ttl']>0) self::$_cacheObj->setTimeout($key, $store['ttl']);//设置过期时间
        return $res;
    }//End Function
    
    public function compress($str)
    {
        if(defined('KV_COMPRESS') && function_exists(constant('KV_COMPRESS'))){
            $str = call_user_func(constant('KV_COMPRESS'), $str);
        } 
        return $str;
    }
    
    public function uncompress($str)
    {
        if(defined('KV_UN_COMPRESS') && function_exists(constant('KV_UN_COMPRESS'))){
            $str = call_user_func(constant('KV_UN_COMPRESS'), $str);
        }
        return $str;
    }
}//End Class