<?php
class taocrm_rpc_response_saasmanager_system
{
     
    function doVersionLimit($data,& $apiObj){
        kernel::single('taocrm_system')->setVersion($data);
        $versionInfo = kernel::single('taocrm_system')->getVersion();
        $apiObj->api_response('已设置,订单数:'.$versionInfo['order_nums'].',客户数:'.$versionInfo['member_nums'].',店铺数:'.$versionInfo['shop_nums'].'');
    }

    function getVersionInfo($data,& $apiObj){
        $versionInfo = kernel::single('taocrm_system')->getVersion();
        $apiObj->api_response('订单数:'.$versionInfo['order_nums'].',客户数:'.$versionInfo['member_nums'].',店铺数:'.$versionInfo['shop_nums'].'');
    }

    function getCertificateInfo($data,& $apiObj){
        $info = get_certificate();
        $apiObj->api_response(var_export($info,true));
    }

    function doSystemType($data,& $apiObj){

        //检测数据是否合法
        if(intval($data['pay_rule'])==0 || intval($data['freeze_rule'])==0){
            $apiObj->api_response('计费规则和冻结规则必须输入整数！');
            return false;
        }

        $sys_type_arr = array(1=>'月租客户',2=>'按效果计费客户');

        kernel::single('taocrm_system')->setSystemType($data);
        $systemType = kernel::single('taocrm_system')->getSystemType();
        $msg = '设置完成,系统类型:'.$sys_type_arr[$systemType['system_type']];
        if($systemType['system_type'] == 2){
            $msg .= ',计费规则:'.$systemType['pay_rule'];
            $msg .= ',冻结规则:'.$systemType['freeze_rule'];
        }
        $apiObj->api_response($msg);
    }

    function getSystemType($data,& $apiObj){

        $sys_type_arr = array(1=>'月租客户',2=>'按效果计费客户');

        $systemType = kernel::single('taocrm_system')->getSystemType();
        $msg = '当前系统类型:'.$sys_type_arr[$systemType['system_type']];
        if($systemType['system_type'] == 2){
            $msg .= ',计费规则:'.$systemType['pay_rule'];
            $msg .= ',冻结规则:'.$systemType['freeze_rule'];
        }

        $msg .= ',营销超市计费规则:'.$systemType['market_pay_rule'];
        //$msg .= ',营销超市冻结规则:'.$systemType['market_freeze_rule'];

        $apiObj->api_response($msg);
    }

    function do_chang_pwd($data,& $apiObj){
        $db = kernel::database();
        $op = $db->selectrow('select account_id from sdb_pam_account where login_name = "'.$data['login_name'].'"');
        if($op){
            $db->exec('update sdb_pam_account set login_password = "'.md5($data['login_password']).'" where account_id='.$op['account_id']);
            $apiObj->api_response('已重置');
        }else{
            $apiObj->api_response('没有此管理员');
        }

    }
    
    function set_version_code($data,& $apiObj)
    {
        ob_start();
        $version_code = $data['version_code'];
        kernel::single('taocrm_system')->set_version_code($version_code, 'reset');
        ob_clean();
        $apiObj->api_response('菜单版本已重置为:'.$version_code);
    }
}
