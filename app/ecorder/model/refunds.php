<?php

class ecorder_mdl_refunds extends dbeav_model{
    var $has_many = array(
       'delivery' => 'delivery'
    );

    /* create_refunds 添加退款单
     * @param sdf $sdf
     * @return sdf
     */
    function create_refunds(&$sdf){
        $this->save($sdf);
    }


    function refund_detail($refund_id){
        $refund_detail = $this->dump($refund_id);
        return $refund_detail;
    }

    function save(&$refund_data,$mustUpdate=NULL){
        return parent::save($refund_data,$mustUpdate,true);
    }
    /*
     * 生成退款单号
     *
     *
     * @return 退款单号
     */
     function gen_id(){
        $i = rand(0,9999);
        do{
            if(9999==$i){
                $i=0;
            }
            $i++;
            $refund_bn = date("YmdH").'14'.str_pad($i,6,'0',STR_PAD_LEFT);
            $row = $this->db->selectrow('select refund_bn from sdb_ome_refunds where refund_bn =\''.$refund_bn.'\'');
        }while($row);
        return $refund_bn;
    }

    function searchOptions(){
        return array(

            );
    }
}
?>