<script>
<?= $rendered_grid ?>
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
</script>
	
<div id="descr">
	General example.
</div>