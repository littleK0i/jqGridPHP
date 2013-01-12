<?php

class jqGrid_Exception_Render extends jqGrid_Exception
{
    protected $exception_type = 'render';
    protected $output_type = 'trigger_error';

    public function __toString()
    {
        return $this->getMessage();
    }
}