<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class ecorder_mdl_shop_lv extends dbeav_model{
    var $defaultOrder = array('min_amount', ' ASC');
    
    function save(&$aData){
        //$default_lv_id = $this->get_default_lv($aData['shop_id']);
        //if(isset($aData['point'])) $aData['experience'] = $aData['point'];
        //if(isset($aData['experience'])) $aData['point'] = $aData['experience'];
        return parent::save($aData);
    }
    
    // 初始化店铺的客户等级规则
    public function set_default_lv($shop_id) {
        
        if(!$shop_id) return false;
        $arr['name'] = '普通客户';
        $arr['shop_id'] = $shop_id;
        $arr['is_active'] = '1';
        $arr['is_default'] = '1';
        $arr['create_time'] = time();
        $q = $this->save($arr);
        return $q;
    }
    
    function get_lv_name()
    {
        $rs = $this->getList('lv_id,name');
        if($rs){
            foreach($rs as $v){
                $levels[$v['lv_id']] = $v['name'];
            }
        }
        return $levels;
    }

    function get_default_lv($shop_id){
        $ret = $this->getList('shop_lv_id',array('default_lv'=>1,'shop_id'=>$shop_id));
        return $ret[0]['shop_lv_id'];
    }

    function unset_default_lv($default_lv_id,$shop_id){
        $sdf['shop_lv_id'] = $default_lv_id;
        $sdf['default_lv'] = 0;
        $sdf['shop_id'] = $shop_id;
        $this->save($sdf);
    }

    /**
     * 
     * 获取用户等级
     * @param int $experience
     * @param string $shop_id
     */
    function getLvExperience($experience,$shop_id) {
        $ret = $this->getList('shop_lv_id',array('experience|sthan'=>$experience,'shop_id'=>$shop_id),0,-1,'experience desc');
        /**
         * Modify by Tian Xingang 2011-11-02
         * 对于积分为0的客户，按照原方法得到的等级也为0，应该设置为默认等级
         */
        return $ret[0]['shop_lv_id'] ? $ret[0]['shop_lv_id'] : $this->get_default_lv($shop_id);
    }

    function valid_data(&$data,&$msg,$tag){
        $fag = 1;
        if($data['shop_id']==''){
            $msg = app::get('taocrm')->_('店铺信息出错！');
            $fag = 0;
        }
        if($data['name']==''){
            $msg = app::get('taocrm')->_('等级名称不能为空！');
            $fag = 0;
        }
        	if ($tag==0) {
	        $filter=array('shop_id'=>$data['shop_id'],'is_default'=>1);
	        $ret = $this->getList('*',$filter);
	        if ($ret && $data['is_default']==1){
	        	$msg = app::get('taocrm')->_('同一个店铺不能有两个默认等级！');
	            $fag = 0;
	        }
        	}
      
        /*
        $ret = $this->getList('shop_lv_id',array('name'=>$data['name'],'shop_id'=>$data['shop_id']));
        $shop_lv_id = $ret[0]['shop_lv_id'];
        $lv = $this->getList('*',array('default_lv'=>1,'shop_id'=>$data['shop_id']));
        if(isset($data['experience'])){
            $data['experience'] = intval($data['experience']);
            $filter = array('experience' => $data['experience'],'shop_id' => $data['shop_id']);
            $levelSwitch = app::get('taocrm')->_("经验值");
            $exist = $this->getList('*',$filter);
            $default_lv = $lv[0]['name'];
            if($exist && ($exist[0]['shop_lv_id'] != $data['shop_lv_id'])){
                $msg = app::get('taocrm')->_('已存在').$levelSwitch.app::get('taocrm')->_('相同的客户等级');
                $fag = 0;
            }
        }
        
        if( $shop_lv_id && $shop_lv_id != $data['shop_lv_id']){
             $msg = app::get('taocrm')->_('同名客户等级存在！');
             $fag = 0;
        }
        if(($data['default_lv'] == 1 && $default_lv)&&$data['shop_lv_id'] !=$lv[0]['shop_lv_id']){
             $msg = $default_lv.app::get('taocrm')->_('  已是默认等级，请先取消！！');
             $fag = 0;
        }
        if($data['point'] < 0 || $data['experience'] < 0){
            $msg = $levelSwitch.app::get('taocrm')->_('不能为负！');
            $fag = 0;
        }
        */
        return $fag;
    }

    /**
     * get_member_lv_switch
     * 客户等级升级提示信息
     *
     * @access public
     * @return array
     */
    public function get_member_lv_switch($data=array(),$type='next'){
        if(!$data['shop_lv_id']) return null;
        $arr_member_lv = $this->getList('shop_lv_id,name,point,experience',array('shop_id'=>$data['shop_id']),0,-1,'experience ASC');
        foreach($arr_member_lv as $k => $v){
            if($v['shop_lv_id'] == $data['shop_lv_id']){
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

        $result['shop_lv_id'] = $arr_member_lv[$i]['shop_lv_id'];
        $result['name'] = $arr_member_lv[$i]['name'];
        $result['experience'] = $arr_member_lv[$i]['experience'];
        return $result;
    }

    function pre_recycle($data){
    	foreach ($data as $k=>$v) {
    		$lv_filter=array('lv_id'=>$v['lv_id']);
    		 $shopObj = &app::get('taocrm')->model('member_analysis');
    		$count = $shopObj->count($lv_filter);
    		if ($count){
    			$this->recycle_msg = "该等级下存在客户，不能删除";
    			return false;
    		}
    	}
        $lvCount = $this->count(array('shop_id'=>$data[0]['shop_id']));
        if(count($data)>=$lvCount){
            $this->recycle_msg = '不能删除该店铺下所有等级！';
            return false;
        }
//        foreach($data as $val){
//            $pre_lv = $this->get_member_lv_switch($val,'pre');
//            if($pre_lv['show']=='YES' && $pre_lv['shop_lv_id']){
//                $setSql = '`shop_lv_id`='.$pre_lv['shop_lv_id'];
//            }else{
//                $default_lv = $this->get_default_lv($val['shop_id']);
//                $default_lv = $default_lv ? $default_lv : 0;
//                $setSql = '`shop_lv_id`='.$default_lv;
//            }
//
//            $sql = 'UPDATE sdb_taocrm_members SET '.$setSql.' WHERE `shop_lv_id`='.$val['shop_lv_id'];
//            if(!$this->db->exec($sql)){
//                $this->recycle_msg = '该等级下客户更新失败,不能删除！';
//                return false;
//            }
//        }
        return true;
    }
}
