<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
/**
 * @author chenping
 * @version 1.0 2010-3-29 11:02:02
 * @package paypal
 *
 */

final class ectools_payment_plugin_paypal extends ectools_payment_app implements ectools_interface_payment_app {
    public $name = 'PayPal';
	public $app_name = 'Paypal interface';
	public $app_key = 'paypal';
	/** 中心化统一的key **/
	public $app_rpc_key = 'paypal';
    public $display_name = 'PayPal';
    public $curname = 'CNY';
    public $ver = '1.0';
	
	// 扩展参数
	public $supportCurrency =  array(
		"USD"=>"USD", 
		"CAD"=>"CAD", 
		"EUR"=>"EUR", 
		"GBP"=>"GBP", 
		"JPY"=>"JPY", 
		"AUD"=>"AUD",
		"NZD"=>"NZD",
		"CHF"=>"CHF",
		"HKD"=>"HKD",
		"SGD"=>"SGD",
		"SEK"=>"SEK",
		"DKK"=>"DKK",
		"PLZ"=>"PLZ",
		"NOK"=>"NOK",
		"HUF"=>"HUF",
		"CSK"=>"CSK"
	);

    public function __construct($app){
		parent::__construct($app);
		
		$this->callback_url = kernel::openapi_url('openapi.ectools_payment/parse/' . $this->app->app_id . '/ectools_payment_plugin_paypal', 'callback');		
		if (preg_match("/^(http):\/\/?([^\/]+)/i", $this->callback_url, $matches))
		{
			$this->callback_url = str_replace('http://','',$this->callback_url);
			$this->callback_url = preg_replace("|/+|","/", $this->callback_url);
			$this->callback_url = "http://" . $this->callback_url;
		}
		else
		{
			$this->callback_url = str_replace('https://','',$this->callback_url);
			$this->callback_url = preg_replace("|/+|","/", $this->callback_url);
			$this->callback_url = "https://" . $this->callback_url;
		}
        //$this->submit_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
		$this->submit_url = 'https://www.paypal.com/cgi-bin/webscr';
		$this->notify_url = kernel::openapi_url('openapi.ectools_payment/parse/' . $this->app->app_id . '/ectools_payment_plugin_paypal_server', 'callback');
		if (preg_match("/^(http):\/\/?([^\/]+)/i", $this->notify_url, $matches))
		{
			$this->notify_url = str_replace('http://','',$this->notify_url);
			$this->notify_url = preg_replace("|/+|","/", $this->notify_url);
			$this->notify_url = "http://" . $this->notify_url;
		}
		else
		{
			$this->notify_url = str_replace('https://','',$this->notify_url);
			$this->notify_url = preg_replace("|/+|","/", $this->notify_url);
			$this->notify_url = "https://" . $this->notify_url;
		}
        $this->submit_method = 'POST';
        $this->submit_charset = 'utf-8';
    }
	
	function is_fields_valiad(){
		return true;
	}
	
    /**
     * 支付介绍
     *
     */
    function intro(){
        $intro = app::get('ectools')->_("PayPal 是全球最大的在线支付平台，同时也是目前全球贸易网上支付标准，在全球 103个国家和地区支持多达 16种外币，并拥有 1亿 3千万的客户资源，支持流行的国际信用卡支付。外贸网站首选。")."<br><font color='red'>".app::get('ectools')->_('本接口需点击【立即申请PAYPAL】链接进行在线签约后方可使用。')."</font>";
        return $intro;
    }
	
