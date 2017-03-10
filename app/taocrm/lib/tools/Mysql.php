<?php

class taocrm_tools_Mysql {

    var $_conn; // 数据库连接句柄
    var $_host; // 服务器地址
    var $_user; // 用户
    var $_password; // 密码
    var $_db; // 库
    var $_charset; // 编码
    var $_error;

    /**
     * 设置错误信息
     *
     * @param	string	$p_function		错误信息所在函数
     * @param	string	$p_error_string	具体错误信息
     * @return	无
     */
    function setError($p_function, $p_error_string) {
        $this->_error = ("[Mysql::$p_function] $p_error_string. (" . $this->_user . "@" . $this->_host . "::" . $this->_db . ")" . "[" . mysql_errno () . "::" . mysql_error () . "]");
    }

    public function getError() {
        return $this->_error;
    }

    /**
     * 打开数据库
     *
     * @param	string	$p_dbid	连接选择 例,$_CFG["DB"] 默认为default 连接
     * @return	boolean	成功返回 true, 失败返回 false
     */
    function open($dbconfig) {

        $this->_host = $dbconfig['host'];
        $this->_user = $dbconfig ['user'];
        $this->_password = $dbconfig ['password'];
        $this->_db = $dbconfig['db'];
        $this->_charset = 'UTF8';


        // 连接数据库
        $this->_conn = mysql_connect ( $this->_host, $this->_user, $this->_password, true );
        if (! $this->_conn) {
            $this->setError ( "open", "Can't connect server" );
            return false;
        }

        // 选择数据库，如果失败自动关闭连接
        $result = mysql_select_db ( $this->_db, $this->_conn );
        if (! $result) {
            $this->setError ( "open", "Can't select db" );
            mysql_close ( $this->_conn );
            return false;
        }

        // 如果设置了编码，自动调整
        if ($this->_charset != "") {
            $this->query ( "SET NAMES '" . $this->_charset . "'" );
            $this->query ( "SET CHARACTER_SET_CLIENT=" . $this->_charset );
            $this->query ( "SET CHARACTER_SET_RESULTS=" . $this->_charset );
        }

        return true;
    }

    /**
     * SQL查询
     *
     * @param	string		$p_sql	要查询的sql语句
     * @return	resource	成功返回 查询结果集, 失败返回 false
     */
    function query($p_sql) {
        // 执行查询语句
        $result = mysql_query ( $p_sql, $this->_conn );
        if (! $result) {
            print $this->_error.PHP_EOL;
            $this->setError ( "query", "SQL Error: " . $p_sql );
        }

        return $result;
    }

    /**
     * 插入数据
     *
     * @param	string	$p_table	表名
     * @param	array	$p_values	一个以下标对应字段名,值对应插入的值的数组
     * @return	boolean	成功返回 true, 失败返回 false
     */
    function insert($p_table, $p_values) {
        $sql_add_sub_name = '';
        $sql_add_var_name = '';
        foreach ( $p_values as $key => $var ) {
            $sql_add_sub_name .= " `" . $key . "` ,";
            $sql_add_var_name .= " '" . mysql_real_escape_string ( $var ) . "' ,";
        }
        $sql_add_sub_name = substr ( $sql_add_sub_name, 0, - 1 );
        $sql_add_var_name = substr ( $sql_add_var_name, 0, - 1 );

        $real_sql = "INSERT INTO " . $p_table . " (" . $sql_add_sub_name . ") VALUES (" . $sql_add_var_name . ")";
        return $this->query ( $real_sql );
    }

    /**
     * 替换唯一数据
     *
     * @param	string	$p_table	表名
     * @param	array	$p_values	一个以下标对应字段名,值对应插入的值的数组
     * @return	boolean	成功返回 true, 失败返回 false
     */
    function replace($p_table, $p_values) {
        $sql_add_sub_name = '';
        $sql_add_var_name = '';
        foreach ( $p_values as $key => $var ) {
            $sql_add_sub_name .= " `" . $key . "` ,";
            $sql_add_var_name .= " '" . mysql_real_escape_string ( $var ) . "' ,";
        }
        $sql_add_sub_name = substr ( $sql_add_sub_name, 0, - 1 );
        $sql_add_var_name = substr ( $sql_add_var_name, 0, - 1 );

        $real_sql = "REPLACE INTO " . $p_table . " (" . $sql_add_sub_name . ") VALUES (" . $sql_add_var_name . ")";
        return $this->query ( $real_sql );
    }

