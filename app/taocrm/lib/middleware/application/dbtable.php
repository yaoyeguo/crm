<?php

class taocrm_middleware_application_dbtable extends taocrm_middleware_application_prototype_filepath
{
    public $path = 'middlewareshema';
    public $_define = null;
    static $__type_define = array();
    
    public function __construct($app = null) {
        parent::__construct($app);
    }
    
    function &load($check_lastmodified = true) {
        $real_table_name = $this->key();
        if ($this->_define[$real_table_name]) {
            return $this->_define[$real_table_name];
        }
        
//        if(kernel::is_online() && !($this->target_app->app_id=='base' && $this->key()=='kvstore')){
//            if(defined('CUSTOM_CORE_DIR') && file_exists(CUSTOM_CORE_DIR.'/'.$this->target_app->app_id.'/mongoschema/'.$this->key.'.php')){
//                 $define_lastmodified = ($check_lastmodified) ? filemtime(CUSTOM_CORE_DIR.'/'.$this->target_app->app_id.'/mongoschema/'.$this->key.'.php') : null;
//            }else{
//                 $define_lastmodified = ($check_lastmodified) ? filemtime($this->target_app->app_dir.'/mongoschema/'.$this->key.'.php') : null;
//            }
//            $define_flag = base_kvstore::instance('tbdefine')->fetch($this->target_app->app_id.$this->key, $define, $define_lastmodified);
//        }else{
//            $define_flag = false;
//        }

        $define_flag = false;
        if ($define_flag === false) {
            if (defined('CUSTOM_CORE_DIR') && file_exists(CUSTOM_CORE_DIR . '/' . $this->target_app->app_id . '/middlewareshema/'.$this->key.'.php')) {
                require(CUSTOM_CORE_DIR.'/'.$this->target_app->app_id.'/middlewareshema/'.$this->key.'.php');
            }
            else {
                require($this->target_app->app_dir . '/middlewareshema/' . $this->key . '.php');
            }
            $define = &$db[$this->key()];
            $this->_define[$real_table_name] = &$define;
            foreach ($define['columns'] as $k => $v) {
                if ($v['pkey']) {
                    $define['idColumn'][$k] = $k;
                }
                
                if ($v['is_title']) {
                    $define['textColumn'][$k] = $k;
                }
                
                if ($v['in_list']) {
                    $define['in_list'][] = $k;
                    if ($v['default_in_list']) {
                        $define['default_in_list'][] = $k;
                    }
                }
                
                $define['columns'][$k] = $this->_prepare_column($k, $v);
                
                if (isset($v['pkey']) && $v['pkey']) {
                    $define['pkeys'][$k] = $k;
                }
            }
            
            if (!$define['idColumn']) {
                $define['idColumn'] = key($define['columns']);
            }
            elseif(count($define['idColumn'])==1) {
                $define['idColumn'] = current($define['idColumn']);
            }
            
            if (!$define['textColumn']) {
                $keys = array_keys($define['columns']);
                $define['textColumn'] = $keys[1];
            }
            elseif(count($define['idColumn'])==1) {
                $define['textColumn'] = current($define['textColumn']);
            }
            
//            if(kernel::is_online() && !($this->target_app->app_id=='base' && $this->key()=='kvstore')){
//                base_kvstore::instance('tbdefine')->store($this->target_app->app_id.$this->key,$define);
//            }
        }
        return $define;
    }
    
    
    function _prepare_column($col_name, $col_set){
        $col_set['realtype'] = $col_set['type'];
        if(is_array($col_set['type'])){
            $col_set['realtype'] = 'enum(\''.implode('\',\'',array_keys($col_set['type'])).'\')';
        }elseif(substr($col_set['type'],0,6)=='table:'){
            list(,$tablename,$column) = explode(':',$col_set['type']);
            if($p=strpos($tablename,'@')){
                $app = substr($tablename,$p+1);
                $tablename = substr($tablename,0,$p);
            }else{
                $app = $this->target_app;
            }

            $table = new taocrm_middleware_application_dbtable;
            $def = $table->detect($app,$tablename)->load();

            if(!$column){
                $pkeyfounded = false;
                foreach($def['columns'] as $cn=>$ci){
                    if($ci['pkey']){
                        $column = $cn;
                        $pkeyfounded = true;
                        break;
                    }
                }
                if(!$pkeyfounded){
                    $column = key($def['columns']);
                }
            }
            if($col_set['pkey'] !== true){
                $define = &$this->load();
                $define['index']['idx_c_'.$col_name] = array('columns'=>array($col_name));
            }
            $col_set['realtype'] = $def['columns'][$column]['realtype'];
        }elseif($this->type_define($col_set['type'])){
            $col_set['realtype'] = $this->type_define($col_set['type']);
        }

        if(substr(trim($col_set['realtype']),-4,4)=='text'){
            unset($col_set['default']);
        }else{
            //int
            $col_set['realtype'] = str_replace('integer','int',$col_set['realtype']);
            if(false===strpos($col_set['realtype'],'(')){
                $int_length = 0;
                if(false!==strpos($col_set['realtype'],'tinyint')){
                    $int_length = 4;
                }elseif(false!==strpos($col_set['realtype'],'smallint')){
                    $int_length = 6;
                }elseif(false!==strpos($col_set['realtype'],'mediumint')){
                    $int_length = 9;
                }elseif(false!==strpos($col_set['realtype'],'bigint')){
                    $int_length = 20;
                }elseif(false!==strpos($col_set['realtype'],'int')){
                    $int_length = 11;
                }
                if($int_length){
                    if($int_length<20 && false!==strpos($col_set['realtype'],'unsigned')){
                        $int_length--;
                    }
                    $col_set['realtype'] = str_replace('int','int('.$int_length.')',$col_set['realtype']);
                }
            }
        }
        return $col_set;
    }
    
    function type_define($type){
        if(!self::$__type_define){
            if(defined('CUSTOM_CORE_DIR') && file_exists(CUSTOM_CORE_DIR.'/base/datatypes.php')){
                 require(CUSTOM_CORE_DIR.'/base/datatypes.php');
            }else{
                 require(APP_DIR.'/base/datatypes.php');
            }

            $types = array();
            foreach($datatypes as $k=>$v){
                if($v['sql']){
                    $types[$k] = $v['sql'];
                }
            }
            self::$__type_define = &$types;
        }
        return isset(self::$__type_define[$type])?self::$__type_define[$type]:false;
    }
}