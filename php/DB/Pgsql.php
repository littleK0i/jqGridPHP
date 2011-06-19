<?php

class jqGrid_DB_Pgsql extends jqGrid_DB
{
	protected $db_type = 'postgresql';
	
	public function link()
	{
		static $link = null;

		if(!$link)
		{
			$connect = $this->loader->get('db_pg_connect');
			$link = pg_connect($connect);
		}

		return $link;
	}

	public function query($query)
	{
		return pg_query($this->link(), $query);
	}

	public function fetch($result)
	{
		return pg_fetch_assoc($result);
	}

	public function quote($val)
	{
		if(is_null($val))
		{
			return $val;
		}

		return "'" . pg_escape_string($this->link(), $val) . "'";
	}

	public function rowCount($result)
	{
		return pg_affected_rows($result);
	}
}