<?php

class jq_general extends jqGrid
{
	protected function init()
	{
		#Set database table
		$this->table = 'tbl_customer';

		$this->cols_default = array('editable' => true);

		#Set columns
		$this->cols = array(
			
			'id'        =>array('label' => 'ID',
								'width' => 10,
								'align' => 'center',
								'editable' => false,
								'search_op' => 'in',
								),

			'first_name'=>array('label' => 'Frist name',
								'width'	=> 35,
								'search_op' => 'in',
								),

			'last_name' =>array('label' => 'Last name',
								'width' => 35,
								),

			'email'     =>array('label' => 'Email',
								'width' => 30,
								),

			'phone'     =>array('label' => 'Phone',
								'width'	=> 25,
								'align' => 'center',
								),

			'discount'	=>array('label' => 'Discount',
								'width'	=> 15,
								'formatter' => 'numeric',
								'align'	=> 'center',
								),

			'date_register'=>array('label' => 'Register',
								'width'	=> 20,
								'formatter' => 'date',
								'align'	=> 'center',
								),
		);

		#Set nav
		$this->nav = array('add' => true, 'edit' => true, 'del' => true);
	}

	protected function operData($r)
	{
		throw new jqGrid_Exception('ohhh');
		return $r;
	}

	protected function opGenerateData()
	{
		set_time_limit(60 * 10);

		$this->DB->query('TRUNCATE TABLE tbl_books');
		$this->DB->query('TRUNCATE TABLE tbl_customer');
		$this->DB->query('TRUNCATE TABLE tbl_order');
		$this->DB->query('TRUNCATE TABLE tbl_order_item');

		$first_names = array_map('trim', array_map('ucfirst', array_map('strtolower', file('misc/first_names.txt'))));
		$last_names  = array_map('trim', array_map('ucfirst', array_map('strtolower', file('misc/last_names.txt'))));

		$mail_suffix = array('@mail.ru', '@list.ru', '@gmail.com', '@yahoo.com', '@yandex.ru', '@inbox.ru');

		$book_prefix = array('Life of', 'Death of', 'Boots of', 'Smile at', 'Laugh at', 'Lurk behind', 'Attack of', 'Toy for', 'Database on', 'Postgresql for', 'Mysql on', 'PC under', 'Macs for', 'Unlimited', 'Pirates kills', 'Parrot behind', 'Virtual', 'Default', 'Rendering of', 'Cartoons about', 'PHP for', 'jQuery rulez on', 'Mail for', 'Dinosaur bites', 'Random name for', 'Scary spider', 'Blue dragon', 'Final fantasy of', 'Black magic for', 'Frogs dream about');

		#Generate books
		for($i=0;$i<= 10000;$i++)
		{
			$book = array(
				'name' => $book_prefix[array_rand($book_prefix)] . ' ' . $first_names[array_rand($first_names)] . ' ' . $last_names[array_rand($last_names)],
				'price' => mt_rand(10, 3000),
			);

			$this->DB->insert('tbl_books', $book);
		}

		#Customers and order
		for($i=0;$i<1500;$i++)
		{
			#1. Generate customer
			$customer = array(
				'first_name' => $first_names[array_rand($first_names)],
				'last_name'  => $last_names[array_rand($last_names)],
				'phone'		 => mt_rand(1,7) . mt_rand(900, 925) . mt_rand(1000000, 9999999),
				'discount'	 => mt_rand(0, 30) / 100,
				'date_register' => date('Y-m-d H:i:s', mt_rand(strtotime('01.01.2011'), strtotime('10.06.2011'))),
				'date_birth' => date('Y-m-d', mt_rand(strtotime('01.01.1950'), strtotime('01.01.2000'))),
			);

			$customer['email'] = strtolower($customer['first_name']) . mt_rand(10, 99) . $mail_suffix[array_rand($mail_suffix)];

			$customer_id = $this->DB->insert('tbl_customer', $customer, true);

			#2. Generate orders
			$order_cnt = mt_rand(-5, 7);

			if($order_cnt)
			{
				for($j=0;$j<$order_cnt;$j++)
				{
					$order = array(
						'customer_id' => $customer_id,
						'date_create' => date('Y-m-d H:i:s', mt_rand(strtotime('01.01.2011'), strtotime('10.06.2011'))),
						'delivery_type' => mt_rand(1,3),
						'delivery_cost' => mt_rand(0, 50),
					);

					$order_id = $this->DB->insert('tbl_order', $order, true);

					$item_cnt = mt_rand(1,5);

					for($u=0;$u<$item_cnt;$u++)
					{
						$item = array(
							'order_id' => $order_id,
							'book_id'  => mt_rand(1,10000),
							'price'    => mt_rand(10, 3000),
							'quantity' => mt_rand(1,10),
						);

						$this->DB->insert('tbl_order_item', $item);
					}
				}
			}
		}

		return array('success' => 1);
	}
}