<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class desktop_finder_builder_filter2packet extends desktop_finder_builder_prototype{

    function main(){
        $render = app::get('desktop')->render();
        $render->pagedata['app'] = $_GET['app'];
        $render->pagedata['act'] = $_GET['act'];
        $render->pagedata['ctl'] = $_GET['ctl'];
        $render->pagedata['model'] = $this->object_name;
        
        
        $filterquery = $_POST['filterquery'];
        $tabs = $this->get_views();
        if($tabs&&$_GET['view']){
            $filterquery = $filterquery.'&'.http_build_query($tabs[$_GET['view']]['filter']);
        }
        
        //附加额外的过滤条件
        $addon_filter = array('shop_id','filter_type','order_status','date','date_from','date_to','area','hours','relation','goods_a','goods_b','count_by','member_status');
        foreach($addon_filter as $v){
            if($_GET[$v]) $filterquery .= "&$v=".$_GET[$v];
        }
        
        $render->pagedata['filterquery'] = $filterquery;
        echo $render->fetch('finder/view/filter2packet.html');
    }
}