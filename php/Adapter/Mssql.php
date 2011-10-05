<?php
/**
* Mssql adapter for jqGrid
*/

abstract class jqGrid_Adapter_Mssql extends jqGrid
{
	protected $do_sort = false;
	protected $do_limit = false;
	
	protected $where_empty = '1=1';
	
	protected function buildFields($cols)
	{
		return parent::buildFields($cols) . ', ROW_NUMBER() OVER (' . $this->buildOrderBy($this->sidx, $this->sord) . ') AS _row_number';
	}
	
	protected function buildOrderBy($sidx, $sord)
	{
		if(!$sidx) $sidx = $this->primary_key;
		
		return parent::buildOrderBy($sidx, $sord);
	}
	
	protected function buildQueryRows($q)
	{
		$query = parent::buildQueryRows($q);
		
		$offset_min = max($this->page * $this->limit - $this->limit, 0) + 1;
		$offset_max = $offset_min + $this->limit;
		
		$query = "
			SELECT *
			FROM ($query) a
			WHERE _row_number BETWEEN $offset_min AND $offset_max
		";
		
		return $query;
	}
}