<?php
require 'config.php';

header("Content-Type: text/html; charset={$_CONFIG['encoding']};");

require_once($_CONFIG['root_path'] . 'jqGridLoader.php');

$jq_loader = new jqGridLoader();

$jq_loader->set('grid_path', 'grids/');

$jq_loader->set('pdo_dsn', $_CONFIG['pdo_dsn']);
$jq_loader->set('pdo_user', $_CONFIG['pdo_user']);
$jq_loader->set('pdo_pass', $_CONFIG['pdo_pass']);

$jq_loader->set('debug_output', true);

//-----------
// Generate test data
//-----------

require 'misc/test_data_generator.php';
$lib = new test_data_generator($jq_loader);
$lib->run();