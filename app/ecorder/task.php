<?php
class ecorder_task{
    function post_install($options){
        kernel::single('base_initial', 'ecorder')->init();
    }
}
