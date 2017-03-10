#!/bin/bash
function checkprocess(){
    if (ps aux|grep -v grep|grep "$1" )
    then
        echo "active"
    else
        echo "miss"
        #echo $1
        PHP_BIN_VAL $1 &
    fi
}

cd CRM_DIR_VAL/script/
checkprocess "CRM_DIR_VAL/script/thread_order.php"
checkprocess "CRM_DIR_VAL/script/thread_cstools_order.php"
checkprocess "CRM_DIR_VAL/script/queue/queue.php"
checkprocess "CRM_DIR_VAL/script/queuewaiting/queue.php"
