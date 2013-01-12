<?php

abstract class jqGrid_DB
{
    /** @var $Loader jqGridLoader */
    protected $Loader;
    protected $db_type;

    /**
     * Establish connection to database
     *
     * @abstract
     * @return void
     */
    abstract public function link();

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
     * Like PDO::quote
     * @abstract
     * @param mixed $value
     * @return string
     */
    abstract public function quote($value);

    /**
     * Like PDO::rowCount and *_affected_rows
     * @abstract
     * @param resource $result
     * @return integer
     */
    abstract public function rowCount($result);

    /**
     * @param jqGridLoader $loader
     */
    public function __construct(jqGridLoader $loader)
    {
        $this->Loader = $loader;
    }

    /**
     * INSERT query wrapper
     *
     * @param string $tblName - table name
     * @param array $ins - key => value pairs
     * @param bool $last_insert_id - if true - returns last insert id
     * @return mixed
     */
    public function insert($tblName, array $ins, $last_insert_id = false)
    {
        $tblName = jqGrid_Utils::checkAlphanum($tblName);
        $ins = $this->cleanArray($ins);

        $q = "INSERT INTO $tblName (" . implode(', ', array_keys($ins)) . ") VALUES (" . implode(', ', $ins) . ")";

        #Special handling for PostgreSQL
        if($last_insert_id and $this->db_type == 'postgresql')
        {
            $q .= ' RETURNING *';
        }

        $result = $this->query($q);

        if($last_insert_id)
        {
            switch($this->db_type)
            {
                case 'postgresql':
                    return array_shift($this->fetch($result));
                    break;

                case 'mssql':
                    return array_shift($this->fetch($this->query("SELECT @@IDENTITY AS mixLastId")));
                    break;

                default:
                    return $this->lastInsertId();
                    break;
            }
        }

        return $result;
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
        foreach($upd as $k => $v)
        {
            $set[] = $k . '=' . $v;
        }

        #Build 'where'
        if(is_numeric($cond)) //simple id=
        {
            $where[] = 'id=' . intval($cond);
        }
        elseif(is_array($cond))
        {
            $cond = $this->cleanArray($cond);

            foreach($cond as $k => $v)
            {
                if($v === 'NULL')
                {
                    $where[] = $k . ' IS NULL';
                }
                else
                {
                    $where[] = $k . '=' . $v;
                }
            }
        }

        #Execute
        $q = "UPDATE $tblName SET " . implode(', ', $set) . " WHERE " . ($where ? implode(' AND ', $where) : $cond);

        $result = $this->query($q);

        if($row_count)
        {
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
        if(is_numeric($cond))
        {
            $where[] = 'id=' . intval($cond);
        }
        elseif(is_array($cond))
        {
            $cond = $this->cleanArray($cond);

            foreach($cond as $k => $v)
            {
                if($v === 'NULL')
                {
                    $where[] = $k . ' IS NULL';
                }
                else
                {
                    $where[] = $k . '=' . $v;
                }
            }
        }

        $q = "DELETE FROM $tblName WHERE " . ($where ? implode(' AND ', $where) : $cond);

        $result = $this->query($q);

        return $result;
    }

    /**
     * Get database-spcific lastInsertId
     * Overload it!
     *
     * @return integer
     */
    public function lastInsertId()
    {
        return null;
    }

    /**
     * Get database type
     * @return string
     */
    public function getType()
    {
        return $this->db_type;
    }

    /**
     * Shortcut for creating new 'jqGrid_Data_Raw' object
     *
     * @param string $val
     * @return jqGrid_Data_Raw
     */
    public function raw($val)
    {
        return new jqGrid_Data_Raw($val);
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

        foreach($arr as $k => $v)
        {
            $key = jqGrid_Utils::checkAlphanum($k);

            if(is_object($v) and $v instanceof jqGrid_Data)
            {
                $val = strval($v); //no escaping on specififc field
            }
            else
            {
                $val = is_null($v) ? 'NULL' : $this->quote($v);
            }

            $clean[$key] = $val;
        }

        return $clean;
    }
}