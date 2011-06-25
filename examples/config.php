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
		'name'	=> 'General',
		'items' => array('simple'  => 'Basic grid',
						 'cols'	   => 'New column options',
						 //'csv'     => 'Alternative data source',
						),
	),

	'render'	=> array(
		'name'	=> 'Rendering',
		'items' => array('render_1' => 'Set grid options',
						 'render_2' => 'Set nav options',
						 //'render_alt' => 'Alternative render',
						),
	),

	'output'	=> array(
		'name'	=> 'Output',
		'items' => array('out_search' => 'Searching',
						 'out_sort'   => 'Sorting',
						 'out_excel' => 'Export to Excel',
						 'out_complex'=> 'Complex queries',
						),
	),

	'oper'		=> array(
		'name'	=> 'Operations',
		'items' => array('oper_basic' => 'Extend basic oper',
						 'oper_custom'=> 'Custom oper',
						 //'oper_valid' => 'Server-side validation',
						 'oper_upload' => 'Upload files',
						),
	),
);