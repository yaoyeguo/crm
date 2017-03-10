<?php

class market_ctl_admin_weixin_vote extends market_ctl_admin_weixin{
     
    var $workground = 'market.weixin';
    var $_extra_view = array('market'=>'admin/weixin/guide/step.html');
    
    public function __construct($app)
    {
        parent::__construct($app);
    }
     
    public function index()
    {
        $param = array(
            'title'=>'投票调查',
            'use_buildin_recycle'=>false,
            'use_buildin_filter'=>true,
        	'use_buildin_selectrow' => true,
            'orderBy' => "modified DESC",
            'base_filter'=>array(),
            'actions'=>array(
                array(
                'label'=>'添加投票',
                'href'=>'index.php?app=market&ctl=admin_weixin_vote&act=vote_edit',
                'target'=>'dialog::{width:800,height:400,title:\'添加投票\'}'
                ),
                array(
                'label'=>'删除',
                'submit'=>'index.php?app=market&ctl=admin_weixin_vote&act=delete_vote',
                )
            ),
        );
        
        $this->finder('market_mdl_wx_vote',$param);
    }

    public function vote_edit()
    {
        $oVote = $this->app->model('wx_vote');
        if($_POST){
            $this->begin('index.php?app=market&ctl=admin_weixin_vote&act=index');
            $data = $_POST['fields'];
            $vote_id = intval($_POST['vote_id']);

            //关键词检验
            if($vote_id>0){
                $result = $oVote->dump(array('vote_id' => $vote_id));
                $selfKeyWords = array($result['keywords']);
            }else{
                $selfKeyWords = array();
            }
            $postKeyWords = array($data['keywords']);
            $this->processKeyWords($postKeyWords,$selfKeyWords,'vote');

            $data['start_date'] = strtotime($data['start_date']);
            $data['end_date'] = strtotime($data['end_date']);

            $data['req_fields'] = json_encode($data['req_fields']);
            $data['vote_items'] = json_encode($data['vote_items']);
            //echo('<pre>');var_dump($data);die();
            if($vote_id>0){
                //unset($data['_DTYPE_TIME'],$data['_DTIME_']);
                $data['modified'] = date('Y-m-d H:i:s');
                $q = $oVote->update($data, array('vote_id'=>$vote_id));
            }else{
                unset($data['survey_id']);
                $data['modified'] = date('Y-m-d H:i:s');
                $data['created'] = date('Y-m-d H:i:s');
                $q = $oVote->insert($data);
            }
            //var_dump($data);die();
            $this->end(true,'保存成功');
            exit;
        }

        //获取默认的问答题库
        $items = array();

        $vote_id = intval($_GET['vote_id']);
        $sel_items = array();
        $rs['is_active'] = 1;
        $rs['req_fields'] = array('mobile','name');

        if($vote_id>0){
            $rs = $oVote->dump($vote_id);
            $rs['vote_items'] = json_decode($rs['vote_items'], true);
            $rs['req_fields'] = json_decode($rs['req_fields'], true);
        }
        if(!$rs['start_date']) $rs['start_date'] = date('Y-m-d');
        if(!$rs['end_date']) $rs['end_date'] = date('Y-m-d', strtotime('+30 days'));
        $this->pagedata['rs'] = $rs;
         
        $this->display("admin/weixin/vote_edit.html");
    }

    function delete_vote(){
        $oVote = &$this->app->model('wx_vote');
        $data = $_POST;

        $this->begin();
        if(!$data['vote_id']){
            $this->end(false,app::get('taocrm')->_('无数据提交'));
        }

        //删除关键字总表
        $rs = $oVote->getList('keywords',array('vote_id'=>$data['vote_id']));
        foreach($rs as $v){
            $keywords[] = $v['keywords'];
        }        
        $objWxKeyWords = &app::get('market')->model('wx_keywords');
        $objWxKeyWords->delete($keywords);

        if($oVote->delete($data['vote_id'])){
            $this->end(true,app::get('taocrm')->_('操作成功'),'index.php?app=market&ctl=admin_weixin_vote&act=index');
        }else{
            $this->end(false,app::get('taocrm')->_('操作失败'));
        }
    }
}
