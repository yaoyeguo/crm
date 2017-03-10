<?php
/**
 * ShopEx
 *
 * @author Tian Xingang
 * @email ttian20@gmail.com
 * @copyright 2003-2011 Shanghai ShopEx Network Tech. Co., Ltd.
 * @website http://www.shopex.cn/
 *
 */
 
class taocrm_finder_sms_log {

    var $detail_sms_content = '短信内容';
    public function detail_sms_content($id)
    {
        $app = app::get('taocrm');
        
        $sms = $app->model('sms_log')->dump($id);
        $sms['create_time'] = date('Y-m-d H:i:s', $sms['create_time']);
        $sms['sms_len'] = mb_strlen($sms['content'], 'utf-8');
        $sms['sms_size'] = ceil($sms['sms_len']/67);
        
        $render = $app->render();
        $render->pagedata['sms'] = $sms;
        return $render->fetch('admin/sms/detail.html');
    }
    
} 