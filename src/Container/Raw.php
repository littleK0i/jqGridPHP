<?php

namespace jqGridPHP\Container;

class Raw extends AbstractContainer
{
    public function __toString()
    {
        return $this->data;
    }
}
