<?php

class taocrm_middleware_application_prototype_filepath extends taocrm_middleware_application_prototype_content
{
    var $current;
    var $path;
    private $_mtime = 0;

    function init_iterator(){
        if(is_dir($this->target_app->app_dir.'/'.$this->path)){
            if(defined('CUSTOM_CORE_DIR') && is_dir(CUSTOM_CORE_DIR.'/'.$this->target_app->app_id.'/'.$this->path)){
                 $this->_mtime = filemtime(CUSTOM_CORE_DIR.'/'.$this->target_app->app_id.'/'.$this->path);
                 return new DirectoryIterator(CUSTOM_CORE_DIR.'/'.$this->target_app->app_id.'/'.$this->path);
            }else{
                 $this->_mtime = filemtime($this->target_app->app_dir.'/'.$this->path);
                 return new DirectoryIterator($this->target_app->app_dir.'/'.$this->path);
            }
            
        }else{
            return new ArrayIterator(array());
        }
    }
}