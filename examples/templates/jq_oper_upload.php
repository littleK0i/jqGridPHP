<script>

var opts = 
{
	'editurl'   : null, //this is required in order dataProxy to take effect
	'dataProxy' : $.jgrid.ext.ajaxFormProxy //our charming dataProxy ^__^
}

<?=$jq_loader->render('jq_oper_upload');?>
$grid.filterToolbar();

</script>
	
<div id="descr">
	This example shows how to completely override the common editing process.<br>
	Data will be updated in two separate tables with signle ajax-request.<br><br>
	
	It also validates the book name in PHP.<br>
	Try to enter very short 'Book name' to see it in action.
	
	Just throw an exception anywhere to stop the script execution and display error to user.
</div>