<?php
/**
 * Special data structures for jqGrid
 * Uncommon serialization
 */
abstract class jqGrid_Data
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