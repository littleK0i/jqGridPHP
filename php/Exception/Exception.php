<?php

class jqGrid_Exception extends Exception
{
	protected $data;
	protected $type = 'general';
	
	public function __construct($message, $data=null, $code=0, Exception $previous = null)
	{
		$this->data = $data;
		return parent::__construct($message, $code, $previous);
	}

	public function getData()
	{
		return $this->data;
	}

	public function getType()
	{
		return $this->type;
	}
}