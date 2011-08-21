<?php

class jqOperBasic extends jqGrid
{
	protected function init()
	{
		$this->table = 'tbl_order_item';
		
		$this->query = "
			SELECT {fields}
			FROM tbl_order_item i
				JOIN tbl_books b ON (i.book_id=b.id)
			WHERE {where}
		";

		#Set columns
		$this->cols = array(
			
			'item_id'   =>array('label' => 'ID',
								'db'    => 'i.id',
								'width' => 10,
								'align' => 'center',
								'formatter' => 'integer',
								),

			'order_id'	=>array('label' => 'Order id',
								'db'    => 'i.order_id',
								'width' => 15,
								'align' => 'center',
								'formatter' => 'integer',
								),

			'name'		=>array('label' => 'Book name',
								'db'    => 'b.name',
								'width' => 30,
								'editable' => true,
								'editrules' => array('required' => true),
								),

			'price'		=>array('label' => 'Price',
								'db'    => 'i.price',
								'width' => 15,
								'align' => 'center',
								'formatter' => 'integer',
								'editable' => true,
								'editrules' => array('required' => true,
													 'integer' => true,
													 'minValue' => 1,
													 'maxValue' => 3000
													),
								),
		);

		#Set nav
		$this->nav = array('edit' => true, 'edittext' => 'Edit');
	}

	#Save columns to different tables
	protected function opEdit($id, $upd)
	{
		#Server-side validation
		if(strlen($upd['name']) < 5)
		{
			#Just throw the exception anywhere inside the oper functions to stop execution and display error
			throw new jqGrid_Exception('The book name is too short!');
		}

		#Get editing row
		$result = $this->DB->query('SELECT * FROM tbl_order_item WHERE id='.intval($id));
		$row = $this->DB->fetch($result);

		#Save book name to books table
		$this->DB->update('tbl_books', array('name' => $upd['name']), array('id' => $row['book_id']));
		unset($upd['name']);

		#Save other vars to items table
		$this->DB->update('tbl_order_item', $upd, array('id' => $id));
	}
}