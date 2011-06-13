<?php

abstract class jqGrid_Adapter_Example extends jqGrid
{
	protected function dbConnect()
	{
		static $link = null;

		if(!$link)
		{
			$link = $this->loader->get('DB');
		}

		return $link;
	}

	protected function dbQuery($q)
	{
		return pg_query($this->dbConnect(), $q);
	}

	protected function dbFetch($result)
	{
		return pg_fetch_assoc($result);
	}

	protected function dbQuote($val)
	{
		if(is_null($val))
		{
			return $val;
		}

		return "'" . pg_escape_string($this->dbConnect(), $val) . '"';
	}

	protected function dbRowCount($result)
	{
		return pg_affected_rows($result);	
	}
}