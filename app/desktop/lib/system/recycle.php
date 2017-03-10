<?php

class desktop_system_recycle {

    function dorecycle($mdl_name,$filter=null){
        $oRecycle = app::get('desktop')->model('recycle');
        list($app_id,$table) = explode('_mdl_',$mdl_name);
        $o = app::get($app_id)->model($table);


        $recycle_item = array();
        $recycle_item['drop_time'] = time();
        $recycle_item['item_type'] = $o->table_name();

        $dbschema = $o->get_schema();

        $textColumn = $dbschema['textColumn'];
        $pkey = $dbschema['idColumn'];

        foreach($dbschema['columns'] as $k=>$col){
            if($col['is_title']&&$col['sdfpath']){
                $textColumn = $col['sdfpath'];
                break;
            }
        }

        $rows = $o->getList('*',$filter,0,-1);

        if(method_exists($o, 'pre_recycle')){
            if(!$o->pre_recycle($rows)){
                return false;
            }
        }

        foreach($rows as $k=>$v){
            $pkey_value = $v[$pkey];

            if(!$o->no_recycle){
                $v = $o->dump($v[$pkey],'*','delete');
                $recycle_item['item_sdf'] = $v;
                $recycle_item['app_key'] = $app_id;
                $recycle_item['item_title'] = $v[$textColumn];
                if(method_exists($o,'title_recycle'))
                $recycle_item['item_title'] = $o->title_recycle($v);
                $tmp = $recycle_item;
                $return = $oRecycle->save($tmp);
                unset($tmp[$pkey]);
            }
            $o->delete(array($pkey=>$pkey_value));
        }

        if(method_exists($o, 'suf_recycle')){
            if(!$o->suf_recycle($_POST)){
                return false;
            }
        }

        $services = kernel::serviceList('desktop_finder_callback.' . get_class($o));
        foreach($services AS $service){
            if(method_exists($service, 'recycle')){
                $service->recycle($_POST);
            }
        }
        return true;
    }
}
