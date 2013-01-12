<?php
/**
 * Sample MySQL driver
 * It's just an example - use PDO if you can
 */

class jqGrid_DB_Mysql extends jqGrid_DB
{
    protected $db_type = 'mysql';

    public function link()
    {
        static $link = null;

        if(!$link)
        {
            $host = $this->Loader->get('db_host');
            $user = $this->Loader->get('db_user');
            $pass = $this->Loader->get('db_pass');
            $name = $this->Loader->get('db_name');

            $link = mysql_connect($host, $user, $pass);

            if(!$link)
            {
                $this->throwMysqlException();
            }

            if(!mysql_select_db($name, $link))
            {
                $this->throwMysqlException();
            }
        }

        return $link;
    }

    public function query($query)
    {
        $result = mysql_query($query, $this->link());

        if(!$result)
        {
            $this->throwMysqlException($query);
        }

        return $result;
    }

    public function fetch($result)
    {
        return mysql_fetch_assoc($result);
    }

    public function quote($val)
    {
        if(is_null($val))
        {
            return null;
        }

        return "'" . mysql_real_escape_string($val, $this->link()) . "'";
    }

    public function rowCount($result)
    {
        return mysql_affected_rows($this->link());
    }

    public function lastInsertId()
    {
        return mysql_insert_id($this->link());
    }

    protected function throwMysqlException($query = null)
    {
        throw new jqGrid_Exception_DB(mysql_error(), array('query' => $query), mysql_errno());
    }
}