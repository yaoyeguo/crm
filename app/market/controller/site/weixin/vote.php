<?php
class market_ctl_site_weixin_vote extends base_controller{
    
    function __construct($app){
        parent::__construct($app);
    }
    
    public function index(){
        //每个微信用户只能参与一次
        $oVoteResult = app::get('market')->model("wx_vote_result");
        $result = $oVoteResult->dump(array('wx_id'=>$_GET['wx_id']));
        if($result){
            echo '您已经参与了投票~';
            exit;
        }

        if($_POST){
            //投票送积分
            $this->send_point(array('vote_id' => $_POST['vote_id'],'wx_id' => $_POST['wx_id']));

            $data = array(
                'result' => $_POST['vote_items'],
                'truename' => $_POST['truename'],
                'mobile' => $_POST['mobile'],
                'wx_id' => $_POST['wx_id'],
                'created' => date('Y-m-d H:i:s'),
                'log' => json_encode($_REQUEST),
                'vote_id' => intval($_POST['vote_id']),
            );
            
            $model = $this->app->model('wx_vote_result');
            $model->insert($data);

            
            $this->display('site/weixin/success.html');
            exit;
        }
    
        $id = intval($_GET['id']);
        
        $oVote = $this->app->model('wx_vote');
        
        //kernel::single('market_service_weixin')->processKeyWord('周末');
        
        //$rs = $oVote->dump(array('keywords'=>'周末'));
        //var_dump($rs);        
        
        $rs = $oVote->dump($id);
        $rs['vote_items'] = json_decode($rs['vote_items'], true);
        $rs['req_fields'] = json_decode($rs['req_fields'], true);
        //$rs['link'] = kernel::base_url(1);
        //var_dump($rs);
    
        $this->pagedata['wx_id'] = $_GET['wx_id'];
        $this->pagedata['rs'] = $rs;
        $this->display('site/weixin/vote.html');
    }
    public function send_point($data){
        $objWxMember = app::get('market')->model('wx_member');
        $objVote = app::get('market')->model('wx_vote');
        if(intval($data['vote_id'])){
            $voteData = $objVote->dump(array('vote_id'=>intval($data['vote_id'])));
        }
        if($data['wx_id']){
            $wxMemberData = $objWxMember->dump(array('FromUserName'=>$data['wx_id']));
        }
        $msg = '';
        //微信会员表中，有member_id（即手机绑定过）,此时送积分更新全局积分明细表；member_id为空（即未手机未绑定）,此时更新微信会员表
        if($wxMemberData['member_id']){
            $id = kernel::single('taocrm_member_point')->update('',$wxMemberData['member_id'],2,$voteData['points'],'微信投票送积分',$msg,null,'wechat');
        }else{
            $id = $objWxMember->updatePoint($wxMemberData['wx_member_id'],2,$voteData['points'],'投票积分',$msg);
        }

        return $id;
    }
    
}
