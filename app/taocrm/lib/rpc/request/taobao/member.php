<?php

/**
 * 客户同步请求
 * @author sy
 * @access public
 * @copyright www.shopex.cn 2010
 *
 */
class taocrm_rpc_request_taobao_member extends taocrm_rpc_request {

    protected $topClient;

    protected $_shopInfo = array();

    protected $_rertyCount = 0;

    protected $_rertyMaxCount = 3;

    public function __construct(){
        $c = new ectools_top_TopClient();
        $c->format = "json";
        $c->appkey = TOP_APP_KEY;
        $c->secretKey = TOP_SECRET_KEY;
        $this->topClient = $c;
    }

    public function download(){
        kernel::ilog(__CLASS__ . ' download start......');

        /*$memberInfo = array(
         'uname'=>'岑超茹',
         'sex'=>'f',
         'birthday'=>'2010-12-21',
         'alipay_no'=>1,
         'alipay_account'=>2,
         'email'=>'sdfds@ss.com',
         );
         $memberId =  kernel::single('taocrm_service_member')->saveMember('3036ca3fffda4a6b6f31aab95c3527cf',$memberInfo);
         exit;*/

        $shopList = kernel::database()->select('select * from sdb_ecorder_shop where node_id is not null and node_type="taobao"');
        foreach($shopList as $shop){
            $addon = unserialize($shop['addon']);
            if($addon && !empty($addon['session'])){
                kernel::ilog($shop['name'] . ' start......');
                echo($shop['name'].' finish; '); 
                $this->_shopInfo = array('channel_id'=>$shop['channel_id'],'shop_id'=>$shop['shop_id'],'session'=>$addon['session'],'nickname'=>$addon['nickname']);
                //$this->getAll('UserGetRequest');
                $this->getIncrementMembers(1);
                kernel::ilog($shop['name'] . ' end......');
            }
        }
        
        kernel::ilog(__CLASS__ . ' download end......');
    }

    //增量获取店铺客户
    public function getIncrementMembers($page_no=1){
        $page_size = 50;
        $start_modify = date('Y-m-d H:i:s', strtotime('-90 days'));
        $req = new ectools_top_request_CrmMembersIncrementGetRequest();
        $req->setCurrentPage($page_no);
        $req->setStartModify($start_modify);
        $req->setEndModify($end_modify);
        $req->setPageSize($page_size);

        $resp = $this->topClient->execute($req,$this->_shopInfo['session']);
        //var_dump($resp);exit;
        if($resp->code || $resp->msg){
            $msg = ('【code】'.$resp->code.'<br/>【msg】'.$resp->msg);
            if($resp->sub_code){
                $msg .= '<br/>【sub_code】' . $resp->sub_code;
            }
            if($resp->sub_msg){
                $msg .= '<br/>【sub_msg】' . $resp->sub_msg;
            }
            if($resp->code == 'Remote service error' && $resp->msg == 'isp.top-remote-connection-timeout'){
                kernel::ilog($msg);
                $msg = ('timeout');
            }
            echo($this->_shopInfo['nickname'].' error: '.$msg);
        }else if(!$resp){ // 超时错误
            $msg = ('timeout');
        }else{
            $resp = json_encode($resp);
            $resp = json_decode($resp, true);
            $members = $resp['members'];
            $total_result = $resp['total_result'];
            
            if($total_result>0 && $members){
                foreach($members as $basic_member){
                    $sql = "update sdb_taocrm_member_analysis as a left join sdb_taocrm_members as b on a.member_id=b.member_id set a.shop_grade='".$basic_member['grade']."' where b.uname='".$basic_member['buyer_nick']."'";
                    kernel::database()->exec($sql);
                }
                
                if($page_no * $page_size < $total_result){
                    $page_no++;
                    $this->getIncrementMembers($page_no);
                }
            }
            echo($this->_shopInfo['nickname'].' finish; ');            
            
            $msg = 'success';
        }

        return $msg;
    } 
    
