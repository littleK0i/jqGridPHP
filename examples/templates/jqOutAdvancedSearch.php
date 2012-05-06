<script>
var template1 = {
	'groupOp' : 'AND',
	'rules'   : [
		{'field': 'id', 'op' : 'ge', 'data' : 500},
		{'field': 'customer_name', 'op': 'bw', 'data' : 'Joh'},
		{'field': 'delivery_type', 'op': 'eq', 'data' : '1'}
	]
};

var template2 = {
	'groupOp' : 'AND',
	'rules'   : [
		{'field': 'price', 'op' : 'lt', 'data': '1500'}
	],
	'groups'  : [
		{
			'groupOp': 'OR',
			'rules'  : [
				{'field': 'customer_name', 'op': 'cn', 'data' : 'Clyde'},
				{'field': 'customer_name', 'op': 'cn', 'data' : 'Ernest'}
			]
		}
	]
};
<?= $rendered_grid ?>
</script>

<div id="descr">
	The example of advanced search.
</div>

<div id="descr_rus">
	Продвинутый поиск по произвольной комбинации условий.<br>
	Помимо стандартных операций поиска по колонкам, можно создавать свои особые через параметр <b>search_op</b>.
</div>