<?php
/**
 * 队列
 * 
 * @author hzjsq@msn.com
 * @version 0.1b
 */
 
class ecorder_rpc_queue extends ecorder_redis_redis {
	
	/**
	 * 队列KEY
	 * @var String
	 */
	const __KEY = '_ORDER_QUEUE';
	
	/**
	 * 服务器IP地址
	 * @var String
	 */
	const __HOST = '112.125.109.89';
	
	/**
	 * 服务器端口
	 * @var String
	 */
	const __PORT = '6379';
	
	/**
	 * 析构
	 */
	public function __construct() {
		
		parent::__construct(self::__HOST, self::__PORT);	
	}
	
	/**
	 * 加入队列
	 * 
	 * @param mixed $value 增加的值
	 * @return void
	 */
	public function push($value) {

		$this->append(self::__KEY, $value);	
	}
	
	/**
	 * 获取要操作值
	 * 
	 * @param void
	 * @return mixed
	 */
	public function pop() {
		
		return $this->lpop(self::__KEY);
	}
}	