    protected function getAll($task,$pageNo=1){
        //echo $pageNo."\n";
        kernel::database()->dbclose();
        $result = $this->switchTask($task,$pageNo);
        if($result == 'timeout'){
            kernel::ilog($task . '-'. $pageNo . ' is ' . $result);
            kernel::ilog('sleep 3 sec...');
            sleep(3);
            //echo '$this->_rertyCount:'.$this->_rertyCount."\n";
            if( $this->_rertyCount < $this->_rertyMaxCount ){
                $this->_rertyCount++;
                $this->getAll($task,$pageNo);
            }else{
                kernel::ilog('rerty finish...');
                $this->_rertyCount = 0;
                $pageNo++;
                $this->getAll($task,$pageNo);
            }
        }else if($result == 'success'){
            $pageNo++;
            $this->getAll($task,$pageNo);
        }else if($result == 'finish'){
             

        }else{
            kernel::ilog($result);
        }
    }

    protected function switchTask($task,$pageNo=1,$pageSize=100){
        return $this->{$task}($pageNo,$pageSize);
    }

    protected function UserGetRequest($pageNo,$pageSize){
        $msg = '';
        $membersObj = app::get('taocrm')->model('members');
        $rows = $membersObj->getList('uname','',($pageNo-1),1,'member_id');
        if(!$rows)return 'finish';

        $memberInfo = $rows[0];
        //$memberInfo = array('uname'=>'xyxiaoyu2010');
        $req = new ectools_top_request_UserGetRequest();
        $req->setFields('sex,buyer_credit,location,created,last_visit,birthday,status,alipay_account,alipay_no,email,vip_info');
        $req->setNick($memberInfo['uname']);

        $resp = $this->topClient->execute($req,$this->_shopInfo['session']);
        //var_dump($resp);exit;
        if($resp->code || $resp->msg){
            $msg = ('【code】'.$resp->code.'<br/>【msg】'.$resp->msg);
            if($resp->sub_code){
                $msg .= '<br/>【sub_code】' . $resp->sub_code;
            }
            if($resp->sub_msg){
                $msg .= '<br/>【sub_msg】' . $resp->sub_msg;
            }
             if($resp->code == 'Remote service error' && $resp->msg == 'isp.top-remote-connection-timeout'){
                kernel::ilog($msg);
                $msg = ('timeout');
            }
        }else if(!$resp){ // 超时错误
            $msg = ('timeout');
        }else{
            if(is_object($resp->user)) $v = get_object_vars($resp->user);
            if(is_object($v['buyer_credit'])) $v['buyer_credit'] = get_object_vars($v['buyer_credit']);
            if(is_object($v['location'])) $v['location'] = get_object_vars($v['location']);
            $v['uname'] = $memberInfo['uname'];
            //echo $v['uname']."\n";
            $memberId = $this->processMember($v);
            if(!$memberId){
                kernel::ilog($memberInfo['uname'] . ' update failed.');
            }
            //exit;
            $msg = 'success';
        }

        if($pageNo % 100 == 0){
            kernel::ilog('member % 100 is sleep 3 sec...');
            sleep(3);
        }
        return $msg;
    }


    protected function processMember($member){
        $m = $this->checkMember($member['uname']);
        if(!$m){
            return false;
        }
        $memberId = $m['member_id'];

        $memberInfo = array(
            'sex'=>$member['sex'],
            'birthday'=>strtotime($member['birthday']),
            'alipay_no'=>$member['alipay_no'],
            'alipay_account'=>$member['alipay_account'],
            'email'=>$member['email'],
        	'uname'=>$member['uname'],
        );
        //var_dump($memberInfo);exit;
        $memberId =  kernel::single('taocrm_service_member')->saveMember($this->_shopInfo['shop_id'],$memberInfo);

        if($memberId){
            $analysisData = array(
                'f_vip_info'=>$member['vip_info'],
                'f_level'=>$member['buyer_credit']['level'],
                'f_score'=>$member['buyer_credit']['score'],
                'status'=>$member['status'],
             	'f_created'=>strtotime($member['created']),
        		'f_last_visit'=>strtotime($member['last_visit']),
            );
            kernel::single('taocrm_service_member')->addMemberAnalysis($this->_shopInfo['shop_id'],$memberId,$analysisData);
        }

        return $memberId;
    }

    protected function checkMember($uname){
        $row = kernel::database()->selectrow('select member_id from sdb_taocrm_members where uname = "'. $uname .'" ');
        if($row){
            return $row;
        }else{
            return false;
        }
    }

     
     
}