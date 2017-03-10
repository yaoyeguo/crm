<?php
class market_ctl_admin_exchange extends desktop_controller{

    var $workground = 'market.sales';

    public function index(){
        $url = kernel::base_url(1);
        $this->pagedata['url'] = $url;
        $this->page('admin/exchange.html');
    }
    
}