<?php
/**
 * Mssql adapter for jqGridPHP
 */

abstract class jqGrid_Adapter_Mssql extends jqGrid
{
    protected $do_sort = false;
    protected $do_limit = false;
    protected $where_empty = '1=1';

    protected function buildFields($cols)
    {
        $fields = parent::buildFields($cols);

        if($this->limit > -1)
        {
            $fields .= ', ROW_NUMBER() OVER (' . $this->buildOrderBy($this->sidx, $this->sord) . ') AS _rownum';
        }

        return $fields;
    }

    protected function buildQueryRows($q)
    {
        $query = parent::buildQueryRows($q);

        if($this->limit > -1)
        {
            $offset_min = max($this->page * $this->limit - $this->limit, 0) + 1;
            $offset_max = $offset_min + $this->limit;

            $query = "
				SELECT *
				FROM ($query) a
				WHERE _rownum BETWEEN $offset_min AND $offset_max
			";
        }

        return $query;
    }
}