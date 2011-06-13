<?php
require 'config.php';

header("Content-Type: text/html; charset={$_CONFIG['encoding']};");

require_once($_CONFIG['root_path'] . 'jqGridLoader.class.php');

$jq_loader = new jqGridLoader();

$jq_loader->set('grid_path', 'grids/');

$jq_loader->set('pdo_dsn', $_CONFIG['pdo_dsn']);
$jq_loader->set('pdo_user', $_CONFIG['pdo_user']);
$jq_loader->set('pdo_pass', $_CONFIG['pdo_pass']);

$jq_loader->set('debug_output', true);

$jq_loader->autorun();

//-----------
// Get grid
//-----------

$grid = isset($_REQUEST['render']) ? $_REQUEST['render'] : 'jq_simple';
$grid = preg_replace('#[^a-zA-Z0-9_-]#', '', $grid); //safe

//-----------
// Get sources
//-----------

$source_php = file_get_contents('grids/' . $grid . '.php');

$source_tpl = file_get_contents('templates/' . $grid . '.php');

preg_match('#<script>(.+)<\/script>#is', $source_tpl, $m);
$source_js = $m[1];

require 'templates/_layout.php';