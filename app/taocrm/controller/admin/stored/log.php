<?php
class taocrm_ctl_admin_stored_log extends desktop_controller{


    public function index()
    {
        $actions = array(

        );
        $this->finder('taocrm_mdl_stored_value_log',
            array(
                'title' => '储值流水记录',
                'actions' => $actions,
                'use_buildin_export' => false,
                'use_buildin_recycle' => false,
                'use_buildin_set_tag' => false,
                'use_buildin_tagedit' => false,
                'use_buildin_selectrow' => false,
                'orderBy' => 'log_id desc',
            )
        );
    }

    /**
     * 修改客户预存款
     * @access public
     * @param $log_id
     */
    public function edit_manual()
    {
        if($_GET['member_id'])
        {
            $member_ids[] = $_GET['member_id'];
            $objMembers = $this->app->model('members');
            $member_info = $objMembers->getList('member_id,mobile,uname,shop_id,stored_value',array('member_id'=>$member_ids));
            $this->pagedata['member_info']=$member_info;
            $this->pagedata['stored_value'] = $member_info[0]['stored_value'];
        }
        $shopObj = app::get('ecorder')->model('shop');
        $shopList=$shopObj->get_shops('all', 'select');

        $this->pagedata['shopList']=$shopList;//店铺信息
        $this->pagedata['finder_id']=$_GET['finder_id'];//店铺信息
        $this->display('admin/member/storedValue.html');
    }
    
    /**
     * 更新客户预存款
     * @access private
     * @param array $_POST 客户ID
     */
    function storedValue(){
        if(isset($_POST['id']) && $_POST['id']){
            $member_ids = $_POST['id'];
        }elseif(isset($_POST['member_id']) && $_POST['member_id']){
            $member_ids = $_POST['member_id'];
        }else{
            $member_ids = $_GET['id'];
        }
        $this->pagedata['member_ids'] = implode(',', $member_ids);
        $this->display('admin/member/storedValue.html');
    }

    /**
     * 保存客户预存款
     * @access private
     * @param array $_POST 客户ID 预存款值
     */
    public function saveStoredValue()
    {
        $this->begin();
        $taocrm_members = kernel::single('taocrm_members');
        $msg = '';
        //print_r($_POST);exit;
        if(strlen($_POST['stored_value'])>20){
            $this->end(false,app::get('taocrm')->_('储值数据过大,最多不能超过20位'));
        }
        if(strlen($_POST['remark'])>200){
            $this->end(false,app::get('taocrm')->_('备注过长,不能超过200个字符'));
        }
        $return_data = $taocrm_members->update_member_stored_value($_POST,$msg);
        if($return_data['errcode'] == 0){
            $this->end(true,app::get('taocrm')->_('操作成功'));
        }else{
            $this->end(false,app::get('taocrm')->_($return_data['errmsg']));
        }
    }
}
