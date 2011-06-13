<!DOCTYPE html>
<html>
<head>
	<title>jqGridPHP examples</title>

	<!--jQuery-->
	<script src="http://yandex.st/jquery/1.5.2/jquery.min.js"></script>

	<!--jQuery UI-->
	<script src="http://yandex.st/jquery-ui/1.8.11/jquery-ui.min.js"></script>
	<link href="http://yandex.st/jquery-ui/1.8.11/themes/redmond/jquery.ui.all.min.css" rel="stylesheet" type="text/css"></link>

	<!--jqGrid-->
	<link href="/jqgrid/css/ui.jqgrid.css" rel="stylesheet" type="text/css" />
	<script src="/jqgrid/js/i18n/grid.locale-en.js"></script>
    <script src="/jqgrid/js/jquery.jqGrid.min.js"></script>

	<!--jqGrid Extension-->
	<link href="/jqgrid_ext/jqgrid-ext.css" rel="stylesheet" type="text/css" />
    <script src="/jqgrid_ext/jqgrid-ext.js"></script>
	
	<!-- Other plugins -->
	<script src="http://yandex.st/jquery/form/2.67/jquery.form.min.js"></script>
	
	<!-- Code highlighter -->
	<script src="http://yandex.st/highlightjs/6.0/highlight.min.js"></script>
	<link href="http://yandex.st/highlightjs/6.0/styles/vs.css" rel="stylesheet" type="text/css" />
	
	<script>
	//$.jgrid.defaults.height = '400px';
	$.jgrid.nav.refreshtext = 'Refresh';
	$.jgrid.formatter.date.newformat = 'ISO8601Short';
	
	$.jgrid.edit.closeAfterEdit = true;
	$.jgrid.edit.closeAfterAdd = true;
	
	$(function()
	{
		$('#tabs-info').html($('#descr').html());
	
		$('#accordion').accordion({
			'animated' : false,
			'navigation' : true
		});
		
		$('#tabs').tabs();
		
		hljs.tabReplace = '    ';
		hljs.initHighlightingOnLoad();
	});
	</script>
	
	<style>
	body {background: #F5F5F5; font-size: 11px; padding: 10px;}
	#descr {display: none;}
	
	#accordion UL {padding: 0; margin: 0; list-style-type: circle;}
	#accordion UL A {text-decoration: none; font-size: 11px;}
	#accordion UL A:hover {text-decoration: underline;}
	#accordion UL LI.active {list-style-type: disc;}
	
	.ui-widget {font-family: verdana; font-size: 12px;}

	.ui-jqgrid {font-family: tahoma, arial;}
	.ui-jqgrid TR.jqgrow TD {font-size: 11px;}
	.ui-jqgrid TR.jqgrow TD {padding-left: 5px; padding-right: 5px;}
	.ui-jqgrid TR.jqgrow A {color: blue;}

	.ui-jqgrid INPUT,
	.ui-jqgrid SELECT,
	.ui-jqgrid TEXTAREA, 
	.ui-jqgrid BUTTON {font-family: tahoma, arial;}
	</style>
</head>

<body>
	<table>
	<tr>
		<td width="260px" valign="top"><?php require 'templates/_accordion.php'; ?></td>
		<td valign="top" style="padding-left: 10px;">
			<?php require 'templates/' . $grid . '.php'; ?>
			<?php require 'templates/_sources.php'; ?>
		</td>
	</tr>
	</table>
</body>
</html>