<?php
class market_ctl_site_shop_search extends base_controller{

    var $default_label = '请选择';
    
    function __construct($app){
        parent::__construct($app);
    }

    /*
     * 线下门店查询
     */
    public function index()
    {
        $this->filter = array();
        $p_state = str_replace($this->default_label,'',$_GET['p_state']);
        $p_city = str_replace($this->default_label,'',$_GET['p_city']);
        $p_area = str_replace($this->default_label,'',$_GET['p_area']);
        $my_xy = trim($_GET['my_xy']);
        
        $this->get_area();
    
        $mdl = $this->app->model('wx_store_subbranch');
        $shops = $mdl->getList('*', $this->filter, 0, 20);
        foreach($shops as &$v){
            $store_area = $v['store_area'];
            preg_match("/:(.+?):/", $store_area, $store_area);
            $v['store_area'] = $store_area[1];
        }

        $this->pagedata['shops'] = $shops;
        $this->pagedata['my_xy'] = $my_xy;
        $this->display('site/shop/list.html');
    }
    
    public function get_area()
    {
        $p_state = str_replace($this->default_label,'',$_GET['p_state']);
        $p_city = str_replace($this->default_label,'',$_GET['p_city']);
        $p_area = str_replace($this->default_label,'',$_GET['p_area']);
        
        if($p_state) $this->filter['store_area|head'] = 'mainland:'.$p_state;
        if($p_state && $p_city) $this->filter['store_area|head'] .= '/'.$p_city;
        if($p_state && $p_city && $p_area) $this->filter['store_area|head'] .= '/'.$p_area;
        
        if($p_state) $city[] = $this->default_label;
        if($p_city) $area[] = $this->default_label;
        
        $sql = "select store_area from sdb_market_wx_store_subbranch group by store_area";
        $rs = $this->app->model('wx_store_subbranch')->db->select($sql);
        if($rs){
            foreach($rs as $v){
                $store_area = $v['store_area'];
                preg_match("/:(.+?):/", $store_area, $store_area);
                $store_area = explode('/', $store_area[1]);
                $state[] = $store_area[0];
                
                if($p_state && $p_state==$store_area[0]){
                    $city[] = $store_area[1];
                    
                    if($p_city==$store_area[1]){
                        $area[] = $store_area[2];
                    }
                }                
            }
        }
        
        $this->pagedata['p_state'] = $p_state;
        $this->pagedata['p_city'] = $p_city;
        $this->pagedata['p_area'] = $p_area;
        
        $this->pagedata['state'] = $state;
        $this->pagedata['city'] = $city;
        $this->pagedata['area'] = $area;
    }
    
}
