<?php
class taocrm_cache_report{

    function getCacheId($func,$filter){

        //过滤多余的参数
        foreach($filter as $k=>$v){
            if(stristr('shop_id,goods_id,date_from,date_to,count_by,r,f,nums,money',$k) == false)
            unset($filter[$k]);
        }
        //var_dump($filter);

        return sprintf($_SERVER['SERVER_NAME'].':cache:report:%s:%s',$func,$this->assemble($filter));

    }

    function assemble($params)
    {
        if(!is_array($params))  return '';

        ksort($params, SORT_STRING);
        $sign = '';
        foreach($params AS $key=>$val){
            if(is_null($val))   continue;
            if(is_bool($val))   $val = ($val) ? 1 : 0;
            $sign .= $key . (is_array($val) ? $this->assemble($val) : $val);
        }

        $sign = md5($sign);

        return $sign;
    }

    function isCache(){

    }

    /**
     *
     *$result = array('status'=>'request');
     *$result = array('status'=>'runing');
     *$result = array('status'=>'finish','data'=>array(),'expired'=>113123);
     *
     *REQ_CACHE:请求缓存
     *PRE_CACHE:准备缓存
     *SUCC:成功
     *EXPIRED_CACHE:缓存过期
     *EX_CACHE:缓存异常 redis数据返回未知的状态
     *NO_CACHE:没有缓存
     *
     */
    function get($cacheId){
        $result = kernel::single('taocrm_service_redis')->redis->GET($cacheId);
        $return = array();
        if(!empty($result)){
            $result = json_decode($result,true);
            if($result['status'] == 'request'){
                $return = array('status'=>'REQ_CACHE');
            }elseif($result['status'] == 'runing'){
                $return = array('status'=>'PRE_CACHE');
            }elseif($result['status'] == 'finish'){
                if($result['expired'] > time()){
                    $return = array('status'=>'SUCC','data'=>$result['data']);
                }else{
                    $return = array('status'=>'EXPIRED_CACHE');
                }
            }else{
                $return = array('status'=>'EX_CACHE');
            }
        }else{
            $return = array('status'=>'NO_CACHE');
        }

        return $return;
    }

    function clear(){
        while(true){
            $cacheId = kernel::single('taocrm_service_redis')->redis->LPOP('global:cache_ids');
            if(!$cacheId)break;

            kernel::single('taocrm_service_redis')->redis->DEL($cacheId);
        }
    }

    function clearById($cacheId){
        if($cacheId){
            kernel::single('taocrm_service_redis')->redis->DEL($cacheId);
        }
    }

    function fetch($func,$filter){
        $cacheId = $this->getCacheId($func, $filter);
        $result = array('status'=>'request');
        kernel::single('taocrm_service_redis')->redis->SET($cacheId,json_encode($result));

        kernel::single('taocrm_service_redis')->redis->LPUSH('global:cache_ids',$cacheId);

        $jobarray = array('func'=>$func,'filter'=>$filter,'cacheId'=>$cacheId);
        $jobId = kernel::single('taocrm_service_queue')->addJob('market_backstage_report@fetch',$jobarray);
        if($jobId){
            return true;
        }else{
            return false;
        }
    }

}