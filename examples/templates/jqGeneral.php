<script>

<?=$jq_loader->render('jqGeneral');?>
$grid.filterToolbar();

$grid.jqGrid('navButtonAdd', pager, 
{
	'caption'      : 'Excel', 
	'buttonicon'   : 'ui-icon-extlink', 
	'onClickButton': function()
	{
		$(this).jqGrid('extExport',
		{
			'export' : 'ExcelHtml',
			'rows'	 : -1
		});
	}
});

/*
$grid.jqGrid('navButtonAdd', pager, 
{
	'caption'      : 'Restore Data', 
	'buttonicon'   : 'ui-icon-home', 
	'onClickButton': function()
	{
		$(this).jqGrid('extRequest',
		{
			'oper' : 'generateData', 
			'foo'  : 'bar'
		}, function(ret)
		{
			alert(ret);
		}, true);
	}
});
*/

</script>
	
<div id="descr">
	General example.
</div>