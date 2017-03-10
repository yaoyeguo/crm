<?php

class taocrm_database {

    var $host;
    var $pConnect = 0;
    var $user;
    var $pass;
    var $name;
    var $queries;
    var $linkID = 0;

    function connect($host, $user, $pass, $name,$port) {

        $this->host = $host;
        $this->user = $user;
        $this->pass = $pass;
        $this->name = $name;
        $this->port = $port;
        $this->linkID = 0;
		
        $this->linkID = @mysql_connect($this->host.':'.$this->port, $this->user, $this->pass, true)
                or $this->error('无法连接到数据库服务器');
       if(preg_match('/[0-9\.]+/is',mysql_get_server_info($this->linkID),$match)){
            $dbver = $match[0];
            if(version_compare($dbver,'4.1.1','<')){
                define('DB_OLDVERSION',1);
                $this->dbver = 3;
            }else{
                mysql_query('SET NAMES \'UTF8\'',$this->linkID);
                if(!version_compare($dbver,'6','<')){
                    $this->dbver = 6;
                }
            }
        }
        $this->selectDb($this->name);
		
        unset($database);
    }

    function selectDb($db="") {

        if (!$db)
            $db = $this->name;
        @mysql_select_db($db, $this->linkID) or $this->error("无法选择数据库 $db");
    }

    function query($queryString = '', $returnType = '') {
        if ($queryString != '') {
            $this->queries[] = $queryString;

            $result = @mysql_query($queryString, $this->linkID) or $this->error("执行{$queryString}失败！");
            //or $this->error();
            if ($returnType == '') {

                return $result;
            } elseif ($returnType == '1') {

                $row = $this->fetchRow($result);
                return $row["0"];
            } elseif ($returnType == 'row') {

                return $this->fetchRow($result);
            } elseif ($returnType == 'array') {

                return $this->fetchArray($result);
            } elseif ($returnType == 'all') {
                $rows = array();
                while ($row = $this->fetchArray($result)) {
                    $rows[] = $row;
                }
                return $rows;
            } else {

                return $this->fetchObject($result);
            }
        } else {

            $this->queries[] = "未指定SQL语句！";
            $this->error();
            //$this->error ( '未指定SQL语句!' );
        }
    }

    function freeResult($res) {

        return @mysql_free_result($res);
    }

    function fetchRow($fetchObject) {

        return @mysql_fetch_row($fetchObject);
    }

    function fetchArray($fetchObject) {

        return @mysql_fetch_array($fetchObject, MYSQL_ASSOC);
    }

    function fetchObject($fetchObject) {

        return @mysql_fetch_object($fetchObject);
    }

    function numRows($result) {

        return @mysql_num_rows($result);
    }

    function affectedRows() {

        return @mysql_affected_rows($this->linkID);
    }

    function insertId() {

        $ID = @mysql_insert_id($this->linkID);
        return $ID;
    }

    function dataSeek($result, $torow = 0) {

        return @mysql_data_seek($result, $torow);
    }

    function createDb($dbname = '') {

        if ($dbname == '') {

            $this->error('未指定数据库');
        }
        return @mysql_create_db($dbname);
    }

    function dropDb($dbname = '') {

        if ($dbname == '') {

            $this->error('未指定数据库');
        }
        return @mysql_drop_db($dbname);
    }

    function listTables($dbname = '') {

        if ($dbname == '') {

            $this->error('未指定数据库');
        }
        return @mysql_list_tables($dbname);
    }

    function tableName($result, $i) {

        if ($result == '') {

            $this->error('未指定数据表来源');
        }
        return @mysql_tablename($result, $i);
    }

    function close() {

        @mysql_close($this->linkID);
    }

    function error($error = '') {

        $sqlreturnError = mysql_error($this->linkID);
        $sqlreturnErrno = mysql_errno($this->linkID);
        $message = "错误信息描述：　  $msg<BR><BR>\n\n";
        $message.="SQL返回错误信息： $sqlreturnError<BR><BR>\n\n";
        $message.="SQL返回错误编号：$sqlreturnErrno<BR><BR>\n\n";
        $message.="发生错误时间：　 " . date("Y-m-d H:i:s") . "<BR><BR>\n\n";
        //echo "$message";
        //exit;
    }

}