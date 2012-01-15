<?php

class jqOutAdvancedSearch extends jqGrid
{
	protected function init()
	{
		$delivery_types = $this->getDeliveryTypes();

		$this->table = 'tbl_order_item';

		$this->query = "
			SELECT {fields}
			FROM tbl_order_item i
				JOIN tbl_books b ON (i.book_id=b.id)
				JOIN tbl_order o ON (i.order_id=o.id)
				JOIN tbl_customer c ON (c.id=o.customer_id)
			WHERE {where}
		";

		#Set columns
		$this->cols = array(

			'id'  			=>array('label' => 'ID',
									'db'    => 'i.id',
									'width' => 10,
									'align' => 'center',
									'formatter' => 'integer',
									'searchtype' => 'integer',
									),

			'order_id'		=>array('label' => 'Order id',
									'db'    => 'i.order_id',
									'width' => 15,
									'align' => 'center',
									'formatter' => 'integer',
									'searchtype' => 'integer',
									),

			'delivery_type' =>array('label' => 'Delivery',
									'db'	=> 'o.delivery_type',
									'width' => 20,

									'replace' => $delivery_types,

									'stype' => 'select',
									'searchoptions' => array(
										'value' => new jqGrid_Data_Value($delivery_types, 'All'),
										'sopt'	=> array('eq', 'ne'),
										),
									),

			'delivery_cost' =>array('label' => 'Deliv. cost',
									'db'    => 'o.delivery_cost',
									'width' => 15,
									'align' => 'center',
									'searchtype' => 'integer',
									),

			'customer_name'=>array('label'  => 'Customer name',
									'db'     => "CONCAT(c.first_name, ' ', c.last_name)",
									'width' => 20,
									),

			'name'			=>array('label' => 'Book name',
									'db'    => 'b.name',
									'width' => 30,
									'search_op' => 'book',
									'searchoptions' => array('sopt' => array('eq')),
									),

			'price'			=>array('label' => 'Price',
									'db'    => 'i.price',
									'width' => 15,
									'align' => 'center',
									'formatter' => 'integer',
									'searchtype' => 'integer',
									),
		);

		#Set nav
		$this->nav = array(
			'search' => true,
			'searchtext' => 'Search',
			'prmSearch' => array(
				'multipleSearch' => true,
				'multipleGroup' => true,
				'tmplNames' => array('Test template 1', 'Test template 2'),
				'tmplFilters' => array(new jqGrid_Data_Raw('template1'), new jqGrid_Data_Raw('template2')),
			),
		);
	}

	#Custom search operation
	protected function searchOpBook($c, $val)
	{
		#If numeric input -> search by book id
		if(is_numeric($val))
		{
			return "b.id='$val'";
		}
		#Non-numeric? Perform common 'LIKE' search by book name
		else
		{
			return parent::searchOpLike($c, $val);
		}
	}

	protected function getDeliveryTypes()
	{
		$result = $this->DB->query("SELECT * FROM lst_delivery_types");

		$rows = array();
		while($r = $this->DB->fetch($result))
		{
			$rows[$r['id']] = $r['name'];
		}

		return $rows;
	}
}