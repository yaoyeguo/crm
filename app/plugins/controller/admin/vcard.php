<?php 

class plugins_ctl_admin_vcard extends desktop_controller{

    var $workground = 'plugins.vcard';
    
    public function __construct($app)
    {
        parent::__construct($app);
    }

    public function index()
    {
        if(!$_GET['view']) $_GET['view'] = 0;
        
        //将店铺信息同步到vcard表
        $this->init_vcard_shops();
    
        $this->finder('ecorder_mdl_shop_vcard',array(
            'title'=>'店铺名片',
            'use_buildin_set_tag'=>false,
            'use_buildin_import'=>false,
            'use_buildin_export'=>false,
            'use_buildin_recycle'=>false,
            'use_buildin_filter'=>false,
            'use_buildin_tagedit'=>true,
            'use_view_tab'=>false,
        ));
    }
    
    //将店铺信息同步到vcard表
    public function init_vcard_shops()
    { 
        $oShopVcard = &app::get('ecorder')->model('shop_vcard');
        $sql = "select * from sdb_ecorder_shop where shop_id not in (
            select shop_id from sdb_ecorder_shop_vcard
        )";
        $rs = $oShopVcard->db->select($sql);
        if( ! $rs) $rs = array();
        foreach($rs as $v){
            $v['vcard_id'] = 0;
            $v['nick'] = $v['name'];
            $v['address'] = $v['addr'];
            $v['company'] = $v['name'];
            $oShopVcard->save($v);
        }
    } 
}

