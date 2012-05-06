<script>
//JS functions in JS code - recommended way
var opts = {
	onSelectAll: function()
	{
		alert("All rows selected!");
	}
};

<?= $rendered_grid ?>
</script>
	
<div id="descr">
	You can set grid options in a numerous ways. Each step is merging with the previous one.<br><br>
	The order is the following:<br><br>
	1. (JS) Altering <b>$.jqgrid.defaults</b><br>
	2. (PHP) Setting property <b>$this->options</b><br>
	3. (PHP) Overloading function <b>renderOptions</b><br>
	4. (JS) Creating object, passed as <b>2nd argument</b> to 'render' method<br><br>
	
	It is highly recommended to use JS ways. This is the most natural approach.<br>
	But if you need to generate some settings dynamically in PHP - you welcome.<br><br>
	
	Please click some rows and the "Select all" checkbox to see JS events are in place.
</div>

<div id="descr_rus">
	Вы можете указывать настройки таблицы несколькими способами.<br><br>
	1. (JS) Изменяя <b>$.jqgrid.defaults</b><br>
	2. (PHP) Изменяя массив <b>$this->options</b><br>
	3. (PHP) Перегружая фукнцию <b>renderOptions</b><br>
	4. (JS) Передавая объект с настройками <b>вторым аргументом</b> в функцию <b>render</b>.<br><br>
	
	Каждый следующий метод перезаписывает настройки, которые были указаны ранее.<br><br>
	
	В большинстве случаев рекомендуется использовать javascript.<br>
	Если вам необходимо динамически генерировать определенные настройки в PHP - используйте серверные методы.<br><br>
	
	Покликайте на ряды таблицы, а также на галочку "Выделить все".
</div>