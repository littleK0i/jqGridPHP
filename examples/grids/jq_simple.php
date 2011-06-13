<?php

class jq_simple extends jqGrid
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

			'first_name'=>array('label' => 'Frist name',
								'width'	=> 35,
								),

			'last_name' =>array('label' => 'Last name',
								'width' => 35,
								),

			'email'     =>array('label' => 'Email',
								'width' => 30,
								),

			'phone'     =>array('label' => 'Phone',
								'width'	=> 25,
								'align' => 'center',
								),

			'discount'	=>array('label' => 'Discount',
								'width'	=> 15,
								'formatter' => 'numeric',
								'align'	=> 'center',
								),

			'date_register'=>array('label' => 'Register',
								'width'	=> 20,
								'formatter' => 'date',
								'align'	=> 'center',
								'editrules' => array('required' => true, 'date' => true),
								),
		);

		#Set nav
		$this->nav = array('add' => true, 'edit' => true, 'del' => true);
	}
}