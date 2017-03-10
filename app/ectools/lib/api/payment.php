<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

/**
 * b2c order interactor with center
 * shopex team
 * dev@shopex.cn
 */
class ectools_api_payment
{
	/**
     * app object
     */
    public $app;

    /**
     * 构造方法
     * @param object app
     */
    public function __construct($app)
    {
        $this->app = $app;
        $this->objMath = kernel::single("ectools_math");
    }
	
	public function get_all($sdf)
	{
		$arr_payments = array();
		$obj_payments_service_all = kernel::servicelist('ectools_payment.ectools_mdl_payment_cfgs');
		foreach ($obj_payments_service_all as $obj)
		{
			switch ($obj->app_key)
			{
				case 'offline':
					$payout_type = 'offline';
					break;
				case 'deposit':
					$payout_type = 'deposit';
					break;
				default:
					$payout_type = 'online';
					break;
			}
			$strPayment = $this->app->getConf(get_class($obj));
			$arrPaymnet = unserialize($strPayment);
			
			if (isset($arrPaymnet['status']) && $arrPaymnet['status'] == 'true')
			{
				$arr_payments[$obj->app_key] = array(
					'payout_type'=>$payout_type,
					'payment_name'=>(isset($arrPaymnet['setting']['pay_name']) && $arrPaymnet['setting']['pay_name']) ? $arrPaymnet['setting']['pay_name'] : $obj->display_name,
					'payment_id'=>(isset($obj->app_rpc_key) && $obj->app_rpc_key) ? $obj->app_rpc_key : $obj->app_key,
				);
			}
		}		
		
		return $arr_payments;
	}
}