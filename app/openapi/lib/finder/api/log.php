<?php
class openapi_finder_api_log{

    var  $column_edit = '返回信息解析';
    var $column_edit_width = 160;
    var $column_edit_order = 80;
    function column_edit($row) {
        $sql = 'SELECT msg FROM sdb_openapi_api_log WHERE log_id = '.$row[$this->col_prefix.'log_id'];
        $db = kernel::database();
        $msg = $db->select($sql);
        $res = unserialize($msg[0]['msg']);
        $rerurn_var = '';
        if(is_array($res)){
            foreach($res as $k=>$v){
                $rerurn_var.=$k.':'.$v.';';
            }
        }else{
            $rerurn_var = $res;
        }
        return $rerurn_var;
    }

    var $detail_params = '参数信息';
    public function detail_params($log_id){
        $app = app::get('openapi');
        $thisapp = app::get('openapi');
        $render = $thisapp->render();
        $api_log = $app->model('api_log');
        $api_log_data = $api_log->dump($log_id);
        if(!empty($api_log_data)){
            $params = unserialize($api_log_data['params']);
            if(!empty($params)){
                $content = json_decode($params['content'],true);
            }else{
                $content = array();
            }
        }else{
            $params = array();
        }
        $render->pagedata['params'] = $params;
        $render->pagedata['content'] = $content;
        $v = var_export($params, TRUE);
        $render->pagedata['v'] = $v;
        return $render->fetch('admin/log/params.html');
    }


    var $detail_return = '返回值信息';
    public function detail_return($log_id){
        $app = app::get('openapi');
        $thisapp = app::get('openapi');
        $render = $thisapp->render();
        $api_log = $app->model('api_log');
        $api_log_data = $api_log->dump($log_id);
        if(!empty($api_log_data)){
            $msg = unserialize($api_log_data['msg']);
        }else{
            $msg = array();
        }
        $v = var_export($msg, TRUE);
        $render->pagedata['v'] = $v;
        $render->pagedata['msg'] = $msg;
        return $render->fetch('admin/log/return.html');
    }

}
