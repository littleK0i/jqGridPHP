<?php

class jqGeneral extends jqGrid
{
	protected function init()
	{
		#Set database table
		$this->table = 'tbl_customer';

		$this->cols_default = array('editable' => true);

		#Set columns
		$this->cols = array(
			
			'id'        =>array('label' => 'ID',
								'width' => 10,
								'align' => 'center',
								'editable' => false,
								'search_op' => 'in',
								),

			'first_name'=>array('label' => 'Frist name',
								'width'	=> 35,
								'search_op' => 'in',
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
								),
		);

		#Set nav
		$this->nav = array('add' => true, 'edit' => true, 'del' => true);
	}

	protected function operData($r)
	{
		throw new jqGrid_Exception('ohhh');
		return $r;
	}
}