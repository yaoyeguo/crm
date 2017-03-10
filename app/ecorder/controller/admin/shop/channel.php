<?php
class ecorder_ctl_admin_shop_channel extends desktop_controller{
    var $workground = 'taocrm.shop';
    
    function index()
    {
        $this->pagedata['shop_type'] = $shop_type;
        $this->page("admin/shop/channel.html");
    }
    
    public function old_index(){
        $memberObj = &app::get('taocrm')->model('members');
        $shopObj = &app::get(ORDER_APP)->model('shop');
        $shopList = $shopObj->getList('shop_id,name');

        $view = (isset($_GET['view']) && intval($_GET['view']) >= 0 ) ? intval($_GET['view']) : 0;
        $base_filter = array('shop_id' => $shopList[$view]['shop_id']);
        
        $actions[] = array(
                    'label'=>'添加分类',
                    'href'=>'index.php?app=ecorder&ctl=admin_shop_channel&act=addnew',
                    'target'=>'dialog::{width:680,height:250,title:\'添加店铺分类\'}'
                    );

        $this->finder('ecorder_mdl_shop_channel',array(
            'title' => '店铺分类',
            'actions' => $actions,
            'use_buildin_recycle' => false,
            'base_filter' => $base_filter,
            ));
    }

    public function addnew($channel_id=null){
        
        if($channel_id!=null){
            $oShopChannel = &app::get('ecorder')->model('shop_channel');
            $rs = $oShopChannel->dump(array('channel_id'=>$channel_id),'*');
            $this->pagedata['channel'] = $rs;
        }
        
        $this->display('admin/shop/channel.html');
    }

    function save(){
        $this->begin();
        $channel_name = trim($_POST['channel_name']);
        $channel_bn = trim($_POST['channel_bn']);
        $channel_id = intval($_POST['channel_id']);
        $oShopChannel = &app::get('ecorder')->model('shop_channel');
        $rs = $oShopChannel->dump(array('channel_name'=>$channel_name),'channel_id');
        if(!$rs || $channel_id==$rs['channel_id']){
            $arr['channel_bn'] = $channel_bn;
            $arr['channel_name'] = $channel_name;
            $arr['channel_id'] = $channel_id;
            $arr['create_time'] = time();
            if($oShopChannel->save($arr)){
                $this->end(true,'保存成功');
            }else{
                $this->end(false,'保存失败');
            }
        }else{
            $this->end(false,'分类名称不允许重复');
        }
    }

    public function getShopLv($shop_id){
        $shopLvObj = &$this->app->model('shop_lv');
        $member_level = $shopLvObj->getList('shop_lv_id,name',array('shop_id'=>$shop_id));
        if($member_level){
            $this->pagedata['member_level'] = $member_level;
            echo $this->fetch('admin/shop/shop_lv.html');
        }else{
            echo '<span class="red">此店铺没有添加客户等级</span>';
        }
    }
}
