<?php

class taocrm_ctl_admin_wangwangchild extends desktop_controller
{
    public function index()
    {
        $title = '子旺旺号';
        $actions = array();
        $baseFilter = array();
        $this->finder('taocrm_mdl_wangwang_shop',array(
            'title'=> $title,
            'actions' => $actions,
            'base_filter'=>$baseFilter,
            'use_buildin_set_tag'=>false,
            'use_buildin_import'=>false,
            'use_buildin_export'=>false,
            'use_buildin_recycle'=>false,
            'use_buildin_filter'=>false,
            'use_buildin_tagedit'=>true,
        ));
    }
    
    public function script()
    {
        $wangwangShop = kernel::single("taocrm_wangwangjingling_shop");
        $message = $wangwangShop->run();
        echo $message;
    }
    
    public function script2()
    {
        $wangwangShopChatLog = kernel::single('taocrm_wangwangjingling_chat_log');
        $wangwangShopChatLog->run();
        echo "OK";
    }
}
