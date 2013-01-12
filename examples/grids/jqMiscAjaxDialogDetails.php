<?php

class jqMiscAjaxDialogDetails extends jqGrid
{
    protected function init()
    {
        $this->options = array(
            'width' => 496,
            'height' => 185,
            'rowNum' => -1,
        );

        $this->query = "
			SELECT {fields}
			FROM tbl_order o
				JOIN tbl_order_item i ON (o.id=i.order_id)
			WHERE {where}
			GROUP BY o.id
		";

        $this->cols_default = array('align' => 'center');

        #Set columns
        $this->cols = array(
            'id' => array('label' => 'ID',
                'db' => 'o.id',
                'width' => 10,
            ),

            'customer_id' => array('hidden' => true), //to make auto-search work

            'quantity' => array('label' => 'Total quantity',
                'db' => 'count(i.quantity)',
                'width' => 15,
            ),

            'price' => array('label' => 'Total price',
                'db' => 'sum(i.price * i.quantity)',
                'width' => 15,
            ),

            'date_create' => array('label' => 'Date',
                'db' => 'o.date_create',
                'width' => 18,
            ),
        );

        $this->render_suffix_col = 'customer_id';
    }
}