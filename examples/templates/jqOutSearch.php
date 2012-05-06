<script>
<?= $rendered_grid ?>
</script>
	
<div id="descr">
	This example shows the full potential of toolbar search.<br>
	Column option <b>search_op</b> defines the search method for each column.<br><br>
	
	Try the following searches:<br>
	1. <b>1,2,3</b> for column <b>ID</b> (IN search)<br>
	2. <b>&gt;10</b> for column <b>Order id</b> (Numeric search)<br>
	3. Any delivery type (Equal search)<br>
	4. Any on <b>Delivery cost</b> (nothing will happen)<br>
	5. <b>ohn</b> for column <b>Customer name</b> (LIKE search)<br>
	6. <b>jQuery</b> for column <b>Book name</b> (custom search)<br>
	7. <b>123</b> for column <b>Book name</b> (custom search - will find books by id)<br>
	8. <b>&lt;=100</b> for column <b>Price</b> (auto search assumed numeric search)
</div>

<div id="descr_rus">
	Этот пример показывает весь потенциал автоматического поиска.<br>
	Опция <b>search_op</b> определеяет метод поиска для каждой колонки.<br><br>
	
	Попробуйте поискать:<br>
	1. <b>1,2,3</b> в колонке <b>ID</b> (id 1, 2 и 3)<br>
	2. <b>&gt;10</b> в колонке <b>Order id</b> (order_id больше 10)<br>
	3. Любой тип доставки<br>
	4. Любое значение в <b>Delivery cost</b> (ничего не произойдет - ignore)<br>
	5. <b>ohn</b> в колонке <b>Customer name</b> (LIKE '%ohn%')<br>
	6. <b>jQuery</b> в колонке <b>Book name</b> (пользовательский поиск - строки ищет LIKE'ом)<br>
	7. <b>123</b> в колонке <b>Book name</b> (пользовательский поиск - числа ищет по ID в другой колонке)<br>
	8. <b>&lt;=100</b> в колонке <b>Price</b> (цена меньше или равна 100)
</div>