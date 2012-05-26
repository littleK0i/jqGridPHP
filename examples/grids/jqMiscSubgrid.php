<?php

class jqMiscSubgrid extends jqGrid
{
	protected function init()
	{
		#Set database table
		$this->table = 'tbl_customer';

		#Set columns
		$this->cols = array(
			
			'id'        =>array('label' => 'ID',
								'width' => 10,
								'align' => 'center',
								),

			'first_name'=>array('label' => 'First name',
								'width'	=> 35,
								'editable' => true,
								),

			'last_name' =>array('label' => 'Last name',
								'width' => 35,
								'editable' => true,
								),

			'email'     =>array('label' => 'Email',
								'width' => 30,
								'editable' => true,
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
	
	protected function opRenderSubgrid()
	{
		echo $this->loader->render('jqMiscSubgrid2', array('customer_id' => $this->input('customer_id')));
		exit;
	}
	
	protected function opEdit($id, $upd)
	{
		return true;
	}
}