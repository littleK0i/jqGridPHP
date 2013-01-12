<?php

class test_data_generator
{
    protected $DB;

    public function __construct(jqGridLoader $loader)
    {
        $this->DB = $loader->loadDB();
    }

    public function run()
    {
        echo 'generating test data<br>';

        //-----------
        // Get essential data
        //-----------

        $first_names = array_map('trim', array_map('ucfirst', array_map('strtolower', file('misc/first_names.txt'))));
        $last_names = array_map('trim', array_map('ucfirst', array_map('strtolower', file('misc/last_names.txt'))));
        $country_list = array_map('trim', file('misc/countries.txt'));

        $mail_suffix = array('@mail.ru', '@list.ru', '@gmail.com', '@yahoo.com', '@yandex.ru', '@inbox.ru');

        $book_prefix = array('Life of', 'Death of', 'Boots of', 'Smile at', 'Laugh at', 'Lurk behind', 'Attack of', 'Toy for', 'Database on', 'Postgresql for', 'Mysql on', 'PC under', 'Macs for', 'Unlimited', 'Pirates kills', 'Parrot behind', 'Virtual', 'Default', 'Rendering of', 'Cartoons about', 'PHP for', 'jQuery rulez on', 'Mail for', 'Dinosaur bites', 'Random name for', 'Scary spider', 'Blue dragon', 'Final fantasy of', 'Black magic for', 'Frogs dream about');

        //-----------
        // Create tables
        //-----------

        set_time_limit(60 * 10);

        $this->DB->query("
			CREATE TABLE IF NOT EXISTS `lst_delivery_types` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `name` text NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB;
		");

        $this->DB->query("
			CREATE TABLE IF NOT EXISTS `tbl_books` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `name` varchar(100) NOT NULL,
			  `price` int(11) unsigned NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB;
		");

        $this->DB->query("
			CREATE TABLE IF NOT EXISTS `tbl_customer` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `first_name` text NOT NULL,
			  `last_name` text,
			  `email` text NOT NULL,
			  `phone` text,
			  `discount` decimal(4,2) NOT NULL DEFAULT '0.00',
			  `date_register` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
			  `date_birth` date DEFAULT NULL,
			  `contract_type` tinyint(4) unsigned NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB;
		");

        $this->DB->query("
			CREATE TABLE IF NOT EXISTS `tbl_order` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `customer_id` int(11) unsigned NOT NULL,
			  `date_create` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
			  `delivery_type` tinyint(4) unsigned NOT NULL,
			  `delivery_cost` int(11) unsigned NOT NULL DEFAULT '0',
			  `comment` text,
			  PRIMARY KEY (`id`),
			  KEY `customer_id` (`customer_id`)
			) ENGINE=InnoDB;
		");

        $this->DB->query("
			CREATE TABLE IF NOT EXISTS `tbl_order_item` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `order_id` int(11) unsigned NOT NULL,
			  `book_id` int(11) unsigned NOT NULL,
			  `price` int(11) unsigned NOT NULL,
			  `quantity` tinyint(4) unsigned NOT NULL,
			  PRIMARY KEY (`id`),
			  KEY `order_id` (`order_id`),
			  KEY `book_id` (`book_id`)
			) ENGINE=InnoDB;
		");

        $this->DB->query("
			CREATE TABLE IF NOT EXISTS `tbl_files` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `filename` text NOT NULL,
			  `size` int(11) unsigned NOT NULL,
			  `comment` text,
			  `version` int(11) NOT NULL DEFAULT '0',
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB;
		");

        $this->DB->query("
			CREATE TABLE IF NOT EXISTS `tbl_tree` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `parent_id` int(11) unsigned NOT NULL,
			  `node_name` text NOT NULL,
			  `price` int(11) unsigned NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB;
		");

        $this->DB->query("
			CREATE TABLE IF NOT EXISTS `bnd_customer_country` (
			  `customer_id` int(11) NOT NULL,
			  `country_name` varchar(255) NOT NULL,
			  `value` int(11) NOT NULL,
			  PRIMARY KEY (`customer_id`, `country_name`)
			) ENGINE=InnoDB;
		");

        //-----------
        // Truncate
        //-----------

        $this->DB->query('TRUNCATE TABLE lst_delivery_types');
        $this->DB->query('TRUNCATE TABLE tbl_books');
        $this->DB->query('TRUNCATE TABLE tbl_customer');
        $this->DB->query('TRUNCATE TABLE tbl_order');
        $this->DB->query('TRUNCATE TABLE tbl_order_item');
        //$this->DB->query('TRUNCATE TABLE tbl_files');
        $this->DB->query('TRUNCATE TABLE tbl_tree');
        $this->DB->query('TRUNCATE TABLE bnd_customer_country');


        //-----------
        // Generate data
        //-----------

        $this->DB->insert('lst_delivery_types', array('name' => 'Courier'));
        $this->DB->insert('lst_delivery_types', array('name' => 'Cash and carry'));
        $this->DB->insert('lst_delivery_types', array('name' => 'DHL'));

        #Generate books
        for($i = 0; $i <= 10000; $i++)
        {
            $book = array(
                'name' => $book_prefix[array_rand($book_prefix)] . ' ' . $first_names[array_rand($first_names)] . ' ' . $last_names[array_rand($last_names)],
                'price' => mt_rand(10, 3000),
            );

            $this->DB->insert('tbl_books', $book);
        }

        #Customers and order
        for($i = 0; $i < 1500; $i++)
        {
            #1. Generate customer
            $customer = array(
                'first_name' => $first_names[array_rand($first_names)],
                'last_name' => $last_names[array_rand($last_names)],
                'phone' => mt_rand(1, 7) . mt_rand(900, 925) . mt_rand(1000000, 9999999),
                'discount' => mt_rand(0, 30) / 100,
                'date_register' => date('Y-m-d H:i:s', mt_rand(strtotime('01.01.2011'), strtotime('10.06.2011'))),
                'date_birth' => date('Y-m-d', mt_rand(strtotime('01.01.1950'), strtotime('01.01.2000'))),
                'contract_type' => mt_rand(1, 3),
            );

            $customer['email'] = strtolower($customer['first_name']) . mt_rand(10, 99) . $mail_suffix[array_rand($mail_suffix)];

            $customer_id = $this->DB->insert('tbl_customer', $customer, true);

            #2. Generate orders
            $order_cnt = mt_rand(2, 5);

            for($j = 0; $j < $order_cnt; $j++)
            {
                $order = array(
                    'customer_id' => $customer_id,
                    'date_create' => date('Y-m-d H:i:s', mt_rand(strtotime('01.01.2011'), strtotime('10.06.2011'))),
                    'delivery_type' => mt_rand(1, 3),
                    'delivery_cost' => mt_rand(0, 50),
                );

                $order_id = $this->DB->insert('tbl_order', $order, true);

                $item_cnt = mt_rand(1, 5);

                for($u = 0; $u < $item_cnt; $u++)
                {
                    $item = array(
                        'order_id' => $order_id,
                        'book_id' => mt_rand(1, 10000),
                        'price' => mt_rand(10, 3000),
                        'quantity' => mt_rand(1, 10),
                    );

                    $this->DB->insert('tbl_order_item', $item);
                }
            }

            #3. Generate multi-col primary key
            $country_cnt = mt_rand(2, 5);

            foreach(array_rand($country_list, $country_cnt) as $k)
            {
                $ins = array(
                    'customer_id' => $customer_id,
                    'country_name' => $country_list[$k],
                    'value' => mt_rand(1, 100),
                );

                $this->DB->insert('bnd_customer_country', $ins);
            }
        }

        #tree
        $main_nodes = array(
            1 => 'Books',
            2 => 'Games',
            3 => 'Toys',
            4 => 'Cards',
            5 => 'Animals',
        );

        foreach($main_nodes as $k => $v)
        {
            $this->DB->insert('tbl_tree', array('node_name' => $v, 'parent_id' => 0, 'price' => mt_rand(30, 1000)));
        }

        for($i = 6; $i <= 500; $i++)
        {
            $ins = array(
                'parent_id' => mt_rand(1, $i),
                'node_name' => $book_prefix[array_rand($book_prefix)] . ' ' . $first_names[array_rand($first_names)],
                'price' => mt_rand(30, 1000),
            );

            $this->DB->insert('tbl_tree', $ins);
        }

        echo 'process complete!';
    }
}