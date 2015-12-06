<?php
/**
 * Sample MySQL driver
 */

namespace jqGridPHP;

class Mysql extends AbstractDB
{
    /**
     * @return \mysqli
     * @throws jqException
     */
    protected function initConnection()
    {
        $Mysqli = mysqli_init();

        if (isset($this->config['options'])) {
            foreach ($this->config['options'] as $k => $v) {
                if ($this->Mysqli->options($k, $v)) {
                    throw new jqException("Could not set mysqli option [$k] to [$v]");
                }
            }
        }

        $result = $Mysqli->real_connect($this->config['host'], $this->config['user'], $this->config['pass'], $this->config['dbname']);

        if (empty($result)) {
            throw new jqException($Mysqli->connect_error);
        }

        if (isset($this->config['charset'])) {
            $result = $Mysqli->set_charset($this->config['charset']);

            if (empty($result)) {
                throw new jqException($Mysqli->error);
            }
        }

        return $Mysqli;
    }

    /**
     * @param $query
     * @return bool|\mysqli_result
     * @throws jqException
     */
    public function query($query)
    {
        /** @var $Mysqli \mysqli */
        $Mysqli = $this->getConnection();
        $result = $Mysqli->query($query);

        if (empty($result)) {
            throw new jqException($Mysqli->error, array('query' => $query));
        }

        return $result;
    }

    /**
     * @param \mysqli_result $result
     * @return array|void
     */
    public function fetch($result)
    {
        return $result->fetch_assoc();
    }

    /**
     * @param mixed $val
     * @return null|string
     */
    public function quote($val)
    {
        if (is_null($val)) {
            return null;
        }

        /** @var $Mysqli \mysqli */
        $Mysqli = $this->getConnection();

        return "'" . $Mysqli->real_escape_string($val) . "'";
    }

    public function lastInsertId()
    {
        /** @var $Mysqli \mysqli */
        $Mysqli = $this->getConnection();
        return $Mysqli->insert_id;
    }
}