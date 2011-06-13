<?php

class jq_xml extends jq_base
{
	protected function init()
	{
		$types = array(
			'book' 		=> '�����',
			
			'author' 	=> '�����',
			'genre'		=> '����',
			'series'	=> '�����',
			'pubhouse'	=> '��������',
		);
		
		$this->tblName = 'tbl_xml_exclude';
		
		$this->query = "
			SELECT {fields}
			FROM {$this->tblName}
			WHERE {where}
		";
		
		$this->cols_default = array('align' => 'center');
		
		$this->cols = array(
			'id'		=>array('hidden'=> true,
								),
			'item_type'	=>array('name'		=> '���',
								'width'		=> 15,
								'editable'	=> true,
								'edittype'	=> 'select',
								'editoptions' => array('value' => $types),
								'stype'		=> 'select',
								'searchoptions' => array('value' => array_merge(array('' => '-���-'), $types)),
								'replace'	=> $types,

								'colType'	=>
								),
								
			'item_id'	=>array('name'		=> 'ID',
								'width'		=> 10,
								'formatter' => 'integer',
								'editable'	=> true,
								'editrules'	=> array('required' => true, 'integer' => true),
								),
								
			'xml'		=>array('name'	=> 'XML',
								'db'	=> 'text',
								'width'	=> 26,
								'editable' => true,
								'editrules' => array('required' => true),
								'editoptions' => array('defaultValue' => 'Yandex'),
								),
								
			'active'	=>array('name'	=> '�������',
								'width'	=> 5,
								'formatter' => 'checkbox',
								'editable'	=> true,
								'edittype' => 'checkbox',
								'editoptions' => array('defaultValue' => 1, 'value' => '1:0'),
								'search'	=> false,
								),
		);
	}
	
	protected function parseRow($r)
	{
		$r['xml'] = substr($r['xml'], 1, -1);

		$r['_class'] = 'green';
		return $r;
	}
	
	protected function oper_data($r)
	{
		$r['xml'] = utils::implodePgArray(array_map('trim', explode(',', $r['xml'])));
		return $r;
	}
	
	public function form()
	{
		$hash = array_merge(
			glob('/www/site.read.ru/xml/y4dXML/*.gz'),
			glob('/www/site.read.ru/xml/*.gz'),
			glob('/www/site.read.ru/xml/partner/*.gz')
		);
		
		$files = array();
		
		foreach($hash as $f)
		{
			$files[$f] = basename($f, '.gz');
		}
		
		arsort($files);
		
		$this->output->toTemplate('files', $files);
	}
	
	protected function op_check_id()
	{
		$file = escapeshellcmd($this->request['file']);
		$id = intval($this->request['id']);
		
		$ret = array();
		
		//$cmd = 'less '.$file.' | grep "<offer id=\"'.$id.'\"';
		$cmd = "zless $file | grep '<offer id=\"$id\"'";
		//$cmd = '"'.$cmd.'"';
		
		$ret = shell_exec($cmd);
		
		echo $ret ? 'yes' : 'no';
	}
}