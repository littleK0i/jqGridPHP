<script>
<?=$rendered_grid?>
</script>
	
<div id="descr">
	This exmaple introduce new column options: <b>db</b>, <b>db_agg</b>, <b>manual</b> and <b>unset</b>.<br><br>
	
	<b>db</b>- column representation in database queries.<br>
	<b>db_agg</b> - calculates aggregate function among the whole data set (all pages, not one).<br>
	<b>manual</b> - column is ignored by database. You should fill it manually within <b>parseRow</b> function.<br>
	<b>unset</b> - column is ignored by client script. It is only visible in PHP.<br><br>
</div>

<div id="descr_rus">
	Этот пример показывает новые опции колонок: <b>db</b>, <b>db_agg</b>, <b>manual</b> и <b>unset</b>.<br><br>
	
	<b>db</b> - имя колонки в SQL запросе<br><br>
	<b>db_agg</b> - выполнение aggregate функции (count, sum и т.п.) - часто используется для вывода дополнительного ряда "Итого:"<br><br>
	<b>manual</b> - колонка игнорируется при составлении запросов к БД. Вы должны явно определить её значение в функции <b>parseRow</b>.<br><br>
	<b>unset</b> - колонка игнорируется клиентской частью. Она будет отсутствовать в colModel на стороне клиента. Её видно только на стороне сервера.<br><br>
</div>