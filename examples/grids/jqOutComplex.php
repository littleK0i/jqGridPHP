<?php

class jqOutComplex extends jqGrid
{
    protected function init()
    {
        #The complex query
        $this->query = "
			SELECT {fields}
			FROM (
				SELECT
					 c.id
					,c.first_name
					,c.last_name

					,count(o.id) AS order_cnt
					,sum(i.price * i.quantity) AS items_sum

					,(SELECT id
					  FROM tbl_order
					  WHERE customer_id=c.id
					  ORDER BY delivery_cost DESC
					  LIMIT 1
					) AS order_max_delivery
					
				FROM tbl_customer c
					JOIN tbl_order o ON (c.id=o.customer_id)
					JOIN tbl_order_item i ON (o.id=i.order_id)
				GROUP BY c.id, c.first_name, c.last_name
			) AS a
			WHERE {where}
		";

        #Set columns
        $this->cols = array(

            'id' => array('label' => 'ID',
                'width' => 10,
                'align' => 'center',
                'formatter' => 'integer',
            ),

            'c_name' => array('label' => 'Name',
                'db' => "CONCAT(first_name, ' ', SUBSTRING(last_name FROM 1 FOR 1))",
                'width' => 35,
            ),

            'order_cnt' => array('label' => 'Orders',
                'width' => 10,
                'formatter' => 'integer',
            ),

            'items_sum' => array('label' => "Order sum",
                'width' => 15,
                'formatter' => 'integer',
            ),

            'order_max_delivery'
            => array('label' => 'Highest delivery',
                'width' => 15,
                'formatter' => 'integer',
            ),
        );
    }
}