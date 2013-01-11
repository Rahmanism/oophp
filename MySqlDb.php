<?php

class MySqlDb {

    protected $_mysql;
    protected $_query;
    protected $_where = array();
    protected $_paramTypeList;

    /**
     * Initiates the DB object.
     * 
     * @param string $host The MySql DB host
     * @param string $username User name
     * @param string $password Password
     * @param string $db The mysql database name
     */
    public function __construct($host, $username, $password, $db) {
        $this->_mysql = new mysqli($host, $username, $password, $db)
                or die('Problem in connecting to the DB.');
    }

    /**
     * Executes a query.
     * 
     * @param string $query Contains a user-provided select query.
     */
    public function Query($query) {
        $this->_query = filter_var($query, FILTER_SANITIZE_STRING);

        $stmt = $this->_PrepareQuery();
        $stmt->execute();
        $results = $this->_DynamicBindResults($stmt);
        return $results;
    }

    function Get($tableName, $numRows = NULL) {
        $this->_query = "select * from $tableName";
        $stmt = $this->_BuildQuery($numRows);
        $results = $stmt->execute();

        $results = $this->_DynamicBindResults($stmt);
        return $results;
    }

    function Insert($tableName, $insertData) {
        $this->_query = "insert into $tableName";
        $stmt = $this->_BuildQuery(NULL, $insertData);
        $stmt->execute();

        if ($stmt->affected_rows) {
            return true;
        }
    }

    function Update($tableName, $updateData) {
        
    }

    public function Delete($tableName) {
        
    }

    public function Where($whereProp, $whereValue) {
        $this->_where[$whereProp] = $whereValue;
    }

    protected function _PrepareQuery() {
        if (!$stmt = $this->_mysql->prepare($this->_query)) {
            trigger_error('Problem preparing query', E_USER_ERROR);
        }
        return $stmt;
    }

    protected function _BuildQuery($numRows = NULL, $tableData = false) {
        $hasTableData = false;
        if (gettype($tableData) === 'array') {
            $hasTableData = true;
        }

        // Did the user call the where method?
        if (!empty($this->_where)) {
            $keys = array_keys($this->_where);
            $whereProp = $keys[0];
            $whereValue = $this->_where[$whereProp];

            // if data was passed, filter through and 
            // create the SQL query, accordingly.
            if ($hasTableData) {
                $i = 1;
                foreach ($tableData as $prop => $value) {
                    echo $prop . ' ' . $value . '<Br>';
                }
            } else { // no table data was passed. Might be a SELECT statement.
                $this->_paramTypeList = $this->_DetermineType($whereValue);
                $this->_query .= " where $whereProp = ?";
            }
        }
        
        // Determine if is INSERT query
        if ($hasTableData) {
            $pos = strpos($this->_query, 'insert');
        }
        
        if ($pos !== false) {
            // is INSERT statement
            $keys = array_keys($tableData);
            $values = array_values($tableData);
            $num = count($keys);
            
            if ($num > 0) {
                // wrap values in quotes
                foreach ($values as $key => $value) {
                    $values[$key] = "'{value}'";
                    $this->_paramTypeList .= $this->_DetermineType($value);
                }

                $this->_query .= ' (' . implode($keys, ', ') . ')';
                $this->_query .= ' values (';

                while ($num > 1) {
                    //$this->_query .=  ($num !== 1) ? '?, ' : '?)';
                    $this->_query .=  '?, ';
                    $num--;
                }
                $this->_query .= '?)';
            }
        }

        // Did the user set a limit?
        if (isset($numRows)) {
            $this->_query .= " limit " . (int) $numRows;
        }

        $stmt = $this->_PrepareQuery();

        // Bind parameters
        if ($hasTableData) {
            $args = array();
            $args[] = $this->_paramTypeList;
            
            foreach ($tableData as $prop => $val) {
                $args[] = &$tableData[$prop];
            }
            
            call_user_func_array( array($stmt, 'bind_param'), $args);
        } elseif ($this->_where) {
            $stmt->bind_param($this->_paramTypeList, $whereValue);
        }

        return $stmt;
    }

    protected function _DetermineType($item) {
        switch (gettype($item)) {
            case 'string' :
                $paramType = 's';
                break;
            case 'integer' :
                $paramType = 'i';
                break;
            case 'blob' :
                $paramType = 'b';
                break;
            case 'double' :
                $paramType = 'd';
                break;
        }

        return $paramType;
    }

    /**
     * This helper method takes care of prepared statements' "bind_result"
     * method, when the number of variables to pass is unknown.
     * 
     * @param object $stmt Equal to the prepared statement object.
     * @return array The results of the SQL fetch.
     */
    protected function _DynamicBindResults($stmt) {
        $parameters = array();
        $results = array();
        $meta = $stmt->result_metadata();

        while ($field = $meta->fetch_field()) {
            $parameters[] = &$row[$field->name];
        }

        call_user_func_array(array($stmt, 'bind_result'), $parameters);
        // array($stmt, 'bind_result') ==> $stmt.bind_result();

        while ($stmt->fetch()) {
            $x = array();

            foreach ($row as $key => $val) {
                $x[$key] = $val;
            }
            $results[] = $x;
        }
        return $results;
    }

    function __destruct() {
        $this->_mysql->close();
    }

}

?>
