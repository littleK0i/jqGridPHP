<script>

var options = 
{
	'groupHeader' : 
	{
		'book'     : {'label' : 'Book'},
		'order'    : {'label' : 'Order'},
		'customer' : {'label' : 'Customer'}
	},
	'sortable'	  : true
};

<?=$jq_loader->render('jqMiscGroupHeaderEx', 'options');?>
$grid.jqGrid('updateGroupHeader');

//sortable
$('.ui-sortable').bind('sortstop', function()
{
	$grid.jqGrid('updateGroupHeader');
});

$grid.navButtonAdd(pager, { caption:"Column chooser", buttonicon:"ui-icon-newwin", onClickButton: function()
{
	$grid.jqGrid('columnChooser', {'done' : function(perm)
	{
		//this.jqGrid("remapColumns", perm, true);
		this.jqGrid('updateGroupHeader');
	}});
}}); 
</script>
	
<div id="descr">
	
</div>

<div id="descr_rus">
	Еще один пример группировки.
</div>