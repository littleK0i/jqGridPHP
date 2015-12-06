<?php
/**
 * The recommended PDO driver for jqGridPHP
 */

class jqGrid_DB_Pdo extends jqGrid_DB
{
    public function __construct(jqGridLoader $loader)
    {
        parent::__construct($loader);

        $dsn = $loader->get('pdo_dsn');
        $this->db_type = substr($dsn, 0, strpos($dsn, ':') + 1);
    }

    public function link()
    {
        static $link = null;

        if($link) return $link;

        $dsn = $this->Loader->get('pdo_dsn');
        $user = $this->Loader->get('pdo_user');
        $pass = $this->Loader->get('pdo_pass');
        $opts = $this->Loader->get('pdo_options');

        try
        {
            $link = new PDO($dsn, $user, $pass, $opts);
            $link->setAttribute(PDO::ATTR_ERRMODE, 2);
        }
        catch(PDOException $e)
        {
            throw new jqGrid_Exception_DB($e->getMessage(), null, $e->getCode());
        }

        return $link;
    }

    public function query($sql)
    {
        try
        {
            return $this->link()->query($sql);
        }
        catch(PDOException $e)
        {
            throw new jqGrid_Exception_DB($e->getMessage(), array('query' => $sql), $e->getCode());
        }
    }

    public function fetch($result)
    {
        return $result->fetch(PDO::FETCH_ASSOC);
    }

    public function quote($val)
    {
        if(is_null($val))
        {
            return $val;
        }

        return $this->link()->quote($val);
    }

    public function rowCount($result)
    {
        return $result->rowCount();
    }

    public function lastInsertId($name = null)
    {
        return $this->link()->lastInsertId($name);
    }
}