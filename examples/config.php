<?php

$_CONFIG = array(
	'root_path' => dirname(__FILE__) . '/../php/',

	'encoding' => 'utf-8',

	'pdo_dsn'  => 'mysql:dbname=jqgrid_test;host=127.0.0.1',
	'pdo_user' => 'root',
	'pdo_pass' => '',
);

$_SECTIONS = array(
	'general'	=> array(
		'name'	=> array('en' => 'General', 'ru' => 'Основные'),
		'items' => array('simple'  => array('en' => 'Basic grid', 'ru' => 'Базовая таблица'),
						 'cols'	   => array('en' => 'New column options', 'ru' => 'Новые опции колонок'),
						 //'csv'     => 'Alternative data source',
						),
	),

	'render'	=> array(
		'name'	=> array('en' => 'Rendering', 'ru' => 'Рендеринг'),
		'items' => array('render1' => array('en' => 'Set grid options', 'ru' => 'Опции таблицы'),
						 'render2' => array('en' => 'Set nav options', 'ru' => 'Опции навигатора'),
						 //'render_alt' => 'Alternative render',
						),
	),

	'output'	=> array(
		'name'	=> array('en' => 'Output', 'ru' => 'Вывод данных'),
		'items' => array('out_search' => array('en' => 'Searching', 'ru' => 'Поиск'),
						 'out_sort'   => array('en' => 'Sorting', 'ru' => 'Сортировка'),
						 'out_excel' => array('en' => 'Export to Excel', 'ru' => 'Экспорт в Excel'),
						 'out_complex'=> array('en' => 'Complex queries', 'ru' => 'Сложные SQL-запросы'),
						),
	),

	'oper'		=> array(
		'name'	=> array('en' => 'Operations', 'ru' => 'Операции'),
		'items' => array('oper_basic' => array('en' => 'Extend basic oper', 'ru' => 'Расширение операций'),
						 'oper_custom'=> array('en' => 'Custom oper', 'ru' => 'Пользовательские операции'),
						 'oper_upload' => array('en' => 'Upload files', 'ru' => 'Загрузка файлов'),
						),
	),
);

$lang = 'ru';