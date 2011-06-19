<script>

var opts = {
	sortname : 'book_id',
	sortorder: 'asc',
	
	//additional options to render summary row
	'footerrow'    : true,
	'gridComplete' : function()
	{
		$(this).jqGrid('extFooterAgg');
		$(this).jqGrid('extHighlight');
	}
};

<?=$jq_loader->render('jq_cols', 'opts');?>
$grid.filterToolbar();

</script>
	
<div id="descr">
	This exmaple introduce new column options: <b>db</b>, <b>db_agg</b>, <b>manual</b> and <b>unset</b>.<br><br>
	
	<b>db</b>- column representation in database queries.<br>
	<b>db_agg</b> - calculates aggregate function among the whole data set (all pages, not one).<br>
	<b>manual</b> - column is ignored by database. You should fill it manually within <b>parseRow</b> function.<br>
	<b>unset</b> - column is ignored by client script. It is only visible in PHP.<br><br>
</div>