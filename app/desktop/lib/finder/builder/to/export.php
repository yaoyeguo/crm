<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class desktop_finder_builder_to_export extends desktop_finder_builder_prototype{


    function main(){
        $oIo = kernel::servicelist('desktop_io');
        foreach( $oIo as $aIo ){
            if( $aIo->io_type_name == ($_POST['_io_type']?$_POST['_io_type']:'csv') ){
                $oImportType = $aIo;
                break;
            }
        }
        unset($oIo);

        $oName = substr($this->object_name,strlen($this->app->app_id.'_mdl_'));
        $model = app::get($this->app->app_id)->model( $oName );
		$model->filter_use_like = true;
        $oImportType->init($model);
        $offset = 0;
        $data = array('name'=> $oName );
        if($_POST['view']){
            $_view = $this->get_views();
            if(count($this->get_views())){
                $view_filter = (array)$_view[$_POST['view']]['filter'];
                $_POST = array_merge($_POST,$view_filter);
            }
        }
        if( method_exists($model,'fgetlist_csv') ){
            while( $listFlag = $model->fgetlist_csv($data,$_POST,$offset,$_POST['_export_type']) ){
                $offset++;
				$oImportType->export( $data,$model,$_POST['_export_type'] );
            }			
        }else{
            while( $listFlag = $oImportType->fgetlist($data,$model,$_POST,$offset,$_POST['_export_type']) ){
                $offset++;
				$oImportType->export( $data,$model,$_POST['_export_type'] );
            }
        }
    }


}
