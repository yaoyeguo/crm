<?php

//$domain = $argv[1];
//$sDate = $argv[2];
//$eDate = $argv[3];


define('COMMAND_MODE', true);
//define('SAAS_MODE', true);

set_time_limit(0);

define('LIB_DIR', dirname(__FILE__) . '/lib/');
require_once(dirname(__FILE__) . '/lib/init.php');

$flag = 2;

switch ($flag){
	case 0:
		$sdf['name'] = '注册积分';
		$sdf['code'] = 'reg_point';
		$sdf['method'] = 'taocrm.pointtype.add';
		$sdf['sign'] = base_certificate::gen_sign($sdf);
		break;
	case 1:
		$sdf['method'] = 'taocrm.pointtype.getlist';
		$sdf['sign'] = base_certificate::gen_sign($sdf);
		break;
	case 2:
		/*$sdf['method'] = 'taocrm.point.update';
		$sdf['shop_id'] = '96a3be3cf272e017046d1b2674a52bd3';
		$sdf['member_id'] = 1;
		$sdf['point'] =  800;
		$sdf['type'] = 2;
		$sdf['point_type'] = 'reg_point';
		$sdf['point_desc'] = '注册积分aa';
		$sdf['sign'] = base_certificate::gen_sign($sdf);*/
        $sdf = array (
            'task' => 'e9aaf0fb1beacb4f2e8f9c5e3ba591c0',
            'point' => '8',
            'point_desc' => '注册赠送积分.',
            'node_version' => '2.0',
            'app_id' => 'ecos.taocrm',
            'node_id' => '1437333036',
            'member_id' => '45',
            'date' => '2015-07-09 17:39:31',
            'sign' => 'BCD2E6BE69E130BEDB9A1B2C50B3CCC1',
            'type' => '2',
            'method' => 'taocrm.point.update',
        );
		break;
	case 3:
		$sdf['method'] = 'taocrm.point.get';
		$sdf['member_id'] = 1;
		$sdf['sign'] = base_certificate::gen_sign($sdf);
		break;
	case 4:
		$sdf['method'] = 'taocrm.pointlog.getlist';
		$sdf['member_id'] = 1;
		$sdf['shop_id'] = '96a3be3cf272e017046d1b2674a52bd3';
		$sdf['page_size'] = 10;
		$sdf['page'] = 1;
		$sdf['sign'] = base_certificate::gen_sign($sdf);
		break;
	case 5:
		$sdf['method'] = 'taocrm.members.add';
        $sdf['node_id'] = 1736323932;
        $sdf['uid'] = 100000;
        $sdf['uname'] = 'shiyao22';
        $sdf['real_name'] = 'shiyao2';
        $sdf['source_terminal'] = 'ps11111';
        $sdf['state'] = '上海';
        $sdf['city'] = '上海市';
        $sdf['district'] = '徐汇区';
        $sdf['address'] = '桂林路333号';
        $sdf['mobile'] = '13917771100';
        $sdf['email'] = 'shiyao@shopex.cn';
        $sdf['birthday'] = '1978-09-09';
        $sdf['sex'] = 0;
        $sdf['zip'] = 20063;
        $sdf['tel'] = 62444111;
        $sdf['is_vip'] = 0;
        $sdf['is_sms_black'] = 0;
        $sdf['is_email_black'] = 0;
        $sdf['alipay'] = 'shiyao@alipay.com';
        $sdf['remark'] = 'test';
        $sdf['sex'] = 2;
        $sdf['uname'] = 'shiyao11';
        $sdf['reg_time'] = '2013-07-28';
        $sdf['sign'] = base_certificate::gen_sign($sdf);
		break;
	case 6:
		$sdf['method'] = 'taocrm.members.update';
		$sdf['member_id'] = 50852;
		$sdf['sex'] = 1;
		/*  $sdf['real_name'] = 'shiyao21111';
		 $sdf['state'] = '上海';
		 $sdf['city'] = '上海市';
		 $sdf['district'] = '徐汇区';
		 $sdf['address'] = '桂林路333号';
		 $sdf['mobile'] = '13917771100';
		 $sdf['email'] = 'shiyao@shopex.cn';
		 $sdf['birthday'] = '1978-09-09';
		 $sdf['sex'] = 0;
		 $sdf['zip'] = 20063;
		 $sdf['tel'] = 624441112;
		 $sdf['is_vip'] = 1;
		 $sdf['is_sms_black'] = 1;
		 $sdf['is_email_black'] = 1;
		 $sdf['alipay'] = 'shiyao@alipay.com';
		 $sdf['remark'] = 'test';
		 $sdf['reg_time'] = '2013-07-28';*/
		$sdf['sign'] = base_certificate::gen_sign($sdf);
		break;
	case 7:
		$sdf['method'] = 'taocrm.members.get';
		$sdf['member_id'] = 50852;
		$sdf['sign'] = base_certificate::gen_sign($sdf);
		break;
	case 8:
		$sdf['method'] = 'taocrm.orders.search';
		$sdf['member_id'] = 1;
		$sdf['page'] = 1;
		$sdf['page_size'] = 10;
		$sdf['sign'] = base_certificate::gen_sign($sdf);
		break;
	case 9:
		$sdf['method'] = 'taocrm.member_export.finish';
		$sdf['export_id'] = 1;
		$sdf['download_url'] = '1.csv';
		$sdf['sign'] = base_certificate::gen_sign($sdf);
			
		break;
	case 10:
		$sdf['method'] = 'taocrm.member_benefits.additem';
		$sdf['benefits_code'] = 'advance';
		$sdf['benefits_name'] = '预存款';
		$sdf['source'] = '线下';
		$sdf['is_enable'] = 1;
		$sdf['op_name'] = 'admin';
		$sdf['op_time'] = '2014-06-01 00:00:00';

		$sdf['sign'] = base_certificate::gen_sign($sdf);
			
		break;
	case 11:
		$sdf = array(
        	 'member_id'=> 1,
             'benefits_type'=> 1,
             'get_benefits_mode'=>'0',
             'op_mode'=>'0',
             'get_benefits_desc'=>'获取预存款100',
             'benefits_code'=>'advance',
             'benefits_name'=>'预存款',
             'nums'=>'100',
             'effectie_time'=>'2014-06-01 00:00:00',
             'failure_time'=>'2014-06-11 00:00:00',
             'is_enable'=>'1',
             'source_order_bn'=>'111111',
             'source_business_code'=>'22222',
             'source_business_name'=>'33333333333',
             'source_store_name'=>'44444444444',
             'source_terminal_code'=>'55555555',
             'memo'=>'6666666666',
             'create_op_name'=>'admin',
             'create_op_time'=>'2014-06-01 00:00:00',
		);
		$sdf['method'] = 'taocrm.member_benefits.add';

		$sdf['sign'] = base_certificate::gen_sign($sdf);
			
		break;
	case 12:
		$sdf['method'] = 'taocrm.member_benefits.getlogs';
		$sdf['member_id'] = 1;
		$sdf['start_date'] = '2014-06-01';
		$sdf['end_date'] = '2014-06-04';

		$sdf['sign'] = base_certificate::gen_sign($sdf);
			
		break;
	case 13:
		$order_items = array();
		$order_items[] = array('goods_bn'=>'111','name'=>'营养品','nums'=>1,'price'=>10,'total_price'=>10,'bn'=>'1111_1');

		$order_items[] = array('goods_bn'=>'111','name'=>'营养品','nums'=>3,'price'=>30,'total_price'=>90,'bn'=>'1111_2');
		$sdf = array(
            'order_bn'=>'1250',
        	'shop_node_id'=>'20140605152226',
            'uname'=>'shiyao',
            'name'=>'石尧',
            'buy_time'=>'2014-06-01 00:00:00',
            'is_refund'=>1,
            'refund_order_bn'=>'1111',
            'order_amount'=>110,
            'order_status'=>'active',
            'pay_status'=>1,
            'ship_status'=>1,
            'shipping'=>'申通',
            'item_amount'=>100,
            'shipping_fee'=>10,
            'consignee'=>'石尧1',
            'consignee_state'=>'上海',
            'consignee_city'=>'上海市',
            'consignee_area'=>'普陀区',
            'consignee_address'=>'武宁路133号',
         	'consignee_zip'=>'200063',
        	'consignee_mobile'=>'13123123',
            'consignee_telephone'=>'5223444',
        	'payment'=>'支付宝',
            'pay_time'=>'2014-06-01 02:00:00',
		/*'pay_trade_no'=>array('label'=>'付款交易号','required'=>true),
		 'pay_account'=>array('label'=>'付款账号','required'=>true),
		 'pay_currency'=>array('label'=>'付款币别','required'=>true),*/
            'pay_money'=>110,
            'delivery_time'=>'2014-06-05 02:00:00',
            'finish_time'=>'2014-06-05 06:00:00',
		//'soure_shop'=>array('label'=>'来源店铺','required'=>true),
            'consumer_terminal'=>'pos1',
            'op_name'=>'admin',
            'buy_msg'=>'白色',
            'buy_remark'=>'红色',
            'order_items'=>$order_items
		);

		$sdf['method'] = 'taocrm.posorder.add';

		$sdf['sign'] = base_certificate::gen_sign($sdf);
			
		break;
	case 14:
		$sdf['method'] = 'taocrm.members.getlist';
		// $sdf['start_update_date'] = '2013-07-28 00:00:00';
		// $sdf['end_update_date'] = '2014-07-28 00:00:00';
		$sdf['start_created_date'] = '2013-07-28 00:00:00';
		$sdf['end_created_date'] = '2014-07-28 00:00:00';
		$sdf['page_size'] = 1;
		$sdf['page'] = 1;
		$sdf['sign'] = base_certificate::gen_sign($sdf);
		break;
	case 15:
		$sdf['method'] = 'taocrm.membercard.bind';
		// $sdf['start_update_date'] = '2013-07-28 00:00:00';
		$sdf['member_id'] = '1';
		$sdf['card_no'] = 'sh000002';
		$sdf['card_pwd'] = '82000318';
		$sdf['bind_card_channel'] = '月子会员';
		$sdf['sign'] = base_certificate::gen_sign($sdf);
		break;
	case 16:
		$sdf['method'] = 'taocrm.membercard.check';
		$sdf['card_no'] = 'sh000002';
		$sdf['card_pwd'] = '82000318';
		$sdf['sign'] = base_certificate::gen_sign($sdf);
		break;

}
//echo base_certificate::token()."\n";exit;
$sdf = array (
    'task' => 'e9aaf0fb1beacb4f2e8f9c5e3ba591c0',
    'point' => '8',
    'point_desc' => '注册赠送积分.',
    'node_version' => '2.0',
    'app_id' => 'ecos.taocrm',
    'node_id' => '1437333036',
    'member_id' => '45',
    'date' => '2015-07-09 17:39:31',
    'sign' => 'BCD2E6BE69E130BEDB9A1B2C50B3CCC1',
    'type' => '2',
    'method' => 'taocrm.point.update',
);

