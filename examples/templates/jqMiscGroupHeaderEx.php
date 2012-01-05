<script>

var options = 
{
	'groupHeaderWR' : 
	{
		'book'     : {'label' : 'Book'},
		'order'    : {'label' : 'Order'},
		'customer' : {'label' : 'Customer'}
	},
	'sortable'	  : true
};

<?=$jq_loader->render('jqMiscGroupHeaderEx', 'options');?>
$grid.jqGrid('updateGroupHeaderWR');

//sortable
$('.ui-sortable').bind('sortstop', function()
{
	$grid.jqGrid('updateGroupHeaderWR');
});

$grid.navButtonAdd(pager, { caption:"Column chooser", buttonicon:"ui-icon-newwin", onClickButton: function()
{
	$grid.jqGrid('columnChooser', {'done' : function(perm)
	{
		//this.jqGrid("remapColumns", perm, true);
		this.jqGrid('updateGroupHeaderWR');
	}});
}}); 
</script>
	
<div id="descr">
	
</div>

<div id="descr_rus">
	Еще один пример группировки.
</div>