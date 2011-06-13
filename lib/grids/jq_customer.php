<?php

class jq_customer extends jqGrid
{
	protected function init()
	{
		$this->tblName = 'tbl_customer';
		$this->gender = array(1 => 'Ж', 2 => 'М');

		$this->cols_default = array('align' => 'center', 'editable' => true);
		
		$this->cols = array(
			'id'		=>array('label'		=> 'ID',
								'width'		=> 15,
								'formatter' => 'integer',
								'editable'	=> false,
								'supertest' => new jqGridRawData('function(){alert("gg!");}'),
								),

			'last_name' =>array('label'		=> 'Фамилия',
								'width'		=> 40,
								'align'		=> 'left',
								'search_op'	=> 'like',
								'formatter' => 'ext_link',
								'formatoptions' => array('href'	=> 'http://microsoft.com/{id}/{phone}/',
														 'class'=> 'gg',
														// 'target' => '_zomg!',
													),
								),

			'first_name'=>array('label'		=> 'Имя',
								'width'		=> 40,
								'align'		=> 'left',
								'search_op'	=> 'like',
								),

			'gender'	=>array('label'		=> 'Пол',
								'width'		=> 12,
								'edittype'	=> 'select',
								'editoptions' => array('value' => $this->gender),
								'replace'	=> $this->gender,
								'search_op'	=> 'ignore',
								'stype'		=> 'select',
								'searchoptions' => array('value' => array('' => 'Все') + $this->gender),
								),

			'email'		=>array('label'		=> 'Email',
								'width'		=> 30,
								'formatter' => 'email',
								'search_op'	=> 'like',
								),
 
			'discount'	=>array('label'		=> 'Скидка',
								'width'		=> 15,
								'search_op'	=> 'numeric',
								'db_agg'	=> 'sum',
								),

			'phone'		=>array('label'		=> 'Телефон',
								'width'		=> 30,
								'search_op'	=> 'like',
								),

		);
	}

	protected function parseRow($r)
	{
		$r['_class'] = array(
			'_row' => ($r['id'] % 3 == 0) ? 'green' : '',
			'first_name' => ($r['id'] % 2 == 0) ? 'grey' : '',
			'id'		=> ($r['id'] % 5 == 0) ? 'orange' : '',
		);

		return $r;
	}

	protected function operData($r)
	{
		if(!$r['first_name'])
		{
			throw new jqGridException('Wah!', 'first_name');
		}
		
		return $r;
	}

	protected function renderOptions($r)
	{
		$r['ZOMG'] = new jqGridRawData('{}');
		return $r;
	}

	public function test()
	{
		$first_names = array('John', 'James', 'Bill', 'Ted');
		$last_names = array('Clinton', 'zong', 'gg');
		
		for($i=0; $i <= 300; $i++)
		{
			$ins = array(
				'first_name'	=> $first_names[array_rand($first_names)],
				'last_name'		=> $last_names[array_rand($last_names)],
				'email'			=> 'sometest@gmail.com',
				'discount'		=> mt_rand(0, 20) / 100,
				'gender'		=> mt_rand(1,2),
				'phone'			=> 7916 . mt_rand(0, 9999999),
			);

			$this->dbInsert($this->tblName, $ins);
		}

		echo 'done!';
	}
}