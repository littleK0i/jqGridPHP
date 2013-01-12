<?php

class jqMiscGroupHeaderEx extends jqGrid
{
    protected function init()
    {
        $this->options = array('caption' => 'Group Headers');

        $this->table = 'tbl_order_item';

        $this->query = "
			SELECT {fields}
			FROM tbl_books b
				JOIN tbl_order_item i ON (b.id=i.book_id)
				JOIN tbl_order o ON (i.order_id=o.id)
				JOIN tbl_customer c ON (o.customer_id=c.id)
			WHERE {where}
		";

        $this->cols_default = array('align' => 'center', 'width' => 10);

        #Set columns
        $this->cols = array(

            'id' => array('label' => 'ID',
                'db' => 'i.id',
                'width' => 7,
            ),

            'book_name' => array('label' => 'Book name',
                'db' => 'b.name',
                'width' => 30,
                'align' => 'left',
                'hgroup' => 'book',
            ),

            'orig_price' => array('label' => 'Orig price',
                'db' => 'b.price',
                'formatter' => 'integer',
                'hgroup' => 'book',
            ),

            'order_price' => array('label' => 'Price',
                'db' => 'i.price',
                'formatter' => 'integer',
                'hgroup' => 'order',
            ),

            'quantity' => array('label' => 'Quantity',
                'db' => 'i.quantity',
                'hgroup' => 'order',
            ),

            'sum' => array('label' => 'Sum',
                'db' => 'i.price * i.quantity',
                'hgroup' => 'order',
            ),

            'customer_name' => array('label' => 'Customer name',
                'db' => "CONCAT(c.first_name, ' ', c.last_name)",
                'width' => 18,
                'align' => 'left',
                'hgroup' => 'customer',
            ),

            'date_birth' => array('label' => 'Birthdate',
                'db' => 'c.date_birth',
                'formatter' => 'date',
                'hgroup' => 'customer',
            ),

            'random' => array('label' => 'Random',
                'db' => 'CEIL(RAND() * 100)',
            ),

        );
    }
}