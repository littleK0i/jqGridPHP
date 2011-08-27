<?php

class jqWelcome extends jqGrid
{
	protected function init()
	{
		$this->contract_types = array(
			1 => 'Short',
			2 => 'Long',
			3 => 'Permanent',
		);
	
		#Set database table
		$this->table = 'tbl_customer';

		#Make all columns editable by default
		$this->cols_default = array('editable' => true);

		#Set columns
		$this->cols = array(
			
			'id'        =>array('label' => 'ID',
								'width' => 10,
								'align' => 'center',
								'editable' => false, //id is non-editable
								),

			'first_name'=>array('label' => 'Frist name',
								'width'	=> 30,
								'editrules' => array('required' => true),
								'formoptions' => array('label' => 'First name <span style="color: red;">*</span>'),
								),

			'last_name' =>array('label' => 'Last name',
								'width' => 30,
								'editrules' => array('required' => true),
								'formoptions' => array('label' => 'Last name <span style="color: red;">*</span>'),
								),

			'email'     =>array('label' => 'Email',
								'width' => 30,
								'formatter' => 'email',
								'editrules' => array('email' => true),
								'formoptions' => array('label' => 'Email <span style="color: red;">*</span>'),
								),

			'phone'     =>array('label' => 'Phone',
								'width'	=> 25,
								'align' => 'left',
								'formatoptions' => array('prefix' => '+'),
								'formoptions' => array('elmsuffix' => '&nbsp;&nbsp;(11 digits)'),
								),

			'discount'	=>array('label' => 'Discount',
								'width'	=> 15,
								'formatter' => 'numeric',
								'align'	=> 'center',
								'editable' => true,
								),
								
			'contract_type'
						=>array('label' => 'Contract',
								'width'	=> 20,
								'formatter' => 'ext_replace',
								'formatoptions' => array('value' => $this->contract_types),
								'editable' => true,
								'edittype' => 'select',
								'editoptions' => array('value' => new jqGrid_Data_Value($this->contract_types)),
								'stype'   => 'select',
								'searchoptions' => array('value' => new jqGrid_Data_Value($this->contract_types, 'All')),
								),
		);

		#Set nav
		$this->nav = array('add' => true, 'addtext' => 'Add', 'edit' => true, 'edittext' => 'Edit', 'del' => true, 'deltext' => 'Delete', 'prmEdit' => array('width' => '300px'), 'prmAdd' => array('width' => '300px'), 'excel' => true, 'exceltext' => 'Excel');
	}
	
	protected function parseRow($r)
	{
		$r['contract_type'] = $this->contract_types[$r['contract_type']];
		
		if($r['discount'] > 0.15)
		{
			$r['_class']['discount'] = 'font-red';
		}
		
		if($r['phone'])
		{
			$r['phone'] = preg_replace('#(\d{1})(\d{3})(\d{3})(\d{2})(\d{2})#', '\1 \2 \3 \4 \5', $r['phone']);
			$r['phone'] = '+' . $r['phone'];
		}
		
		return $r;
	}
	
	protected function operData($r)
	{
		if(strlen($r['first_name']) < 3)
		{
			throw new jqGrid_Exception('Frist name is too short');
		}
		
		if(strlen($r['last_name']) < 3)
		{
			throw new jqGrid_Exception('Last name is too short');
		}
		
		#Email already exists?
		$result = $this->DB->query("SELECT id FROM {$this->table} WHERE email=" . $this->DB->quote($r['email']));
		$row = $this->DB->fetch($result);
		
		if($row and $row['id'] != $this->input('id'))
		{
			throw new jqGrid_Exception('Email already exists');
		}
		
		#Phone has letters?
		if($r['phone'] and preg_match('#[^0-9]#', $r['phone']))
		{
			$r['phone'] = preg_replace('#\D#', '', $r['phone']);
			
			if(strlen($r['phone']) != 11)
			{
				throw new jqGrid_Exception('Phone must contain 11 digits');
			}
		}
		
		$r['discount'] = 0;
		
		return $r;
	}
}