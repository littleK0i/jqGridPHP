<script>

//JS functions in JS code - recommended way
var opts = {
	onSelectAll: function()
	{
		alert("All rows selected!");
	}
};

<?=$jq_loader->render('jq_render_1', 'opts');?>
$grid.filterToolbar();

</script>
	
<div id="descr">
	You can set grid options in a numerous ways. Each step is merging with the previous one.<br><br>
	The order is the following:<br><br>
	1. (JS) Altering <b>$.jqgrid.defaults</b><br>
	2. (PHP) Setting property <b>$this->options</b><br>
	3. (PHP) Overloading function <b>renderOptions</b><br>
	4. (JS) Creating object, passed as <b>2nd argument</b> to 'render' method<br><br>
	
	It is highly recommended to use JS ways. This is the most natural approach.<br>
	But if you need to generate some settings dynamically in PHP - you welcome.<br><br>
	
	Please click some rows and the "Select all" checkbox to see JS events are in place.
</div>