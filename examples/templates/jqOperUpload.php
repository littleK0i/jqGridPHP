<script>
var opts = 
{
	'caption'	: 'File Uploading',
	'editurl'   : null, //this is required for dataProxy effect
	'dataProxy' : $.jgrid.ext.ajaxFormProxy //our charming dataProxy ^__^
}

<?= $rendered_grid ?>
$grid.filterToolbar();

</script>
	
<div id="descr">
	Please upload any image below 2MB.<br>
	Form data and file upload submit in a signle request.<br>
	This approach requires 'jQuery ajaxForm' plugin.
</div>

<div id="descr_rus">
	Загрузите изображение размером до 2MB.<br>
	Все данные отправляются на сервер в одном запросе.<br>
	Для отправки используется <b>jQuery Ajax Form</b> плагин.
</div>

<style>
.ui-jqgrid TR.jqgrow IMG
{
	margin: 2px;
}
</style>