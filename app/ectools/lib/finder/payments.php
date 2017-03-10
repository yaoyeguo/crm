<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class ectools_finder_payments{

    public $detail_payments = '参数设置';
    public function __construct($app){
        $this->app = $app;
    }

    public function detail_payments($payment_id)
    {
        $payment = $this->app->model('payments');
        if($_POST['payment_id']){
            $sdf = $_POST;
            unset($_POST['_method']);
            if($payment->save($sdf)){
                echo 'ok';
            }
        }else{
            $sdf_payment = $payment->dump($payment_id, '*', array('orders' => '*'));
            if($sdf_payment)
            {
                $render = $this->app->render();
                
                $render->pagedata['payments'] = $sdf_payment;
                if (isset($render->pagedata['payments']['op_id']) && $render->pagedata['payments']['op_id'])
                {
                    $obj_pam = app::get('pam')->model('account');
                    $arr_pam = $obj_pam->dump(array('account_id' => $render->pagedata['payments']['op_id']), 'login_name');
                    $render->pagedata['payments']['op_id'] = $arr_pam['login_name'] ? $arr_pam['login_name'] : '-';
                }
				else
				{
					$render->pagedata['payments']['op_id'] = '-';
				}
                if (isset($render->pagedata['payments']['orders']) && $render->pagedata['payments']['orders'])
                {
                    foreach ($render->pagedata['payments']['orders'] as $key=>$arr_order_bills)
                    {
                        $render->pagedata['payments']['order_id'] = $key;
                    }
                }
                return $render->fetch('payments/payments.html',$this->app->app_id);
            }else{
                return app::get('ectools')->_('无内容');
            }
        }
    }
	
	public $column_order_id = '支付对象';
	public function column_order_id($row)
	{
		$obj_payment = $this->app->model('payments');
		
		$arr_payment = $obj_payment->dump($row['payment_id'], '*', array('orders' => '*'));
		if ($arr_payment)
			$order_bill = array_shift($arr_payment['orders']);
		
		return $order_bill['rel_id'];
	}
}
