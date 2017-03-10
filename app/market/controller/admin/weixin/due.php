<?php

class market_ctl_admin_weixin_due extends market_ctl_admin_weixin{
     
    var $workground = 'market.weixin';
    
    public function __construct($app)
    {
        parent::__construct($app);
    }
     
    public function index()
    {
        if($_GET['view'] == '1'){
            $this->orders();
            die();
        }   
        $param = array(
            'title'=>'预约服务',
            'use_buildin_recycle'=>false,
            'use_buildin_filter'=>true,
        	'use_buildin_selectrow' => true,
            'orderBy' => "modified DESC",
            'base_filter'=>array(),
            'actions'=>array(
                array(
                'label'=>'添加预约',
                'href'=>'index.php?app=market&ctl=admin_weixin_due&act=due_edit',
                'target'=>'dialog::{width:800,height:400,title:\'添加预约\'}'
                ),
                array(
                'label'=>'删除',
                'submit'=>'index.php?app=market&ctl=admin_weixin_due&act=delete_due',
                )
            ),
        );
        
        $this->finder('market_mdl_wx_due',$param);
    }
    
    public function orders()
    {
        $actions = array(
            array(
            'label'=>'删除',
            'submit'=>'index.php?app=market&ctl=admin_weixin_due&act=delete_due',
            )
        );
        $param = array(
            'title'=>'预约订单',
            'use_buildin_recycle'=>false,
            'use_buildin_filter'=>true,
        	'use_buildin_selectrow' => true,
            'orderBy' => "order_id DESC",
            'base_filter'=>array(),
            //'actions'=>'',
        );
        
        $this->finder('market_mdl_wx_due_orders',$param);
    }
    
    function _views()
    {
        $oDueOrder = $this->app->model('wx_due_orders');
        $oDue = $this->app->model('wx_due');
        
        $due_count = $oDue->count();
        $due_order_count = $oDueOrder->count();
        
        $sub_menu[] = array(
            'label'=>'预约项目',
            'filter'=>array(),
            'optional'=>false,
            'addon'=>$due_count,
            'href'=>'index.php?app=market&ctl=admin_weixin_due&act=index&view=0'
        );
        
        $sub_menu[] = array(
            'label'=>'预约订单',
            'filter'=>array(),
            'optional'=>false,
            'addon'=>$due_order_count,
            'href'=>'index.php?app=market&ctl=admin_weixin_due&act=index&view=1'
        );
        return $sub_menu;
    }

    public function due_edit()
    {
        $oDue = $this->app->model('wx_due');
        if($_POST){
            $this->begin('index.php?app=market&ctl=admin_weixin_due&act=index');
            $data = $_POST['fields'];
            $due_id = intval($_POST['due_id']);

            //关键词检验
            if($due_id>0){
                $result = $oDue->dump(array('due_id' => $due_id));
                $selfKeyWords = array($result['keywords']);
            }else{
                $selfKeyWords = array();
            }
            $postKeyWords = array($data['keywords']);
            $this->processKeyWords($postKeyWords,$selfKeyWords,'due');

            $data['req_fields'] = json_encode($data['req_fields']);
            //echo('<pre>');var_dump($data);die();
            if($due_id>0){
                //unset($data['_DTYPE_TIME'],$data['_DTIME_']);
                $data['modified'] = date('Y-m-d H:i:s');
                $q = $oDue->update($data, array('due_id'=>$due_id));
            }else{
                unset($data['due_id']);
                $data['modified'] = date('Y-m-d H:i:s');
                $data['created'] = date('Y-m-d H:i:s');
                $q = $oDue->insert($data);
            }
            //var_dump($data);die();
            $this->end(true,'保存成功');
            exit;
        }

        $due_id = intval($_GET['due_id']);
        $sel_items = array();
        $rs['is_active'] = 1;
        $rs['req_fields'] = array('num','mobile');
        $rs['req_fields']['other']['name'] = array('','','','','');

        if($due_id>0){
            $rs = $oDue->dump($due_id);
            $rs['req_fields'] = json_decode($rs['req_fields'], true);
            //var_dump($rs['req_fields']);
            
            $other_size = count($rs['req_fields']['other']['name']);
            if($other_size<5){
                for($i=$other_size;$i<5;$i++){
                    $rs['req_fields']['other']['name'][] = '';
                }
            }
        }
        $this->pagedata['rs'] = $rs;
         
        $this->display("admin/weixin/due_edit.html");
    }

    function delete_due(){
        $oDue= &$this->app->model('wx_due');
        $data = $_POST;

        $this->begin();
        if(!$data['due_id']){
            $this->end(false,app::get('taocrm')->_('无数据提交'));
        }

        //删除关键字总表
        $rs = $oDue->getList('keywords',array('due_id'=>$data['due_id']));
        foreach($rs as $v){
            $keywords[] = $v['keywords'];
        }        
        $objWxKeyWords = &app::get('market')->model('wx_keywords');
        $objWxKeyWords->delete($keywords);

        if($oDue->delete($data['due_id'])){
            $this->end(true,app::get('taocrm')->_('操作成功'),'index.php?app=market&ctl=admin_weixin_due&act=index');
        }else{
            $this->end(false,app::get('taocrm')->_('操作失败'));
        }
    }
}
