<?php
class openapi_mdl_sales extends ome_mdl_sales{

    var $has_many = array(
       'sales_items' => 'sales_items',
    );

    function __construct($app){
        parent::__construct(app::get('ome'));
    }

    public function table_name($real=false){
        $table_name = "sales";
        if($real){
            return kernel::database()->prefix.$this->app->app_id.'_'.$table_name;
        }else{
            return $table_name;
        }
    }

     function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderby=null){

        return parent::getList($cols, $filter, $offset, $limit, $orderby);

     }


}

?>