<?php
/**
 * Special data structures for jqGrid
 * Uncommon serialization
 */

namespace jqGridPHP\Container;

abstract class AbstractContainer
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    abstract function __toString();
}
