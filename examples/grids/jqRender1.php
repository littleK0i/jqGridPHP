<?php

class jqRender1 extends jqGrid
{
	protected function init()
	{
		#Grid options in PHP init
		$this->options = array(
			'multiselect'  => true,
			'multiboxonly' => true,

			#JS function in PHP are possible, but not recommended!
			'onSelectRow' => new jqGrid_Data_Raw("function(id){ alert('Row '+id+' selected'); }"),
		);

		$this->table = 'tbl_customer';

		$this->cols = array(

			'id'        =>array('label' => 'ID',
								'width' => 10,
								'align' => 'center',
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
		);
	}

	#Final options hook. Called on rendering only!
	protected function renderOptions($opts)
	{
		$opts['caption'] = 'Members on ' . date('d.m.Y');

		return $opts;
	}
}