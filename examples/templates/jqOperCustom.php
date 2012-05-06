<script>
<?= $rendered_grid ?>

//custom button
$grid.jqGrid("navButtonAdd", pager, 
{
	caption : "Change price", 
	title   : "Custom oper", 
	icon    : "ui-icon-flag",
	onClickButton: function()
	{
		var price = prompt("Enter new price.\Number between 1 and 3000");
		if(!price) return;
		
		$(this).jqGrid("extRequest", 
		{
			"oper" : "price", //oper name
			"price": price    //and other values
		}, 
		{
			'selrow' : true   //add selected rows to request
		});
	}
});
</script>
	
<div id="descr">
	The completely custom oper example.<br>
	Select some rows and give it a shot.<br><br>
	
	Please note the server-side price validation.
</div>

<div id="descr_rus">
	Пример создания нестандартной пользовательской операции.<br>
	Выберите несколько рядов и задайте им цену.<br><br>
	
	Обратите внимание на валидацию данных на стороне сервера при помощи исключений.
</div>