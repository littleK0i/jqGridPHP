<?php

abstract class jqGrid_DB
{
	protected $loader;
	protected $db_type;

	abstract public function link();
	abstract public function query($query);
	abstract public function fetch($result);
	abstract public function quote($value);
	abstract public function rowCount($result);

	public function __construct(jqGridLoader $loader)
	{
		$this->loader = $loader;
	}

	public function insert($tblName, array $ins, $last_id=false)
	{
		$tblName = jqGrid_Utils::checkAlphanum($tblName);
		$ins = $this->cleanArray($ins);

		$q = "INSERT INTO $tblName (" . implode(', ', array_keys($ins)) . ") VALUES (" . implode(', ', $ins) . ")";

		if($last_id and $this->db_type == 'postgresql')
		{
			$q .= ' RETURNING ' . $this->primary_key;
		}

		$result = $this->query($q);

		if($last_id)
		{
			switch($this->db_type)
			{
				case 'postgresql';
					return array_shift($this->fetch($result));
					break;

				default:
					return $this->link()->lastInsertId();
					break;
			}
		}

		return $result;
	}

	public function update($tblName, array $upd, $cond)
	{
		$tblName = jqGrid_Utils::checkAlphanum($tblName);
		$upd = $this->cleanArray($upd);

		$set = array();
		$where = array();

		#Build 'set'
		foreach($upd as $k => $v)
		{
			$set[] = $k . '=' . $v;
		}

		#Build 'where'
		if(is_numeric($cond)) //simple id=
		{
			$where[] = 'id=' .intval($cond);
		}
		elseif(is_array($cond))
		{
			$cond = $this->cleanArray($cond);

			foreach($cond as $k => $v)
			{
				if($v === 'NULL')
				{
					$where[] = $k . ' IS NULL';
				}
				else
				{
					$where[] = $k . '=' . $v;
				}
			}
		}

		#Execute
		$q = "UPDATE $tblName SET " . implode(', ', $set) . " WHERE " . ($where ? implode(' AND ', $where) : $cond);
		
		$result = $this->query($q);

		return $result;
	}

	public function delete($tblName, $cond)
	{
		$tblName = jqGrid_Utils::checkAlphanum($tblName);

		#Build 'where'
		if(is_numeric($cond))
		{
			$where[] =  'id=' .intval($cond);
		}
		elseif(is_array($cond))
		{
			$cond = $this->cleanArray($cond);

			foreach($cond as $k => $v)
			{
				if($v === 'NULL')
				{
					$where[] = $k . ' IS NULL';
				}
				else
				{
					$where[] = $k . '=' . $v;
				}
			}
		}

		$q = "DELETE FROM $tblName WHERE " . ($where ? implode(' AND ', $where) : $cond);

		$result = $this->query($q);

		return $result;
	}

	public function getType()
	{
		return $this->db_type;
	}

	protected function cleanArray(array $arr)
	{
		$clean = array();

		foreach($arr as $k => $v)
		{
			$key = jqGrid_Utils::checkAlphanum($k);

			if(is_object($v) and $v instanceof jqGridRawData)
			{
				$val = strval($v); //no escaping on specififc field
			}
			else
			{
				$val = is_null($v) ? 'NULL' : $this->quote($v);
			}

			$clean[$key] = $val;
		}

		return $clean;
	}
}