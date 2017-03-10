<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

class ectools_payment_api
{
	// 应用对象的实例。
	private $app;
	
	public function __construct($app)
	{
		$this->app = $app;
	}
	
	/**
	 * 支付返回后的同意支付处理
	 * @params array - 页面参数
	 * @return null
	 */
	public function parse($params='')
	{		
		// 取到内部系统参数
		$pathInfo = substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], "parse/") + 6); 
		$objShopApp = $this->getAppName($pathInfo);
		$innerArgs = explode('/', $pathInfo);
		$class_name = array_shift($innerArgs);
		$class_name = array_shift($innerArgs);
		$method = array_shift($innerArgs);
		
		$arrStr = array();
		$arrSplits = array();
		$arrQueryStrs = array();
		// QUERY_STRING
		if (isset($innerArgs) && $innerArgs)
		{
			$querystring = array_shift($innerArgs);
			if ($querystring)
			{
				$querystring = substr($querystring, 1);
				$arrStr = explode("&", $querystring);
				
				foreach ($arrStr as $str)
				{
					$arrSplits = explode("=", $str);
					$arrQueryStrs[urldecode($arrSplits[0])] = urldecode($arrSplits[1]);
				}
			}
			else
			{
				if ($_POST)
				{
					$arrQueryStrs = $_POST;
				}
			}
		}
		
		$payments_bill = new $class_name($objShopApp);
		$ret = $payments_bill->$method($arrQueryStrs);
		// 支付结束，回调服务.
		if ($ret['status'] == 'succ' || $ret['status'] == 'progress')
		{
			$obj_payments = app::get('ectools')->model('payments');
			$sdf = $obj_payments->dump($ret['payment_id'], '*', '*');
			if ($sdf['status'] != 'succ' && $sdf['status'] != 'progress')
			{
				$sdf['account'] = $ret['account'];
				$sdf['bank'] = $ret['bank'];
				$sdf['pay_account'] = $ret['pay_account'];
				$sdf['currency'] = $ret['currency'];
				$sdf['trade_no'] = $ret['trade_no'];
				$sdf['t_payed'] = $ret['t_payed'];
				$sdf['pay_app_id'] = $ret['pay_app_id'];
				$sdf['pay_type'] = $ret['pay_type'];			
				$sdf['memo'] = $ret['memo'];
				
				$is_updated = false;
				$obj_payment_update = kernel::single('ectools_payment_update');
				$is_updated = $obj_payment_update->generate($ret, $msg);
								
				$obj_pay_lists = kernel::servicelist("order.pay_finish");
				foreach ($obj_pay_lists as $order_pay_service_object)
				{
					$class_name = get_class($order_pay_service_object);
					
					// 防止重复充值
					if ($is_updated)
					{
						if ($ret['status'] == 'succ' || $ret['status'] == 'progress')
						{
							$db = kernel::database();
							$transaction_status = $db->beginTransaction();
							$is_updated = $order_pay_service_object->order_pay_finish($sdf, $ret['status'], 'font',$msg);
							if (!$is_updated)
							{
								kernel::log(app::get('ectools')->_('支付失败') . " " . $msg ."\n");
								$db->rollback();
							}
							else
							{
								$db->commit($transaction_status);
								// 支付扩展事宜 - 如果上面与中心没有发生交互，那么此处会发出和中心交互事宜.
								if (method_exists($order_pay_service_object, 'order_pay_finish_extends'))
									$order_pay_service_object->order_pay_finish_extends($sdf);
							}
						}
						else
						{
							echo 'succ';
						}					
					}
				}
			}
			// Redirect page.
			if ($sdf['return_url'])
			{                
				header('Location: '.strtolower(kernel::request()->get_schema().'://'.kernel::request()->get_host()).$sdf['return_url']);
			}
		}else{
			if (!isset($ret['status']) || $ret['status'] == '') $ret['status'] = 'failed';
			$obj_payments = app::get('ectools')->model('payments');
			$sdf = $obj_payments->dump($ret['payment_id'], '*', '*');
			$sdf['account'] = $ret['account'];
			$sdf['bank'] = $ret['bank'];
			$sdf['pay_account'] = $ret['pay_account'];
			$sdf['currency'] = $ret['currency'];
			$sdf['trade_no'] = $ret['trade_no'];
			$sdf['t_payed'] = $ret['t_payed'];
			$sdf['pay_app_id'] = $ret['pay_app_id'];
			$sdf['pay_type'] = $ret['pay_type'];			
			$sdf['memo'] = $ret['memo'];
			
			if ($ret['status'] == 'failed' && (!isset($ret['pdt_status']) || $ret['pdt_status'] == 'failed'))
			{			
				$is_updated = false;
				$obj_payment_update = kernel::single('ectools_payment_update');
				$is_updated = $obj_payment_update->generate($ret, $msg);
			}
			elseif(isset($ret['pdt_status']) && $ret['pdt_status'] == 'true')
			{
				
			}
			
			// Redirect page.
			if ($sdf['return_url'])
			{ 
				header('Location: '.strtolower(kernel::request()->get_schema().'://'.kernel::request()->get_host()).$sdf['return_url']);
			}
		}
	}
	
	/** 
	 * 得到实例应用名
	 * @params string - 请求的url
	 * @return object - 应用实例
	 */
	private function getAppName($strUrl='')
	{
		//todo.
		if (strpos($strUrl, '/') !== false)
		{
			$arrUrl = explode('/', $strUrl);
		}
		return app::get($arrUrl[0]);
	}
}
