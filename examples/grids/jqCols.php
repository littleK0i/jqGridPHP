<?php

class jqCols extends jqGrid
{
	protected function init()
	{
		$this->query = "
			SELECT {fields}
			FROM tbl_order_item i
				JOIN tbl_order o ON (i.order_id=o.id)
				JOIN tbl_customer c ON (o.customer_id=c.id)
				JOIN tbl_books b ON (i.book_id=b.id)
			WHERE {where}
		";

		#Set columns
		$this->cols = array(

			#Real id is hidden
			'id'            =>array('hidden'=> true,
									'db'    => 'i.id',
									),
			
			#This column is constructed by SQL-expression
			#Searching and sorting still works!
			'c_name'		=>array('label' => 'Customer name',
									'db'	=> "CONCAT(c.first_name, ' ', c.last_name)",
									'width' => 35,
									),

			'book_id'		=>array('label' => 'Book ID',
									'db'    => 'b.id',
									'width' => 10,
									'align' => 'center',
									'formatter' => 'integer',
									),

			'book_name'		=>array('label' => 'Book Name',
									'db'    => 'b.name',
									'width'	=> 30,
									),

			'quantity'		=>array('label' => 'Quantity',
									'db'    => 'i.quantity',
									'db_agg'=> 'sum',
				                    'width' => 10,
									'formatter' => 'integer',
									'align' => 'center',
									),

			#Field with same name, but different tables
			'orig_price'	=>array('label'	 => 'Orig price',
									'db'     => 'b.price', #price from 1st table
									'db_agg' => 'sum',
									'width' => 14,
									'formatter' => 'integer',
									'align' => 'right',
									),

			'item_price'	=>array('label'  => 'Item price',
									'db'     => 'i.price', #price from 2nd table
									'db_agg' => 'sum',     #avg price for all items
									'width'  => 14,
									'formatter' => 'integer',
									'align'  => 'right',
									),


			#This column is processed MANUALLY in PHP code
			'diff_price'	=>array('label' => 'Diff',
									'manual'=> true,
									'width' => 12,
									'search' => false,
									'sortable' => false,
									'align' => 'right',
									),

			#This column exists only in PHP code
			'discount'		=>array('db'	=> 'c.discount',
									'unset' => true,
									),
		);
	}

	protected function parseRow($r)
	{
		#Calc diff_price in PHP
		$r['diff_price'] = $r['orig_price'] - $r['item_price'];

		#Highlight customers with discount > 0.1
		$r['_class'] = array('c_name' => ($r['discount'] > 0.1) ? 'bold font-green' : null);

		return $r;
	}
}