<?php
class market_ctl_site_weixin_due extends base_controller{
    
    function __construct($app){
        parent::__construct($app);
    }
    
    public function index(){
    
        if($_POST){
            //var_dump($_POST);
            
            $data = array(
                'order_content' => $_POST['order_content'],
                'color' => $_POST['color'],
                'size' => $_POST['size'],
                'truename' => $_POST['truename'],
                'mobile' => $_POST['mobile'],
                'addr' => $_POST['addr'],
                'wx_id' => $_POST['wx_id'],
                'created' => date('Y-m-d H:i:s'),
                'log' => json_encode($_REQUEST),
                'due_id' => intval($_POST['due_id']),
                'num' => intval($_POST['num']),
            );
            
            $model = $this->app->model('wx_due_orders');
            $q = $model->insert($data);
            
            $this->display('site/weixin/success.html');
            exit;
        }
    
        $id = intval($_GET['id']);
        
        $oVote = $this->app->model('wx_due');
        $rs = $oVote->dump($id);
        $rs['req_fields'] = json_decode($rs['req_fields'], true);
        //var_dump($rs);
    
        $this->pagedata['rs'] = $rs;
        $this->pagedata['wx_id'] = $_GET['wx_id'];
        $this->display('site/weixin/due.html');
    }
    
}
