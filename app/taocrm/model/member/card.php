<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class taocrm_mdl_member_card extends dbeav_model{

	function doMakeCard($memberCardTemplateId,$makeCount,& $msg){

		$user = kernel::single('desktop_user');
		$memberCardTemplateObj = &$this->app->model('member_card_template');
		$memberCardTemplate = $memberCardTemplateObj->dump($memberCardTemplateId);

		$memberCardTypeObj = &$this->app->model('member_card_type');
		$memberCardTtype = $memberCardTypeObj->dump($memberCardTemplate['member_card_type_id']);
		$memberCardTemplate['type_code'] = $memberCardTtype['type_code'];
		$current_card_no = $memberCardTtype['current_card_no'];

		$memberCardMakeLogObj = &$this->app->model('member_card_make_log');
		$log = array(
		'member_card_type_id'=>$memberCardTemplate['member_card_type_id'],
		'member_card_template_id'=>$memberCardTemplate['id'],
		'send_card_channel'=>$memberCardTtype['type_name'],
		'is_type_code'=>$memberCardTemplate['is_type_code'],
		'card_len'=>$memberCardTemplate['card_len'],
		'card_pwd_len'=>$memberCardTemplate['card_pwd_len'],
		'card_pwd_rule'=>$memberCardTemplate['card_pwd_rule'],
		'make_count'=>$makeCount,
		'op_name'=>$user->get_login_name(),
		'batch_no' => date('YmdHis'),
		'create_time'=>time()
		);
		$memberCardMakeLogObj->save($log);
		$logId = $log['id'];

		if(empty($logId))return false;

		$card_pwd_arr = array();
		$msg = '';
		for($i=0;$i<$makeCount;$i++){
			$card_pwd = $this->getCardPwd($memberCardTemplate,$msg);
			if(!empty($card_pwd)){
				$card_pwd_arr[] = $card_pwd;
			}else{
				//之前规则是卡密不能重复，现在允许重复，所以下面不起作用
				$msg = '会员卡卡密获取失败，卡密长度可能长度不够，因为会员卡卡密不能重复!';
				return false;
			}
		}

		for($i=0;$i<$makeCount;$i++){
			$card_no = $this->getCardNo($memberCardTemplate,$current_card_no);
			$data = array(
			'member_card_type_id'=>$memberCardTemplate['member_card_type_id'],
			'member_card_template_id'=>$memberCardTemplate['id'],
			'member_card_make_log_id'=>$logId,
			'card_no'=>$card_no,
			'card_pwd'=>$card_pwd_arr[$i],
			'create_time'=>time(),
			'update_time'=>time(),
			'send_card_channel'=>$memberCardTtype['type_name'],
			'card_status'=>'unactive'
			);

			$this->save($data);
		}

		//同会员卡类型会员卡号顺序累计，不同会员卡类型会员卡号分别累计
		$this->db->exec('update sdb_taocrm_member_card_type set current_card_no="'.$current_card_no.'" where id='.$memberCardTemplate['member_card_type_id']);

		return true;
	}

	function doBindMemberCard($memberId,$memberCardId){
		$data = array('id'=>$memberCardId,'member_id'=>$memberId);

		return $this->save($data);
	}

	function getCardNo($memberCardTemplate,& $current_card_no){
		$current_card_no++;
		$type_code = $memberCardTemplate['type_code'];

		$cardNo = '';
		$cardNo .= $current_card_no;
		$cardNo = str_pad($cardNo,$memberCardTemplate['card_len'],'0',STR_PAD_LEFT);

		//加上会员卡类型编码
		if($memberCardTemplate['is_type_code'] == 1){
			$cardNo = $type_code . $cardNo;
		}

		return $cardNo;
	}

	function getCardPwd($memberCardTemplate){
		$enumPwd0 = array('a','b','c','d','e','f','g','h','i','j','k','l','n','m','o','p','q','r','s','t','u','v','w','x','y','z');
		$enumPwd1 = array('1','2','3','4','5','6','7','8','9');
		$enumPwd2 = array('1','2','3','4','5','6','7','8','9','a','b','c','d','e','f','g','h','i','j','k','l','n','m','o','p','q','r','s','t','u','v','w','x','y','z');


		/*$makeCount = 0;
		 do{
			if($makeCount == 5){
			return false;
			}*/

		$cardPwd = '';
		switch ($memberCardTemplate['card_pwd_rule']){
			case '0':
				for($i=0;$i<$memberCardTemplate['card_pwd_len'];$i++){
					$cardPwd .= $enumPwd0[rand(0,25)];
				}
				break;
			case '1':
				for($i=0;$i<$memberCardTemplate['card_pwd_len'];$i++){
					$cardPwd .= $enumPwd1[rand(0,8)];
				}
				break;
			case '2':
				for($i=0;$i<$memberCardTemplate['card_pwd_len'];$i++){
					$cardPwd .= $enumPwd2[rand(0,33)];
				}
				break;
		}

		/*$row = $this->db->selectrow('SELECT id from sdb_taocrm_member_card where card_pwd="'.$cardPwd.'"');
			$makeCount++;

			}while($row);*/

		return $cardPwd;
	}

	function bind($member_id,$card_no,$card_pwd,$bind_card_channel,& $msg){
		if( !($card = $this->checkCard($card_no,$card_pwd)) ){
			$msg = '会员卡不存在或者不合法';
			return false;
		}

		$card_status_arr = array ('unactive' => '未激活','active' => '激活','loss' => '挂失','logout'=>'注销');
		if($card['card_status'] != 'unactive'){
			$msg = '会员卡已激活，目前状态:'.$card_status_arr[$card['card_status']];
			return false;
		}

		$id = $card['id'];

		$memberCardObj = app::get('taocrm')->model('member_card');
		if(!$memberCardObj->dump($member_id,'member_id')){
			$msg = '会员不存在';
			return false;
		}

		$data = array(
			'id'=>$id,
			'member_id'=>$member_id,
			'bind_card_channel'=>$bind_card_channel,
			'bind_time'=>time(),
			'card_status'=>'active'
			);
			$this->save($data);

			return $member_id;
	}

	function checkCard($card_no,$card_pwd){
		$row = $this->db->selectRow('select id,card_status from sdb_taocrm_member_card where card_no="'.$card_no.'" and card_pwd="'.$card_pwd.'"');
		if($row){

			return $row;
		}else{
			return false;
		}
	}
    //绑定手机号时，自动发放一张会员卡
    function doMakeCard_wx($memberCardTemplateId,$makeCount,$member_id,& $msg){

        $user = kernel::single('desktop_user');
        $memberCardTemplateObj = &$this->app->model('member_card_template');
        $memberCardTemplate = $memberCardTemplateObj->dump($memberCardTemplateId);

        $memberCardTypeObj = &$this->app->model('member_card_type');
        $memberCardTtype = $memberCardTypeObj->dump($memberCardTemplate['member_card_type_id']);
        $memberCardTemplate['type_code'] = $memberCardTtype['type_code'];
        $current_card_no = $memberCardTtype['current_card_no'];

        $memberCardMakeLogObj = &$this->app->model('member_card_make_log');
        $members = app::get('taocrm')->model("members");
        $members_data = $members->dump(array('member_id'=>$member_id));
        $log = array(
            'member_card_type_id'=>$memberCardTemplate['member_card_type_id'],
            'member_card_template_id'=>$memberCardTemplate['id'],
            'send_card_channel'=>$memberCardTtype['type_name'],
            'is_type_code'=>$memberCardTemplate['is_type_code'],
            'card_len'=>$memberCardTemplate['card_len'],
            'card_pwd_len'=>$memberCardTemplate['card_pwd_len'],
            'card_pwd_rule'=>$memberCardTemplate['card_pwd_rule'],
            'op_name'=>$members_data['account']['uname']
        );
        $data_log = $memberCardMakeLogObj->dump(array('batch_no' => date('Ym')));
        if(empty($data_log)){
            $log['make_count'] = $makeCount;
            $log['bind_count'] = $makeCount;
            $log['batch_no'] = date('Ym');
            $log['create_time'] = time();
            $memberCardMakeLogObj->save($log);
            $logId = $log['id'];
        }else{
            $log['make_count'] = $data_log['make_count'] + $makeCount;
            $log['bind_count'] = $data_log['make_count'] + $makeCount;
            $log['update_time'] = time();
            $memberCardMakeLogObj->update($log,array('batch_no' => date('Ym')));
            $logId = $data_log['id'];
        }

        if(empty($logId))return false;

        $card_pwd_arr = array();
        $msg = '';
        //for($i=0;$i<$makeCount;$i++){
            $card_pwd = $this->getCardPwd($memberCardTemplate,$msg);
            if(!empty($card_pwd)){
                $card_pwd_arr = $card_pwd;
            }else{
                //之前规则是卡密不能重复，现在允许重复，所以下面不起作用
                $msg = '会员卡卡密获取失败，卡密长度可能长度不够，因为会员卡卡密不能重复!';
                return false;
            }
       // }

       // for($i=0;$i<$makeCount;$i++){
            $card_no = $this->getCardNo($memberCardTemplate,$current_card_no);
            $data = array(
                'member_card_type_id'=>$memberCardTemplate['member_card_type_id'],
                'member_card_template_id'=>$memberCardTemplate['id'],
                'member_card_make_log_id'=>$logId,
                'member_id'=>$member_id,
                'card_no'=>$card_no,
                'card_pwd'=>$card_pwd_arr,
                'create_time'=>time(),
                'update_time'=>time(),
                'send_card_channel'=>$memberCardTtype['type_name'],
                'bind_card_channel'=>$memberCardTtype['type_name'],
                'bind_time'=>time(),
                'card_status'=>'active'
            );

            $this->save($data);
      //  }

        //同会员卡类型会员卡号顺序累计，不同会员卡类型会员卡号分别累计
        $this->db->exec('update sdb_taocrm_member_card_type set current_card_no="'.$current_card_no.'" where id='.$memberCardTemplate['member_card_type_id']);

        //更新全局会员表中会员卡字段
        $this->db->exec('update sdb_taocrm_members set member_card="'.$card_no.'" where member_id='.$member_id);
        return true;
    }
}
