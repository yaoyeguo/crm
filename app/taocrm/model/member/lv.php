<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class taocrm_mdl_member_lv extends dbeav_model{
    var $defaultOrder = array('experience', ' ASC');
    function save(&$aData){
        $default_lv_id = $this->get_default_lv();
        if(isset($aData['point'])) $aData['experience'] = $aData['point'];
        if(isset($aData['experience'])) $aData['point'] = $aData['experience'];
        return parent::save($aData);
    }

    function get_default_lv(){
        $ret = $this->getList('member_lv_id',array('default_lv'=>1));
        return $ret[0]['member_lv_id'];
    }

    function unset_default_lv($default_lv_id){
        $sdf['member_lv_id'] = $default_lv_id;
        $sdf['default_lv'] = 0;
        $this->save($sdf);
    }

    function getLvExperience($experience){
        $ret = $this->getList('member_lv_id',array('experience|sthan'=>$experience),0,-1,'experience desc');
        return $ret[0]['member_lv_id'];
    }

    function validate(&$data,&$msg){
       $fag = 1;
       if($data['name']==''){
             $msg = app::get('taocrm')->_('等级名称不能为空！');
             $fag = 0;
        }
        $ret = $this->getList('member_lv_id',array('name'=>$data['name']));
        $member_lv_id = $ret[0]['member_lv_id'];
        $lv = $this->getList('*',array('default_lv'=>1));
        if(isset($data['experience'])){
            $data['experience'] = intval($data['experience']);
            $filter = array('experience' => $data['experience']);
            $levelSwitch = app::get('taocrm')->_("经验值");
            $exist = $this->getList('*',$filter);
            $default_lv = $lv[0]['name'];
            if($exist && ($exist[0]['member_lv_id'] != $data['member_lv_id'])){
                $msg = app::get('taocrm')->_('已存在').$levelSwitch.app::get('taocrm')->_('相同的客户等级');
                $fag = 0;
            }
        }
        
        if( $member_lv_id && $member_lv_id != $data['member_lv_id']){
             $msg = app::get('taocrm')->_('同名客户等级存在！');
             $fag = 0;
        }
        if(($data['default_lv'] == 1 && $default_lv)&&$data['member_lv_id'] !=$lv[0]['member_lv_id']){
             $msg = $default_lv.app::get('taocrm')->_('  已是默认等级，请先取消！！');
             $fag = 0;
        }
        if($data['point'] < 0 || $data['experience'] < 0){
            $msg = $levelSwitch.app::get('taocrm')->_('不能为负！');
            $fag = 0;
        }
        return $fag;
    }

    /**
     * get_member_lv_switch
     * 客户等级升级提示信息
     *
     * @access public
     * @return array
     */
    public function get_member_lv_switch($member_lv_id=null,$type='next'){
        if(!$member_lv_id) return null;
        $arr_member_lv = $this->getList('member_lv_id,name,point,experience',array(),0,-1,'experience ASC');
        foreach($arr_member_lv as $k => $v){
            if($v['member_lv_id'] == $member_lv_id){
                if($type=='next'){
                    $i = $k+1;
                }elseif($type=='pre'){
                    $i = $k-1;
                }
                break;
            }
        }

        if($type=='next'){
            $result['show'] = ($i>=count($arr_member_lv))? 'NO' : 'YES';
        }elseif($type=='pre'){
            $result['show'] = ($i<0)? 'NO' : 'YES';
        }

        $result['member_lv_id'] = $arr_member_lv[$i]['member_lv_id'];
        $result['name'] = $arr_member_lv[$i]['name'];
        $result['experience'] = $arr_member_lv[$i]['experience'];
        return $result;
    }

    function pre_recycle($data){
        $members = $this->app->model('members');
        foreach($data as $val){
            $pre_lv = $this->get_member_lv_switch($val['member_lv_id'],'pre');
            if($pre_lv['show']=='YES' && $pre_lv['member_lv_id']){
                $setSql = '`member_lv_id`='.$pre_lv['member_lv_id'];
            }else{
                $default_lv = $this->get_default_lv();
                $default_lv = $default_lv ? $default_lv : 0;
                $setSql = '`member_lv_id`='.$default_lv;
            }

            $sql = 'UPDATE sdb_taocrm_members SET '.$setSql.' WHERE `member_lv_id`='.$val['member_lv_id'];
            if(!$this->db->exec($sql)){
                $this->recycle_msg = '该等级下客户更新失败,不能删除！';
                return false;
            }
        }
        return true;
    }
}
