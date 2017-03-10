<?php

class taocrm_ctl_admin_tips extends desktop_controller
{
    var $workground = 'tg.crm';
    protected static $redisConn = null;
    public function __construct($app){
        parent::__construct($app);
    }
    
    public function get_tips_info()
    {
        $key_prefix = $this->getKeyPrefix();
        $redis = $this->getRedisConn();
        $news_list['title'] = $redis->lRange($key_prefix.'title',0,-1);
        $news_list['start_date'] = $redis->lRange($key_prefix.'start_date',0,-1);
        $news_list['days'] = $redis->lRange($key_prefix.'days',0,-1);
        $news_list['content'] = $redis->lRange($key_prefix.'content',0,-1);
        $news_list['status'] = $redis->lRange($key_prefix.'status',0,-1);
        $news_list['select_product'] = $redis->lRange($key_prefix.'select_product',0,-1);
        $data = array();
        $i = 0;
        foreach ($news_list['status'] as $k => $v) {
            $days = $news_list['days'][$k];
            $start_date = $news_list['start_date'][$k];
            $status = $news_list['status'][$k];
            $selectProduct = $news_list['select_product'][$k];
            if ($status == 1 && strtotime("+$days days",strtotime($start_date))>=time() && $selectProduct == 'tgcrm') {
                $data[$i]['title'] = $news_list['title'][$k];
                $data[$i]['start_date'] = $news_list['start_date'][$k];
                $data[$i]['days'] = $news_list['days'][$k];
                $data[$i]['content'] = $news_list['content'][$k];
                $data[$i]['status'] = $news_list['status'][$k];
                $i++;
            }
        }
        return $data;
    }
    
    /**
     * 获取key_prefix前缀
     */
    protected function getKeyPrefix()
    {
        $key_prefix = 'tgcrm:tips:';
        return $key_prefix;
    }
    
    protected function getRedisConn()
    {
        if (self::$redisConn == null) {
            self::$redisConn = kernel::single('taocrm_service_redis')->redis;
        }
        return self::$redisConn;
    }
}
