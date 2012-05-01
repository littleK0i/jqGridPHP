<?php
require 'config.php';
require 'sections.php';

header("Content-Type: text/html; charset={$_CONFIG['encoding']};");

require_once($_CONFIG['root_path'] . 'jqGridLoader.php');

$jq_loader = new jqGridLoader();

$jq_loader->set('grid_path', 'grids' . DIRECTORY_SEPARATOR);

$jq_loader->set('pdo_dsn', $_CONFIG['pdo_dsn']);
$jq_loader->set('pdo_user', $_CONFIG['pdo_user']);
$jq_loader->set('pdo_pass', $_CONFIG['pdo_pass']);

$jq_loader->set('debug_output', true);

if(isset($_SERVER['HTTP_HOST']) and $_SERVER['HTTP_HOST'] == 'jqgrid-php.net')
{
	$jq_loader->addInitQuery("SET NAMES 'utf8'");
}

$jq_loader->autorun();

//-----------
// Get grid
//-----------

$grid = isset($_REQUEST['render']) ? $_REQUEST['render'] : 'jqSimple';
$grid = preg_replace('#[^a-zA-Z0-9_-]#', '', $grid); //safe

//-----------
// Get sources
//-----------

$source_php = file_get_contents('grids/' . $grid . '.php');

if(file_exists('grids/' . $grid . '2.php'))
{
	$source_php2 = file_get_contents('grids/' . $grid . '2.php');
}

$source_js = $source_tpl = file_get_contents('templates/' . $grid . '.php');

//preg_match('#<script>(.+)<\/script>#is', $source_tpl, $m);
//$source_js = $m[1];

require 'templates/_layout.php';