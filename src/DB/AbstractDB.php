<?php
namespace jqGridPHP\DB;

abstract class AbstractDB
{
    protected $config = array();
    protected $connection;

    /**
     * @param $config
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * Establish connection to database
     *
     * @return mixed
     */
    protected function getConnection()
    {
        if (empty($this->connection)) {
            $this->connection = $this->initConnection();
        }

        return $this->connection;
    }

    abstract protected function initConnection();

    /**
     * INSERT query wrapper
     *
     * @param string $table_name
     * @param array $ins - key => value pairs
     * @param bool $last_insert_id - if true - returns last insert id
     * @return mixed
     */
    public function insert($table_name, array $ins, $last_insert_id = false)
    {
        $table_name = \jqGridPHP\Utils::checkAlphanum($table_name);
        $ins = $this->cleanArray($ins);

        $q = "INSERT INTO $table_name (" . implode(', ', array_keys($ins)) . ") VALUES (" . implode(', ', $ins) . ")";
        $this->query($q);

        return $this->lastInsertId() ?: true;
    }

    /**
     * Clean array keys and values for later use in SQL
     *
     * @param array $arr
     * @return array
     */
    protected function cleanArray(array $arr)
    {
        $clean = array();

        foreach ($arr as $k => $v) {
            $key = Utils::checkAlphanum($k);

            if ($v instanceof \jqGridPHP\Container\AbstractContainer) {
                $val = strval($v); //no escaping for containers
            } else {
                $val = is_null($v) ? 'NULL' : $this->quote($v);
            }

            $clean[$key] = $val;
        }

        return $clean;
    }

    /**
     * Like PDO::quote
     * @abstract
     * @param mixed $value
     * @return string
     */
    abstract public function quote($value);

    /**
     * Execute SQL-query
     *
     * @abstract
     * @param $query
     * @return resource
     */
    abstract public function query($query);

    /**
     * Fetch-assoc one row
     *
     * @abstract
     * @param resource $result
     * @return array
     */
    abstract public function fetch($result);

    /**
     * Get database-specific lastInsertId
     * Overload it!
     *
     * @return integer
     */
    public function lastInsertId()
    {
        return null;
    }

    /**
     * UPDATE query wrapper
     * Be careful with string $cond - it is not clean!
     *
     * @param string $tblName - table name
     * @param array $upd - key => value pairs
     * @param mixed $cond - key => value pairs, integer (for id=) or string
     * @param bool $row_count - if true - return row count (affected_rows)
     * @return mixed
     */
    public function update($tblName, array $upd, $cond, $row_count = false)
    {
        $tblName = jqGrid_Utils::checkAlphanum($tblName);
        $upd = $this->cleanArray($upd);

        $set = array();
        $where = array();

        #Build 'set'
        foreach ($upd as $k => $v) {
            $set[] = $k . '=' . $v;
        }

        #Build 'where'
        if (is_numeric($cond)) //simple id=
        {
            $where[] = 'id=' . intval($cond);
        } elseif (is_array($cond)) {
            $cond = $this->cleanArray($cond);

            foreach ($cond as $k => $v) {
                if ($v === 'NULL') {
                    $where[] = $k . ' IS NULL';
                } else {
                    $where[] = $k . '=' . $v;
                }
            }
        }

        #Execute
        $q = "UPDATE $tblName SET " . implode(', ', $set) . " WHERE " . ($where ? implode(' AND ', $where) : $cond);

        $result = $this->query($q);

        if ($row_count) {
            return $this->rowCount($result);
        }

        return $result;
    }

    /**
     * DELETE query wrapper
     *
     * @param string $tblName - table name
     * @param mixed $cond - key => value pairs, integer (for id=) or string
     * @return resource
     */
    public function delete($tblName, $cond)
    {
        $tblName = jqGrid_Utils::checkAlphanum($tblName);
        $where = array();

        #Build 'where'
        if (is_numeric($cond)) {
            $where[] = 'id=' . intval($cond);
        } elseif (is_array($cond)) {
            $cond = $this->cleanArray($cond);

            foreach ($cond as $k => $v) {
                if ($v === 'NULL') {
                    $where[] = $k . ' IS NULL';
                } else {
                    $where[] = $k . '=' . $v;
                }
            }
        }

        $q = "DELETE FROM $tblName WHERE " . ($where ? implode(' AND ', $where) : $cond);

        $result = $this->query($q);

        return $result;
    }

    /**
     * Shortcut for creating new 'jqGrid_Data_Raw' object
     *
     * @param string $val
     * @return jqGrid_Data_Raw
     */
    public function raw($val)
    {
        return new \jqGridPHP\Container\Raw($val);
    }
}
