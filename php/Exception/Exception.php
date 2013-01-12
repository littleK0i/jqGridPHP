<?php

class jqGrid_Exception extends Exception
{
    protected $data;
    protected $exception_type = 'common';
    protected $output_type = 'json';

    public function __construct($message, $data = null, $code = 0)
    {
        $this->data = $data;
        return parent::__construct($message, $code);
    }

    public function getData()
    {
        return $this->data;
    }

    public function getExceptionType()
    {
        return $this->exception_type;
    }

    public function getOutputType()
    {
        return $this->output_type;
    }
}