<?php

class taocrm_middleware_model implements taocrm_interface_middleware_model
{
    protected $db = null;
    
    public function __construct()
    {
        $this->db = kernel::single('taocrm_middleware_connect');
        if ($this->searchOptionsOpen) {
            $this->schema = $this->get_schema();
        }
        
        if ($this->schema) {
            $this->metaColumn = $this->schema['metaColumn'];
            $this->idColumn = $this->schema['idColumn'];
            $this->textColumn = $this->schema['textColumn'];
            $this->skipModifiedMark = ($this->schema['ignore_cache']===true) ? true : false;
            if(!is_array($this->idColumn) && array_key_exists('extra', $this->schema['columns'][$this->idColumn])){
                $this->idColumnExtra = $this->schema['columns'][$this->idColumn]['extra'];
            }
        }
    }
    
    public function getList($cols = '*', $filter = array(), $offset = 0, $limit = -1, $orderby = null)
    {
        $table_name = $this->table_name();
        $orderType = $orderby ? $orderby : $this->defaultOrder;
        if ($this->_filter) {
            $filter = $this->_filter($filter);
        }
        if ($orderby == 'none') {
            $orderType = null;
        }
        $this->_plimit_unset($filter);
        $cursor = $this->db->getList($table_name, $filter, $offset, $limit, $orderType);

        
        return  $this->iterator_to_array($cursor);

    }
    
    public function count($filter = null)
    {
        $table_name = $this->table_name();
        $this->_plimit_unset($filter);
        if ($this->_filter) {
            $filter = $this->_filter($filter);
        }
        return $this->db->count($table_name, $filter);
    }
    
    public function get_schema()
    {
        $table = $this->table_name();
        if (!isset($this->__exists_schema[$this->app->app_id][$table])) {
            if (!isset($this->table_define)) {
                $this->table_define = new taocrm_middleware_application_dbtable;
            }
            $this->__exists_schema[$this->app->app_id][$table] = $this->table_define->detect($this->app, $table)->load(false);
        }
        
        return $this->__exists_schema[$this->app->app_id][$table];
    }
    
    public function table_name($real = null)
    {
        if ($this->table_name) {
            return $this->table_name;
        }
        $class_name = get_class($this);
        $app_id = substr($class_name, 0, strpos($class_name, '_mdl_'));
        $p = strpos($class_name, '_mdl_');
        $app_id_len = strlen($app_id);
        $this->app = app::get($app_id);
        $table_name = substr($class_name, $p + 5);
        return $table_name;
    }
    
    public function _filter($filter)
    {
        return $filter;
    }
    
    public function iterator_to_array($cursor) {
        $array = array();
        $i = 0;
        foreach ($cursor as $data) {
            $array[$i++] = $data;
        }
        unset($i);
        return $array;
    }
    
    protected function _columns(){
        $schema = new taocrm_middleware_application_dbtable;
        $dbinfo = $schema->detect($this->app,$this->table_name())->load();
        return (array)$dbinfo['columns'];
    }
    
    public function searchOptions(){
        if (empty($this->searchOptionsOpen)) {
            return '';
        }
        $columns = array();
        foreach($this->_columns() as $k=>$v){
            if(isset($v['searchtype']) && $v['searchtype']){
                $columns[$k] = $v['label'];
            }
        }
        return $columns;
    }
    
    protected function _plimit_unset(&$filter) {
        if (isset($_POST['plimit']) && $_POST['plimit']) {
            unset($filter['plimit']);
        }
    }
}
