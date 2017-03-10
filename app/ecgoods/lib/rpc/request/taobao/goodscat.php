<?php
/**
 * 商品分类同步请求
 * @author ome
 * @access public
 * @copyright www.shopex.cn 2010
 *
 */
class ecgoods_rpc_request_taobao_goodscat extends ecgoods_rpc_request {

    var $topClient;
    var $goodsCats = array();
    var $count = 0;

    public function get(){

        $c = new ectools_top_TopClient;
        $c->format = "json";
        $c->appkey = "10011902";
        $c->secretKey = "2fc426b0109908169017efb33a71f15c";
        $this->topClient = $c;
        
        $this->buildTree(0);
        //error_log(var_export($this->goodsCats,true),3,DATA_DIR.'/sy.txt');

    }

    protected function findChild($parentId){
        $req = new ectools_top_request_ItemcatsGetRequest();
        $req->setFields('cid,parent_cid,name,is_parent,status,sort_order');
        $req->setParentCid($parentId);
        $resp = $this->topClient->execute($req,$top_session);
        //var_dump($resp);exit;
        $childs=array();
        if($resp->code || $resp->msg):
        $resp = ('【code】'.$resp->code.'<br/>【msg】'.$resp->msg);
        elseif(!$resp):
        $resp = ('timeout');
        else:
        if($resp->item_cats->item_cat) {
            foreach($resp->item_cats->item_cat as $v) {
                if(is_object($v)) $v = get_object_vars($v);
                $childs[] = $v;
                $this->count++;
                echo $this->count . ':' . $v['name']."\n";
            }
        }
        endif;

        return $childs;
    }

    protected function buildTree($parentId=0){
    //   $this->count++;
        $childs= $this->findChild($parentId);
        //var_dump($childs);exit;
        if(empty($childs)){
            return null;
        }

        foreach ($childs as $k => $v){
            $rescurTree = $this->buildTree($v['parent_cid']);
            if( null !=   $rescurTree){
                //$this->goodsCats[$k]['childs'] = $rescurTree;
            }
        }
    }
}