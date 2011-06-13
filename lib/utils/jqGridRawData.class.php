<?php

class jqGridRawData
{
	protected $text = '';
	
	public function __construct($text)
	{
		$this->text = $text;
	}

	public function __toString()
	{
		return $this->getText();
	}

	public function getText()
	{
		return $this->text;
	}
}