<?php

class taocrm_ctl_admin_rebate_payment extends desktop_controller{

    var $workground = 'plugins.manage';

    /**
     *  返利周期
     */
    public function index()
    {

        $title = '返利周期统计';
        $actions = array();
        $baseFilter = array();
        $actions[] = array(
            'label'=>'一键返积分',
            'submit'=>'index.php?app=taocrm&ctl=admin_rebate_payment&act=return_rebate&rebate_type=1',
            'target'=>'dialog::{width:650,height:355,title:\'确定返利？\'}'
        );

        $actions[] = array(
            'label'=>'一键返利',
            'submit'=>'index.php?app=taocrm&ctl=admin_rebate_payment&act=return_rebate&rebate_type=2',
            'target'=>'dialog::{width:650,height:355,title:\'确定返利？\'}'
        );
        $params = array(
            'title'=> $title,
            'actions' => $actions,
            'base_filter'=>$baseFilter,
            'orderBy' => 'create_time DESC',//默认排序
            'use_buildin_set_tag'=>false,
            'use_buildin_import'=>false,
            'use_buildin_export'=>false,
            'use_buildin_recycle'=>false,
            'use_buildin_tagedit'=>true,
            'use_view_tab'=>true,
        );
        $this->finder('taocrm_mdl_rebate_cycle', $params);
    }


    /**
     *  返利发放记录
     */
    public function statistics()
    {
        $title = '返利发放记录';
        $actions = array();
        //$baseFilter = array('parent_member_id'=>0);
        $baseFilter = array("is_send"=>'true');
        $params = array(
            'title'=> $title,
            'actions' => '',
            'base_filter'=>$baseFilter,
            'orderBy' => 'create_time DESC',//默认排序
            'use_buildin_set_tag'=>false,
            'use_buildin_import'=>false,
            'use_buildin_export'=>false,
            'use_buildin_recycle'=>false,
            'use_buildin_tagedit'=>true,
            'use_view_tab'=>true,
        );
        $this->finder('taocrm_mdl_rebate', $params);
    }

    public function return_rebate()
    {
        if($_POST['id']){
            $ids = implode(',',$_POST['id']);
        }
        $rebate_type = $_GET['rebate_type'];
        $res = app::get('taocrm')->model('rebate_cycle')->getList('*',array('id |in'=>$_POST['id']));
        if(count($res)){
            $rebate_number = $rebate_price = 0;
            foreach($res as $key =>$value){
                $rebate_cycle[$key] = date("Y-m-d h:s:i",$value['rebate_start_time']).'-'.date("Y-m-d h:s:i",$value['rebate_end_time']);
                $rebate_number += $value['rebate_number'];
                $rebate_price += $value['rebate_price'];
            }
            $this->pagedata['rebate_cycle']= implode(',',$rebate_cycle);
            $this->pagedata['rebate_number']=$rebate_number;
            $this->pagedata['rebate_price']=$rebate_price.($rebate_type==1 ? '积分' : '元');
            $this->pagedata['res']= $res;

        }
        $this->pagedata['rebate_type']=$rebate_type;
        $this->pagedata['rebate_ids']=$ids;
        $this->display('admin/rebate/return_rebate.html');
    }

    /**
     * 发放返利
     * @access private
     * @param array $_POST 客户ID 预存款值
     */
    public function send_rebate()
    {
        $this->begin();
        $taocrm_members = kernel::single('taocrm_members');
        $msg = '';
        //$arr = array('member_id'=>'2656294');
        //$return_data = $taocrm_members->get_member_stored_value($arr,$msg);
        //$return_data = $taocrm_members->get_member_stored_value_log($arr,$msg);
        $return_data = $taocrm_members->return_rebate($_POST,$msg);
        if($return_data['errcode'] == 0){
            $this->end(true,app::get('taocrm')->_('操作成功'));
        }else{
            $this->end(false,app::get('taocrm')->_($return_data['errmsg']));
        }
    }
}

