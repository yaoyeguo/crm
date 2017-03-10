<?php
class taocrm_mdl_member_messenger {

    var $plugin_type = 'dir';
    var $plugin_name = 'messenger';
    var $prefix = 'messenger.';
    var $db;
    function __construct(&$app){
        $this->app = $app;
        $this->db = kernel::database();
    }
    
    function getList($filter=array(), $ifMethods=true,$withDesc=false){
        $services = kernel::servicelist('taocrm_messenger');
        $service = array();
        foreach($services as $key=>$v){
            $service[$key] = (array)$v;
            $service[$key]['methods'] = get_class_methods($v);
        }
        return $service;
    }

    function &_load($sender){
        if(!$this->_sender[$sender]){
            $obj = $this->load($sender);
            $this->_sender[$sender] = &$obj;
            if(method_exists($obj,'getOptions')||method_exists($obj,'getoptions'))
                $obj->config = $this->getOptions($sender,true);
            if(method_exists($obj,'outgoingConfig')||method_exists($obj,'outgoingconfig'))
                $obj->outgoingOptions = $this->outgoingConfig($sender,true);
        }else{
            $obj = &$this->_sender[$sender];
        }
        return $obj;
    }

    function _ready(&$obj){
        if(!$obj->_isReady){
            if(method_exists($obj,'ready')) $obj->ready($obj->config);
            if(method_exists($obj,'finish')){
                if(!$this->_finishCall){
                    register_shutdown_function(array(&$this,'_finish'));
                    $this->_finishCall=array();
                }
                $this->_finishCall[] = &$obj;
            }
            $obj->_isReady = true;
        }
    }

    function _send($sendMethod,$tmpl_name,$target,$data,$type,$title=null){
        $sender = &$this->_load($sendMethod);
        $this->_ready($sender);
        if(!$this->_systmpl){
            $this->_systmpl = &$this->app->model('member_systmpl');
        }
        $content = $this->_systmpl->fetch($tmpl_name,$data);
        $tile = $this->loadTitle($type,$sendMethod,'',$data);
        $service = kernel::service("b2c.messenger.fireEvent_content");
        if(is_object($service))
        {
            if(method_exists($service,'get_content'))
                $content = $service->get_content($content);
                $tile = $service->get_content($tile);
        }
        $to = $this->get_send_type(get_class($sender),$data,$data['member_id']);
        if($tile=='') $tile = app::get('site')->getConf('site.name');
        $sender->config['shopname'] = app::get('site')->getConf('site.name');
        $sender->send($target,$tile,$content,$sender->config);
        return ($ret || !is_bool($ret));


    }
    
    ##获取发送对象的联系方式 /email,ID,phone
    
    function get_send_type($sdfpath='pam_account/account_id',$data,$member_id){
        
        $type_msg = $type;
        $obj_member = $this->app->model('members');
        $sdf = $obj_member->dump($member_id);
        eval(' $target= $sdf["'.implode('"]["',explode('/',$sdfpath)).'"]; ');
        /*
        if($type_msg == "b2c_messenger_msgbox") {
        $target = $member_id; 
        }
        if($type_msg == "b2c_messenger_email"){
        $target = $sdf['contact']['email'];
        if(!$target) $target = $data['email'];
        }
        if($type_msg == "b2c_messenger_sms") {
       $target = $sdf['contact']['phone']['mobile'];
        }*/
        return $target;
    }

    function _finish(){
        foreach($this->_finishCall as $obj){
            $obj->finish($obj->config);
        }
    }

    function _target($sender,$contectInfo,$member_id){
        $obj = &$this->_load($sender);
        if(($dataname = $obj->dataname) && $contectInfo[$dataname]){
            return $contectInfo[$dataname];
        }else{
            $row = $this->db->selectrow('select email,member_id,name,custom,mobile from sdb_b2c_members where member_id='.intval($member_id));
            if($dataname){
                return $row[$dataname];
            }elseif($custom = unserialize($row['custom'])){
                return $custom[$sender];
            }else{
                return false;
            }
        }
    }

