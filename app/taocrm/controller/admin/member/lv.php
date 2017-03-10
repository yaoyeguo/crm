<?php
class taocrm_ctl_admin_member_lv extends desktop_controller{
    var $workground = 'taocrm.member';

    public function index(){
        $this->finder('taocrm_mdl_member_lv',array(
            'title'=>'客户等级',
            'actions'=>array(
                 array(
                    'label'=>'添加客户等级',
                    'href'=>'index.php?app=taocrm&ctl=admin_member_lv&act=addnew',
                    'target'=>'dialog::{width:680,height:250,title:\'添加客户等级\'}'),
                )
            ));
    }

    public function addnew($member_lv_id=null){
        $aLv['default_lv_options'] = array('1'=>'是','0'=>'否');
        $aLv['default_lv'] = '0';
        $aLv['lv_type_options'] = array('retail'=>'普通零售客户等级','wholesale'=>'批发代理客户等级');
        $aLv['lv_type'] = 'retail';
        $this->pagedata['levelSwitch']= $this->app->getConf('site.level_switch');
        $this->pagedata['lv'] = $aLv;

        if($member_lv_id!=null){
            $memLvObj = $this->app->model('member_lv');
            $aLv = $memLvObj->dump($member_lv_id); 
            $aLv['default_lv_options'] = array('1'=>'是','0'=>'否');
          $this->pagedata['lv'] = $aLv;
        }
        
        $this->display('admin/member/lv.html');
    }

    function save(){
        $this->begin();
        $lvData = $_POST;
        $memLvObj = $this->app->model('member_lv');
        if($memLvObj->validate($lvData,$msg)){
            if($memLvObj->save($lvData)){
                $memberObj = $this->app->model('members');
                if($memberObj->member_lv_change_next($lvData) && $memberObj->member_lv_change_pre($lvData)){
                    $this->end(true,'保存成功');
                }else{
                    $this->end(false,'对应客户的等级更新失败');
                }
            }else{
                $this->end(false,'保存失败');
           }
        }else{
            $this->end(false,$msg);
        }
    }

}
