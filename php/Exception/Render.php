<?php

class jqGrid_Exception_Render extends jqGrid_Exception
{
	protected $exception_type = 'render';
	protected $output_type    = '';

	public function __toString()
	{
		return 'document.write("Grid render failed: ' . htmlspecialchars($this->getMessage(), ENT_QUOTES) . '");';
	}
}