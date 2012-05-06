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
	Вывод всего дерева целиком.<br>
	Подходит только в том случае, если в дереве небольшое количество узлов.<br><br>
	
	По запросу с PHPClub.ru.
</div>