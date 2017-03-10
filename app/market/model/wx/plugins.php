<?php
class market_mdl_wx_plugins extends dbeav_model {


    function init(){
        $row = $this->db->selectrow('select count(*) as total from sdb_market_wx_plugins');
        if(intval($row['total']) == 0){

            $plugins = array();
            /*$plugins[] = array(
                'img'=>'vcard.png',
                'keyword'=>'tb#',
                'plugin_name'=>'绑定账号',
                'desc'=>'当微信用户进行和公众账号进行互动时，用户通过发送【tb#】来进行淘宝账号绑定。',
                'status'=>0
            );*/
            $plugins[] = array(
                'img'=>'step.png',
                'keyword'=>'sj#',
                'plugin_name'=>'绑定手机',
                'desc'=>'当微信账号和公用账号进行企业互动时，用户通过发送【sj#】来进行手机绑定',
                'status'=>0
            );
            $plugins[] = array(
                'img'=>'sign.png',
                'keyword'=>'jf#',
                'plugin_name'=>'查询积分',
                'desc'=>'微信关注用户可以通过微信发送【jf#】查看当前微信账号对应账号拥有的店铺积分。',
                'status'=>0
            );
            $plugins[] = array(
                'img'=>'sender.png',
                'keyword'=>'wl#',
                'plugin_name'=>'查询物流',
                'desc'=>'微信关注用户可以通过微信发送【wl#】查看当前微信账号对应的快递单号。',
                'status'=>0
            );
            $objWxKeyWords = &app::get('market')->model('wx_keywords');
            foreach($plugins as $item){
                $this->save($item);
                $keyword = array('keyword'=>$item['keyword'],'source'=>'plugin');
                $objWxKeyWords->save($keyword);
            }
        }
    }

    function getPlugins(){
        return $this->db->select('select * from sdb_market_wx_plugins');
    }

    function isOpen($keyword){
        $row = $this->db->selectrow('select * from sdb_market_wx_plugins where keyword="'.$keyword.'"');
        if($row && $row['status'] == 1){
            return true;
        }else{
            return false;
        }
    }
}