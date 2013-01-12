<?php
/**
 * Sample MySQL driver
 * It's just an example - use PDO if you can
 */

class jqGrid_DB_Mssql extends jqGrid_DB
{
    protected $db_type = 'mssql';

    public function link()
    {
        static $link = null;

        if(!$link)
        {
            $host = $this->Loader->get('db_host');
            $user = $this->Loader->get('db_user');
            $pass = $this->Loader->get('db_pass');
            $name = $this->Loader->get('db_name');

            $link = mssql_connect($host, $user, $pass);

            if(!$link)
            {
                $this->throwMssqlException();
            }

            if(!mssql_select_db($name, $link))
            {
                $this->throwMssqlException();
            }
        }

        return $link;
    }

    public function query($query)
    {
        $result = mssql_query($query, $this->link());

        return $result;
    }

    public function fetch($result)
    {
        return mssql_fetch_assoc($result);
    }

    function mssql_escape_string($string)
    {
        return str_replace("'", "''", $string);
    }

    public function quote($val)
    {
        if(is_null($val))
        {
            return null;
        }

        return "'" . str_replace("'", "''", $val) . "'";
    }

    public function rowCount($result)
    {
        return mssql_rows_affected($this->link());
    }

    protected function throwMssqlException()
    {
        throw new jqGrid_Exception_DB(mssql_error(), null, mssql_errno());
    }
}