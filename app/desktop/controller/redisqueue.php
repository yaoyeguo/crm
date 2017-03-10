<?php

class desktop_ctl_redisqueue extends desktop_controller{
	var $workground = 'sys.config';


	public function __construct($app){
		parent::__construct($app);
	}

	public function queue($type){
		$type = 'tgcrm';
		$len_normal_queue = kernel::single('taocrm_service_redis')->redis->llen($type.':SYS_NORMAL_QUEUE');
		$len_realtime_queue = kernel::single('taocrm_service_redis')->redis->llen($type.':SYS_REALTIME_QUEUE');
		$host = $_SERVER['SERVER_NAME'];
		$waiting_queue[$host] = kernel::single('taocrm_service_redis')->redis->llen($type.':'.$host.':queue');


		$len_orders_queue = kernel::single('taocrm_service_redis')->redis->llen($type.':SYS_ORDER_QUEUE');
		$len_cstools_orders_queue = kernel::single('taocrm_service_redis')->redis->llen($type.':SYS_CSTOOLS_ORDER_QUEUE');

		$this->pagedata['len_orders_queue']=$len_orders_queue;
		$this->pagedata['len_cstools_orders_queue']=$len_cstools_orders_queue;
		$this->pagedata['len_normal_queue']=$len_normal_queue;
		$this->pagedata['len_realtime_queue']=$len_realtime_queue;
		$this->pagedata['waiting_queue']=$waiting_queue;


		$this->pagedata['type'] = $type;
		$this->page('system/redisqueue.html');
	}

}


