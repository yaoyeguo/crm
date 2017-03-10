<?php
class market_mdl_wx_keywords extends dbeav_model {


    function check($keyword,& $msg){
        if(empty($keyword))return false;

        if(!is_array($keyword)){
            $keyword = array($keyword);
        }

        $rows = $this->db->select('select * from sdb_market_wx_keywords where keyword in("'.implode('","', $keyword).'")');
        if($rows){
            $type =  array(
                'products'=>'商品推荐',
                'reply'=>'关键词回复',
        		'system'=>'系统',
                'survey'=>'问答活动',
            	'regist'=>'签到回复',
                'vote'=>'投票',
                'due'=>'预约',
            );
            foreach($rows as $row){
                $msg .= sprintf('关键字【%s】已经存在于【%s】里<br />',$row['keyword'],$type[$row['source']]);
            }
            return true;
        }else{
            return false;
        }
    }

    function delete($keyword){
        if(empty($keyword))return false;
         
        if(!is_array($keyword)){
            $keyword = array($keyword);
        }
        $this->db->exec('delete from sdb_market_wx_keywords where keyword in("'.implode('","', $keyword).'")');
    }

    function add($keyword,$source){
        if(empty($keyword))return false;
         
        if(!is_array($keyword)){
            $keyword = array($keyword);
        }

        foreach($keyword as $v){
            if(!empty($v)){
                $data = array('keyword'=>$v,'source'=>$source);
                $this->save($data);
            }
        }
    }

    function getPluginsKey(){
        $keys = array();
        $rows = $this->db->select('select keyword from sdb_market_wx_keywords where source="plugin"');
        foreach($rows as $row){
            $keys[] = $row['keyword'];
        }

        return $keys;
    }

    function getRowByKeyWord($searchkey){
        return $this->db->selectrow('select * from sdb_market_wx_keywords where keyword="'.$searchkey.'"');
    }

}