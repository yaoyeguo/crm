<?php

class taocrm_rpc_response_membercard extends taocrm_rpc_response
{

	/**
	 *
	 *
	 *
	 * @param unknown_type $sdf
	 * @param unknown_type $responseObj
	 */
	public function bind($sdf, &$responseObj){

		$apiParams = array(
        	'member_id'=>array('label'=>'会员ID','required'=>true),
            'card_no'=>array('label'=>'会员卡号','required'=>true),
            'card_pwd'=>array('label'=>'会员卡密','required'=>true),
            'bind_card_channel'=>array('label'=>'激活渠道','required'=>true),
		);
		$this->checkApiParams($apiParams,$sdf, $responseObj);


		$memberCardObj = app::get('taocrm')->model('member_card');
		$msg = '';
		$id = $memberCardObj->bind($sdf['member_id'],$sdf['card_no'],$sdf['card_pwd'],$sdf['bind_card_channel'],$msg);
		if(!$id){
			$responseObj->send_user_error(app::get('base')->_($msg));
		}

		return array('member_id'=>$id);
	}

	/**
	 *
	 *
	 *
	 * @param unknown_type $sdf
	 * @param unknown_type $responseObj
	 */
	public function check($sdf, &$responseObj){

		$apiParams = array(
            'card_no'=>array('label'=>'会员卡号','required'=>true),
            'card_pwd'=>array('label'=>'会员卡密','required'=>true),
		);
		$this->checkApiParams($apiParams,$sdf, $responseObj);


		$memberCardObj = app::get('taocrm')->model('member_card');
		$card = $memberCardObj->checkCard($sdf['card_no'],$sdf['card_pwd']);
		if(!$card){
			$responseObj->send_user_error(app::get('base')->_('会员卡不存在或者不合法'));
		}

		return array('id'=>$card['id']);
	}

}