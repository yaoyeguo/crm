<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class desktop_ctl_users extends desktop_controller{

    var $workground = 'desktop_ctl_system';

    public function __construct($app)
    {
        parent::__construct($app);
        //$this->member_model = $this->app->model('members');
        header("cache-control: no-store, no-cache, must-revalidate");
    }

    function index()
    {
        $this->finder(
            'desktop_mdl_users',
        array(
            'title'=>app::get('desktop')->_('员工帐号设置'),
            'actions'=>array(
        array(
                'label'=>app::get('desktop')->_('添加帐号'),'href'=>'index.php?ctl=users&act=addnew','target'=>'dialog::{title:\''.app::get('desktop')->_('添加员工帐号').'\'}'),
        ),
            'use_buildin_export'=>false,
            'use_buildin_recycle'=>true,//设置删除按钮
        ));
    }

    function addnew(){
        $roles = $this->app->model('roles');
        $users = $this->app->model('users');
        if($_POST){
            $this->begin('index.php?app=desktop&ctl=users&act=index');
            if($users->validate($_POST,$msg)){
                if($_POST['super']==0 && (!$_POST['role'])){
                    $this->end(false,app::get('desktop')->_('请至少选择一个工作组'));
                }
                elseif($_POST['super'] == 0 && ($_POST['role'])){
                    foreach($_POST['role'] as $roles)
                    $_POST['roles'][]=array('role_id'=>$roles);
                }
                $_POST['pam_account']['login_password'] = pam_encrypt::get_encrypted_password($_POST['pam_account']['login_password'],pam_account::get_account_type($this->app->app_id));
                $_POST['pam_account']['account_type'] = pam_account::get_account_type($this->app->app_id);
                if($users->save($_POST)){
                    foreach(kernel::servicelist('desktop_useradd') as $key=>$service){
                        if($service instanceof desktop_interface_useradd){
                            $service->useradd($_POST);
                        }
                    }
                    if($_POST['super'] == 0){   //是超管就不保存
                        $this->save_ground($_POST);
                    }
                    $this->end(true,app::get('desktop')->_('保存成功'));
                }else{
                    $this->end(false,app::get('desktop')->_('保存失败'));
                }

            }
            else{
                $this->end(false,__($msg));
            }
        }
        else{
            $workgroup=$roles->getList('*');
            $this->pagedata['workgroup']=$workgroup;
            $this->display('users/users_add.html');
        }
    }


    ####修改密码
    function chkpassword()
    {
        $this->begin('index.php?app=desktop&ctl=users&act=index');
        $users = $this->app->model('users');
        if($_POST){
            $sdf = $users->dump($_POST['user_id'],'*',array( ':account@pam'=>array('*'),'roles'=>array('*') ));
            $old_password = $sdf['account']['login_password'];
            $filter['account_id'] = 1;
            $filter['account_type'] = pam_account::get_account_type($this->app->app_id);
            $filter['login_password'] = pam_encrypt::get_encrypted_password(trim($_POST['old_login_password']),pam_account::get_account_type($this->app->app_id));
            $pass_row = app::get('pam')->model('account')->getList('account_id',$filter);
            if(!$pass_row){
                $this->end(false,app::get('desktop')->_('超级管理员密码不正确'));
            }
            elseif($_POST['new_login_password']!=$_POST['pam_account']['login_password']){
                $this->end(false,app::get('desktop')->_('两次密码不一致'));
            }
            else{
                $_POST['pam_account']['account_id'] = $_POST['user_id'];
                $_POST['pam_account']['login_password'] = pam_encrypt::get_encrypted_password(trim($_POST['new_login_password']),pam_account::get_account_type($this->app->app_id));
                $users->save($_POST);
                $this->end(true,app::get('desktop')->_('密码修改成功'));
            }
        }
        $this->pagedata['user_id'] = $_GET['id'];
        $this->display('users/chkpass.html');

    }

    /**
     * This is method saveUser
     * 添加编辑
     * @return mixed This is the return value description
     *
     */

    function saveUser(){
        $this->begin();
        $users = $this->app->model('users');
        $roles=$this->app->model('roles');
        $workgroup=$roles->getList('*');
        $param_id = $_POST['account_id'];
        if(!$param_id) $this->end(false, app::get('desktop')->_('编辑失败,参数丢失！'));
        $sdf_users = $users->dump($param_id);
        if(!$sdf_users) $this->end(false, app::get('desktop')->_('编辑失败,参数错误！'));
        //if($sdf_users['super']==1) $this->end(false, app::get('desktop')->_('不能编辑超级管理员！'));
        if($_POST){
            $_POST['pam_account']['account_id'] = $param_id;
            if($sdf_users['super']==1){
                $users->editUser($_POST);
                $this->end(true, app::get('desktop')->_('编辑成功！'));
            }
            elseif($_POST['super'] == 0 && $_POST['role']){
                foreach($_POST['role'] as $roles){
                    $_POST['roles'][]=array('role_id'=>$roles);
                }
                $users->editUser($_POST);
                $users->save_per($_POST);
                $this->end(true, app::get('desktop')->_('编辑成功！'));
            }
            else{
                $this->end(false, app::get('desktop')->_('请至少选择一个工作组！'));
            }
        }
    }
    /**
     * This is method edit
     * 添加编辑
     * @return mixed This is the return value description
     *
     */
     
    function edit($param_id){
        $users = $this->app->model('users');
        $roles=$this->app->model('roles');
        $workgroup=$roles->getList('*');
        $user = kernel::single('desktop_user');
        $sdf_users = $users->dump($param_id);
        if(empty($sdf_users)) return app::get('desktop')->_('无内容');
        $hasrole=$this->app->model('hasrole');
        foreach($workgroup as $key=>$group){
            $rolesData=$hasrole->getList('*',array('user_id'=>$param_id,'role_id'=>$group['role_id']));
            if($rolesData){
                $check_id[] = $group['role_id'];
                $workgroup[$key]['checked']="true";
            }
            else{
                $workgroup[$key]['checked']="false";
            }
        }
        $this->pagedata['workgroup'] = $workgroup;
        $this->pagedata['account_id'] = $param_id;
        $this->pagedata['name'] = $sdf_users['name'];
        $this->pagedata['super'] = $sdf_users['super'];
        $this->pagedata['status'] = $sdf_users['status'];
        $this->pagedata['customer_delete'] = $sdf_users['customer_delete'];
        $this->pagedata['user_is_super'] = $user->is_super();
        $this->pagedata['ismyself'] = $user->user_id===$param_id?'true':'false';
        if(!$sdf_users['super']){
            //$this->pagedata['per'] = $users->detail_per($check_id,$param_id);
        }

        if(app::get('bizcloud')->is_installed()){
            $this->pagedata['bizcloud'] = true;#这个值，如果为真，就应该在编辑账号时，屏蔽启用设置
        }else{
            $this->pagedata['bizcloud'] = false;
        }

        $this->display('users/users_detail.html');

    }

    //获取工作组细分
    function detail_ground(){
        $role_id = $_POST['name'];
        $roles = $this->app->model('roles');
        $menus =$this->app->model('menus');
        $check_id = json_decode($_POST['checkedName']);
        $aPermission =array();
        if(!$check_id) {
            echo '';exit;
        }
        foreach($check_id as $val){
            $result = $roles->dump($val);
            $data = unserialize($result['workground']);
            foreach((array)$data as $row){
                $aPermission[] = $row;
            }
        }
        $aPermission = array_unique($aPermission);
        if(!$aPermission){
            echo '';exit;
        }
        $addonmethod = array();
        foreach((array)$aPermission as $val){
            $sdf = $menus->dump(array('menu_type' => 'permission','permission' => $val));
            $addon = unserialize($sdf['addon']);
            if($addon['show']&&$addon['save']){  //如果存在控制
                if(!in_array($addon['show'],$addonmethod)){
                    $access = explode(':',$addon['show']);
                    $classname = $access[0];
                    $method = $access[1];
                    $obj = kernel::single($classname);
                    $html.=$obj->$method()."<br />";
                }
                $addonmethod[] = $addon['show'];
            }
            else{
                echo '';
            }
        }
        echo $html;
    }
     
    //保存工作组细分
    function save_ground($aData){
        $workgrounds = $aData['role'];
        $menus = $this->app->model('menus');
        $roles =  $this->app->model('roles');
        foreach($workgrounds as $val){
            $result = $roles->dump($val);
            $data = unserialize($result['workground']);
            foreach((array)$data as $row){
                $aPermission[] = $row;
            }
        }
        $aPermission = array_unique($aPermission);
        if($aPermission){
            $addonmethod = array();
            foreach((array)$aPermission as $key=>$val){
                $sdf = $menus->dump(array('menu_type' => 'permission','permission' => $val));
                $addon = unserialize($sdf['addon']);
                if($addon['show']&&$addon['save']){  //如果存在控制
                    if(!in_array($addon['save'],$addonmethod)){
                        $access = explode(':',$addon['save']);
                        $classname = $access[0];
                        $method = $access[1];
                        $obj = kernel::single($classname);
                        $obj->$method($aData['user_id'],$aData);
                    }
                    $addonmethod[] = $addon['save'];
                }
            }
        }
    }

    //推荐链接设置
    public function recommend_link_set(){
        if($_POST){
            $this->begin('index.php?app=desktop&ctl=users&act=recommend_link_set');
            if(trim($_POST['recommend_point']) < 0){
                $this->end(false, app::get('desktop')->_('推荐积分不能为负值！'));
            }

            base_kvstore::instance('desktop')->store('recommend_arr', $_POST);
            //===============更新到ecstore==========
            //从会员店铺表查店铺类型,店铺类型为ecos.b2c时，
            $objShop = app::get('ecorder')->model('shop');
            $node_list = $objShop->getList('node_id',array('shop_type'=>'ecos.b2c'));
            if(empty($node_list)){
                $this->end(false, app::get('desktop')->_('to_node_id不存在！'));
            }

            $couponObj = kernel::single('market_service_coupon');
            $re_bool = false;
            foreach($node_list as $nk => $nv){
                $data = array('node_id'=>$nv['node_id'],'recommend_status'=>0,'recommend_point'=>$_POST['recommend_point']);//recommend_status暂时用不到
                $response = $couponObj->store_referrals_update($data);
                if($response['rsp'] == 'succ'){
                    $re_bool = true;
                }else{
                    if($response['err_msg']){
                        $err_msg = $response['err_msg'];
                    }else{
                        $err_msg = json_encode($response);
                    }
                }
            }
            //==============更新ecstore end=============
            if($re_bool){
                $this->end(true, app::get('desktop')->_('保存成功！'));
            }else{
                $this->end(false, app::get('desktop')->_('推荐设置更新到ecstore失败！'.$err_msg));
            }

        }
        base_kvstore::instance('desktop')->fetch('recommend_arr', $recommend_arr);
        $this->pagedata['recommend_arr'] = $recommend_arr;
        $this->page('users/recommend_link_set.html');
    }

    //可配置自服务菜单
    public function self_service_menu()
    {
        $conf_menu = array(
            'exchange' => '我的兑换单',
            'code' => '我的邀请码',
            'shop_search' => '线下门店查询',
        );
    
        if($_POST){
            $this->begin('index.php?app=desktop&ctl=users&act=self_service_menu');
            base_kvstore::instance('desktop')->store('self_service_menu', $_POST['menu']);
            $this->end(true, '保存成功');
        }
        base_kvstore::instance('desktop')->fetch('self_service_menu', $service_menu);

        $this->pagedata['conf_menu'] = $conf_menu;
        $this->pagedata['service_menu'] = $service_menu;
        $this->page('users/self_service_menu.html');
    }

}
