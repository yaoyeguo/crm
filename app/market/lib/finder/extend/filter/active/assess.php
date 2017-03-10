<?php
class market_finder_extend_filter_active_assess {
    public function get_extend_colums() {
        $db['active_assess'] = array(
            'columns' => array(
                'active_name' => array(
                    'type' => 'varchar(255)',
                    'label' => '活动名称',
                    'filtertype' => 'normal',
                    'filterdefault' => true,
                ),
            ),
        );
        return $db;
    }
}
