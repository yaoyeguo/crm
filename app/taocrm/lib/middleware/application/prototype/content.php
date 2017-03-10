<?php

class taocrm_middleware_application_prototype_content implements Iterator{
    protected $current;
    protected $path;
    protected $iterator = false;
    
    public function __construct($app = null) {
        if ($app) {
            $this->app = $app;
        }
    }
    
    public function detect($app, $current = null) {
         $this->iterator = null;
         $this->target_app = is_string($app) ? app::get($app) : $app;
         if ($current) {
             $this->set_current($current);
         }
         
         return $this;
    }
    
    function iterator(){
        if(!is_object($this->iterator)){
            $this->iterator = $this->init_iterator();
        }
        return $this->iterator;
    }
    
    public function set_current($key){
        $this->key = $key;
    }
    
    public function current() {
        return $this;
    }
    
    function filter(){
        return true;
    }
    
    public function rewind() {
        $this->iterator()->rewind();
    }
    
    public function key() {
        return $this->key;
    }
    
    public function next() {
        return $this->iterator()->next();
    }
    
    public function valid() {
        while($this->iterator()->valid()){
            if($this->prototype_filter()){
                return true;
            }else{
                $this->iterator()->next();
            }
        };
        return false;
    }
    
    function prototype_filter(){
        return $this->filter();
    }
}