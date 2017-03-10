<?php
class market_ctl_admin_exchange_items extends desktop_controller{
    var $workground = 'market.sales';

    public function index(){
        $this->finder('market_mdl_exchange_items',
            array(
                'title' => '可兑换物品',
                'use_buildin_export' => false,
                'use_buildin_recycle' => false,
                'use_buildin_set_tag' => false,
                'use_buildin_tagedit' => false,
                'orderBy' => 'log_id desc',
            )
        );
    }
    
	public function save(){
        $this->begin();
        $items = $_POST;
        $item_id = intval($items['item_id']);
        unset($items['item_id']);
        
        $items['create_time'] = time();
        $items['op_user'] = kernel::single('desktop_user')->get_name();
        $items['user_ip'] = $_SERVER['REMOTE_ADDR'];
        $items['end_time'] = strtotime($items['end_time']);
        
        if($item_id > 0){
            $this->app->model('exchange_items')->update($items,array('item_id'=>$item_id));
        }else{
            $this->app->model('exchange_items')->insert($items);            
        }
        
        if($items['item_type']=='coupon'){
            $sql = 'update sdb_market_coupons set is_exchange="'.$items['is_active'].'" where coupon_id='.$items['relate_id'];kernel::database()->exec($sql);            
        }

		$this->end(true,'保存成功');
	}
	
}
