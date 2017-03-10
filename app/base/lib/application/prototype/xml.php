<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class base_application_prototype_xml extends base_application_prototype_content{

    var $current;
    var $xml;
    var $xsd;
    var $path;
    static $__appinfo;
    
    public function chk_version_code($app)
    {
        $this->new_xml = $this->xml;
        
        //处理不同版本的菜单
        if($this->xml=='desktop.xml'){
            //获取crm版本
            $version_code = kernel::single('taocrm_system')->get_version_code();
            $version_code = strtolower($version_code);
            
            if(defined('CUSTOM_CORE_DIR') && file_exists(CUSTOM_CORE_DIR.'/'.$app->app_id.'/desktop_'.$version_code.'.xml')){                
                $xml_path = CUSTOM_CORE_DIR.'/'.$app->app_id.'/desktop_'.$version_code.'.xml';
            }else{
                $xml_path = $app->app_dir.'/desktop_'.$version_code.'.xml';
            }
            
            if(file_exists($xml_path)){
                $this->new_xml = 'desktop_'.$version_code.'.xml';  
            }
            //err_log($xml_path);
            //err_log($this->new_xml);
        }
    }

    public function init_iterator()
    {
        $this->chk_version_code($this->target_app);
    
        if(defined('CUSTOM_CORE_DIR') && file_exists(CUSTOM_CORE_DIR.'/'.$this->target_app->app_id.'/'.$this->new_xml)){
            $ident = $this->target_app->app_id.'-'.$this->new_xml;
            if(!isset(self::$__appinfo[$ident])){
                self::$__appinfo[$ident] = kernel::single('base_xml')->xml2array(
                    file_get_contents(CUSTOM_CORE_DIR.'/'.$this->target_app->app_id.'/'.$this->new_xml),$this->xsd);
            }            
            eval('$array = &self::$__appinfo[$ident]['.str_replace('/','][',$this->path).'];');
        }elseif(file_exists($this->target_app->app_dir.'/'.$this->new_xml)){
            $ident = $this->target_app->app_id.'-'.$this->new_xml;
            if(!isset(self::$__appinfo[$ident])){
                self::$__appinfo[$ident] = kernel::single('base_xml')->xml2array(
                    file_get_contents($this->target_app->app_dir.'/'.$this->new_xml),$this->xsd);
            }
            
            eval('$array = &self::$__appinfo[$ident]['.str_replace('/','][',$this->path).'];');
        }else{
            $array = array();
        }
        return new ArrayIterator((array)$array);
    }
    
    function last_modified($app_id)
    {
        $this->chk_version_code(app::get($app_id));
    
        if(defined('CUSTOM_CORE_DIR') && file_exists(CUSTOM_CORE_DIR.'/'.app::get($app_id)->app_id.'/'.$this->new_xml)){
            $file = CUSTOM_CORE_DIR.'/'.app::get($app_id)->app_id.'/'.$this->new_xml;
        }else{
            $file = app::get($app_id)->app_dir.'/'.$this->new_xml;
        }
        
        if(file_exists($file)){
            //return filemtime($file);
            //todo: md5
            return md5_file($file);
        }else{
            return false;
        }
    }

}
