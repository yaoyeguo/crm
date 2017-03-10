<?php

class ecorder_mdl_fx_order_objects extends dbeav_model{
    var $has_many = array(
       'order_items' => 'fx_order_items',
    );

}
?>