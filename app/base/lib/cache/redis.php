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
class base_cache_redis extends base_cache_abstract implements base_interface_cache
{
    static private $_cacheObj = null;

    function __construct() 
    {
        $this->connect();
        $this->check_vary_list();
    }//End Function

    public function connect() 
    {        
        if(!isset(self::$_cacheObj)){
            if(defined('CACHE_MEMCACHE_CONFIG') && constant('CACHE_MEMCACHE_CONFIG')){
                self::$_cacheObj = new Redis();
                $config = explode(':', CACHE_MEMCACHE_CONFIG);
                if( ! $config[1]) $config[1] = '6379';
                self::$_cacheObj->connect($config[0], $config[1], 3);
            }else{
                trigger_error('can\'t load CACHE_MEMCACHE_CONFIG, please check it', E_USER_ERROR);
            }
        }
    }//End Function

    public function fetch($key, &$result) 
    {
        $result = self::$_cacheObj->get($key);
        if($result === false){
            return false;
        }else{
            //$result = $this->uncompress($result);
            return true;
        }
    }//End Function

    public function store($key, $value) 
    {
        //$value = $this->compress($value);
        return self::$_cacheObj->set($key, $value);
    }//End Function

    /**
     *  获取缓存资源占用
     *  redis不支持
     */
    public function status() 
    {
        //$status = self::$_cacheObj->getStats();
        $return['缓存获取'] = 0;
        $return['缓存存储'] = 0;
        $return['可使用缓存'] = 0;
        return $return;
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
