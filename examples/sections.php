<?php

$_SECTIONS = array(
	'general'	=> array(
		'name'	=> array('en' => 'General', 'ru' => 'Основные'),
		'items' => array('simple'  => array('en' => 'Basic grid', 'ru' => 'Базовая таблица'),
						 'cols'	   => array('en' => 'New column options', 'ru' => 'Новые опции колонок'),
			             'renderData' => array('en' => 'Passing render data', 'ru' => 'Передача render data'),
						 //'csv'     => 'Alternative data source',
						),
	),
	
	'output'	=> array(
		'name'	=> array('en' => 'Output', 'ru' => 'Вывод данных'),
		'items' => array('outSearch' => array('en' => 'Searching', 'ru' => 'Поиск'),
						 'outSort'   => array('en' => 'Sorting', 'ru' => 'Сортировка'),
						 'outExcel' => array('en' => 'Export to Excel', 'ru' => 'Экспорт в Excel'),
						 'outComplex'=> array('en' => 'Complex queries', 'ru' => 'Сложные SQL-запросы'),
						 'outTree'	 => array('en' => 'Tree grid', 'ru' => 'Вывод дерева по уровням'),
						 'outTreeFull' => array('en' => 'Tree grid (Full)', 'ru' => 'Вывод дерева сразу целиком'),
						 'outAdvancedSearch' => array('en' => 'Advanced Search', 'ru' => 'Продвинутый поиск'),
						),
	),

	'render'	=> array(
		'name'	=> array('en' => 'Rendering', 'ru' => 'Рендеринг'),
		'items' => array('render1' => array('en' => 'Set grid options', 'ru' => 'Опции таблицы'),
						 'render2' => array('en' => 'Set nav options', 'ru' => 'Опции навигатора'),
						 //'render_alt' => 'Alternative render',
						),
	),

	'oper'		=> array(
		'name'	=> array('en' => 'Operations', 'ru' => 'Операции'),
		'items' => array('operBasic' => array('en' => 'Extend basic oper', 'ru' => 'Расширение операций'),
						 'operCustom'=> array('en' => 'Custom oper', 'ru' => 'Пользовательские операции'),
						 'operUpload' => array('en' => 'Upload files', 'ru' => 'Загрузка файлов'),
						),
	),
	
	'exception' => array(
		'name'	=> array('en' => 'Exceptions', 'ru' => 'Обработка ошибок'),
		'items' => array('exceptionOper'	=> array('en' => 'Oper exception', 'ru' => 'Ошибки операций'),
						 'exceptionOutput' => array('en' => 'Output exceptions', 'ru' => 'Ошибки вывода данных'),
						 //'exceptionRender' => array('en' => 'Render exceptions', 'ru' => 'Ошибки рендеринга'),
						 ),
	),
	
	'other'	=> array(
		'name'	=> array('en' => 'Other', 'ru' => 'Прочее'),
		'items' => array('miscGroupHeader'		=> array('en' => 'Grouping header', 'ru' => 'Группировка заголовков'),
						 'miscGroupHeaderEx'	=> array('en' => 'Grouping header 2', 'ru' => 'Группировка заголовков 2'),
						 'miscSubgrid'			=> array('en' => 'Grid as subgrid', 'ru' => 'Вложенные таблицы'),
						 'miscDatepickers'      => array('en' => 'Datepickers', 'ru' => 'Выбор даты'),
						 'miscAjaxDialog'       => array('en' => 'Grid in ajax dialog', 'ru' => 'Загрузка таблиц через AJAX'),
						),					
	),
);
