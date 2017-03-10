<?php
class ecorder_ctl_admin_help_support extends desktop_controller{

    var $name = "服务支持";
    var $workground = "setting_tools";

    function index()
    {
        $this->page("admin/help/support.html");
    }
    
    function ajax_get_bbs_list()
    {
        $bbs_url = 'http://pingjia.taoex.com/';
        $bbs_list = '';
        
        $httpclient = new base_httpclient();
        $html = $httpclient->get($bbs_url.'bbslist.asp?zid=5/305546885');
        //var_dump($html);
        
         preg_match_all("/bbsview\.asp\?id=(.+?)\"/i",$html,$bbs_list_id);
         preg_match_all("/ title=\"(.+?)\"> <span/i",$html,$bbs_list_title);
         
         foreach($bbs_list_id[1] as $k=>$v){
            if($k>=8) break;
            $id = $v;
            $title = iconv('GBK','UTF-8//IGNORE',$bbs_list_title[1][$k]);
            $bbs_list .= "<li>· <a href='{$bbs_url}bbsview.asp?id={$id}' target='_blank'>{$title}</li>";
         }
         
         $bbs_list = "<ul>{$bbs_list}</ul>";
         echo($bbs_list);
    }
    
    //检测是否开启分销功能
    public function fx_support()
    {
        $this->page('admin/help/fx_support.html');
    }
}
