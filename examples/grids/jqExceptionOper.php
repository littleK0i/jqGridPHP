<?php

class jqExceptionOper extends jqGrid
{
	protected function init()
	{
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

			'first_name'=>array('label' => 'First name',
								'width'	=> 35,
								'editrules' => array('required' => true),
								'formoptions' => array('suffix' => 'gg'),
								),

			'last_name' =>array('label' => 'Last name',
								'width' => 35,
								'editrules' => array('required' => true),
								),

			'email'     =>array('label' => 'Email',
								'width' => 30,
								'editrules' => array('email' => true),
								),

			'phone'     =>array('label' => 'Phone',
								'width'	=> 25,
								'align' => 'center',
								),

			'discount'	=>array('label' => 'Discount',
								'width'	=> 15,
								'formatter' => 'numeric',
								'align'	=> 'center',
								'editable' => false,
								),
		);

		#Set nav
		$this->nav = array('add' => true, 'edit' => false, 'del' => false);
	}
	
	protected function operData($r)
	{
		if(strlen($r['first_name']) < 3)
		{
			throw new jqGrid_Exception('First name is too short');
		}
		
		if(strlen($r['last_name']) < 3)
		{
			throw new jqGrid_Exception('Last name is too short');
		}
		
		#Email already exists?
		$result = $this->DB->query("SELECT id FROM {$this->table} WHERE email=" . $this->DB->quote($r['email']));
		
		if($this->DB->fetch($result))
		{
			throw new jqGrid_Exception('Email already exists');
		}
		
		#Phone has letters?
		if($r['phone'] and preg_match('#[^0-9]#', $r['phone']))
		{
			throw new jqGrid_Exception('Phone number must contain only numbers');
		}
		
		throw new jqGrid_Exception('You have passed all requirements! But operation fails anyway for example purpose.');
		
		return $r;
	}
}