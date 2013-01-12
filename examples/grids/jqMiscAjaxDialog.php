<?php

class jqMiscAjaxDialog extends jqGrid
{
    protected function init()
    {
        $this->query = "
			SELECT {fields}
			FROM tbl_customer c
				JOIN tbl_order o ON (c.id=o.customer_id)
			WHERE {where}
			GROUP BY c.id
		";

        #Set columns
        $this->cols = array(
            'id' => array('label' => 'ID',
                'db' => 'c.id',
                'width' => 10,
                'align' => 'center',
            ),

            'c_name' => array('label' => 'Customer name',
                'db' => "CONCAT(c.first_name, ' ', c.last_name)",
                'width' => 35,
            ),

            'order_cnt' => array('label' => 'Order count',
                'db' => 'count(o.id)',
                'width' => 15,
            ),

            'order_latest' => array('label' => 'Latest order',
                'db' => 'max(o.date_create)',
                'width' => 15,
            ),
        );
    }

    protected function opDialog()
    {
        $rendered_grid = $this->Loader->render('jqMiscAjaxDialogDetails', array('customer_id' => $this->input('id')));
        $this->response['html'] = "<script> $rendered_grid </script>";
    }
}