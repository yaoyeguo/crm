<?php

class taocrm_mdl_wangwang_shop extends dbeav_model
{
    /**
     * 保存信息
     */
    public function saveInfo($data)
    {

        $filter = array();
        $filter['shop_id'] = $data['shop_id'];
        $filter['sub_id'] = $data['sub_id'];
        $filter['seller_id'] = $data['seller_id'];
        $result = $this->dump($filter);
        if ($result) {
            $this->update($data, array('id' => $result['id']));
        }
        else {
            $this->insert($data);
        }
    }
}
