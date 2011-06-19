<script>

<?=$jq_loader->render('jq_out_excel');?>
$grid.filterToolbar();

//custom excel export
$grid.jqGrid("navButtonAdd", pager, 
{
	caption : "Excel some rows", 
	title   : "Excel", 
	icon    : "ui-extlink",
	onClickButton: function()
	{
		var rows = prompt("How many rows to export?");
		if(!rows) return;
		
		$(this).jqGrid("extExport", {"export" : "ExcelHtml", "rows" : rows});
	}
});

</script>
	
<div id="descr">
	The easiest way to active Excel export is setting <b>$this->nav['excel'] = true</b><br>
	The first Excel button is generated this way.<br><br>
	
	<b>Please note:</b> export preserves current sorting and filtering.<br><br>
	
	Of course, you may create your own export button with custom handler.<br>
	The second button is created that way.
</div>