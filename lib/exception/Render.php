<?php

class jqGrid_Exception_Render extends jqGrid_Exception
{
	protected $data;
	protected $type = 'render';
	
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