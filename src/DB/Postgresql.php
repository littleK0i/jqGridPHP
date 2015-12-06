<?php
/**
 * Sample PostgreSQL driver
 */

namespace jqGridPHP;

class Postgresql extends AbstractDB
{
    protected function initConnection()
    {
        $connection = pg_connect($this->config['connection_string']);

        if (empty($connection)) {
            throw new jqException(pg_last_error());
        }

        return $connection;
    }

    public function query($query)
    {
        $result = pg_query($this->getConnection(), $query);

        if (!$result) {
            throw new jqException(pg_last_error(), array('query' => $query));
        }

        return $result;
    }

    public function fetch($result)
    {
        return pg_fetch_assoc($result);
    }

    public function quote($val)
    {
        if (is_null($val)) {
            return $val;
        }

        return "'" . pg_escape_string($this->getConnection(), $val) . "'";
    }
}