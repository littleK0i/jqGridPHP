<script>
<?= $rendered_grid ?>

$grid.jqGrid('extGroupHeader',
{
	'book_name'     : [2, 'Book group'],
	'order_price'   : [3, 'Order group'],
	'customer_name' : [2, 'Customer group']
});
</script>
	
<div id="descr">
	
</div>

<div id="descr_rus">
	Для группировки заголовков вызываем метод <b>extGroupHeader</b> после рендеринга таблицы.<br>
	Все изменения происходят на стороне клиента. Сервер ничего не знает о группировке.<br><br>

	Поддерживается динамическое изменение ширины колонок.<br><br>
	
	По просьбе с <a href="http://phpclub.ru/talk/threads/jqgridphp-%D1%82%D0%B0%D0%B1%D0%BB%D0%B8%D1%86%D1%8B-%D0%BD%D0%B0-ajax-%D0%B1%D0%B5%D0%B7-%D0%B3%D0%BE%D0%BB%D0%BE%D0%B2%D0%BD%D0%BE%D0%B9-%D0%B1%D0%BE%D0%BB%D0%B8.69132/" target="_blank">PHPClub</a>
</div>