<script>
<?= $rendered_grid ?>
$grid.filterToolbar();
</script>
	
<div id="descr">
	To handle a complex query with 'GROUP BY' part or sub-selectes - <b>wrap it</b> into higher level SELECT.<br>
	Database query optimizers easily recognize that trick and produce fine execution plans.<br><br>
	
	You can <b>filter</b> and <b>sort</b> by result of aggregate function or sub-query like common values.<br>
	It just works. No need for special handling at all. 
</div>

<div id="descr_rus">
	Если необходимо использовать сложный SQL-запрос, включающий в себя GROUP BY или под-запросы - <b>оберните</b> его в дополнительный SELECT.<br><br>
	Поиск и сортировка по всем полям будут работать как обычно.
</div>