<?php

class jqGrid_DB_Mysql extends jqGrid_DB
{
	protected $db_type = 'mysql';

	public function link()
	{
		static $link = null;

		if(!$link)
		{
			$host = $this->loader->get('db_host');
			$user = $this->loader->get('db_user');
			$pass = $this->loader->get('db_pass');
			$name = $this->loader->get('db_name');
			
			$link = mysql_connect($host, $user, $pass);
			mysql_select_db($name, $link);
		}

		return $link;
	}

	public function query($query)
	{
		return mysql_query($query, $this->link());
	}

	public function fetch($result)
	{
		return mysql_fetch_assoc($result);
	}

	public function quote($val)
	{
		if(is_null($val))
		{
			return null;
		}

		return "'" . mysql_real_escape_string($val, $this->link());
	}

	public function rowCount($result)
	{
		return mysql_affected_rows($result);
	}
}