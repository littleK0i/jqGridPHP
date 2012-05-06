<script>
var opts = {
	'treeGrid'      : true,
	'treeGridModel' : 'adjacency',
	'ExpandColumn'  : 'node_name',
	
	'viewrecords'   : false
};

<?= $rendered_grid ?>
</script>

<div id="descr_rus">
	Пример использования <b>treegrid</b> с динамической подгрузкой данных.<br><br>
	Для создания таблицы с деревом необходимо:<br><br>
	
	1. Настроить <b>treegrid</b> на стороне клиента;<br>
	2. Установить свойство <b>$this->treegrid = 'adjacency'</b> внутри метода <b>init</b>.<br>
	3. В функции <b>parseRow</b> вручную задать значения колонок <b>level</b>, <b>parent</b>, <b>isLeaf</b>, <b>expanded</b>.<br>
	4. Дополнить SQL-запрос таким образом, чтобы за раз он выбирал только один уровень.<br><br>
	
	Разумеется, вы можете использовать любые другие модели treegrid, а также загружать все дерево сразу целиком.<br>
	Просто модифицируйте вывод таким образом, как вам удобно.
</div>