    /**
     * actionSend
     *
     * @param mixed $type 类型
     * @param mixed $contectInfo  联系数组
     * @param mixed $member_id 客户id
     * @param mixed $data 信息
     * @access public
     * @return void
     */
    function actionSend($type,$data,$member_id=null){
        $actions = $this->actions();
        $senders = $this->getSenders($type); //email/msbox/sms
        $level = $actions[$type]['level'];
        $desc = $actions[$type]['label'];
        foreach($senders as $sender){
            $tmpl_name = 'messenger:'.$sender.'/'.$type;
            $contractInfo = $data;
          
             if($sender && ($target = $this->get_send_type(kernel::single($sender)->sdfpath,$data,$member_id))){
                if($level < 9){ //队列
                  //  $this->addQueue($sender,$target,$desc,$data,$tmpl_name,$level,$type);
                }else{ //直接发送 print
                    
                    $this->_send($sender,$tmpl_name,$target,$data,$type);
                }
            }
        }
        
    }

    function getSenders($act){
        $ret = $this->app->getConf('messenger.actions.'.$act);
        return explode(',',$ret);
    }

    function saveActions($actions){
        foreach($this->actions() as $act=>$info){
            if(!$actions[$act]){
                $actions[$act] = array();
            }
        }
        foreach($actions as $act=>$call){
            $this->app->setConf('messenger.actions.'.$act,implode(',',array_keys($call)));
        }
        return true;
    }

    /**
     * actions
     * 所有自动消息发送列表，只要触发匹配格式的事件就会发送
     *
     * 格式：
     *            对象-事件 => array(label=>名称 , level=>紧急程度)
     *
     * 如果不存在匹配的事件，则需要手动通过send()方法发送
     *
     * @access public
     * @return void
     */
    function actions(){
        $actions = array(
            'send-coupon'=>array('label'=>app::get('taocrm')->_('发送优惠券'),'level'=>9,'varmap'=>app::get('taocrm')->_('用户名').'&nbsp;<{$uname}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('taocrm')->_('优惠券').'&nbsp;<{$coupon}>'),
        );
        foreach(kernel::servicelist('firevent_type') as $service){
            if(is_object($service)){
                if(method_exists($service,'get_type')){
                    $data = $service->get_type();
                }
            }
        }
        $actions = array_merge($actions,(array)$data);
        return $actions;
    }


    function loadTmpl($action,$msg,$lang=''){
        $systmpl = &$this->app->model('member_systmpl');
        return $systmpl->get('messenger:'.$msg.'/'.$action);
    }

    function loadTitle($action,$msg,$lang='',$data=""){

        $tmpArr=$data;
        $title = $this->app->getConf('messenger.title.'.$action.'.'.$msg);

        if($data!=""){
            preg_match_all('/<\{\$(\S+)\}>/iU', $title, $result);

            foreach($result[1] as $k => $v){
               $v=explode('.',$v);
               $data=$tmpArr;

               foreach($v as $key => $val){

                     $data=$data[$val];

                     if(is_array($data))
                     continue ;
                     else{

                         $title = str_replace($result[0][$k],$data,$title);

                     }

                 }
             }

         }

        return $title;
    }

    function saveContent($action,$msg,$data){
        $systmpl = &$this->app->model('member_systmpl');    
         $info = $this->getParams($msg);  
        if($info['hasTitle']) $this->app->setConf('messenger.title.'.$action.'.'.$msg,$data['title']);
        return $systmpl->set('messenger:'.$msg.'/'.$action,$data['content']);
    }
 
    function &load($item){
        if (!$this->_plugin_obj[$item]) {
           if($obj = kernel::single($item))   return $obj;
           else return null;
        }
        return $this->_plugin_obj[$item];
    }

    function getOptions($item,$valueOnly = false){
        $obj = $this->load($item);
        if(method_exists($obj,'getOptions')||method_exists($obj,'getoptions')){
            $options = $obj->getOptions();      #print_r($options);exit;
            foreach($options as $key=>$value){
                $app = app::get('desktop');
                $v = $app->getConf('email.config.'.$key);
                if($valueOnly){
                    $options[$key] = (is_null($v))?$options[$key]:$v;
                }
                else{
                    $options[$key]['value'] = (is_null($v))?$options[$key]['value']:$v;
                }
            }
            return $options;
        }
    }

     function getParams($msg){
        $aData = $this->getList();
        return $aData[$msg];
    }
}

?>
