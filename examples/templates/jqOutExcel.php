<script>
<?= $rendered_grid ?>
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

<div id="descr_rus">
	Простейший способ включить экспорт в Excel - установить <b>$this->nav['excel'] = true</b><br>
	При этом экспортируются сразу все страницы, сохраняя текущий поиск и сортировку.
	Первая кнопка создана именно таким образом.<br><br>
	
	Вы также можете задать свои параметры экспорта. При клике на вторую кнопку пользователя спросит, сколько рядов экспортировать.
</div>