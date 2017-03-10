<?php

/**
 * 前端店铺订单数据业务处理
 * @author ome
 * @access public
 * @copyright www.shopex.cn 2010
 *
 */

class plugins_rpc_response_sms extends plugins_rpc_response {
	
	function __construct($app){
        parent::check_authority();
    }

    public function send(){
    	$contents = json_decode($_POST['contents'],true);
    	//发送人数
        $nums = 0;
        foreach($contents as $v){
        	$nums += count(explode(",", $v['phones']));
        }

        //查询短信余额
        $smsAPI = kernel::single('market_service_smsinterface');
        $res = $smsAPI->get_usersms_info();
        if($res['res'] == 'fail'){
        	echo json_encode(array('res'=>'','rsp'=>'fail','data'=>array('msg'=>'查询短信余额失败！')));
        	exit();
        }
        $free_num = $res['info']['all_residual'] - $res['info']['block_num'];
        //判断剩余短信是否充足
        if($free_num < $nums){
        	echo  json_encode(array('res'=>'','rsp'=>'fail','data'=>array('msg'=>'短信余额不足,请充值！')));
        	exit();
        }
        //发送短信
        $type = 'fan-out';
        $content = json_encode($contents);
    	$res = $smsAPI->send($content,$type);

    	//写日志
    	$oPlugins = &app::get('plugins')->model('plugins');
		$data = $oPlugins->getList('plugin_id,plugin_name,worker',array('worker'=>'plugins_service_check'),0,1);
		$arr = $data[0];
		$arr['start_time'] = time();
		$arr['desc'] = '发送短信数:'.$nums;
		if($res['res'] == 'succ'){
			$arr['status'] = '成功';
		}else{
			$arr['status'] = '失败';
		}
    	$oLog = &app::get('plugins')->model('log');
    	$oLog -> save($arr);
    	echo json_encode($res);
    	exit();
	
    }
	
}


