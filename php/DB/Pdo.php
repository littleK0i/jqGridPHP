<?php

class jqGrid_DB_Pdo extends jqGrid_DB
{
	public function __construct(jqGridLoader $loader)
	{
		parent::__construct($loader);

		$dsn = $loader->get('pdo_dsn');
		$this->db_type = substr($dsn, 0, strpos($dsn, ':') + 1);
	}

	public function link()
	{
		static $link = null;

		if($link) return $link;

		$dsn  = $this->loader->get('pdo_dsn');
		$user = $this->loader->get('pdo_user');
		$pass = $this->loader->get('pdo_pass');
		$opts = $this->loader->get('pdo_options');

		$link = new PDO($dsn, $user, $pass, $opts);
		$link->setAttribute(PDO::ATTR_ERRMODE, 2);

		return $link;
	}

	public function query($sql)
	{
		return $this->link()->query($sql);
	}

	public function fetch($result)
	{
		return $result->fetch(PDO::FETCH_ASSOC);
	}

	public function quote($val)
	{
		if(is_null($val))
		{
			return $val;
		}

		return $this->link()->quote($val);
	}

	public function rowCount($result)
	{
		return $result->rowCount();
	}
}