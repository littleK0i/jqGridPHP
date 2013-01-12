<?php

class jqGrid_DB_ReadRu extends jqGrid_DB
{
    protected $db_type = 'postgresql';

    public function link()
    {
        return;
    }

    public function query($query)
    {
        if(stripos(ltrim($query), 'SELECT') === 0)
        {
            return coreDB::dbGetData($query);
        }
        else
        {
            return coreDB::dbSetData($query);
        }
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

        return "'" . pg_escape_string($val) . "'";
    }

    public function rowCount($result)
    {
        return pg_affected_rows($result);
    }
}