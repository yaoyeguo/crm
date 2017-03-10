<?php
class market_ctl_admin_api_log extends desktop_controller{
    var $workground = "rolescfg";
    
    function index($status='all', $api_type='request'){
        $base_filter = '';
        switch($status){
            case 'all':
                $this->title = '所有同步日志';
                break;
            case 'running':
                $this->title = '同步运行中日志';
                $base_filter = array('status'=>'running');
                break;
            case 'success':
                $this->title = '同步成功日志';
                $base_filter = array('status'=>'success');
                break;
            case 'fail':
                $this->title = '同步失败日志';
                $orderby = ' createtime ';
                $base_filter = array('status'=>'fail','api_type'=>$api_type);
                break;
            case 'sending':
                $this->title = '发起中日志';
                $base_filter = array('status'=>'sending');
                break;
        }
        if ($status=='fail' and $api_type=='request'){
            $actions = 
              array(
                 array(
                   'label' => '批量重试',
                   'submit' => 'index.php?app=market&ctl=admin_api_log&act=batch_retry&finder_id='.$_GET['finder_id'],
                   'target' => "dialog::{width:550,height:300,title:'批量重试'}",
                 ),
              );
        }
        $params = array(
            'title'=>$this->title,
            'actions'=> $actions,
            'use_buildin_new_dialog' => false,
            'use_buildin_set_tag'=>false,
            'use_buildin_recycle'=>false,
            'use_buildin_export'=>false,
            'use_buildin_import'=>false,
            'use_buildin_filter'=>true,
            'orderBy' => $orderby,
        );
        
        if($base_filter){
            $params['base_filter'] = $base_filter; 
        }
        
        $this->finder('market_mdl_api_log',$params);
    }
    
    /*
     * 重试同步日志
     * @param int or array $log_id 待重试的日志ID
     * @param string $retry_type 重试方式，默认为单个重试，batch:为批量重试
     */
    function retry($log_id='', $retry_type='single'){
        if ($retry_type == 'single'){
            $this->pagedata['log_id'] = $log_id;
        }else{
            if (is_array($log_id['log_id'])){
                $this->pagedata['log_id'] = implode("|", $log_id['log_id']);
            }
        }
        $this->pagedata['isSelectedAll'] = $log_id['isSelectedAll'];
        $this->pagedata['retry_type'] = $retry_type;
        $this->display("admin/api/retry.html");
    }
    
    function retry_do(){
        $log_id = urldecode($_GET['log_id']);
        $retry_type = $_GET['retry_type'];
        $isSelectedAll = $_GET['isSelectedAll'];
        $cursor = $_GET['cursor'];
        $return = $this->app->model('api_log')->retry($log_id, $retry_type, $isSelectedAll, $cursor);
        echo json_encode($return);
        exit;
    }
    
    function batch_retry(){
        $this->retry($_POST, 'batch');
    }
    
}
?>