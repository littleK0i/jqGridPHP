<?php $rendered_grid = $jq_loader->render('jqExceptionOutput'); ?>

<script>

<?= $rendered_grid ?>
$grid.filterToolbar();

</script>

<div id="descr_rus">
	Пример отображения ошибок при выводе данных.
	Попробуйте поискать по полю <b>first_name</b>.
</div>