    function admin_intro(){
        $regIp = isset($_SERVER['SERVER_ADDR'])?$_SERVER['SERVER_ADDR']:$_SERVER['HTTP_HOST'];
        $admin_intro = app::get('ectools')->_("PayPal 是全球最大的在线支付平台，同时也是目前全球贸易网上支付标准，在全球 103个国家和地区支持多达 16种外币，并拥有 1亿 3千万的客户资源，支持流行的国际信用卡支付。外贸网站首选。")."<br><font color='red'>".app::get('ectools')->_('本接口需点击【立即申请PAYPAL】链接进行在线签约后方可使用。')."</font><br/><div style='padding:10px 0 0 388px'><a  href='javascript:void(0)' onclick='document.ALIPAYFORM.submit();'>立即申请 PAYPAL</a><form target='_blank' action='http://top.shopex.cn/recordpayagent.php' method='POST' name='ALIPAYFORM'><input type='hidden' value='POST' name='postmethod'><input type='hidden' value='https://www.paypal.com/row/mrb/pal=XE8XBENY4W9RY' name='agenturl'><input type='hidden' value='PayPal' name='payagentname'><input type='hidden' value='PAYPAL' name='payagentkey'><input type='hidden' value='".$regIp."' name='regIp'><input type='hidden' value='".$this->app->base_url(true)."' name='domain'></form></div>";
        return $admin_intro;
    }
    /**
     * 申请paypal表单
     *
     * @return unknown
     */
    function gen_form(){
	
        $tmp_form.='<a href="javascript:void(0)" onclick="document.applyForm.submit()">'.app::get('ectools')->_('立即申请PAYPAL').'</a>';
        $tmp_form.="<form name='applyForm' method='".$agentfield['postmethod']."' action='http://top.shopex.cn/recordpayagent.php' target='_blank'>";
        foreach($agentfield as $key => $val){
            $tmp_form.="<input type='hidden' name='".$key."' value='".$val."'>";
        }
        $tmp_form.="</form>";
        return $tmp_form;
    }
    /**
     * paypal支付表单
     *
     */
    function setting(){
        return array(
			'pay_name'=>array(
				'title'=>app::get('ectools')->_('支付方式名称'),
				'type'=>'string',
				'validate_type' => 'required',
			),
			'mer_id'=>array(
				'title'=>app::get('ectools')->_('客户号'),
				'type'=>'string',
				'validate_type' => 'required',
			),
			'mer_key'=>array(
				'title'=>app::get('ectools')->_('私钥'),
				'type'=>'string',
				'validate_type' => 'required',
			),
			'support_cur'=>array(
				'title'=>app::get('ectools')->_('支持币种'),
				'type'=>'text hidden cur',
				'options'=>$this->arrayCurrencyOptions,
			),
			'pay_fee'=>array(
				'title'=>app::get('ectools')->_('交易费率'),
				'type'=>'pecentage',
				'validate_type' => 'number',
			),
			'pay_desc'=>array(
				'title'=>app::get('ectools')->_('描述'),
				'type'=>'html',
				'includeBase' => true,
			),
			'pay_type'=>array(
				 'title'=>app::get('ectools')->_('支付类型(是否在线支付)'),
				 'type'=>'hidden',
				 'name' => 'pay_type',
			),
			'status'=>array(
				'title'=>app::get('ectools')->_('是否开启此支付方式'),
				'type'=>'radio',
				'options'=>array('false'=>app::get('ectools')->_('否'),'true'=>app::get('ectools')->_('是')),
				'name' => 'status',
			),
		);
    }
	
	public function dopay($payment){
        $mer_id = $this->getConf('mer_id', __CLASS__);
        $mer_key = $this->getConf('mer_key', __CLASS__);

        $this->add_field('cmd','_xclick');
        $this->add_field('business',$mer_id);
		if (isset($payment['item_name']) && $payment['item_name'])
			$this->add_field('item_name',$payment['item_name']);
		else
			$this->add_field('item_name',"Payment:".$payment['payment_id']);
        $this->add_field('item_number', $payment["payment_id"]);
        $this->add_field('amount',$payment["cur_money"]);
        $this->add_field('currency_code',$payment["currency"]);
        $this->add_field('return',$this->callback_url);
        $this->add_field('notify_url',$this->notify_url);
		//$this->add_field('bn','Shopexport_BuyNow_WPS_CN');
        $this->add_field('lc','US');


        if($this->is_fields_valiad()){
            echo $this->get_html();exit;
        }else{
            return false;
        }
    }
	
    public function callback(&$recv){		
		$objMath = kernel::single('ectools_math');
		$mer_id = $this->getConf('mer_id', __CLASS__);
		$mer_key = $this->getConf('mer_key', __CLASS__);
        $ret['payment_id'] = $paymentId = $recv['item_number'];
		$ret['account'] = $mer_id;
		$ret['bank'] = 'PayPal';
		$ret['pay_account'] = $recv['payer_id'];
		$ret['currency'] = $recv['mc_currency'];
		$ret['money'] = $recv['mc_gross'];
		$ret['paycost'] = '0.000';
		$ret['cur_money'] = $recv['mc_gross'];
		$ret['trade_no'] = $recv['txn_id'];
		$ret['t_payed'] = strtotime($recv['payment_date']);
		$ret['pay_app_id'] = "paypal";
		$ret['pay_type'] = 'online';
		$ret['memo'] = '';
		
        $money = $recv['mc_gross'];		
		$ret['status'] = 'failed';
		$ret['pdt_status'] = 'true';
		
		// Call to a function in shopex function.
		return $ret;
    }
}
