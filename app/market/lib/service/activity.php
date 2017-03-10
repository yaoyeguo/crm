<?php
class market_service_activity{

    /**
     * 对应店铺信息
     * @var Array
     */
    protected $_shopInfo = array();

    function __construct(){
        $this->app = app::get('market');
    }

     

    /**
     * 活动添加与更新
     *
     * @access private
     * @param array $activityInfo 客户信息
     * @param string $shopId 店铺ID
     * @return int 客户ID
     */
    public function saveActivity($activityInfo) {

        if (empty($activityInfo)) {
            return null;
        }

        //获取店铺信息并做检查
        // $this->_shopInfo = $this->fetchShopInfo($shopId);


        $activityDetail = array();
        $activityId = null;
        /*$structs = array(
         'active_id'=> 'active_id',
         'active_name' => 'active_name',
         'filter_mem' => 'filter_mem',
         'shop_id' => 'shop_id',
         'type' => 'type',
         'total_num' => 'total_num',
         'valid_num' => 'valid_num',
         'activity_num' => 'activity_num',
         'is_activity' => 'is_activity',
         'template_id' => 'template_id',
         'create_time' => 'create_time',
         'end_time' => 'end_time',
         'exec_time' => 'exec_time',
         'remark' => 'remark',
         'cost' => 'cost',
         'tags' => 'tags',
         );*/
        $structs = app::get('market')->model('active')->get_structs();

        //增加客户
        $activitysData = utils::structToArray($structs,$activityInfo);
        $activitysData['create_time'] = time();
        if($this->app->model('active')->save($activitysData)){
            $activityId = $activitysData['active_id'];
        }

        return $activityId;
    }


    public function requestActivity($couponId,& $msg){
        $coupon = $this->app->model('coupons')->dump($couponId,'coupon_id,active_id,status,shop_id,coupon_count,outer_coupon_id,person_limit_count');
        $rpcobj = kernel::single('market_rpc_request_activity');
        $msg = $rpcobj->add($coupon);
        if($msg == 'success'){
            return true;
        }else{
            return false;
        }
    }

    public function assess(){
        $jobarray = array();
        return kernel::single('taocrm_service_queue')->addJob('market_backstage_activity@assess',$jobarray);
    }

    function clean(){
        $db = kernel::database();
        //完成队列归档
        $sql = 'INSERT INTO sdb_market_activity_m_queue_finished select * from sdb_market_activity_m_queue where is_send_finish = 1';
        $db->exec($sql);

        //删除完成队列
        $sql = 'delete from sdb_market_activity_m_queue where is_send_finish = 1';
        $db->exec($sql);

        //删除15天未执行的发送队列
        $day = time() - (15 * 86400);
        $sql = 'SELECT active_id FROM `sdb_market_active` WHERE is_active != "finish" and create_time < '.$day;
        $active_list = $db->select($sql);
        foreach($active_list as $active){
            $sql = 'delete from sdb_market_activity_m_queue where active_id = '.$active['active_id'];
            //echo $sql."\n";
            $db->exec($sql);
        }

        //更新短信发送状态，把一天前发送中的改为发送成功
        $day = time() - 86400;
        $sql = 'update sdb_market_sms set is_send="succ" where is_send="sending" and create_time <'.$day;
        $db->exec($sql);
        //优化表
        //$sql = 'OPTIMIZE TABLE `sdb_market_activity_m_queue`';
        //$db->exec($sql);
    }
}