$result = get_http('192.168.10.121',80,'/crmdeploy/index.php/api',$sdf);
var_dump($sdf);
//$result = get_http('commerce.shopex123.com',80,'/crmdeploy/index.php/api',$sdf);
var_dump($result);
var_dump(json_decode($result,true));
exit;




function get_http( $host, $port, $uri, $post_data = "" ,$timeout = 8){
	/*$_post_data = '';
	 if(is_array($post_data)){
	 foreach($post_data as $k=>$v)
	 $_post_data .= $k.'='.$v.'&';

	 $post_data = $_post_data;
	 }
	 */
	if(is_array($post_data)){
		$post_data = http_build_query2($post_data);
	}


	$fp = fsockopen( $host, $port, $errno, $errstr, $timeout );

	if( !$fp )
	{
		ilog("[Info::Info] Can't connect to server: ".$host."! $errstr ($errno)");
		echo  "[Info::Info] Can't connect to server: ".$host."! $errstr ($errno)";
		trigger_error( "[Info::Info] Can't connect to server: ".$host."! $errstr ($errno)", E_USER_WARNING );
		return false;
	}

	$send = "";
	if( $post_data == "" )
	{
		$send .= "GET ".$uri." HTTP/1.1\r\n";
		$send .= "Host: ".$host.":".$port."\r\n";
		$send .= "Connection: Close\r\n\r\n";
	}
	else
	{
		$send .= "POST ".$uri." HTTP/1.1\r\n";
		$send .= "Host: ".$host.":".$port."\r\n";
		$send .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$send .= "Content-Length: ".strlen($post_data)."\r\n";
		$send .= "Connection: Close\r\n\r\n";
		$send .= $post_data;

	}

	fwrite( $fp, $send );

	$status = "OK";
	$clen = 0;
	$header_checked = false;
	while( !feof($fp) )
	{
		//HTTP/1.1 200 OK HTTP/1.1 302 Moved Temporarily
		$buf = trim( fgets( $fp, 1024 ) );
		//echo $buf.'<br />';
		if( !$header_checked && !preg_match('/HTTP+[\s\S]+200/',$buf)){

			return '';
		}else{
			$header_checked=true;
		}

		if( $buf == "")
		{
			break;
		}
		$tmp_buf_key_value = explode( ": ", $buf );

		if( $tmp_buf_key_value[0] == "Content-Length" ) // ?
		{
			$clen = $tmp_buf_key_value[1];
		}
		else if( $tmp_buf_key_value[0] == "Transfer-Encoding" && $tmp_buf_key_value[1] == "chunked" )
		{
			$clen = -1;
		}
		else if($clen == 0)
		{
			$clen = "NO_SEARCH_LEN";
		}
	}

	$return_data = "";
	if( $clen == "NO_SEARCH_LEN" )
	{
		while( !feof($fp) )
		{
			$return_data .= trim( fgets( $fp, 1024 ) );
		}
	}
	else if( $clen == 0 )
	{
	}
	else if( $clen <= -1 )
	{
		$loop_times1 = 0;
		while( 1 )
		{
			$clen = trim( fgets($fp, 128) );
			if( !$clen )
			{
				break;
			}
			$clen = base_convert($clen, 16, 10);
			if( $clen > 0 )
			{
				$tmp_data = "";
				$loop_times2 = 0;
				while( 1 )
				{
					$need_len = $clen - strlen( $tmp_data );
					if( $need_len <= 0 )
					{
						break;
					}
					$tmp_data .= @fread( $fp, $need_len );
					if( $loop_times2++ >= 10000 )
					{
						break;
					}
				}
				$return_data .= $tmp_data;
			}
			if( $loop_times1++ >= 1000 )
			{
				break;
			}
		}
	}
	else
	{
		$return_data = "";
		$loop_times2 = 0;
		while( 1 )
		{
			$need_len = $clen - strlen( $return_data );
			if( $need_len <= 0 )
			{
				break;
			}
			$return_data .= @fread( $fp, $need_len );
			if( $loop_times2++ >= 10000 )
			{
				break;
			}
		}
	}

	fclose($fp);

	return $return_data;

}



function http_build_query2($arr, $prefix='', $arg_separator='&')
{
	if(version_compare(phpversion(), '5.1.2', '>=')){
		return http_build_query($arr, $prefix, $arg_separator);
	}else{
		$org = ini_get('arg_separator.output');
		if($org !== $arg_separator){
			ini_set('arg_separator.output', $arg_separator);
			$replace = $org;
		}
		$string = http_build_query($arr, $prefix);
		if(isset($replace)){
			ini_set('arg_separator.output', $replace);
		}
		return $string;
	}
}





/**
 * 日志
 */
function ilog($str) {

	global $domain;
	$path = dirname(__FILE__) . '/../logs/test/';
	if (!is_dir($path)) {
		mkdir($path, 0777, true);
	}
	$filename = dirname(__FILE__) . '/../logs/test/'.$domain . '.log';
	$fp = fopen($filename, 'a');
	fwrite($fp, date('Y-m-d H:i:s') . ' : ' . $str . "\n");
	fclose($fp);
}