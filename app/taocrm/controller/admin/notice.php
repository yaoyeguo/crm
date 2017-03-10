<?php
class taocrm_ctl_admin_notice extends desktop_controller{

	var $workground = 'tgcrm.host';

	public function __construct($app){
    
		parent::__construct($app);
	}

	public function index(){
		
        $key_prefix = 'tgcrm:notice:';
        $redis = kernel::single('taocrm_service_redis')->redis;
        
        $news_list['title'] = $redis->lRange($key_prefix.'title',0,-1);
        $news_list['start_date'] = $redis->lRange($key_prefix.'start_date',0,-1);
        $news_list['days'] = $redis->lRange($key_prefix.'days',0,-1);
        $news_list['content'] = $redis->lRange($key_prefix.'content',0,-1);
        $news_list['status'] = $redis->lRange($key_prefix.'status',0,-1);
        
        foreach($news_list['title'] as $k=>$v){
            $days = $news_list['days'][$k];
            $start_date = $news_list['start_date'][$k];
            $status = $news_list['status'][$k];
            
            if($status==1 && strtotime("+$days days",strtotime($start_date))>=time()){
                $news['id'] = $k;
                $news['title'] = $news_list['title'][$k];
                $news['start_date'] = $news_list['start_date'][$k];
                $news['days'] = $news_list['days'][$k];
                $news['content'] = $news_list['content'][$k];
                $news['status'] = $news_list['status'][$k];
                
                $news['content'] = str_replace("\n",'<br/>',$news['content']);
                $this->set_hits($k);
                break;
            }
        }

        $this->pagedata['news'] = $news;
        $this->display('admin/notice.html');
	}
    
    public function set_hits($id){
        $key_prefix = 'tgcrm:notice:';
        $redis = kernel::single('taocrm_service_redis')->redis;
        $redis->sAdd($key_prefix.'ahits:'.$id,$_SERVER['HTTP_HOST']);
        $redis->sMembers($key_prefix.'ahits:'.$id);
    }
    
    public function get_notice_id(){
		
        $key_prefix = 'tgcrm:notice:';
        $redis = kernel::single('taocrm_service_redis')->redis;
        
        $news_list['title'] = $redis->lRange($key_prefix.'title',0,-1);
        $news_list['start_date'] = $redis->lRange($key_prefix.'start_date',0,-1);
        $news_list['days'] = $redis->lRange($key_prefix.'days',0,-1);
        $news_list['status'] = $redis->lRange($key_prefix.'status',0,-1);
        
        foreach($news_list['title'] as $k=>$v){
            $days = $news_list['days'][$k];
            $start_date = $news_list['start_date'][$k];
            $status = $news_list['status'][$k];
            
            if($status==1 && strtotime("+$days days",strtotime($start_date))>=time()){
                return $k;
            }
        }
        return '';
	}

}


