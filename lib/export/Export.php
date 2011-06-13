<?php

abstract class jqGrid_Export
{
	protected $loader;

	public $input;
	public $grid_id;

	public $cols;
	public $rows;
	public $userdata;

	public $page;
	public $total;
	public $records;

	public function __construct(jqGridLoader $loader)
	{
		$this->loader = $loader;
	}

	abstract public function doExport();
}