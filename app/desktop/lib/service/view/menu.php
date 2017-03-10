<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class desktop_service_view_menu
{
    function function_menu()
    {
        //$html[] = "<a href='index.php?ctl=shoprelation&act=index&p[0]=apply'>网店邻居</a>";
        /*
        $systemType = kernel::single('taocrm_system')->getSystemType();
        $system_type = $systemType['system_type'];
        //$system_type = 2;
        //用租用户显示应用中心，按效果付费显示条款条件
        if($system_type == 2){
        $html[] = "<a target=\"dialog::{width:650,height:350,title:'条款条件'}\" href='index.php?app=market&ctl=admin_active&act=legal_copy'>".app::get('market')->_('条款条件')."</a>";
        }else{
        $html[] = "<a href='index.php?app=desktop&ctl=appmgr&act=index'>".app::get('desktop')->_('应用中心')."</a>";
        }
        */
        //$html[] = "<a href='index.php?app=desktop&ctl=appmgr&act=index'>".app::get('desktop')->_('应用中心')."</a>";
        if( ! strstr($_SERVER['SERVER_NAME'], 'mcrm.taoex.com')){
            //$html[] = "<a href='/index.php/taocrm/default/index/app/site' target='_blank'>". app::get('desktop')->_('兑换优惠券') . "</a>";
        }

        $html[] = "<a href='javascript:void();'>".CRM_VERSION."</a>";

        //$html[] = "<a href='index.php?app=market&ctl=admin_bind&act=bind' target=\"dialog::{width:576,height:400,title:'使用向导'}\" >".app::get('desktop')->_('使用向导')."</a>";
        //$html[] = "<a href='index.php?ctl=adminpanel'>".app::get('desktop')->_('控制面板')."</a>";
        $html[] = "<a href='index.php?app=desktop&ctl=default&act=alertpages&goto=".urlencode('index.php?app=desktop&ctl=recycle&act=index&nobuttion=1')."' target='_blank'>".app::get('desktop')->_('回收站')."</a>";
        $html[] = "<a href='index.php?ctl=dashboard&act=index'>".app::get('desktop')->_('桌面')."</a>";

        return $html;
    }
}