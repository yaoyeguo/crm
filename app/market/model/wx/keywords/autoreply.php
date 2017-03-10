<?php
class market_mdl_wx_keywords_autoreply extends dbeav_model {

    function modifier_keyword($row){

        if ($row){
            return implode(',', json_decode($row,true));
        }else{
            return '-';
        }
    }

    function delete($id){
        $ids = array();
        if(!is_array($id)){
            $ids[] = $id;
        }else{
            $ids = $id;
        }

        kernel::database()->exec('delete from sdb_market_wx_keywords_autoreply where id in ('.implode(',', $ids).')');

        return true;
    }

    function getKeywordsById($id){
        if(empty($id))return false;

        if(!is_array($id)){
            $ids[] = $id;
        }else{
            $ids = $id;
        }

        $keywords = array();
        $rows = $this->db->select('select keyword from sdb_market_wx_keywords_autoreply where id in ('.implode(',', $ids).')');
        foreach($rows as $v){
            $keyword = json_decode($v['keyword'],true);
            $keywords = array_merge($keywords,$keyword);
        }

        return $keywords;
    }

    function skipSelfKeyWords($id,& $keywords){
        $selfKeyWords = $this->getKeywordsById($id);
        $newKeyWords = array();
        foreach($keywords as $v){
            if(!in_array($v, $selfKeyWords)){
                $newKeyWords[] = $v;
            }
        }
        $keywords = $newKeyWords;
    }

}