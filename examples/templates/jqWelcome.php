<script>

var opts = {
	'sortname' : 'id',
	'sortorder': 'desc',
	'height' : 240,
	'width'  : 800,
	
	//'footerrow': true,
	
	'gridComplete' : function()
	{
		$(this).jqGrid('extHighlight');
		//$(this).jqGrid('extFooterAgg');
	}
};

<?=$jq_loader->render('jqWelcome', 'opts');?>
$grid.filterToolbar();

</script>