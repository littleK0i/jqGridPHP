<?php

class jq_csv extends jqGridCSV
{
	protected function init()
	{
		$this->csv = $this->loader->get('grid_path') . DS . $this->grid_id . DS . 'example.csv';

		$this->options = array(
			'caption'	=> 'CSV Data Wrapper',
			'width'		=> 700,
		);

		$this->cols_default = array('editable' => true);

		$this->cols = array(
			'id'		=>array('label' => 'ID',
								'width'	=> 10,
								'formatter' => 'integer',
								'editable' => false,
								),

			'first_name'=>array('label'	=> 'First Name',
								'width'	=> 25,
								),

			'last_name' =>array('label'	=> 'Last Name',
								'width'	=> 25,
								),

			'address'	=>array('label' => 'Address',
								'width'	=> 35,
								),

			'city'		=>array('label'	=> 'City',
								'width'	=> 20,
								),

			'state'		=>array('label' => 'State',
								'width'	=> 10,
								'align'	=> 'center',
								),

			'post_index'=>array('label'	=> 'Index',
								'width'	=> 15,
								'align'	=> 'center',
								),
		);
	}

}