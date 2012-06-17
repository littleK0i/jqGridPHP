<script src="/range_picker/js/daterangepicker.jQuery.compressed.js"></script>
<link href="/range_picker/css/ui.daterangepicker.css" rel="stylesheet" type="text/css" />

<script>
<?= $rendered_grid ?>

function dateRangePicker_onChange()
{
	var $input = $('#gs_date_register');
	var old_val = $input.val();
	
	setTimeout(function()
	{
		if($input.val() == old_val)
		{
			$grid[0].triggerToolbar();
		}
	}, 50);
}
</script>

<div id="descr_rus">
	Пример использования различных datepicker'ов.<br><br>
	
	Выберите дату <b>1990-03-05</b> в поле <b>Birth Date</b>.<br>
	Выберите дату <b>2011-06-09</b> или любой промежуток дат в поле <b>Register Date</b>.<br><br>

	По просьбе с phpclub.ru.
</div>