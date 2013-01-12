<?php
/**
 * Sample PostgreSQL driver
 * It's just an example - use PDO if you can
 */

class jqGrid_DB_Pgsql extends jqGrid_DB
{
    protected $db_type = 'postgresql';

    public function link()
    {
        static $link = null;

        if(!$link)
        {
            $connect = $this->Loader->get('db_pg_connect');
            $link = pg_connect($connect);

            if(!$link)
            {
                throw new jqGrid_Exception_DB(pg_last_error());
            }
        }

        return $link;
    }

    public function query($query)
    {
        $result = pg_query($this->link(), $query);

        if(!$result)
        {
            throw new jqGrid_Exception_DB(pg_last_error(), array('query' => $query));
        }

        return $result;
    }

    public function fetch($result)
    {
        return pg_fetch_assoc($result);
    }

    public function quote($val)
    {
        if(is_null($val))
        {
            return $val;
        }

        return "'" . pg_escape_string($this->link(), $val) . "'";
    }

    public function rowCount($result)
    {
        return pg_affected_rows($result);
    }
}