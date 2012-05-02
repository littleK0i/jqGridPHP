<?php

class jqRenderData extends jqGrid
{
	protected function init()
	{
		$this->options = array(
			'rowNum' => 10,
		);

		#Set table
		$this->table = 'tbl_order_item';

		$this->query = "
			SELECT {fields}
			FROM tbl_order_item oi
				JOIN tbl_order o ON (oi.order_id=o.id)
				JOIN tbl_books b ON (oi.book_id=b.id)
			WHERE {where}
		";

		$this->cols_default = array('align' => 'center');

		#Set columns
		$this->cols = array(

			'id'        =>array('label' => 'ID',
								'db'    => 'oi.id',
								'hidden'=> true,
								),

			'book_id'   =>array('label' => 'Book ID',
								'db'    => 'oi.book_id',
								'width' => 10,
								),

			'book_name' =>array('label' => 'Book name',
								'db'    => 'b.name',
								'width' => 40,
								'align' => 'left',
								),

			'price'     =>array('label' => 'Price',
								'db'    => 'oi.price',
								'width' => 15,
								),

			'quantity'  =>array('label' => 'quantity',
								'db'    => 'oi.quantity',
								'width' => 12,
								),
		);

		#Set essential condition
		$this->where[] = 'o.customer_id = ' . intval($this->render_data['customer_id']);

		//$this->nav = array('add' => true, 'edit' => true);
	}
}