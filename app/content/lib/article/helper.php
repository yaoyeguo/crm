<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
/**
* 加载头部尾部类
*/
class content_article_helper 
{

	/**
	* 头部
	*/
    function function_header(){
        $ret='<base href="'.kernel::base_url(1).'"/>';
        $path = app::get('site')->res_url;
        
		$css_min = (defined('DEBUG_CSS') && constant('DEBUG_CSS')) ? '' : '_min';
        $ret.= '<link rel="stylesheet" href="'.$path.'/framework'.$css_min.'.css" type="text/css" />';
        $ret.='<link rel="stylesheet" href="'.$path.'/widgets_edit'.$css_min.'.css" type="text/css" />';
        $ret.= kernel::single('base_component_ui')->lang_script(array('src'=>'lang.js', 'app'=>'site'));
        if(defined('DEBUG_JS') && constant('DEBUG_JS')){
        $ret.= '<script src="'.$path.'/js/mootools.js"></script>
                <script src="'.$path.'/js/moomore.js"></script>
                <script src="'.$path.'/js/jstools.js"></script>
                <script src="'.$path.'/js/switchable.js"></script>
                <script src="'.$path.'/js/dragdropplus.js"></script>
                <script src="'.$path.'/js/shopwidgets.js"></script>';
        }else{
            $ret.= '<script src="'.$path.'/js_mini/moo_min.js"></script>
                <script src="'.$path.'/js_mini/shopwidgets_min.js"></script>';
        }
        foreach(kernel::serviceList('site_theme_view_helper') AS $service){
            if(method_exists($service, 'function_header')){
                $ret .= $service->function_header();
            }
        }
        return $ret;
    }
	
	/**
	* 尾部
	*/
    function function_footer(){
       return "<div id='drag_operate_box' class='drag_operate_box' style='visibility:hidden;'>
       <div class='drag_handle_box'>
             <table cellpadding='0' cellspacing='0' width='100%'>
                                           <tr>
                                           <td><span class='dhb_title'>".app::get('content')->_('标题')."</span></td>
                                           <td width='40'><span class='dhb_edit'>".app::get('content')->_('编辑')."</span></td>
                                           <td width='40'><span class='dhb_del'>".app::get('content')->_('删除')."</span></td>
                                           </tr>
              </table>
              </div>
          </div>

          <div id='drag_ghost_box' class='drag_ghost_box' style='visibility:hidden'>

          </div>";
    }

}//End Class