    /**
     * 更新数据
     *
     * @param	string	$p_table	表名
     * @param	array	$p_values	一个以下标对应字段名,值对应插入的值的数组
     * @param	string	$p_where		需要编辑的条件字串,不带WHERE
     * @return	boolean	成功返回 true, 失败返回 false
     */
    function update($p_table, $p_values, $p_where) {
        $edit_sql = "";
        foreach ( $p_values as $key => $var ) {
            $edit_sql .= "`" . $key . "` = " . "'" . mysql_real_escape_string ( $var ) . "',";
        }
        $edit_sql = substr ( $edit_sql, 0, - 1 );
        $real_sql = "UPDATE `" . $p_table . "` SET " . $edit_sql . " WHERE " . $p_where;

        return $this->query ( $real_sql );
    }

    /**
     * 删除数据
     *
     * @param	string	$p_table	表名
     * @param	string	$p_where	需要删除的条件字串,不带WHERE
     * @return	boolean	成功返回 true, 失败返回 false
     */
    function delete($p_table, $p_where) {
        $real_sql = "DELETE FROM " . $p_table . " WHERE " . $p_where;
        return $this->query ( $real_sql );
    }

    /**
     * 获取结果
     *
     * @param	resource	$p_result	query调用返回的查询结果集
     * @param	string		$p_type		返回的结果的形式，默认为数组
     * @return	array		成功返回 结果数据, 失败返回 false
     */
    function fetch($p_result, $p_type = "array", $r_type = MYSQL_ASSOC) {
        if ($p_type == "array") {
            return mysql_fetch_array ( $p_result, $r_type );
        } else if ($p_type == "object") {
            return mysql_fetch_object ( $p_result, $r_type );
        } else {
            return mysql_fetch_row ( $p_result );
        }
    }

    /**
     * 获取最后一次记录ID
     *
     * @return	int	成功返回 最新插入的记录ID, 失败返回 false
     */
    function getInsertID() {
        return mysql_insert_id ( $this->_conn );
    }

    /**
     * 返回最后操作的影响列数
     *
     * @return	int	成功返回 最后操作影响的列数, 失败返回 false
     */
    function getAffectedRows() {
        return mysql_affected_rows ();
    }

    /**
     * 返回结果集列数
     *
     * @param	resource	$p_result	query调用返回的查询结果集
     * @return	int	成功返回 结果集的记录数, 失败返回 false
     */
    public function getNumRows($p_result) {
        return mysql_num_rows ( $p_result );
    }

    /**
     * 定位结果集指针
     *
     * @param	resource	$p_result	query调用返回的查询结果集
     * @param	string	$p_num		定位到第num条
     * @return	int	返回记录的偏移量
     */
    function seek($p_result, $p_num) {
        return mysql_field_seek ( $p_result, $p_num );
    }

    /**
     * 释放结果集
     *
     * @param	resource	$p_result	query调用返回的查询结果集
     * @return	boolean	成功返回 true, 失败返回 false
     */
    function free($p_result) {
        return mysql_free_result ( $p_result );
    }

    /**
     * 关闭数据库连接
     *
     * @return	boolean	成功返回 true, 失败返回 false
     */
    function close() {
        return mysql_close ( $this->_conn );
    }

    /**
     *
     * 一次性返回所有记录集
     * @param string $sql
     */
    public function select($sql) {
        $result = $this->query ( $sql );
        $data = array ();
        if ($num = $this->getNumRows ( $result )) {
            while ( $row = $this->fetch ( $result ) ) {
                $data [] = $row;
            }
            return $data;
        }
        return false;
    }

    /**
     *
     * 返回一行数据
     * @param string $sql
     */
    public function selectrow( $sql) {
        $result = $this->query ( $sql );
        if ($num = $this->getNumRows ( $result )) {
            return $this->fetch ( $result );
        } else {
            return false;
        }
    }
}

?>
