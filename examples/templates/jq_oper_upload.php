<style>
.ui-jqgrid TR.jqgrow IMG
{
	margin: 2px;
}
</style>

<script>
var opts = 
{
	'caption'	: 'File Uploading',
	'editurl'   : null, //this is required in order dataProxy to take effect
	'dataProxy' : $.jgrid.ext.ajaxFormProxy //our charming dataProxy ^__^
}

<?=$jq_loader->render('jq_oper_upload', 'opts');?>
$grid.filterToolbar();

</script>
	
<div id="descr">
	Please upload any image below 2MB.<br>
	Form data and file upload submit in a signle request.<br>
	This approach requires 'jQuery ajaxForm' plugin.
</div>