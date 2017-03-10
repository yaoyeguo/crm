<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class ectools_ctl_payment_cfgs extends desktop_controller{

    var $workground = 'ectools_ctl_payment_cfgs';
	
	public function __construct($app)
	{
		parent::__construct($app);
		header("cache-control: no-store, no-cache, must-revalidate");
	}
	
    function index(){
        $this->finder('ectools_mdl_payment_cfgs',array(
            'title'=>app::get('ectools')->_('支付方式'),
            'actions'=>array(
					//array('label'=>app::get('ectools')->_('添加支付方式'),'icon'=>'add.gif','href'=>'index.php/shopadmin/#app=desktop&ctl=appmgr&act=index','target'=>'_blank'),
				),'use_buildin_recycle'=>false,
		));
    }
    
    function setting($pkey){
        if(!$pkey){
            return false;
        }
       
        if ($_POST['setting'])
		{
			$this->begin('javascript:finderGroup["'.$_POST['finder_id'].'"].refresh();');
			$payment = new $pkey($this->app);
			$setting = $payment->setting();
			
			foreach ($setting as $key=>$setting_item)
			{
				if ($setting_item['type'] == 'pecentage')
					$_POST['setting'][$key] = $_POST['setting'][$key] * 0.01;
			}
            $data['setting'] = $_POST['setting'];
            $data['status'] = $_POST['status'];
			$data['pay_type'] = $_POST['pay_type'];
            // 是否有文件上传
            if ( $_FILES ) {
            	$pos = strpos( $pkey, '_' );
                $bankName = substr( $pkey, $pos+1 );
	            $destination = DATA_DIR . '/cert/' . $bankName;
	            if ( !file_exists( $destination ) ) {
	                utils::mkdir_p( $destination, 0755 );
	            }
	            foreach ( $_FILES['setting']['error'] as $evalue ){
	                if ( $evalue == UPLOAD_ERR_OK ) {
	                	foreach ( $_FILES['setting']['name'] as $nkey=>$nvalue ) {
	                		$data['setting'][$nkey] = $nvalue;
	                	    foreach ( $_FILES['setting']['tmp_name'] as $tkey=>$tvalue ) {
                                if ( is_uploaded_file( $tvalue )) {
                                	if ( $nkey == $tkey ) {
                                        $destination = DATA_DIR . '/cert/' . $bankName . '/' . $nvalue;
                                		move_uploaded_file( $tvalue, $destination );
                                	}
                                }
                            }
	                	}
	                }
	            }
            }

            $this->app->setConf($pkey,serialize($data));
            
            if($_FILES){
                echo "<script>top.paymentsfileUploadSuccess();</script>";
                return;
            }
	 	    $this->end(true, app::get('ectools')->_('支付方式修改成功！'));
        }
		else
		{
			$payment = new $pkey($this->app);
			$setting = $payment->setting();
			if($setting){
				$val = $this->app->getConf($pkey);
				$val = unserialize($val);
				$render = $this->app->render();
				$render->pagedata['admin_info'] = $payment->admin_intro();
				$render->pagedata['settings'] = $setting;
				foreach ($setting as $k=>$v)
				{
					$render->pagedata['settings'][$k]['value'] = $val['setting'][$k] ? $val['setting'][$k] : $val[$k];
					if ($v['type'] == 'pecentage')
						$render->pagedata['settings'][$k]['value'] = $render->pagedata['settings'][$k]['value'] * 100;
					if (strpos($v['type'], 'cur') !== false)
					{
						// 得到所有的货币
						$currency_mdl = $this->app->model('currency');
						$arr_curs = $currency_mdl->getSysCur(false, '', false);
						
						foreach ($arr_curs as &$str_currency)
						{
							if (strpos($str_currency, '，') !== false)
							{
								$str_currency = substr($str_currency, strpos($str_currency, '，')+3);
							}
						}
						
						if ($payment->supportCurrency)
						{
							foreach ($payment->supportCurrency as $key=>$str_support_cur)
							{
								$render->pagedata['settings'][$k]['cur_value'] .= $arr_curs[$key];
							}
						}
					}
				}
				$render->pagedata['classname'] = $pkey;
				$render->display('payments/cfgs/cfgs.html');
			}else{

				echo '<div class="note">'.app::get("ectools")->_("不需要设置参数").'</div>';

			}
        }        
    }
}
