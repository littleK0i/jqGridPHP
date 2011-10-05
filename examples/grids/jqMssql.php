<?php

class jqMssql extends jqGrid_Adapter_Mssql
{	
	public function __construct($loader)
	{
		$loader->set('pdo_dsn', "sqlsrv:Server=localhost\SQLEXPRESS;Database=new_db");
		$loader->set('pdo_user', null);
		$loader->set('pdo_pass', null);
		
		/*
		$loader->set('db_driver', 'mssql');
		$loader->set('db_host', 'localhost');
		$loader->set('db_user', null);
		$loader->set('db_pass', null);
		$loader->set('db_name', 'test');
		*/
	
		parent::__construct($loader);
	}
	
	protected function init()
	{
		$this->options = array('rowNum' => 5);
	
		$this->table = 'test';
		
		$this->cols = array(
			'id'	=>array('width' => 10),
			'name'  =>array('width' => 40),
		);
	}
}