<?php
class taocrm_finder_api_log{
    function __construct(){
        if($_GET['p'][0] != 'fail' and $_GET['p'][0] != 'all'){
            unset($this->column_retry);
        }
    }
    
    /* 详情
     *
     */
    function detail_log($log_id){
        $render = app::get('taocrm')->render();
        $oApilog = &app::get('taocrm')->model("api_log");
        $apilog = $oApilog->dump($log_id);

        $apilog['params'] = unserialize($apilog['params']);
        //发起
        $apilog['send_api_name'] = $apilog['params'][0];#API名称
        if (is_array($apilog['params'][1])){
            foreach ($apilog['params'][1] as $key=>$val){
                if ($key == 'all_list_quantity'){
                    $apilog['all_list_quantity'] = $val;
                    continue;//排除显示所有库存Bn，单独放在外面显示
                }
                if ($key == 'list_quantity'){
                    $params .= $key."(待更新库存):".$val."<br/>";
                }else{
                    $params .= $key."=".$val."<br/>";
                }
            }
        }
        $apilog['send_api_params'] = $params;
        $apilog['send_api_callback'] = $apilog['params'][2];

        $apilog_msg = @json_decode($apilog['msg'],true);
        $api_arr = false;
        $msg = '';
        if (is_array($apilog_msg)){
            $api_arr = true;
            $msg = '-';
            if (is_array($apilog_msg)){
                foreach ($apilog_msg as $key=>$val){
                    $msg .= $key."=".urldecode($val)."<br/>";
                }
            }
        }else{
            $msg = $apilog['msg'];
            $code_msg = taocrm_api_func::api_code2msg($msg, $log_id);
            if (!empty($code_msg)){
                $msg = $code_msg;
            }
        }
        $apilog['msg'] = $msg;
        $apilog['api_arr'] = $api_arr;
        $render->pagedata['apilog'] = $apilog;
        return $render->fetch("admin/api/detail.html");
    }

    var $column_retry='操作';
    var $addon_cols = "status,api_type";
    var $column_retry_width = "50";

    function column_retry($row){
        $log_id = $row['log_id'];
        $api_type = $row[$this->col_prefix.'api_type'];
        $finder_id = $_GET['_finder']['finder_id'];
        $button = "<a class=\"btn\" href=\"index.php?app=taocrm&ctl=admin_api_log&act=retry&p[0]={$log_id}&finder_id={$finder_id}\" target=\"dialog::{title:'日志重试', width:550, height:300}\">重试</a>";
        if ($row[$this->col_prefix.'status']=='fail' and $api_type=='request')
        return $button;
    }


}
?>