<?php
class ecorder_mdl_order_objects extends dbeav_model{

    var $has_many = array(
       'order_items' => 'order_items',
    );

}
