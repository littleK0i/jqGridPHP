<?php

class jqGrid_Data_Raw extends jqGrid_Data
{
    public function __toString()
    {
        return $this->data;
    }
}