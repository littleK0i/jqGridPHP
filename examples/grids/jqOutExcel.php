<?php

class jqOutExcel extends jqGrid
{
	protected function init()
	{
		#This is all you need for simple Excel export
		$this->nav = array('excel' => true);

		$this->table = 'tbl_customer';

		$this->cols = array(

			'id'        =>array('label' => 'ID',
								'width' => 10,
								'align' => 'center',
								),

			'first_name'=>array('label' => 'First name',
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
		);
	}
}