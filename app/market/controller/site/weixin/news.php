<?php
class market_ctl_site_weixin_news extends base_controller{

    function __construct($app){
        parent::__construct($app);
    }

    public function index(){
        $data = $_GET;
        $modelWxNews=app::get('market')->model("wx_news");
        $news = $modelWxNews->dump($data['id']);
        $news_info = json_decode($news['news_info'],true);
        $newsItem = array();
        if(isset($data['i'])){
            $newsItem = $news_info[$data['i']];
        }else{
            $newsItem = $news_info;
        }
        $this->pagedata['newsItem'] = $newsItem;
        $this->display('site/weixin/news.html');
    }

    public function store_map(){
        $id = !empty($_GET['id']) ? intval($_GET['id']) : false;

        $render = app::get('market')->render();

        if(!$id)
        {
            $render->pagedata['info'] = false;
        }else
        {
            $mod_obj = &app::get('market')->model('wx_store_subbranch');
            $info = $mod_obj->dump($id);
            $info['my_xy'] = !empty($_GET['my_xy']) ? trim($_GET['my_xy']) : false;
            $info['map'] = !empty($info['map_x']) ? $info['map_x'].','. $info['map_y'] : '';
            $info['city'] = explode('/',$info['store_area']);
            $info['city'] = $info['city'][1];

            $render->pagedata['info'] = $info;
        }
        $this->display('site/weixin/store_map.html');
    }

}
