<?php

class jqGrid_Exception extends Exception
{
	protected $data;
	protected $type = 'general';
	
	public function __construct($message, $data=null, $code=0)
	{
		$this->data = $data;
		return parent::__construct($message, $code);
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